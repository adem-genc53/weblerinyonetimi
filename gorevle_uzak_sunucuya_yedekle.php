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


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ftp_yedekle']) && $_POST['ftp_yedekle'] == 1 && isset($_POST['dosya_adi_yolu']) && strlen($_POST['dosya_adi_yolu']) > 1){

    $gorevler = $PDOdb->prepare(" SELECT * FROM zamanlanmisgorev WHERE id = ? ");
    $gorevler->execute([$_POST['id']]);
    $row = $gorevler->fetch();

    $uzantilar = ["zip","sql","gz","rar","tar"];

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//r10.net fatal
    $ftp_directory = "";

    $yuklenecek_dizin_veya_dosya = isset($_POST['dosya_adi_yolu']) ? $_POST['dosya_adi_yolu'] : ""; //"../yeni-webyonetimi"; //Bu, tüm alt klasörleri ve dosyalarıyla birlikte yüklemek istediğiniz klasördür veya dosya adıdır.

    $ftpsunucu      = $genel_ayarlar['sunucu']; //ftp domain name
    $ftpusername    = $genel_ayarlar['username']; //ftp user name 
    $ftppass        = $genel_ayarlar['password']; //ftp passowrd

    // Yüklemeler ftp hesap ana dizinden sonra tekrar dizin(ler) varsa oluşturuyor
    if(isset($_POST['ftpsonrakidizin']) && !empty($_POST['ftpsonrakidizin'])){
        $ftp_dizinler = $_POST['ftpsonrakidizin'];
        $ftp_dizinler = preg_replace('/^\/+|\/+$/', '', $ftp_dizinler); // dizin yolunun başında ve veya sonunda / eğik çizgi varsa kaldırır
            // Eğer yüklenne dosya dizin ise ana dizinden sonraki dizin(lere) kaynak dizinide ekle
            if(is_dir($yuklenecek_dizin_veya_dosya)){
                $ftp_directory = "/".$ftp_dizinler."/".basename($yuklenecek_dizin_veya_dosya);
            }else{
                // Eğer yüklenen dosya dizin değil ise eklenen dizinlerle devam et
                $ftp_directory = "/".$ftp_dizinler;
            }
    }else{ // isset post
            // Yüklemeler ftp hesap ana dizinden sonra tekrar dizin(ler) yoksa ve yüklenen kaynak dizin ise dizini yükle
            if(is_dir($yuklenecek_dizin_veya_dosya)){
                $ftp_directory = "/".basename($yuklenecek_dizin_veya_dosya);
            }else{
                // Yüklemeler ftp hesap ana dizinden sonra tekrar dizin(ler) yok, yüklenen kaynakta dizin değil ise boş devam ediyor
                $ftp_directory = "";
            }
    }// isset post else
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // ftp bağlantısı kurma
    $ftp = @ftp_ssl_connect($ftpsunucu)
        or die($ftpsunucu . " sunucuya bağlanamadı");

    if($ftp) {
        //echo "FTP sunucusuna başarıyla bağlanıldı!";
        
        // Kurulan bağlantıya ftp kullanıcı adı şifresi ile giriş yapıyoruz
        $login_result = ftp_login($ftp, $ftpusername, $ftppass);
        ftp_pasv($ftp, true);

        if($login_result){
            //echo "<br>FTP girişi başarılı<br><br>";
#####################################################################################################################################
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function klasoruoku($directory_name) {
        global $current_directory,$full_directory,$ftp_directory,$ftp;

        if(!empty($ftp_directory)){
            ftp_mksubdirs($ftp,null,$ftp_directory);
        }

        chdir($directory_name."/"); // PHP'nin geçerli dizinden belirtilen dizin'e geçmesini sağlar. 
        $directory  =  opendir(".");

        while($row = readdir($directory)) {
            if ($row != '.' and $row != '..' and $row != "Thumbs.db") {
                $tamyol = "$directory_name/$row";

                $lokalkla = str_replace("".$current_directory."/","",$directory_name)."";
                $lokaldosya = "$lokalkla/$row";
                $ftp_upload = str_replace(array("//","/"),array("/","/"),"$ftp_directory".str_replace("".$full_directory."","",$directory_name)."/$row");

                if(!is_dir($row)) {
                    $yükleme = ftp_put($ftp, $ftp_upload, $tamyol, FTP_BINARY); // FTP ile dosyaları uzak sunucuya yükler
                if ($yükleme) {

                }else{

                }
                }else{
                    // Gönderilen dizin ise önce dizin oluşturuyoruz
                    // Eğer aynı dizin varsa }else{ ile dizin oluşturmayı atlıyoruz ve dosyaları yüklüyoruz
                    if(@ftp_mkdir($ftp, $ftp_upload)){
                        ftp_chmod($ftp, 0755, $ftp_upload);    
                        ftp_chdir($ftp, $ftp_upload);
                        klasoruoku("$directory_name/$row");
                        chdir($directory_name."/");
                    }else{
                        klasoruoku("$directory_name/$row");
                        chdir($directory_name."/");             
                    }
                }
            }
        }
        closedir ($directory);
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function ftp_mksubdirs($ftp,$ftpbasedir,$ftpath){
        @ftp_chdir($ftp, $ftpbasedir); // /var/www/uploads sunucudaki dizin yani başlama dizin ancak biz null girdik
        $parts = array_filter(explode('/',$ftpath)); // 2013/06/11/username buda yeni dizi veya dizinler
        foreach($parts as $part){
            if(!@ftp_chdir($ftp, $part) && ftp_nlist($ftp, $part) === false){
                ftp_mkdir($ftp, $part);
                ftp_chmod($ftp, 0755, $part);
                ftp_chdir($ftp, $part);
            }
        }
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $current_directory = getcwd();
    $full_directory = $current_directory."/".$yuklenecek_dizin_veya_dosya;

    // Gönderilecek tek dosya zip, sql, gz mi dosyasımı kontrol ediyoruz
    if(in_array(pathinfo(basename($yuklenecek_dizin_veya_dosya), PATHINFO_EXTENSION), $uzantilar)){

        if(!empty($ftp_directory)){
            ftp_mksubdirs($ftp,null,$ftp_directory);
        }
        // Tek zip dosyayı yüklemek için
        $upload = ftp_put($ftp, basename($yuklenecek_dizin_veya_dosya), $yuklenecek_dizin_veya_dosya, FTP_BINARY); // FTP ile dosyaları uzak sunucuya yükler

        // yükleme durumunu kontrol et
        if ($upload) {
            echo "<span>FTP Sunucusuna Başarıyla Yedeklendi</span>";
        } else {
            echo "<span>FTP Sunucusuna Yükleme BAŞARISIZ</span>";
        }

    // Eğer yedeklenen dizin ise
    }else if(is_dir($yuklenecek_dizin_veya_dosya)){
        klasoruoku($full_directory);
        echo "<span>FTP Sunucusuna Başarıyla Yedeklendi</span>";
    }else{
        echo "<span>FTP Sunucusuna Yükleme BAŞARISIZ</span>";
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $file_list = [];
    //while ($row = $gorevler->fetch()) {
// '/^\/+|\/+$/'


        if($row['yedekleme_gorevi'] == 1){
            $sil_uzantilar = ["sql","gz"];
        }elseif($row['yedekleme_gorevi'] == 2){
            $sil_uzantilar = ["zip"];
        }
        if(!empty($row['uzak_sunucu_ici_dizin_adi']) && strlen($row['uzak_sunucu_ici_dizin_adi'])>2){
            $uzak_sunucu_ici_dizin_adi = preg_replace('/\/+$/', '', $row['uzak_sunucu_ici_dizin_adi']); // dizin yolunun başında ve veya sonunda / eğik çizgi varsa kaldırır. Elle ekledik
        }else{
            $uzak_sunucu_ici_dizin_adi = "";
        }
    if($row['ftp_sunucu_korunacak_yedek'] != '-1'){
        $file_list = ftp_mlsd($ftp, $uzak_sunucu_ici_dizin_adi);

        $ftpdeki_dosyalar = [];
        $ftpdeki_dizinler = [];
        if(is_array($file_list) || is_object($file_list)){
        foreach($file_list as $file_list_arr) {
            if(!in_array($file_list_arr['type'], array("pdir","cdir")) && stripos($file_list_arr['name'], $row['secilen_yedekleme_oneki']) !== false){
                if($file_list_arr['type'] == 'file' && in_array(pathinfo($file_list_arr['name'], PATHINFO_EXTENSION), $sil_uzantilar)){
                    $ftpdeki_dosyalar[$file_list_arr['modify']][] = $uzak_sunucu_ici_dizin_adi."/".$file_list_arr['name'];
                //echo "<b style='color:blue;'>Dosya: </b>"."/".$uzak_sunucu_ici_dizin_adi."/".$file_list_arr['name']."<br>";
                }elseif($file_list_arr['type'] == 'dir'){
                    $ftpdeki_dizinler[$file_list_arr['modify']][] = $uzak_sunucu_ici_dizin_adi."/".$file_list_arr['name'];
                //echo "<b style='color: red;'>Klasör: </b>"."/".$uzak_sunucu_ici_dizin_adi."/".$file_list_arr['name']."<br>";
                }
            }
        } // foreach($file_list as $file_list_arr) {
        } // if(is_array($file_list) || is_object($file_list)){

    if(isset($ftpdeki_dosyalar) && count($ftpdeki_dosyalar)>0) {
    krsort($ftpdeki_dosyalar);
    $ftpdeki_dosyalar = call_user_func_array('array_merge', $ftpdeki_dosyalar);
    }
    if(isset($ftpdeki_dizinler) && count($ftpdeki_dizinler)>0) {
    krsort($ftpdeki_dizinler);
    $ftpdeki_dizinler = call_user_func_array('array_merge', $ftpdeki_dizinler);
    }

    if (!function_exists('validateDate')) {
            function validateDate($date, $format = 'Y-m-d-H-i-s')
            {
                $d = DateTime::createFromFormat($format, $date);
                return $d && $d->format($format) == $date;
            }
    }

    if(count($ftpdeki_dosyalar)>0){
        while (count($ftpdeki_dosyalar) > $row['ftp_sunucu_korunacak_yedek']) {
            $silinendosya = array_pop($ftpdeki_dosyalar);
            $dosya_tarihi = substr($silinendosya, strpos($silinendosya, $row['secilen_yedekleme_oneki']."-") + strlen($row['secilen_yedekleme_oneki']."-"), 19);
            if(validateDate($dosya_tarihi)){
                deleteDirectoryRecursive( $silinendosya, $ftp);
                //echo "<b style='color: red;'>Temsili Silinen dosya: </b>".$silinendosya."<br>";
            }
        }
    }

    if(count($ftpdeki_dizinler)>0){
        while (count($ftpdeki_dizinler) > $row['ftp_sunucu_korunacak_yedek']) {
            $silinendizin = array_pop($ftpdeki_dizinler);
            $dizin_tarihi = substr($silinendizin, -19);
            if(validateDate($dizin_tarihi)){
                deleteDirectoryRecursive( $silinendizin, $ftp);
                //echo "<b style='color: blue;'>Temsili Silinen klasör: </b>".$silinendizin."<br>";
            }
        }
    }

    //echo '<pre>Dosyalar: '.$row['ftp_sunucu_korunacak_yedek'].'<br>' . print_r($ftpdeki_dosyalar, true) . '</pre>';
    //echo '<pre>Dizinler: '.$row['ftp_sunucu_korunacak_yedek'].'<br>' . print_r($ftpdeki_dizinler, true) . '</pre>';
    } // if($row['ftp_sunucu_korunacak_yedek'] != '-1'){

        //} // while ($row = $gorevler->fetch()) {
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
#####################################################################################################################################
        } else { // if($login_result){
            //echo "<br>FTP giriş hatası oluştu!";
        }

        // echo ftp_get_option($ftp, 1);
        // Bağlantıyı kapatıyoruz
        if(ftp_close($ftp)) {
            //echo "<br>FTP Bağlantısı Başarıyla Kapatıldı";
        }
    } // if($ftp) {

} // if($_SERVER['REQUEST_METHOD']
#####################################################################################################################################
#####################################################################################################################################
#####################################################################################################################################
#####################################################################################################################################
#####################################################################################################################################
#####################################################################################################################################
#####################################################################################################################################
#####################################################################################################################################
#####################################################################################################################################
#####################################################################################################################################
#####################################################################################################################################
#####################################################################################################################################
// AŞAĞIDAKİ KOD VERİTABANI VEYA DİZİN ZİP DOSYASI GÖREV İLE GOOGLA YÜKLEME KODU
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['google_yedekle']) && $_POST['google_yedekle'] == 1 && isset($_POST['dosya_adi_yolu']) && strlen($_POST['dosya_adi_yolu']) > 1){

##################################################################################################################################
##################################################################################################################################
// Dosya mevcut mu kontrol ediyoruz. Mevcut ise ID sini alıyoruz
function getFilesIdIfExists($parentId, $fileName) {

    $client = new Google\Client();
    $client->setAuthConfig(AUTHCONFIGPATH);
    $client->addScope(Google\Service\Drive::DRIVE);
    $service = new Google\Service\Drive($client);

    $query = "name='$fileName' and '$parentId' in parents";
    $results = $service->files->listFiles(['q' => $query, 'fields' => 'files(id)']);

    // Dosya bulunamadıysa false döndür
    if (count($results->files) == 0) {
        return false;
    }

    // Dosya türüne göre kontrol et. Klasör değil ise sonucu döndür
    if (count($results->files) > 0) {
        if($results->files[0]->mimetype !== 'application/vnd.google-apps.folder'){
            return $results->files[0]->id;
        }
    }
}
##################################################################################################################################
// Bu fonksiyon, belirtilen isimde bir dizin varsa ID'sini döndürür.
function getFolderIdIfExists($parentId, $folderName) {

    $client = new Google\Client();
    $client->setAuthConfig(AUTHCONFIGPATH);
    $client->addScope(Google\Service\Drive::DRIVE);
    $service = new Google\Service\Drive($client);

    $query = "mimeType='application/vnd.google-apps.folder' and name='$folderName' and '$parentId' in parents";
    $results = $service->files->listFiles(['q' => $query, 'fields' => 'files(id)']);
    if (count($results->files) > 0) {
        return $results->files[0]->id;
    }
    return null;
}
##################################################################################################################################
##################################################################################################################################
// Klasör ve tüm alt-klasörler ve dosyaları google drive a yükle
function uploadFolder($parentId, $folderPath) {

    $client = new Google\Client();
    $client->setAuthConfig(AUTHCONFIGPATH);
    $client->addScope(Google\Service\Drive::DRIVE);
    $service = new Google\Service\Drive($client);

    $google_hedefadi = GOOGLE_HEDEF_ADI == 'root' ? '' : GOOGLE_HEDEF_ADI;
    $folderName = basename($folderPath);

    // Klasörün mevcut olup olmadığını kontrol ediyoruz
    $existingFolderId = getFolderIdIfExists($parentId, $folderName);

    // Klasör mevcut ise klasör ID sini alıyoruz
    if ($existingFolderId) {
        $createdFolder = $existingFolderId;
    } else {
        // Klasör mevcut değil ise yeni klasör oluşturuyoruz
        $folder = new Google\Service\Drive\DriveFile();
        $folder->setName($folderName);
        $folder->setMimeType('application/vnd.google-apps.folder');
        $folder->setParents([$parentId]);

        $createdFolderid = $service->files->create($folder);
        $createdFolder = $createdFolderid->id;
    }

    // Lokal Klasörün içindeki dosyaları google drive'a okuyarak yüklüyoruz
    $files = scandir($folderPath);
    foreach ($files as $dosya_adi) {
        if ($dosya_adi != '.' && $dosya_adi != '..') {
            $filePath = $folderPath . '/' . $dosya_adi;

            if (is_dir($filePath)) {
                // Eğer dosya bir klasör ise, alt klasörü yükle
                uploadFolder($createdFolder, $filePath);
            } else {
                // Eğer dosya bir dosya ise, dosyayı yükle

                // Dosya mecut mu kontrol ediyorum
                $existingFileId = getFilesIdIfExists($createdFolder, $dosya_adi);

// Sonuca göre sıradaki dosyayı yüklemeye başlıyoruz
#########################################################################################
    // Google Drive API'ye gönderilecek dosya nesnesini oluşturuyoruz.
    $file = new Google\Service\Drive\DriveFile();
    $file->name = $dosya_adi;
    // Dosya mevcut değil ise yenin dosyanın yükleneceği klasörün ID sini belirtiyoruz
    if(!$existingFileId){
        $file->setParents([$createdFolder]);
    }
    $chunkSizeBytes = 1 * 1024 * 1024;

    // API'yi çağırıyoruz, ancak hemen yanıt almak yerine erteliyoruz.
    $client->setDefer(true);
        // Mevcut dosyayı güncelleme
        if($existingFileId){
            $request = $service->files->update($existingFileId, $file);
        }else{
        // Mevcut olmayan yeni dosyayı oluşturuyoruz
            $request = $service->files->create($file);
        }

    // Dosya yüklememizi temsil eden bir medya dosya yüklemesi oluşturuyoruz.
    $media = new Google\Http\MediaFileUpload(
        $client,
        $request,
        mime_content_type($filePath),
        null,
        true,
        $chunkSizeBytes
    );
    $media->setFileSize(filesize($filePath));

    // Dosya okuma işlemi için kullanılan fonksiyon
    if(!function_exists("readVideoChunk")){
        function readVideoChunk($handle, $chunkSize)
        {
            $byteCount = 0;
            $giantChunk = "";
            while (!feof($handle)) {
                // fread, okuma tamponlu ve düz bir dosyayı temsil etmiyorsa asla 8192 byte'dan fazla veri döndürmez
                $chunk = fread($handle, 8192);
                $byteCount += strlen($chunk);
                $giantChunk .= $chunk;
                if ($byteCount >= $chunkSize) {
                    return $giantChunk;
                }
            }
            return $giantChunk;
        }
    }

    // Farklı parçaları yüklüyoruz. İşlem tamamlandıkça $status değeri false olacaktır.
    $status = false;
    $handle = fopen($filePath, "rb");
    while (!$status && !feof($handle)) {
        // $filePath'den $chunkSizeBytes kadar oku
        $chunk = readVideoChunk($handle, $chunkSizeBytes);
        $status = $media->nextChunk($chunk);
    }

    // $status'un nihai değeri, yüklenen nesnenin API'den gelen verileri olacaktır.
    $result = $status;
    fclose($handle);
    
    $cikti_yolu_adi = str_replace(array(BACKUPDIR, ZIPDIR, DIZINDIR), '', $filePath);

    // Mevcut dosyalar için sonuç çıktısı
    if($existingFileId){
        //echo "<span style='color: red'>Dosyanın üzerine yazma başarılı-:</span> ".$google_hedefadi."/".$cikti_yolu_adi."<br />";
    }else{
    // Mevcut olmayan yeni dosyalar için sonuç çıktısı
        //echo "<span style='color: blue;'>Başarılı:</span> ".$google_hedefadi."/".$cikti_yolu_adi."<br />";
    }
#########################################################################################
            } // } else { if (is_dir($filePath)) {
        } // if ($dosya_adi != '.' && $dosya_adi != '..') {
    } // foreach ($files as $dosya_adi) {
} // function uploadFolder($client, $service, $parentId, $folderPath) {
##################################################################################################################################
/*
// Klasör ve alt içerikleri yükleme fonksiyonu
function uploadFolder($service, $parentId, $folderPath) {

    $google_hedefadi = GOOGLE_HEDEF_ADI == 'root' ? '' : GOOGLE_HEDEF_ADI;
    $folderName = basename($folderPath);

    // Klasörün mevcut olup olmadığını kontrol et
    $existingFolder = searchFile($service, $parentId, $folderName);

    if ($existingFolder) {
        $createdFolder = $existingFolder;
    } else {
        // Klasörü oluştur
        $folder = new Google_Service_Drive_DriveFile();
        $folder->setName($folderName);
        $folder->setMimeType('application/vnd.google-apps.folder');
        $folder->setParents([$parentId]);

        $createdFolder = $service->files->create($folder);
    }

    // Klasör içindeki dosyaları yükle
    $files = scandir($folderPath);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = $folderPath . '/' . $file;

            if (is_dir($filePath)) {
                // Eğer dosya bir klasör ise, alt klasörü yükle
                uploadFolder($existingFolderId, $filePath);
            } else {
                // Eğer dosya bir dosya ise, dosyayı yükle
                $existingFileId = getFilesIdIfExists($existingFolderId, $file);

                if ($existingFileId) {
                    // Dosya zaten varsa, üzerine yaz
                    $existing_File = new Google_Service_Drive_DriveFile();
                    $service->files->update($existingFileId, $existing_File, array(
                        'data' => file_get_contents($filePath),
                        'mimeType' => mime_content_type($filePath),
                        'uploadType' => 'media'
                    ));
                    $cikti_yolu_adi = str_replace(array(BACKUPDIR, ZIPDIR, DIZINDIR), '', $filePath);
                    //echo "<span style='color: red'>Dosyanın üzerine yazıldı:</span> ".$google_hedefadi."/".$cikti_yolu_adi."<br />";
                } else {
                    // Dosya yoksa, yeni dosya oluştur
                    $fileMetadata = new Google_Service_Drive_DriveFile();
                    $fileMetadata->setName($file);
                    $fileMetadata->setParents([$existingFolderId]);
                    $createdFile = $service->files->create($fileMetadata, [
                        'data' => file_get_contents($filePath),
                        'mimeType' => mime_content_type($filePath),
                        'uploadType' => 'media',
                    ]);
                    $cikti_yolu_adi = str_replace(array(BACKUPDIR, ZIPDIR, DIZINDIR), '', $filePath);
                    //echo "<span style='color: blue;'>Dosya yüklendi:</span> ".$google_hedefadi."/".$cikti_yolu_adi."<br />";
                }
            }
        }
    }
}
*/
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
// ÖN DİZİNLER VARSA ÖNCE ÖN DİZİNLERİ OLUŞTURUP SON DİZİN ID SİNİ ALIP O DİZİNE YEDEK YÜKLENMEYE BAŞLANACAK
/*
// Bu fonksiyon, belirtilen isimde bir dizin varsa ID'sini döndürür.
function getFolderIdIfExists($service, $parentId, $folderName) {
    $query = "mimeType='application/vnd.google-apps.folder' and name='$folderName' and '$parentId' in parents";
    $results = $service->files->listFiles(['q' => $query, 'fields' => 'files(id)']);
    if (count($results->files) > 0) {
        return $results->files[0]->id;
    }
    return null;
}
*/
// Bu fonksiyon, belirtilen isimde bir dizin oluşturur veya varsa ID'sini döndürür.
function createOrGetFolder($service, $parentId, $folderName) {
    $existingFolderId = getFolderIdIfExists($parentId, $folderName);

    if ($existingFolderId) {
        return $existingFolderId;
    }
    // Dizin oluştur
    $folderMetadata = new Google_Service_Drive_DriveFile([
        'name' => $folderName,
        'mimeType' => 'application/vnd.google-apps.folder',
        'parents' => [$parentId],
    ]);
    $folder = $service->files->create($folderMetadata, ['fields' => 'id']);
    return $folder->id;
}

// Bu fonksiyon, belirtilen dizini oluşturur ve son dizinin ID'sini döndürür.
function createDirectory($service, $parentId, $path) {
    $folders = explode('/', $path);
    $currentParentId = $parentId;
    foreach ($folders as $folder) {
        $currentParentId = createOrGetFolder($service, $currentParentId, $folder);
    }
    return $currentParentId;
}

    // ÖN DİZİNLER VARSA ÖNCE ÖN DİZİNLERİ OLUŞTURUP SON DİZİN ID SİNİ ALIP O DİZİNE YEDEK YÜKLENMEYE BAŞLANACAK
    if(isset($_POST['uzak_sunucu_ici_dizin_adi']) && !empty($_POST['uzak_sunucu_ici_dizin_adi'])){
        $ondizin    = ltrim(rtrim($_POST['uzak_sunucu_ici_dizin_adi'],'/'),'/');
        $rootId     = createDirectory($service, 'root', $ondizin);
    }else{
        $rootId     = 'root';
    }

    $yerelden_secilen   = rtrim($_POST['dosya_adi_yolu'],'/');
    $google_hedef_id    = $rootId;
    $google_hedef_adi   = $rootId;
    defined('GOOGLE_HEDEF_ADI')        or define('GOOGLE_HEDEF_ADI', $google_hedef_adi);
/*
try {
    $existingFileId = getFilesIdIfExists($google_hedef_id, $google_hedef_adi);
} catch (Exception $e) {
    echo 'Yakalanan olağandışı durum mesajı: ';
    echo '<pre>' . print_r(json_decode($e->getMessage(), true), true) . '</pre>';
    echo 'Hata mesajını çözmek için bu linki tıklayın<br /><a target="_blank" href="https://developers.google.com/drive/api/guides/handle-errors?hl=tr">https://developers.google.com/drive/api/guides/handle-errors?hl=tr</a>';
    exit;
}
*/
if(pathinfo($yerelden_secilen, PATHINFO_EXTENSION)){

    $google_hedefadi = $google_hedef_adi == 'root' ? '' : $google_hedef_adi;

    // Kaynak dosya olduğundan dosyayı hedefe yükle
    $dosya_adi = basename($yerelden_secilen);
    $existingFileId = getFilesIdIfExists($google_hedef_id, $dosya_adi);

    if($existingFileId){
        // Dosya zaten varsa, üzerine yaz
/*
        $existing_File = new Google_Service_Drive_DriveFile();
        $service->files->update($existingFileId, $existing_File, array(
            'data' => file_get_contents($yerelden_secilen),
            'mimeType' => mime_content_type($yerelden_secilen),
            'uploadType' => 'media'
        ));
*/
        // Google Drive API'ye gönderilecek dosya nesnesini oluşturuyoruz.
        $file = new Google\Service\Drive\DriveFile();
        $file->name = $dosya_adi;
        $chunkSizeBytes = 1 * 1024 * 1024;

        // API'yi çağırıyoruz, ancak hemen yanıt almak yerine erteliyoruz.
        $client->setDefer(true);
        //$request = $service->files->create($file);

        // Dosya yüklememizi temsil eden bir medya dosya yüklemesi oluşturuyoruz.
        $media = new Google\Http\MediaFileUpload(
            $client,
            $service->files->update($existingFileId, $file),
            'text/plain',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($yerelden_secilen));

        // Dosya okuma işlemi için kullanılan fonksiyon
        function readVideoChunk($handle, $chunkSize)
        {
            $byteCount = 0;
            $giantChunk = "";
            while (!feof($handle)) {
                // fread, okuma tamponlu ve düz bir dosyayı temsil etmiyorsa asla 8192 byte'dan fazla veri döndürmez
                $chunk = fread($handle, 8192);
                $byteCount += strlen($chunk);
                $giantChunk .= $chunk;
                if ($byteCount >= $chunkSize) {
                    return $giantChunk;
                }
            }
            return $giantChunk;
        }

        // Farklı parçaları yüklüyoruz. İşlem tamamlandıkça $status değeri false olacaktır.
        $status = false;
        $handle = fopen($yerelden_secilen, "rb");
        while (!$status && !feof($handle)) {
            // $yerelden_secilen'den $chunkSizeBytes kadar oku
            $chunk = readVideoChunk($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }

        // $status'un nihai değeri, yüklenen nesnenin API'den gelen verileri olacaktır.
        $result = $status;
        fclose($handle);

        //echo "<span style='color: red'>Dosyanın üzerine yazıldı:</span> ".$google_hedefadi."/".$dosya_adi."<br />";
    }else{
        // Dosya yoksa, yeni dosya oluştur
/*
        $fileMetadata = new Google_Service_Drive_DriveFile();
        $fileMetadata->setName($dosya_adi);
        $fileMetadata->setParents([$google_hedef_id]);
        $createdFile = $service->files->create($fileMetadata, [
            'data' => file_get_contents($yerelden_secilen),
            'mimeType' => mime_content_type($yerelden_secilen),
            'uploadType' => 'media',
        ]);
*/
        // Dosya yoksa, yeni dosya oluştur
        // Google Drive API'ye gönderilecek dosya nesnesini oluşturuyoruz.
        $file = new Google\Service\Drive\DriveFile();
        $file->name = $dosya_adi;
        $file->setParents([$google_hedef_id]);
        $chunkSizeBytes = 1 * 1024 * 1024;

        // API'yi çağırıyoruz, ancak hemen yanıt almak yerine erteliyoruz.
        $client->setDefer(true);
        $request = $service->files->create($file);

        // Dosya yüklememizi temsil eden bir medya dosya yüklemesi oluşturuyoruz.
        $media = new Google\Http\MediaFileUpload(
            $client,
            $request,
            'text/plain',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($yerelden_secilen));

        // Dosya okuma işlemi için kullanılan fonksiyon
        function readVideoChunk($handle, $chunkSize)
        {
            $byteCount = 0;
            $giantChunk = "";
            while (!feof($handle)) {
                // fread, okuma tamponlu ve düz bir dosyayı temsil etmiyorsa asla 8192 byte'dan fazla veri döndürmez
                $chunk = fread($handle, 8192);
                $byteCount += strlen($chunk);
                $giantChunk .= $chunk;
                if ($byteCount >= $chunkSize) {
                    return $giantChunk;
                }
            }
            return $giantChunk;
        }

        // Farklı parçaları yüklüyoruz. İşlem tamamlandıkça $status değeri false olacaktır.
        $status = false;
        $handle = fopen($yerelden_secilen, "rb");
        while (!$status && !feof($handle)) {
            // $yerelden_secilen'den $chunkSizeBytes kadar oku
            $chunk = readVideoChunk($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }

        // $status'un nihai değeri, yüklenen nesnenin API'den gelen verileri olacaktır.
        $result = $status;
        fclose($handle);
        
        //echo "<span style='color: blue;'>Dosya yüklendi:</span> ".$google_hedefadi."/".$dosya_adi."<br />";
    }
    echo "<span>Google Drive Sunucusuna Başarıyla Yedeklendi</span>";
}else{
    // Kaynak klasör olduğundan fonksiyonu çağırarak dosyaları yükle
    uploadFolder($google_hedef_id, $yerelden_secilen);
    echo "<span>Google Drive Sunucusuna Başarıyla Yedeklendi</span>";
}
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################

} // if($_SERVER['REQUEST_METHOD']

?>