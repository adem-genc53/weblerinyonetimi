<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");

ob_start();
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); //7200 saniye 120 dakikadır, 3600 1 saat

if (!(PHP_VERSION_ID >= 80100)) {
    exit("<div style='font-weight: bold;font-size: 16px;text-align:center;font-family: Arial, Helvetica, sans-serif;'>Google Drive Kütüphanesi En Düşük \">= 8.1.0\" PHP sürümünü gerektirir. Siz " . PHP_VERSION . " Çalıştırıyorsunuz.</div>");
}

if (!file_exists(AUTHCONFIGPATH)) {
    die('Hata: AuthConfig dosyası bulunamadı.');
}

require_once __DIR__.'/plugins/google_drive/vendor/autoload.php';

$client = new Google\Client();
$client->setAuthConfig(AUTHCONFIGPATH);
$client->addScope(Google\Service\Drive::DRIVE);
$service = new Google\Service\Drive($client);

// Yerel alandan seçilen dizin ve
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
if(isset($_POST['ftpden_sil']) && $_POST['ftpden_sil'] == 1 && isset($_POST['ftpden_kaynak_sil']) && !empty($_POST['ftpden_kaynak_sil']))
{
    $ftpden_kaynak_sil = ltrim(rtrim($_POST['ftpden_kaynak_sil'],'/'),'/');
    $ftp_host = $genel_ayarlar['sunucu'];
    $ftp_user = $genel_ayarlar['username'];
    $ftp_pass = $genel_ayarlar['password'];

    $ftp = @ftp_ssl_connect($ftp_host) 
        or die($ftp_host  . " sunucuya bağlanamadı"); 

    $ftp_login = ftp_login($ftp, $ftp_user, $ftp_pass);
    ftp_pasv($ftp, true);
    if ((!$ftp) || (!$ftp_login)) 
        die("FTP Bağlantısı Başarısız");

    function deleteDirectoryRecursive($directory, $ftp) {
        // Fonksiyonla gelen dosya ise siliyoruz, eğer dizin ise bir aşağı fonksiyona geçiyoruz
        if (@ftp_delete($ftp, $directory)) {
            return;
            //echo "Silindi: ".$directory."<br>";
        }
        // Burada dizini silmeye çalışıyoruz dizin içi boş değil ise devam ediyoruz ve dizin içindekilerini siliyoruz
        if( !@ftp_rmdir($ftp, $directory) ) {
            // Dizin içindeki dosyaları listeliyoruz
            if ($files = @ftp_nlist ($ftp, $directory)) {
                foreach ($files as $file){
                    // Dizideki . ve .. ile dizinleri gösterenleri parçıyoruz ve dizideki son öğeyi alıyoruz
                    $haric = explode("/", $file);
                    // Satırlarında . ve .. olanları hariç tutuyoruz
                    if(end($haric)!='.' && end($haric)!='..'){
                        // fonsiyona tekrar gönderip en baştaki ftp_delete() ile dosyaları siliyoruz
                        deleteDirectoryRecursive( $file, $ftp);
                    }
                }
            }
        }
        // Dosyalar silinip dizin boş kaldığında dizinide siliyoruz
        @ftp_rmdir($ftp, $directory);
    }

if(!deleteDirectoryRecursive($ftpden_kaynak_sil, $ftp)){
    echo json_encode(["li_sil_adi"=>basename($ftpden_kaynak_sil),"mesaj"=>"Dosya başarıyla silindi: ". basename($ftpden_kaynak_sil)]);
}else{
    echo json_encode(["li_sil_adi"=>'none',"mesaj"=>"Dosya bir hatadan dolayı silinemedi: ". basename($ftpden_kaynak_sil)]);
}

}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>