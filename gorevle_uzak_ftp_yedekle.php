<?php 
// Bismillahirrahmanirrahim
if(isset($_POST['ftpye_yukle']) && $_POST['ftpye_yukle'] == '1'){
header('Connection: Keep-Alive');
header('Keep-Alive: timeout=5, max=100');
}
require_once __DIR__ . '/includes/connect.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;


ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); // 7200 saniye 120 dakikadır, 3600 1 saat

if (!function_exists('uzakFTPsunucuyaYedekle')) {
    function uzakFTPsunucuyaYedekle($genel_ayarlar, $ftp_server, $ftp_username, $ftp_password, $ftp_path, $dosya_adi_yolu, $yedekleme_gorevi, $uzak_sunucu_ici_dizin_adi, $ftp_sunucu_korunacak_yedek, $secilen_yedekleme_oneki) {
        
    // FTP Bağlantı türü ve modunu ayarlardan al
    $ftp_mode = $genel_ayarlar['ftp_mode']; // 'active' veya 'passive'
    $ftp_ssl = $genel_ayarlar['ftp_ssl']; // true veya false
        
        $ftpyedeklemebasarili = false;
        $ftp_cikti_mesaji = [];

        // FTP bağlantısı kur
        if ($ftp_ssl) {
            // SSL bağlantısı kur ve oturumu aç
            $ftp_connect = ftp_ssl_connect($ftp_server);
            if (!$ftp_connect) {
                file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - FTP SSL bağlantısı kurulamadı.' . "\n", FILE_APPEND);
                    $ftp_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => 'FTP SSL bağlantısı kurulamadı.'
                    ];
                    return json_encode($ftp_cikti_mesaji);  // Mesajı JSON olarak gönder
            }
        } else {
            // Standart bağlantı kur ve oturumu aç
            $ftp_connect = ftp_connect($ftp_server);
            if (!$ftp_connect) {
                file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - FTP bağlantısı kurulamadı.' . "\n", FILE_APPEND);
                    $ftp_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => 'FTP bağlantısı kurulamadı.'
                    ];
                    return json_encode($ftp_cikti_mesaji);  // Mesajı JSON olarak gönder
            }
        }

        // Zaman aşımını ayarla (örneğin, 120 saniye)
        ftp_set_option($ftp_connect, FTP_TIMEOUT_SEC, 120);

        if ($ftp_connect) {
            // Giriş yapmayı dene
            if (!ftp_login($ftp_connect, $ftp_username, $ftp_password)) {
                // Giriş başarısız
                ftp_close($ftp_connect);
                file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - FTP oturum açma başarısız oldu.' . "\n", FILE_APPEND);
                $ftp_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => 'FTP oturum açma başarısız oldu. Lütfen kullanıcı adı ve şifreyi kontrol edin.'
                ];
                return json_encode($ftp_cikti_mesaji);  // Mesajı JSON olarak gönder
            }

            // Pasif/Aktif mod ayarı
            if ($ftp_mode) {
                ftp_pasv($ftp_connect, true);
            } else {
                ftp_pasv($ftp_connect, false);
            }
        }else{
            ftp_close($ftp_connect);
                file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - FTP oturumu açılamadı.' . "\n", FILE_APPEND);
                    $ftp_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => 'FTP oturumu açılamadı.'
                    ];
                    return json_encode($ftp_cikti_mesaji);  // Mesajı JSON olarak gönder
        }

        // TÜM ALT DİZİNLERİ OLUŞTURMA FONKSİYONU
        if (!function_exists('ftp_mkdir_recursive')) {
            function ftp_mkdir_recursive($ftp_connect, $dir, &$ftp_cikti_mesaji) {
                // Yol ayırıcıları düzenleyelim
                $dir = str_replace('\\', '/', $dir);
                $dir = rtrim($dir, '/'); // Sondaki fazladan /'leri kaldır
                $parts = explode('/', $dir);
                $current_dir = '';
                foreach ($parts as $part) {
                    if (!$part || $part == '.' || $part == '..') continue; // Geçersiz dizin adlarını atla
                    $current_dir .= "/$part";
                    if (!@ftp_chdir($ftp_connect, $current_dir)) {
                        if (!@ftp_mkdir($ftp_connect, $current_dir)) {
                            $ftp_cikti_mesaji[] = [
                                'status' => 'error',
                                'message' => 'Alt-Dizin oluşturulamadı: ' . $current_dir
                            ];
                            return false;
                        }
                    }
                }
                ftp_chdir($ftp_connect, "/"); // Ana dizine geri dön
                return true;
            }
        }

        // DOSYA VEYA DİZİN YÜKLEME FONKSİYONU
    if (!function_exists('upload')) {
        function upload($ftp_connect, $dosya_adi_yolu, $remote_path, &$ftp_cikti_mesaji) {
            // Yol ayırıcıları düzenleyelim
            $dosya_adi_yolu = str_replace('\\', '/', $dosya_adi_yolu);
            $remote_path = str_replace('\\', '/', $remote_path);
            // Klasör kontrolü
            if (is_dir($dosya_adi_yolu)) {
                // Hedef dizini oluştur
                if (!ftp_mkdir_recursive($ftp_connect, $remote_path, $ftp_cikti_mesaji)) {
                    $ftp_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => 'Hedef Dizin oluşturulamadı: ' . $remote_path
                    ];
                    return false;
                }

                // Dizin içeriğini yükle
                $files = scandir($dosya_adi_yolu);
                foreach ($files as $file) {
                    if ($file == '.' || $file == '..') continue;
                    $local_file = "$dosya_adi_yolu/$file";
                    $remote_file = "$remote_path/$file";
                    if (!upload($ftp_connect, $local_file, $remote_file, $ftp_cikti_mesaji)) {
                        return false; // Hata olursa false döner
                    }
                }
            } else {
                // Dosya kontrolü
                if (!is_file($dosya_adi_yolu)) {
                    $ftp_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => 'Geçersiz dosya: ' . $dosya_adi_yolu
                    ];
                    return false;
                }

                // Hedef dizini oluştur
                $remote_dir = dirname($remote_path);
                if (!ftp_mkdir_recursive($ftp_connect, $remote_dir, $ftp_cikti_mesaji)) {
                    $ftp_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => 'Hedef Dizin oluşturulamadı: ' . $remote_dir
                    ];
                    return false;
                }

                // Dosyayı yükle
                if (!ftp_put($ftp_connect, $remote_path, $dosya_adi_yolu, FTP_BINARY)) {
                    $ftp_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => 'Dosya yüklenemedi: ' . $dosya_adi_yolu
                    ];
                    return false;
                }
                GLOBAL $secilen_yedekleme_oneki;
                if($secilen_yedekleme_oneki==='|'){
                    $ftp_cikti_mesaji[] = [
                        'status' => 'success',
                        'message' => 'FTP Sunucusuna Başarıyla Yüklendi: ' . ltrim($remote_path,'/')
                    ];
                }else{
                    $ftp_cikti_mesaji[] = [
                        'status' => 'success',
                        'message' => 'FTP Sunucusuna Başarıyla Yüklendi'
                    ];
                }
            }

            return true;
        }
    }

        // Hedef dizini ayarla
        $ftp_path = rtrim($ftp_path, '/') . '/'; // FTP ana dizini

        if ($uzak_sunucu_ici_dizin_adi) {
            // Eğer uzak sunucuda bir dizin seçildiyse, o dizini kullan
            $ftp_path .= rtrim($uzak_sunucu_ici_dizin_adi, '/') . '/';
        } else {
            // Eğer ana dizin kullanılıyorsa, $ftp_path '/' olacak (tek slash)
            $ftp_path = '/'; // FTP ana dizin
        }

        // Yüklenecek yerel dosya veya dizin yolu
        $remote_path = $ftp_path . basename($dosya_adi_yolu);

        // Yükleme işlemini başlat
        if (upload($ftp_connect, $dosya_adi_yolu, $remote_path, $ftp_cikti_mesaji)) {
            // Yerelden FTP ye elle seçilerek yükleme yapıldığında silme işlemi devre dışı
            if($secilen_yedekleme_oneki!=='|'){
                $ftpyedeklemebasarili = true;
            }
        } else {
            $ftp_cikti_mesaji[] = [
                'status' => 'error',
                'message' => 'FTP Sunucusuna Yükleme BAŞARISIZ'
            ];
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // UZAK FTP BAŞARILI İSE ESKİ YEDEKLERİ SİLMEYE BAŞLA
        if ($ftpyedeklemebasarili && $ftp_sunucu_korunacak_yedek != '-1') {
            // ESKİ YEDEKLERİ SİLME FONKSİYONU
            if (!function_exists('deleteDirectoryRecursive')) {
                function deleteDirectoryRecursive($directory, $ftp_connect) {
                    // Fonksiyonla gelen dosya ise siliyoruz, eğer dizin ise bir aşağı fonksiyona geçiyoruz
                    if (@ftp_delete($ftp_connect, $directory)) {
                        return;
                    }
                    // Burada dizini silmeye çalışıyoruz dizin içi boş değil ise devam ediyoruz ve dizin içindekilerini siliyoruz
                    if (!@ftp_rmdir($ftp_connect, $directory)) {
                        // Dizin içindeki dosyaları listeliyoruz
                        if ($files = @ftp_nlist($ftp_connect, $directory)) {
                            foreach ($files as $file) {
                                // Dizideki . ve .. ile dizinleri gösterenleri parçıyoruz ve dizideki son öğeyi alıyoruz
                                $haric = explode("/", $file);
                                // Satırlarında . ve .. olanları hariç tutuyoruz
                                if (end($haric) != '.' && end($haric) != '..') {
                                    // fonsiyona tekrar gönderip en baştaki ftp_delete() ile dosyaları siliyoruz
                                    deleteDirectoryRecursive($file, $ftp_connect);
                                }
                            }
                        }
                    }
                    // Dosyalar silinip dizin boş kaldığında dizinide siliyoruz
                    @ftp_rmdir($ftp_connect, $directory);
                }
            }

            $file_list = [];
            $sil_uzantilar = [];

            if ($yedekleme_gorevi == 1) {
                $sil_uzantilar = ["sql", "gz"];
            } elseif ($yedekleme_gorevi == 2) {
                $sil_uzantilar = ["zip"];
            }

            if (!empty($uzak_sunucu_ici_dizin_adi) && strlen($uzak_sunucu_ici_dizin_adi) > 2) {
                $uzak_sunucu_ici_dizin_adi = '/' . ltrim(preg_replace('/\/+$/', '', $uzak_sunucu_ici_dizin_adi), '/'); // dizin yolunun başında ve veya sonunda / eğik çizgi varsa kaldır. ve tekrar başına eğik / çizgi ekle
            } else {
                $uzak_sunucu_ici_dizin_adi = "";
            }

            $file_list = ftp_mlsd($ftp_connect, $uzak_sunucu_ici_dizin_adi);

            $ftpdeki_dosyalar = [];
            $ftpdeki_dizinler = [];
            if (is_array($file_list) || is_object($file_list)) {
                foreach ($file_list as $file_list_arr) {
                    // Anahtarları küçük harfe dönüştür
                    $file_list_arr = array_change_key_case($file_list_arr, CASE_LOWER);
                    if (!in_array($file_list_arr['type'], array("pdir", "cdir")) && stripos($file_list_arr['name'], $secilen_yedekleme_oneki) !== false) {
                        if ($file_list_arr['type'] == 'file' && in_array(pathinfo($file_list_arr['name'], PATHINFO_EXTENSION), $sil_uzantilar)) {
                            $ftpdeki_dosyalar[$file_list_arr['modify']][] = $uzak_sunucu_ici_dizin_adi . "/" . $file_list_arr['name'];
                        } elseif ($file_list_arr['type'] == 'dir') {
                            $ftpdeki_dizinler[$file_list_arr['modify']][] = $uzak_sunucu_ici_dizin_adi . "/" . $file_list_arr['name'];
                        }
                    }
                }
            }

            if (isset($ftpdeki_dosyalar) && count($ftpdeki_dosyalar) > 0) {
                krsort($ftpdeki_dosyalar);
                $ftpdeki_dosyalar = call_user_func_array('array_merge', $ftpdeki_dosyalar);
            }
            if (isset($ftpdeki_dizinler) && count($ftpdeki_dizinler) > 0) {
                krsort($ftpdeki_dizinler);
                $ftpdeki_dizinler = call_user_func_array('array_merge', $ftpdeki_dizinler);
            }

            // Dosyadaki tarih doğrumu
            if (!function_exists('validateDate')) {
                function validateDate($date, $format = 'Y-m-d-H-i-s') {
                    $d = DateTime::createFromFormat($format, $date);
                    return $d && $d->format($format) == $date;
                }
            }

            if (count($ftpdeki_dosyalar) > 0) {
                while (count($ftpdeki_dosyalar) > $ftp_sunucu_korunacak_yedek) {
                    $silinendosya = array_pop($ftpdeki_dosyalar);
                    $dosya_tarihi = substr($silinendosya, strpos($silinendosya, $secilen_yedekleme_oneki . "-") + strlen($secilen_yedekleme_oneki . "-"), 19);
                    if (validateDate($dosya_tarihi)) {
                        deleteDirectoryRecursive($silinendosya, $ftp_connect);
                        $ftp_cikti_mesaji[] = [
                            'status' => 'success',
                            'message' => 'FTP Sunucusundaki Eski Dosya(lar) Başarıyla Silindi'
                        ];
                    }
                }
            }

            if (count($ftpdeki_dizinler) > 0) {
                while (count($ftpdeki_dizinler) > $ftp_sunucu_korunacak_yedek) {
                    $silinendizin = array_pop($ftpdeki_dizinler);
                    $dizin_tarihi = substr($silinendizin, -19);
                    if (validateDate($dizin_tarihi)) {
                        deleteDirectoryRecursive($silinendizin, $ftp_connect);
                        $ftp_cikti_mesaji[] = [
                            'status' => 'success',
                            'message' => 'FTP Sunucusundaki Eski Klasör(ler) Başarıyla Silindi'
                        ];
                    }
                }
            }
        }

        // BAĞLANTIYI KAPAT
        ftp_close($ftp_connect);

        return $ftp_cikti_mesaji;
    }
}


