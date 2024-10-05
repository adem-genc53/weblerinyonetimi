<?php 
// Bismillahirrahmanirrahim
header('Connection: Keep-Alive');
header('Keep-Alive: timeout=5, max=100');
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;

ob_start();
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); //7200 saniye 120 dakikadır, 3600 1 saat

// Yerel alandan seçilen dizin ve dosya silme kodu
if(isset($_POST['yerelden_sil']) && $_POST['yerelden_sil'] == 1 && isset($_POST['yerel_den_secilen_dosya']) && !empty($_POST['yerel_den_secilen_dosya']))
{
    function delete_directory($dir){
    $sonuc_cikti_mesaji = [];

        if(!file_exists($dir)){
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">Dosya bulunamadı: </span>' . $dir
            ];
        return $sonuc_cikti_mesaji;
        }
    // Silinecek dosya klasör ise
        if(is_dir($dir)){
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach($files as $file){
                if ($file->isDir()){
                    /*
                    $sonuc_cikti_mesaji[] = [
                        'status' => 'success',
                        'message' => '<span style="color:green;">Klasör başarıyla silindi: </span> ' . $file->getRealPath()
                    ];
                    */
                    rmdir($file->getRealPath());
                }else{
                    $sonuc_cikti_mesaji[] = [
                        'status' => 'success',
                        'message' => '<span style="color:green;">Dosya başarıyla silindi: </span> ' . $file->getRealPath()
                    ];
                    unlink($file->getRealPath());
                }
            }
            // Seçilen dizinide silmek içindir. Yukarısı içeriği siler
            if(rmdir($dir) && ($dir != DIZINDIR || $dir != BACKUPDIR))
            { 
                $sonuc_cikti_mesaji[] = [
                    'status' => 'success',
                    'message' => '<span style="color:green;">Dosya başarıyla silindi: </span> ' . $dir
                ];
            } 
            else
            {
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:red;">Dosya silinemedi: </span>' . $dir
                ];
            }
        // Yerelden tek dosya silme alanı
        }else{
            if(unlink($dir))
            {
                $sonuc_cikti_mesaji[] = [
                    'status' => 'success',
                    'message' => '<span style="color:green;">Dosya başarıyla silindi: </span> ' . $dir
                ];
            }
            else
            {
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:red;">Dosya silinemedi:: </span>' . $dir
                ];
            }
        }
        return $sonuc_cikti_mesaji;
    }
    $sonuc_cikti_mesaji = delete_directory($_POST['yerel_den_secilen_dosya']);
    echo json_encode($sonuc_cikti_mesaji, JSON_UNESCAPED_UNICODE);  // Mesajı JSON olarak gönder
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['googdan_sil']) && $_POST['googdan_sil'] == 1 && isset($_POST['google_drive_dan_secilen_dosya_id']) && !empty($_POST['google_drive_dan_secilen_dosya_id']))
{

include __DIR__ . '/google_drive_setup.php';
$sonuc_cikti_mesaji = [];

    if($_POST['google_drive_dan_secilen_dosya_id_sil'] == 'root'){
        $results = $service->files->listFiles(array(
            'q' => "'root' in parents"
        ));
        foreach ($results->getFiles() as $file) {
            try {
                $service->files->delete(trim($file->getId()), array('supportsAllDrives' => true));
            } catch (Exception $e) {
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:red;">Hata: </span>' . $e->getMessage()
                ];
            }
        }
    }else{
        $degistir = [$_POST['google_drive_dan_secilen_dosya_id']=>$_POST['google_drive_dan_secilen_dosya_id_sil']];
        try {
            $service->files->delete(trim($_POST['google_drive_dan_secilen_dosya_id']), array('supportsAllDrives' => true));
        } catch (Exception $e) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">Hata: </span>' . $e->getMessage()
            ];
        }
    }
        $sonuc_cikti_mesaji[] = [
            'status' => 'success',
            'message' => '<span style="color:green;">Dosya başarıyla silindi: </span> ' . $_POST['google_drive_dan_secilen_dosya_id_sil']
        ];
    echo json_encode($sonuc_cikti_mesaji, JSON_UNESCAPED_UNICODE);  // Mesajı JSON olarak gönder

}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_POST['ftpden_sil']) && $_POST['ftpden_sil'] == 1 && isset($_POST['ftp_den_secileni_sil']) && !empty($_POST['ftp_den_secileni_sil'])) {
    
    $ftp_server = $genel_ayarlar['sunucu'] ?? '';
    $ftp_username = !empty($genel_ayarlar['username']) ? $hash->take($genel_ayarlar['username']) : '';
    $ftp_password = !empty($genel_ayarlar['password']) ? $hash->take($genel_ayarlar['password']) : '';
    $ftp_path = $genel_ayarlar['path'];

    // Başlangıçta değişkeni boş olarak tanımla
    $ftp_den_silinecek_kaynak = '';
    $ftp_hesabindaki_dizini_bosalt = false;

    $ftp_den_secileni_sil = $_POST['ftp_den_secileni_sil'];

    // FTP Hesap bilgileri ile DİZİN belirlenmedi ise ve / eğik çizgi varsa
    if ($ftp_path === '/') {

        $ftp_den_silinecek_kaynak .= "/"; // eğik / çizgiyi tekrar ekle

    // FTP hesabında DİZİN var ve Ağaçtan Ev seçildi ise / eğik çizgi geliyor
    }elseif($ftp_path != '/' && $ftp_den_secileni_sil == '/'){

        // FTP hesabında DİZİN var ve AĞAÇ tan Ev seçilerek / eğik çizgi geldi
        // Bu durumda FTP hesabındaki dizini boşaltacağız
        $ftp_den_silinecek_kaynak .= "/".trim($ftp_path, '/');
        // Bu değişken true gönderek FTP hesab dizini silmesini engelleyecek
        $ftp_hesabindaki_dizini_bosalt = true;

    // FTP hesabında DİZİN var ve Ağaçtan DOSYA veya DİZİN seçildiz ise
    }elseif($ftp_path !== '/' && $ftp_den_secileni_sil !== '/'){

        // Ağaçta klasör veya dosya seçildi ise seçileni sil
        $ftp_den_silinecek_kaynak .= "/".trim($ftp_path, '/');

    }

    // Ağaçtan Ev seçili DEĞİL İSE
    if ($ftp_den_secileni_sil !== '/') {

        // Ağaçtan seçilen dosya veya klasörü sil
        $ftp_den_silinecek_kaynak .= $ftp_den_secileni_sil;

    }

    $ftp_den_silinecek_kaynak = str_replace(['//','\\\\'],'/', $ftp_den_silinecek_kaynak);

// FTP Bağlantı türü ve modunu ayarlardan al
$ftp_mode = $genel_ayarlar['ftp_mode']; // 'active' veya 'passive'
$ftp_ssl = $genel_ayarlar['ftp_ssl']; // true veya false

$sonuc_cikti_mesaji = []; // FTP işlem mesajlarını toplamak için global dizi

    // FTP bağlantısı kur
    if ($ftp_ssl) {
        // SSL bağlantısı kur ve oturumu aç
        $ftp_connect = ftp_ssl_connect($ftp_server);
        if (!$ftp_connect) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">FTP SSL bağlantısı kurulamadı.</span>'
            ];
        return json_encode($sonuc_cikti_mesaji, JSON_UNESCAPED_UNICODE);  // Mesajı JSON olarak gönder
        }
    } else {
        // Standart bağlantı kur ve oturumu aç
        $ftp_connect = ftp_connect($ftp_server);
        if (!$ftp_connect) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">FTP Standart bağlantısı kurulamadı.</span>'
            ];
        return json_encode($sonuc_cikti_mesaji, JSON_UNESCAPED_UNICODE);  // Mesajı JSON olarak gönder
        }
    }

    // Zaman aşımını ayarla (örneğin, 120 saniye)
    ftp_set_option($ftp_connect, FTP_TIMEOUT_SEC, 600);

    if ($ftp_connect) {
        ftp_login($ftp_connect, $ftp_username, $ftp_password);

        // Pasif/Aktif mod ayarı
        if ($ftp_mode) {
            ftp_pasv($ftp_connect, true);
        } else {
            ftp_pasv($ftp_connect, false);
        }
    }else{
        ftp_close($ftp_connect);
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">FTP oturumu açılamadı.</span>'
            ];
        return json_encode($sonuc_cikti_mesaji, JSON_UNESCAPED_UNICODE);  // Mesajı JSON olarak gönder
    }

    //$rawList = ftp_mlsd($ftp_connect, '/');

    //file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($rawList, true) . '</pre>' . "\n", FILE_APPEND);

