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
        if(is_dir($dir)){
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach($files as $file){
                if ($file->isDir()){
                    rmdir($file->getRealPath());
                }else{
                    unlink($file->getRealPath());
                }
            }
            // Seçilen dizinide silmek içindir. Yukarısı içeriği siler
            if(rmdir($dir) && ($dir != DIZINDIR || $dir != BACKUPDIR))
            { 
                echo json_encode(["li_sil_adi"=>basename($dir),"mesaj"=>"klasör içeriği ile beraber başarıyla silindi: ". basename($dir)]);
            } 
            else
            {
                echo json_encode(["li_sil_adi"=>'none',"mesaj"=>"Klasörü bir hatadan dolayı silinemedi: ". basename($dir)]);
            }
        // Yerelden tek dosya silme alanı
        }else{
            if(unlink($dir))
            {
                echo json_encode(["li_sil_adi"=>basename($dir),"mesaj"=>"Dosya başarıyla silindi: ". basename($dir)]);
            }
            else
            {
                echo json_encode(["li_sil_adi"=>'none',"mesaj"=>"Dosya bir hatadan dolayı silinemedi: ". basename($dir)]);
            }
        }
    }
    delete_directory($_POST['yerel_den_secilen_dosya']);
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['googdan_sil']) && $_POST['googdan_sil'] == 1 && isset($_POST['google_drive_dan_secilen_dosya_id']) && !empty($_POST['google_drive_dan_secilen_dosya_id']))
{

include __DIR__ . '/google_drive_setup.php';

    if($_POST['google_drive_dan_secilen_dosya_id_sil'] == 'root'){
        $results = $service->files->listFiles(array(
            'q' => "'root' in parents"
        ));
        foreach ($results->getFiles() as $file) {
            try {
                $service->files->delete(trim($file->getId()), array('supportsAllDrives' => true));
            } catch (Exception $e) {
                //$hatamesaji = json_decode($e->getMessage(), true)['error']['message'];
                //echo $hatamesaji;
                //exit();
            }
        }
    }else{
        $degistir = [$_POST['google_drive_dan_secilen_dosya_id']=>$_POST['google_drive_dan_secilen_dosya_id_sil']];
        try {
            $service->files->delete(trim($_POST['google_drive_dan_secilen_dosya_id']), array('supportsAllDrives' => true));
        } catch (Exception $e) {
            $hatamesaji = json_decode($e->getMessage(), true)['error']['message'];
            //echo str_replace(key($degistir), current($degistir), $hatamesaji);
            //print "Bir hata oluştu: " . $e->getMessage();
            //exit();
        }
    }
    echo json_encode(["li_sil_adi"=>$_POST['google_drive_dan_secilen_dosya_id_sil'],"mesaj"=>"Dosya başarıyla silindi: ". $_POST['google_drive_dan_secilen_dosya_id_sil']]);

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

// Genel hata mesajı değişkeni
$error_message = "";

try {
    // FTP bağlantısı kur
    if ($ftp_ssl) {
        // SSL bağlantısı kur ve oturumu aç
        $ftp_connect = ftp_ssl_connect($ftp_server);
        if (!$ftp_connect) {
            throw new Exception("FTP SSL bağlantısı kurulamadı.");
        }
    } else {
        // Standart bağlantı kur ve oturumu aç
        $ftp_connect = ftp_connect($ftp_server);
        if (!$ftp_connect) {
            throw new Exception("FTP Standart bağlantısı kurulamadı.");
        }
    }

    // Zaman aşımını ayarla (örneğin, 120 saniye)
    if (!ftp_set_option($ftp_connect, FTP_TIMEOUT_SEC, 120)) {
        throw new Exception("FTP zaman aşımı ayarlanamadı.");
    }

    // Kullanıcı adı ve parola ile giriş yap
    if (!ftp_login($ftp_connect, $ftp_username, $ftp_password)) {
        throw new Exception("FTP oturumu açılırken kullanıcı adı veya parola hatalı.");
    }

    // Pasif/Aktif mod ayarı
    if ($ftp_mode === '1') {
        if (!ftp_pasv($ftp_connect, true)) {
            throw new Exception("FTP pasif mod ayarlanamadı.");
        }
    } else {
        if (!ftp_pasv($ftp_connect, false)) {
            throw new Exception("FTP aktif mod ayarlanamadı.");
        }
    }

} catch (Exception $e) {
    // Hata oluştuğunda mesajı döndür
    $error_message = $e->getMessage();
    echo json_encode(["li_sil_adi" => 'none', "mesaj" => $error_message]);
}

// Dizin içeriğini boşaltmak için yardımcı fonksiyon
function emptyDirectory($directory, $ftp_connect) {
    if ($files = @ftp_nlist($ftp_connect, $directory)) {
        foreach ($files as $file) {
            $haric = explode("/", $file);
            if (end($haric) != '.' && end($haric) != '..') {
                if (@ftp_delete($ftp_connect, $file)) {
                    continue;
                } else {
                    deleteDirectoryRecursive($file, $ftp_connect);
                    @ftp_rmdir($ftp_connect, $file);
                }
            }
        }
    }
}

// Ana fonksiyon
function deleteDirectoryRecursive($directory, $ftp_connect) {
    GLOBAL $ftp_path, $ftp_hesabindaki_dizini_bosalt;

    // Fonksiyonla gelen dosya ise siliyoruz, eğer dizin ise bir aşağıya geçiyoruz
    if (@ftp_delete($ftp_connect, $directory)) {
        return;
        //echo "Silindi: ".$directory."<br>";
    }
    // Dizin içindeki dosyaları listeliyoruz
    if ($files = @ftp_nlist($ftp_connect, $directory)) {
        foreach ($files as $file) {
            $haric = explode("/", $file);
            if (end($haric) != '.' && end($haric) != '..') {
                // Fonksiyonla gelen dosya ise siliyoruz, eğer dizin ise bir aşağı fonksiyona geçiyoruz
                if (@ftp_delete($ftp_connect, $file)) {
                    // Dosya silindi
                    continue;
                } else {
                    // Dizin içeriğini silmek için tekrar fonksiyonu çağır
                    deleteDirectoryRecursive($file, $ftp_connect);

                    // FTP Hesabında DİZİN belirlendi ise AND
                    // basename($ftp_path) son dizin adi ile === basename($file) eşit ise AND
                    // $ftp_hesabindaki_dizini_bosalt true ise FTP hesabındaki dizin içeriği boşaltılıyor demektir ve dizini silmeyi engelle
                    if ($ftp_path !== '/' && basename($ftp_path) === basename($file) && $ftp_hesabindaki_dizini_bosalt) {
                        // FTP Hesabındaki son dizin silinmesini önlüyoruz
                    } else {
                        @ftp_rmdir($ftp_connect, $file);
                    }
                }
            }
        }

        // Ana dizin içeriği temizlendikten sonra eğer ana dizin $ftp_path değilse onu da sil
        if ($directory !== $ftp_path) {
            @ftp_rmdir($ftp_connect, rtrim($directory,'/'));
        }
    }
}

    // Dizin içeriğini silme işlemini çağır
try {
    deleteDirectoryRecursive($ftp_den_silinecek_kaynak, $ftp_connect);

    if($ftp_den_secileni_sil == '/'){
        echo json_encode(["li_sil_adi"=>basename($ftp_den_silinecek_kaynak),"mesaj"=>"Tüm içerikler başarıyla silindi"]);
    }else{
        echo json_encode(["li_sil_adi"=>basename($ftp_den_silinecek_kaynak),"mesaj"=>"Dosya başarıyla silindi: ". basename($ftp_den_silinecek_kaynak)]);
    }

} catch (Exception $e) {
    echo json_encode(["li_sil_adi"=>'none',"mesaj"=>"Dosya bir hatadan dolayı silinemedi: ". $e->getMessage()]);
}
    // FTP bağlantısını kapat
    ftp_close($ftp_connect);
}

ob_flush();
flush();

?>