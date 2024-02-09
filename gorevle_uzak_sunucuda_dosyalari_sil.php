<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
//require_once('check-login.php');
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

    //echo substr('webleryonetimi-2023-11-19-00-24-29.zip', 15, -4);
    //exit;
/*
    $dosya = fopen ("metin.txt" , "a"); //dosya oluşturma işlemi 
    $yaz = "görev dosyasından\n".print_r($_POST, true); // Yazmak istediginiz yazı 
    fwrite($dosya,$yaz); fclose($dosya);
*/
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gorevle_google_yedek_sil']) && $_POST['gorevle_google_yedek_sil'] == 1){

    $gorevler = $PDOdb->prepare(" SELECT * FROM zamanlanmisgorev WHERE id = ? "); 
    $gorevler->execute([$_POST['id']]);
    $row = $gorevler->fetch();

    if($row['yedekleme_gorevi'] == 1){
        $sil_uzantilar = ["sql","gz"];
    }elseif($row['yedekleme_gorevi'] == 2){
        $sil_uzantilar = ["zip"];
    }

    // Aynı dizin varsa ID sini ver
    function dir_exists($fileid, $service) {
        $folderId = $fileid;
        $results = $service->files->listFiles(array(
            //'q' => "'$folderId' in parents"
            'q' => "mimeType='application/vnd.google-apps.folder' and '$folderId' in parents and trashed=false"
        ));
        $klasorler_dizi = [];
        foreach ($results->getFiles() as $file) {
            $klasorler_dizi[$file->getId()] = $file->getName();
        }
        return $klasorler_dizi;
    }

    // Dizin veya Birden fazla dizinleri döngü ile son dizin ID sini ver
    function onDizinYolu($service, $path, $parentId = null) {
        $directories = explode('/', $path);
        foreach ($directories as $directory) {
            // Dizin ve alt dizinleri oluşturuyoruz
            $dizinveyadosyavarmi = dir_exists($parentId, $service);

            //echo '<pre>' . print_r($dizinveyadosyavarmi, true) . '</pre>';
            if(in_array($directory, $dizinveyadosyavarmi))
            {
                $dizinveyadosyavarmi = array_flip($dizinveyadosyavarmi);
                $parentId = $dizinveyadosyavarmi[$directory];
            }
            else
            {
                $parentId = onAltDizinYolu($service, $parentId, $directory);
            }
        }
        return $parentId; // The ID of the last folder created
    }

    // Yukarıda dizinleri ID leri alırken eğer dizin yoksa burada oluştur. Buda hata vermesini önleyecektir
    function onAltDizinYolu($service, $parentId, $subdirectoryName) {
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $subdirectoryName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => $parentId ? array($parentId) : null
        ));

        try {
            $folder = $service->files->create($fileMetadata, array('fields' => 'id'));
            //printf("Folder ID: %s\n", $folder->id);
            return $folder->id; // Return the ID of the created folder
        } catch (Exception $e) {
            echo "An error occurred: " . $e->getMessage();
        }
        return null; // Return null if something went wrong
    }

    // Görevde ön dizin veya dizinler varsa alıp yukarıdaki fonksiyonlarla son dizin ID sini alıyoruz
    // Eğer dizin yoksa else ise root yani ana dizini listeliyoruz
    if(isset($row['uzak_sunucu_ici_dizin_adi']) && !empty($row['uzak_sunucu_ici_dizin_adi'])){
        $ondizin = ltrim(rtrim($row['uzak_sunucu_ici_dizin_adi'],'/'),'/');
        $root = 'root';
        $folderId = onDizinYolu($service, $ondizin, $root);
    }else{
        $folderId = 'root';
    }

    // root veya dizin ID ile içeriğini array oluşturuyoruz
    $results = $service->files->listFiles(array(
        'q' => "'$folderId' in parents"
    ));

    // Yanlış dosya silmemek için dosyadaki tarihi alıp tarih mi kontrol ediyoruz
    if (!function_exists('validateDate')) {
        function validateDate($date, $format = 'Y-m-d-H-i-s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }
    }

    //echo '<pre>' . print_r($results->getFiles(), true) . '</pre>';
    // Drive'dan aldığımız diziyi klasör ve dosya ayırıyoruz
    // Dosyadaki tarihleri alip unix zaman damgasına dönüştürüp keye giriyoruz ki bu sayede en yeniden eskiye sıralayabilelim
    $drive_dosyalar_arr = [];
    $drive_dizinler_arr = [];
    foreach ($results->getFiles() as $file) {

        if($file->getMimeType() == 'application/vnd.google-apps.folder'){
            $dizin_tarihi = substr($file->getName(), strpos($file->getName(), $row['secilen_yedekleme_oneki']."-") + strlen($row['secilen_yedekleme_oneki']."-"), 19);
            if(validateDate($dizin_tarihi)){
                list($year, $month, $day, $hour, $minute) = explode('-', $dizin_tarihi);
                $unix_time = mktime($hour, $minute, 0, $month, $day, $year);
                $drive_dizinler_arr[$unix_time][] = $file->getId(); //."|".$file->getName();
            }
        }elseif(in_array(pathinfo($file->getName(), PATHINFO_EXTENSION), $sil_uzantilar)){
            $dosya_tarihi = substr($file->getName(), strpos($file->getName(), $row['secilen_yedekleme_oneki']."-") + strlen($row['secilen_yedekleme_oneki']."-"), 19);
            if(validateDate($dosya_tarihi)){
                list($year, $month, $day, $hour, $minute) = explode('-', $dosya_tarihi);
                $unix_time = mktime($hour, $minute, 0, $month, $day, $year);
                $drive_dosyalar_arr[$unix_time][] = $file->getId(); //."|".$file->getName();
            }
        }
    }

    //krsort($drive_dosyalar_arr);
    //echo '<pre>Dosyalar: ' . print_r($drive_dosyalar_arr, true) . '</pre>';

    // Yeni dizileri en yeniden eskiye doğru sıralayalım
    if(isset($drive_dosyalar_arr) && count($drive_dosyalar_arr)>0) {
    krsort($drive_dosyalar_arr);
    $drive_dosyalar_arr = call_user_func_array('array_merge', $drive_dosyalar_arr);
    }
    if(isset($drive_dizinler_arr) && count($drive_dizinler_arr)>0) {
    krsort($drive_dizinler_arr);
    $drive_dizinler_arr = call_user_func_array('array_merge', $drive_dizinler_arr);
    }

    //echo '<pre>Dizinler: ' . print_r($drive_dizinler_arr, true) . '</pre>';
    //echo '<pre>Dosyalar: ' . print_r($drive_dosyalar_arr, true) . '</pre>';

    if(count($drive_dosyalar_arr)>0){
        while (count($drive_dosyalar_arr) > $row['google_sunucu_korunacak_yedek']){
            $silinendosya = array_pop($drive_dosyalar_arr);
            //echo "<b style='color: red;'>Temsili Silinen dosya: </b>".$silinendosya."<br>";
            try {
                $service->files->delete(trim($silinendosya), array('supportsAllDrives' => true));
            } catch (Exception $e) {
                $hatamesaji = json_decode($e->getMessage(), true)['error']['message'];
                echo $silinendosya.": ".$hatamesaji;
                //print "Bir hata oluştu: " . $e->getMessage();
                exit();
            }
        }
    }

    if(count($drive_dizinler_arr)>0){
        while (count($drive_dizinler_arr) > $row['google_sunucu_korunacak_yedek']){
            $silinendizin = array_pop($drive_dizinler_arr);
            //echo "<b style='color: blue;'>Temsili Silinen klasör: </b>".$silinendizin."<br>";
            try {
                $service->files->delete(trim($silinendizin), array('supportsAllDrives' => true));
            } catch (Exception $e) {
                $hatamesaji = json_decode($e->getMessage(), true)['error']['message'];
                echo $silinendizin.": ".$hatamesaji;
                //print "Bir hata oluştu: " . $e->getMessage();
                exit();
            }
        }
    }
    
}

?>