/**
 * FTP sunucusundan belirtilen dosya veya klasörü siler.
 * Eğer klasörse, içindeki tüm dosyalar ve alt klasörler de silinir.
 *
 * @param resource $ftp_connect FTP bağlantı kaynağı
 * @param string $path Silinecek dosya veya klasörün yolu
 * @return array İşlem sonuç mesajlarını içeren dizi döner
 */
function ftpDelete($ftp_connect, $path)
{
    global $sonuc_cikti_mesaji; // Global mesaj dizisini kullan
    // Sonunda eğik çizgi varsa kaldır
    $path = rtrim($path, '/');

    // Dosya mı yoksa klasör mü kontrolü yap
    if (isFtpDirectory($ftp_connect, $path)) {
        deleteDirectoryRecursively($ftp_connect, $path);
    } else {
        deleteFile($ftp_connect, $path);
    }

    return $sonuc_cikti_mesaji; // Tüm işlem sonuçlarını döndür
}

/**
 * Belirtilen dosyayı FTP sunucusundan siler.
 *
 * @param resource $ftp_connect FTP bağlantı kaynağı
 * @param string $filePath Silinecek dosyanın yolu
 * @return void
 */
function deleteFile($ftp_connect, $filePath)
{
    global $sonuc_cikti_mesaji;

    if (ftp_size($ftp_connect, $filePath) == -1) { // Dosya mevcut mu kontrol et
        $sonuc_cikti_mesaji[] = [
            'status' => 'error',
            'message' => '<span style="color:red;">Dosya bulunamadı: </span> ' . $filePath
        ];
        return;
    }

    if (ftp_delete($ftp_connect, $filePath)) {
        $sonuc_cikti_mesaji[] = [
            'status' => 'success',
            'message' => '<span style="color:green;">Dosya başarıyla silindi:</span> ' . $filePath
        ];
    } else {
        /*
        $sonuc_cikti_mesaji[] = [
            'status' => 'error',
            'message' => '<span style="color:red;">Dosya silinemedi:</span> ' . $filePath
        ];
        */
    }
}