if(isset($_POST['ftpye_yukle']) && $_POST['ftpye_yukle'] == '1' && isset($_POST['yerel_den_secilen_dosya']) && !empty($_POST['yerel_den_secilen_dosya']) && isset($_POST['ftp_den_secilen_dosya']) && !empty($_POST['ftp_den_secilen_dosya'])){

ob_start();

    // FTP BAĞLANTI BİLGİLERİ
    $ftp_server     = $genel_ayarlar['sunucu'] ?? ''; //ftp domain name
    $ftp_username   = !empty($genel_ayarlar['username']) ? $hash->take($genel_ayarlar['username']) : ''; //ftp user name 
    $ftp_password   = !empty($genel_ayarlar['password']) ? $hash->take($genel_ayarlar['password']) : ''; //ftp passowrd
    $ftp_path       = $genel_ayarlar['path']; //ftp passowrd

    $dosya_adi_yolu                 = $_POST['yerel_den_secilen_dosya'];
    $uzak_sunucu_ici_dizin_adi      = $_POST['ftp_den_secilen_dosya'];
    $ftp_sunucu_korunacak_yedek     = '-1';
    $secilen_yedekleme_oneki        = "|";
    $yedekleme_gorevi               = "";

try {
    $uzakFTPsunucuyaYedekle = uzakFTPsunucuyaYedekle($genel_ayarlar, $ftp_server, $ftp_username, $ftp_password, $ftp_path, $dosya_adi_yolu, $yedekleme_gorevi, $uzak_sunucu_ici_dizin_adi, $ftp_sunucu_korunacak_yedek, $secilen_yedekleme_oneki);
    //echo "FTP Sunucusuna Başarıyla Yüklendi: ". basename($dosya_adi_yolu);
    echo json_encode($uzakFTPsunucuyaYedekle, JSON_UNESCAPED_UNICODE);
} catch (\Google\Service\Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Hata: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Hata: ' . $e->getMessage()
    ]);
}

ob_flush();
flush();

}

?>