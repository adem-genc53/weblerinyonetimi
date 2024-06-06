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

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    //echo '<pre>' . print_r($_POST, true) . '</pre>';
    //exit;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Her yerel alandan googla yükleme kodu
if(isset($_POST['googla_yukle']) && $_POST['googla_yukle'] == '1' && isset($_POST['yerel_den_secilen_dosya']) && !empty($_POST['yerel_den_secilen_dosya']) && isset($_POST['google_drive_dan_secilen_dosya_id']) && !empty($_POST['google_drive_dan_secilen_dosya_id']))
{
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
// Dosya mevcut mu kontrol ediyoruz. Mevcut ise ID sini alıyoruz
function getFilesIdIfExists($parentId, $fileName) {

    $client = getClient();
    $service = new \Google\Service\Drive($client);

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

    $client = getClient();
    $service = new \Google\Service\Drive($client);

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

    $client = getClient();
    $service = new \Google\Service\Drive($client);

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
        echo "<span style='color: red'>Dosyanın üzerine yazma başarılı-:</span> ".$google_hedefadi."/".$cikti_yolu_adi."<br />";
    }else{
    // Mevcut olmayan yeni dosyalar için sonuç çıktısı
        echo "<span style='color: blue;'>Başarılı:</span> ".$google_hedefadi."/".$cikti_yolu_adi."<br />";
    }
#########################################################################################
            } // } else { if (is_dir($filePath)) {
        } // if ($dosya_adi != '.' && $dosya_adi != '..') {
    } // foreach ($files as $dosya_adi) {
} // function uploadFolder($client, $service, $parentId, $folderPath) {

##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
$yerelden_secilen   = rtrim($_POST['yerel_den_secilen_dosya'], '/');
$google_hedef_id    = $_POST['google_drive_dan_secilen_dosya_id'];
$google_hedef_adi   = $_POST['google_drive_dan_secilen_dosya_adini_goster'];
defined('GOOGLE_HEDEF_ADI')        or define('GOOGLE_HEDEF_ADI', $google_hedef_adi);

try {
    getFolderIdIfExists($google_hedef_id, $google_hedef_adi);
} catch (Exception $e) {
    echo 'Yakalanan olağandışı durum mesajı: ';
    echo '<pre>' . print_r(json_decode($e->getMessage(), true), true) . '</pre>';
    echo 'Hata mesajını çözmek için bu linki tıklayın<br /><a target="_blank" href="https://developers.google.com/drive/api/guides/handle-errors?hl=tr">https://developers.google.com/drive/api/guides/handle-errors?hl=tr</a>';
    exit;
}