/**
 * FTP sunucusundaki klasörü ve içindeki tüm dosyaları/alt klasörleri siler.
 *
 * @param resource $ftp_connect FTP bağlantı kaynağı
 * @param string $dir Silinecek klasörün yolu
 * @return void
 */
function deleteDirectoryRecursively($ftp_connect, $dir)
{
    global $sonuc_cikti_mesaji;

    // Dizindeki dosya ve klasörlerin tam yolunu al
    $fileList = ftp_mlsd_custom($ftp_connect, $dir);

    if ($fileList === false) {
        $sonuc_cikti_mesaji[] = [
            'status' => 'error',
            'message' => '<span style="color:red;">Dizin listesi alınamadı veya dizin bulunamadı:</span> ' . $dir
        ];
        return;
    }

    foreach ($fileList as $file) {
        $fileName = $file['name'];

        // Nokta ve çift nokta dizinlerini yok say ('.', '..')
        if ($fileName == '.' || $fileName == '..') {
            continue;
        }

        $filePath = "$dir/$fileName"; // Tam dosya yolunu oluştur

        // Dosya mı klasör mü olduğunu kontrol et ve uygun işlemi yap
        if (isFtpDirectory($ftp_connect, $filePath)) {
            deleteDirectoryRecursively($ftp_connect, $filePath); // Alt klasörü sil
            if (@ftp_rmdir($ftp_connect, $filePath)) {
                $sonuc_cikti_mesaji[] = [
                    'status' => 'success',
                    'message' => '<span style="color:green;">Klasör başarıyla silindi:</span> ' . $filePath
                ];
            } elseif (!isFtpDirectory($ftp_connect, $filePath)) {
/*
                $sonuc_cikti_mesaji[] = [
                    'status' => 'info',
                    'message' => 'Klasör zaten silinmiş: ' . $filePath
                ];
*/
            } else {
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:red;">Klasör silinemedi:</span> ' . $filePath
                ];
            }
        } else {
            deleteFile($ftp_connect, $filePath); // Dosyayı sil
        }
    }

    // Tüm içeriği temizlenen ana klasörü sil
    if (@ftp_rmdir($ftp_connect, $dir)) {
        $sonuc_cikti_mesaji[] = [
            'status' => 'success',
            'message' => '<span style="color:green;">Klasör başarıyla silindi:</span> ' . $dir
        ];
    } else {
        $sonuc_cikti_mesaji[] = [
            'status' => 'error',
            'message' => '<span style="color:red;">Klasör silinemedi:</span> ' . $dir
        ];
    }
}

