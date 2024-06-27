<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
include __DIR__ . '/google_drive_setup.php';
require_once('check-login.php');
require_once("includes/turkcegunler.php");

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
    
    $ftp_host = $genel_ayarlar['sunucu'];
    $ftp_user = $hash->take($genel_ayarlar['username']);
    $ftp_pass = $hash->take($genel_ayarlar['password']);
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

    // FTP bağlantısını kur
    $ftp = @ftp_ssl_connect($ftp_host) or die($ftp_host . " sunucuya bağlanamadı");
    $ftp_login = ftp_login($ftp, $ftp_user, $ftp_pass);
    ftp_pasv($ftp, true);

    if (!$ftp || !$ftp_login) {
        die("FTP Bağlantısı Başarısız");
    }

// Dizin içeriğini boşaltmak için yardımcı fonksiyon
function emptyDirectory($directory, $ftp) {
    if ($files = @ftp_nlist($ftp, $directory)) {
        foreach ($files as $file) {
            $haric = explode("/", $file);
            if (end($haric) != '.' && end($haric) != '..') {
                if (@ftp_delete($ftp, $file)) {
                    continue;
                } else {
                    deleteDirectoryRecursive($file, $ftp);
                    @ftp_rmdir($ftp, $file);
                }
            }
        }
    }
}

// Ana fonksiyon
function deleteDirectoryRecursive($directory, $ftp) {
    GLOBAL $ftp_path, $ftp_hesabindaki_dizini_bosalt;

    // Fonksiyonla gelen dosya ise siliyoruz, eğer dizin ise bir aşağıya geçiyoruz
    if (@ftp_delete($ftp, $directory)) {
        return;
        //echo "Silindi: ".$directory."<br>";
    }
    // Dizin içindeki dosyaları listeliyoruz
    if ($files = @ftp_nlist($ftp, $directory)) {
        foreach ($files as $file) {
            $haric = explode("/", $file);
            if (end($haric) != '.' && end($haric) != '..') {
                // Fonksiyonla gelen dosya ise siliyoruz, eğer dizin ise bir aşağı fonksiyona geçiyoruz
                if (@ftp_delete($ftp, $file)) {
                    // Dosya silindi
                    continue;
                } else {
                    // Dizin içeriğini silmek için tekrar fonksiyonu çağır
                    deleteDirectoryRecursive($file, $ftp);

                    // FTP Hesabında DİZİN belirlendi ise AND
                    // basename($ftp_path) son dizin adi ile === basename($file) eşit ise AND
                    // $ftp_hesabindaki_dizini_bosalt true ise FTP hesabındaki dizin içeriği boşaltılıyor demektir ve dizini silmeyi engelle
                    if ($ftp_path !== '/' && basename($ftp_path) === basename($file) && $ftp_hesabindaki_dizini_bosalt) {
                        // FTP Hesabındaki son dizin silinmesini önlüyoruz
                    } else {
                        @ftp_rmdir($ftp, $file);
                    }
                }
            }
        }

        // Ana dizin içeriği temizlendikten sonra eğer ana dizin $ftp_path değilse onu da sil
        if ($directory !== $ftp_path) {
            @ftp_rmdir($ftp, rtrim($directory,'/'));
        }
    }
}

    // Dizin içeriğini silme işlemini çağır
try {
    deleteDirectoryRecursive($ftp_den_silinecek_kaynak, $ftp);

    if($ftp_den_secileni_sil == '/'){
        echo json_encode(["li_sil_adi"=>basename($ftp_den_silinecek_kaynak),"mesaj"=>"Tüm içerikler başarıyla silindi"]);
    }else{
        echo json_encode(["li_sil_adi"=>basename($ftp_den_silinecek_kaynak),"mesaj"=>"Dosya başarıyla silindi: ". basename($ftp_den_silinecek_kaynak)]);
    }
    
} catch (Exception $e) {
                //die($e->getMessage());
    echo json_encode(["li_sil_adi"=>'none',"mesaj"=>"Dosya bir hatadan dolayı silinemedi: ". $e->getMessage()]);
}

    // FTP bağlantısını kapat
    ftp_close($ftp);
}

?>