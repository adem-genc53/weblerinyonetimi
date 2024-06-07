<?php 
// Bismillahirrahmanirrahim

if (!function_exists('uzakFTPsunucuyaYedekle')) {
    function uzakFTPsunucuyaYedekle($ftp_server, $ftp_username, $ftp_password, $ftp_path, $dosya_adi_yolu, $yedekleme_gorevi, $uzak_sunucu_ici_dizin_adi, $ftp_sunucu_korunacak_yedek, $secilen_yedekleme_oneki) {
        $ftpyedeklemebasarili = false;
        $ftp_cikti_mesaji = [];

        // BAĞLANTI KUR VE OTURUMU AÇ
        $ftp = ftp_ssl_connect($ftp_server);
        if (!$ftp) {
            die("FTP bağlantısı kurulamadı.");
        }

        $login = ftp_login($ftp, $ftp_username, $ftp_password);
        if (!$login) {
            ftp_close($ftp);
            die("FTP oturumu açılamadı.");
        }

        // PASİF MODA GEÇ
        ftp_pasv($ftp, true);

        // TÜM ALT DİZİNLERİ OLUŞTURMA FONKSİYONU
        if (!function_exists('ftp_mkdir_recursive')) {
            function ftp_mkdir_recursive($ftp, $dir) {
                global $ftp_cikti_mesaji; // Mesajları global olarak tut
                $parts = explode('/', $dir);
                $current_dir = '';
                foreach ($parts as $part) {
                    if (!$part) continue;
                    $current_dir .= "/$part";
                    if (!@ftp_chdir($ftp, $current_dir)) {
                        if (!ftp_mkdir($ftp, $current_dir)) {
                            $ftp_cikti_mesaji[] = "Alt-Dizin oluşturulamadı: $current_dir";
                            return false;
                        }
                    }
                }
                ftp_chdir($ftp, "/"); // ANA DİZİNE GERİ DÖN
                return true;
            }
        }

        // DOSYA VEYA DİZİN YÜKLEME FONKSİYONU
        if (!function_exists('upload')) {
            function upload($ftp, $dosya_adi_yolu, $remote_path) {
                global $ftp_cikti_mesaji; // Mesajları global olarak tut
                if (is_dir($dosya_adi_yolu)) {
                    // HEDEF DİZİNİ OLUŞTUR
                    if (!ftp_mkdir_recursive($ftp, $remote_path)) {
                        $ftp_cikti_mesaji[] = "Hedef Dizin oluşturulamadı: $remote_path";
                        return false;
                    }

                    // DİZİN İÇERİĞİNİ YÜKLE
                    $files = scandir($dosya_adi_yolu);
                    foreach ($files as $file) {
                        if ($file == '.' || $file == '..') continue;
                        $local_file = "$dosya_adi_yolu/$file";
                        $remote_file = "$remote_path/$file";
                        if (!upload($ftp, $local_file, $remote_file)) {
                            return false; // HATA OLURSA false DÖNER
                        }
                    }
                } else {
                    // HEDEF DİZİNİ OLUŞTUR
                    $remote_dir = dirname($remote_path);
                    if (!ftp_mkdir_recursive($ftp, $remote_dir)) {
                        $ftp_cikti_mesaji[] = "Hedef Dizin oluşturulamadı: $remote_dir";
                        return false;
                    }

                    // DOSYA İSE, DOSYAYI YÜKLE
                    if (!is_file($dosya_adi_yolu)) {
                        $ftp_cikti_mesaji[] = "Geçersiz dosya: $dosya_adi_yolu";
                        return false;
                    }
                    if (!ftp_put($ftp, $remote_path, $dosya_adi_yolu, FTP_BINARY)) {
                        $ftp_cikti_mesaji[] = "Dosya yüklenemedi: $dosya_adi_yolu";
                        return false;
                    }
                    $ftp_cikti_mesaji[] = "Dosya yüklendi: $dosya_adi_yolu -> $remote_path";
                }
                return true;
            }
        }

        // HEDEF DİZİNİ AYARLA
        $ftp_path = rtrim($ftp_path, '/') . '/';
        if ($uzak_sunucu_ici_dizin_adi) {
            $ftp_path .= rtrim($uzak_sunucu_ici_dizin_adi, '/') . '/';
        }

        // YÜKLENECEK YEREL DOSYA VEYA DİZİN YOLU
        $remote_path = $ftp_path . basename($dosya_adi_yolu);

        // YÜKLEME İŞLEMİNİ BAŞLAT
        if (upload($ftp, $dosya_adi_yolu, $remote_path)) {
            $ftp_cikti_mesaji[] = "FTP Sunucusuna Başarıyla Yüklendi";
            $ftpyedeklemebasarili = true;
        } else {
            $ftp_cikti_mesaji[] = "FTP Sunucusuna Yükleme BAŞARISIZ";
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // UZAK FTP BAŞARILI İSE ESKİ YEDEKLERİ SİLMEYE BAŞLA
        if ($ftpyedeklemebasarili && $ftp_sunucu_korunacak_yedek != '-1') {
            // ESKİ YEDEKLERİ SİLME FONKSİYONU
            if (!function_exists('deleteDirectoryRecursive')) {
                function deleteDirectoryRecursive($directory, $ftp) {
                    // Fonksiyonla gelen dosya ise siliyoruz, eğer dizin ise bir aşağı fonksiyona geçiyoruz
                    if (@ftp_delete($ftp, $directory)) {
                        return;
                    }
                    // Burada dizini silmeye çalışıyoruz dizin içi boş değil ise devam ediyoruz ve dizin içindekilerini siliyoruz
                    if (!@ftp_rmdir($ftp, $directory)) {
                        // Dizin içindeki dosyaları listeliyoruz
                        if ($files = @ftp_nlist($ftp, $directory)) {
                            foreach ($files as $file) {
                                // Dizideki . ve .. ile dizinleri gösterenleri parçıyoruz ve dizideki son öğeyi alıyoruz
                                $haric = explode("/", $file);
                                // Satırlarında . ve .. olanları hariç tutuyoruz
                                if (end($haric) != '.' && end($haric) != '..') {
                                    // fonsiyona tekrar gönderip en baştaki ftp_delete() ile dosyaları siliyoruz
                                    deleteDirectoryRecursive($file, $ftp);
                                }
                            }
                        }
                    }
                    // Dosyalar silinip dizin boş kaldığında dizinide siliyoruz
                    @ftp_rmdir($ftp, $directory);
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

            $file_list = ftp_mlsd($ftp, $uzak_sunucu_ici_dizin_adi);

            $ftpdeki_dosyalar = [];
            $ftpdeki_dizinler = [];
            if (is_array($file_list) || is_object($file_list)) {
                foreach ($file_list as $file_list_arr) {
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
                        deleteDirectoryRecursive($silinendosya, $ftp);
                        $ftp_cikti_mesaji[] = "FTP Sunucusundaki Eski Dosya(lar) Başarıyla Silindi";
                    }
                }
            }

            if (count($ftpdeki_dizinler) > 0) {
                while (count($ftpdeki_dizinler) > $ftp_sunucu_korunacak_yedek) {
                    $silinendizin = array_pop($ftpdeki_dizinler);
                    $dizin_tarihi = substr($silinendizin, -19);
                    if (validateDate($dizin_tarihi)) {
                        deleteDirectoryRecursive($silinendizin, $ftp);
                        $ftp_cikti_mesaji[] = "FTP Sunucusundaki Eski Klasör(ler) Başarıyla Silindi";
                    }
                }
            }
        }

        // BAĞLANTIYI KAPAT
        ftp_close($ftp);

        return $ftp_cikti_mesaji;
    }
}

if(isset($_POST['ftpye_yukle']) && $_POST['ftpye_yukle'] == '1' && isset($_POST['yerel_den_secilen_dosya']) && !empty($_POST['yerel_den_secilen_dosya']) && isset($_POST['ftp_den_secilen_dosya']) && !empty($_POST['ftp_den_secilen_dosya']))
{
require_once __DIR__ . '/includes/connect.php';
    // FTP BAĞLANTI BİLGİLERİ
    $ftp_server     = $genel_ayarlar['sunucu']; //ftp domain name
    $ftp_username   = $genel_ayarlar['username']; //ftp user name 
    $ftp_password   = $genel_ayarlar['password']; //ftp passowrd
    $ftp_path       = $genel_ayarlar['path']; //ftp passowrd

    $dosya_adi_yolu                 = $_POST['yerel_den_secilen_dosya'];
    $uzak_sunucu_ici_dizin_adi      = $_POST['ftp_den_secilen_dosya'];
    $ftp_sunucu_korunacak_yedek     = '-1';
    $secilen_yedekleme_oneki        = "";
    $yedekleme_gorevi               = "";

try {
    uzakFTPsunucuyaYedekle($ftp_server, $ftp_username, $ftp_password, $ftp_path, $dosya_adi_yolu, $yedekleme_gorevi, $uzak_sunucu_ici_dizin_adi, $ftp_sunucu_korunacak_yedek, $secilen_yedekleme_oneki);
    echo "FTP Sunucusuna Başarıyla Yüklendi: ". basename($dosya_adi_yolu);
} catch (\Google\Service\Exception $e) {
    echo "Hata: " . $e->getMessage();
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
}
?>