/**
 * Verilen yolun FTP sunucusunda bir dizin olup olmadığını kontrol eder.
 *
 * @param resource $ftp_connect FTP bağlantı kaynağı
 * @param string $path Kontrol edilecek yol
 * @return bool Eğer bir dizinse true, değilse false döner
 */
function isFtpDirectory($ftp_connect, $path)
{
    // Mevcut dizini saklayarak bir klasör olup olmadığını kontrol ediyoruz
    $originalDirectory = ftp_pwd($ftp_connect);

    // Eğer klasöre geçiş yapılabiliyorsa, bu bir dizindir
    if (@ftp_chdir($ftp_connect, $path)) {
        ftp_chdir($ftp_connect, $originalDirectory); // Eski dizine geri dön
        return true;
    } else {
        return false;
    }
}

/**
 * Belirtilen dizindeki tüm dosya ve klasörlerin tam yolunu döner.
 * ftp_nlist yerine ftp_rawlist kullanılarak daha güvenilir bir listeleme yapılır.
 *
 * @param resource $ftp_connect FTP bağlantı kaynağı
 * @param string $dir Dizin yolu
 * @return array Dizin içindeki dosyaların ve klasörlerin tam yolları
 */
function ftp_mlsd_custom($ftp_connect, $dir)
{
    $rawList = @ftp_rawlist($ftp_connect, $dir);

    if ($rawList === false) {
        return false;
    }

    $items = [];

    foreach ($rawList as $entry) {
        $chunks = preg_split("/\s+/", $entry, 9);

        if ($chunks[0][0] === 'd') {
            $type = 'directory';
        } else {
            $type = 'file';
        }

        $items[] = [
            'name' => $chunks[8],
            'type' => $type
        ];
    }

    return $items;
}

// Dizin içeriğini silme işlemini çağır
try {
    $sonuc_cikti_mesaji = ftpDelete($ftp_connect, $ftp_den_silinecek_kaynak);
    // file_put_contents(KOKYOLU.'error2.log', date('Y-m-d H:i:s') . '<pre>' . print_r($sonuc_cikti_mesaji, true) . '</pre>' . "\n", FILE_APPEND);
    echo json_encode($sonuc_cikti_mesaji, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $sonuc_cikti_mesaji = [
        'status' => 'error',
        'message' => '<span style="color:red;">Hata:</span> ' . $e->getMessage()
    ];
    echo json_encode($sonuc_cikti_mesaji, JSON_UNESCAPED_UNICODE);  // Mesajı JSON olarak gönder
}

    // FTP bağlantısını kapat
    ftp_close($ftp_connect);
}

ob_flush();
flush();

?>