// Yerelden seçilen kaynak dosya ise uzantısı olması gerekir kontrol ediyoruz ve dosya ise
if(pathinfo($yerelden_secilen, PATHINFO_EXTENSION)){

    $google_hedefadi = $google_hedef_adi == 'root' ? '' : $google_hedef_adi;

    // Kaynak dosya olduğundan dosyayı hedefe yükle
    $dosya_adi = basename($yerelden_secilen);
    $filePath = $yerelden_secilen;
    $existingFileId = getFilesIdIfExists($google_hedef_id, $dosya_adi);

######################################################################################################################################
    // Google Drive API'ye gönderilecek dosya nesnesini oluşturuyoruz.
    $file = new Google\Service\Drive\DriveFile();
    $file->name = $dosya_adi;
// Dosya mevcut değil ise yenin dosyanın yükleneceği klasörün ID sini belirtiyoruz
if(!$existingFileId){
    $file->setParents([$google_hedef_id]);
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
        mime_content_type($yerelden_secilen),
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

    // Mevcut dosyalar için sonuç çıktısı
    if($existingFileId){
        echo "<span style='color: red'>Dosyanın üzerine yazma başarılı:</span> ".$google_hedefadi."/".$dosya_adi."<br />";
    }else{
    // Mevcut olmayan yeni dosyalar için sonuç çıktısı
        echo "<span style='color: blue;'>Başarılı:</span> ".$google_hedefadi."/".$dosya_adi."<br />";
    }
######################################################################################################################################
// Yerelden seçilen kaynak klasör
}else{
    // Kaynak klasör olduğundan fonksiyonu çağırarak dosyaları yüklüyoruz
    $dizin_liste_array = uploadFolder($google_hedef_id, $yerelden_secilen);
}
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
}else if(isset($_POST['ftpye_yukle']) && $_POST['ftpye_yukle'] == '1' && isset($_POST['yerel_den_secilen_dosya']) && !empty($_POST['yerel_den_secilen_dosya']) && isset($_POST['ftp_den_secilen_dosya']) && !empty($_POST['ftp_den_secilen_dosya']))
{

$ftp_server = $genel_ayarlar['sunucu'];
$ftp_user   = $genel_ayarlar['username'];
$ftp_pass   = $genel_ayarlar['password'];

// Bağlantı oluştur
$conn_id = ftp_ssl_connect($ftp_server);

// Giriş yap
$login_result = ftp_login($conn_id, $ftp_user, $ftp_pass);
ftp_pasv($conn_id, true);

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
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function yukleDosyalar($conn_id, $yerel_dizin, $ftp_dizin, $secilen_yol) {

    $dosyalar = scandir($yerel_dizin);

    foreach ($dosyalar as $dosya) {
        if ($dosya != '.' && $dosya != '..') {
            $yerel_dosya_yolu = $yerel_dizin ."/". $dosya;
            $uzak_dosya_yolu = $dosya;

            if (is_dir($yerel_dosya_yolu)) {
                // Eğer bir dizinse, önce o dizine geç ve ardından dosyaları yükle
                if (!@ftp_chdir($conn_id, $uzak_dosya_yolu)) {
                    ftp_mkdir($conn_id, $uzak_dosya_yolu);
                    ftp_chdir($conn_id, $uzak_dosya_yolu);
                }
                yukleDosyalar($conn_id, $yerel_dosya_yolu, $ftp_dizin, $secilen_yol);
                ftp_chdir($conn_id, '..');

            } else {
                // Eğer bir dosyaysa, dosyayı yükle
                if (ftp_put($conn_id, $uzak_dosya_yolu, $yerel_dosya_yolu, FTP_BINARY)) {
                    $yerel_dosyayolu = str_replace(array(BACKUPDIR, ZIPDIR, DIZINDIR), array($ftp_dizin), $yerel_dosya_yolu);
                    echo "<span style='color: blue;'>Başarılı:</span> ".$ftp_dizin."/".substr($yerel_dosya_yolu, strpos($yerel_dosya_yolu, basename($secilen_yol)), null)."<br />";
                } else {
                    echo "<span style='color: red;'>Başarısız:</span> ".$ftp_dizin."/".substr($yerel_dosya_yolu, strpos($yerel_dosya_yolu, basename($secilen_yol)), null)."<br />";
                }
            }
        }
    }
}

if ($login_result) {
    //echo "FTP sunucusuna başarıyla bağlandı ve giriş yapıldı.<br><br>";
    $yerel_dizin = rtrim($_POST['yerel_den_secilen_dosya'], '/');

    // Kaynak dosya ise
    if(pathinfo($yerel_dizin, PATHINFO_EXTENSION)){
    // FTP dizinin başında ve sonunda eğik çizgi olmalıdır
    $ftp_dizin = "/".ltrim(rtrim($_POST['ftp_den_secilen_dosya'], '/'), '/')."/";
        // FTP den dizin seçildi ise ve oluşturmayan dizin varsa önce oluştur
        if($ftp_dizin != '/'){
            ftp_mksubdirs($conn_id,null,$ftp_dizin);
        }
        // Eğer bir dosyaysa, dosyayı yükle
        $ciktiyolu = ltrim($ftp_dizin.basename($yerel_dizin),'/');
        if (ftp_put($conn_id, $ftp_dizin.basename($yerel_dizin), $yerel_dizin, FTP_BINARY)) {
            echo "<span style='color: blue;'>Başarılı:</span> ".$ciktiyolu."<br />";
        } else {
            echo "<span style='color: red;'>Başarısız:</span> ".$ciktiyolu."<br />";
        }
    }else{ // Kaynak klasör ise
    $ftp_dizin = ltrim(rtrim($_POST['ftp_den_secilen_dosya'], '/'), '/'); // Seçilen dizin olduğu için ve seçilen dizinide göndermek için basename() ile ftp dizine ekliyoruz
        // FTP sunucuda dizin kontrolü yap
        if (!@ftp_chdir($conn_id, $ftp_dizin."/".basename($yerel_dizin))) {
            // Dizin yoksa oluştur
            $dizinler = array_filter(explode('/', $ftp_dizin."/".basename($yerel_dizin)));
            foreach ($dizinler as $dizin) {
                if (!@ftp_chdir($conn_id, $dizin)) {
                    ftp_mkdir($conn_id, $dizin);
                    ftp_chmod($conn_id, 0755, $dizin);
                    ftp_chdir($conn_id, $dizin);
                }
            }
        }
        // Yerel dizinindeki dosyaları FTP sunucuya yükle
        yukleDosyalar($conn_id, $yerel_dizin, $ftp_dizin, $secilen_yol=$yerel_dizin);
        echo "<b>Tüm dosyalar FTP sunucusuna başarıyla yüklendi</b>";
    }
    // Bağlantıyı kapat
    ftp_close($conn_id);
} else {
    echo "FTP sunucusuna bağlanırken bir hata oluştu.<br>";
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

}
?>