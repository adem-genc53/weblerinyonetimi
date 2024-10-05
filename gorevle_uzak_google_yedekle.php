<?php 
// Bismillahirrahmanirrahim
if(isset($_POST['googla_yukle']) && $_POST['googla_yukle'] == '1'){
header('Connection: Keep-Alive');
header('Keep-Alive: timeout=5, max=100');
}
require_once __DIR__ . '/includes/connect.php';
include __DIR__ . '/google_drive_setup.php';

ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); // 7200 saniye 120 dakikadır, 3600 1 saat
##################################################################################################################################

if (!function_exists('uzakGoogleSunucudaDosyaSil')) {
function uzakGoogleSunucudaDosyaSil($dosya_adi_yolu, $yedekleme_gorevi, $silinecek_dosya_tipi, $uzak_sunucu_ici_dizin_adi, $google_sunucu_korunacak_yedek, $secilen_yedekleme_oneki) {

    $googlesilmemesaji = [];
    $sonuc_cikti_mesaji = [];

    $client = getClient();
    $service = new \Google\Service\Drive($client);

    if($yedekleme_gorevi == 1){
        $sil_uzantilar = ["sql","gz"];
    }elseif($yedekleme_gorevi == 2){
        $sil_uzantilar = ["zip"];
    }

    // Aynı dizin varsa ID sini ver
if (!function_exists('dir_exists')) {
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
}
    // Dizin veya Birden fazla dizinleri döngü ile son dizin ID sini ver
if (!function_exists('onDizinYolu')) {
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
}
    // Yukarıda dizinleri ID leri alırken eğer dizin yoksa burada oluştur. Buda hata vermesini önleyecektir
if (!function_exists('onAltDizinYolu')) {
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
            //echo "An error occurred: " . $e->getMessage();
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">Hata1: </span>' . $e->getMessage()
            ];
        }
        return null; // Return null if something went wrong
    }
}
    // Görevde ön dizin veya dizinler varsa alıp yukarıdaki fonksiyonlarla son dizin ID sini alıyoruz
    // Eğer dizin yoksa else ise root yani ana dizini listeliyoruz
    if(isset($uzak_sunucu_ici_dizin_adi) && !empty($uzak_sunucu_ici_dizin_adi)){
        $ondizin = trim($uzak_sunucu_ici_dizin_adi,'/');
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
            $dizin_tarihi = substr($file->getName(), strpos($file->getName(), $secilen_yedekleme_oneki."-") + strlen($secilen_yedekleme_oneki."-"), 19);
            if(validateDate($dizin_tarihi)){
                list($year, $month, $day, $hour, $minute) = explode('-', $dizin_tarihi);
                $unix_time = mktime($hour, $minute, 0, $month, $day, $year);
                $drive_dizinler_arr[$unix_time][] = $file->getId(); //."|".$file->getName();
            }
        }elseif(in_array(pathinfo($file->getName(), PATHINFO_EXTENSION), $sil_uzantilar)){
            $dosya_tarihi = substr($file->getName(), strpos($file->getName(), $secilen_yedekleme_oneki."-") + strlen($secilen_yedekleme_oneki."-"), 19);
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
        while (count($drive_dosyalar_arr) > $google_sunucu_korunacak_yedek){
            $silinendosya = array_pop($drive_dosyalar_arr);
            //echo "<b style='color: red;'>Temsili Silinen dosya: </b>".$silinendosya."<br>";
            try {
                $service->files->delete(trim($silinendosya), array('supportsAllDrives' => true));
                $sonuc_cikti_mesaji[] = [
                    'status' => 'success',
                    'message' => '<span style="color:green;">Google Drive Sunucusundaki Eski DOSYA(lar) Başarıyla Silindi</span>'
                ];
            } catch (Exception $e) {
                //$googlesilmemesaji[] = "Hata: " . $e->getMessage()['error']['message'];
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:red;">Hata2: </span>' . $e->getMessage()['error']['message']
                ];
                //print "Bir hata oluştu: " . $e->getMessage();
                //exit();
            }
        }
    }

    if(count($drive_dizinler_arr)>0){
        while (count($drive_dizinler_arr) > $google_sunucu_korunacak_yedek){
            $silinendizin = array_pop($drive_dizinler_arr);
            //echo "<b style='color: blue;'>Temsili Silinen klasör: </b>".$silinendizin."<br>";
            try {
                $service->files->delete(trim($silinendizin), array('supportsAllDrives' => true));
                $sonuc_cikti_mesaji[] = [
                    'status' => 'success',
                    'message' => '<span style="color:green;">Google Drive Sunucusundaki Eski KLASÖR(ler) Başarıyla Silindi</span>'
                ];
            } catch (Exception $e) {
                //$googlesilmemesaji[] = "Hata: " . $e->getMessage()['error']['message'];
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:red;">Hata3: </span>' . $e->getMessage()['error']['message']
                ];
                //print "Bir hata oluştu: " . $e->getMessage();
                //exit();
            }
        }
    }
    return $sonuc_cikti_mesaji;
}
}

##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################

//$sonuc_cikti_mesaji = [];  // Global hatalar arrayi

// Dosya Yükleme Fonksiyonu
function uploadFile($service, $filePath, $parentFolderId = null) {
    global $sonuc_cikti_mesaji;
    $fileInfo = pathinfo($filePath);
    $fileName = $fileInfo['basename'];
    $fileMimeType = mime_content_type($filePath);

    // Aynı dosya var mı kontrol et
    $existingFileId = getFileIdByName($service, $fileName, $parentFolderId);
    if ($existingFileId) {
        // Aynı isimde dosya varsa, önce bu dosyayı sil
        deleteFile($service, $existingFileId);
    }

    $fileMetadata = new \Google\Service\Drive\DriveFile([
        'name' => $fileName,
        'parents' => $parentFolderId ? [$parentFolderId] : []
    ]);

    $fileSize = filesize($filePath);
    $handle = fopen($filePath, 'rb');

    $chunkSize = 1 * 1024 * 1024; // 1 MB
    $client = $service->getClient();
    $client->setDefer(true);

    $request = $service->files->create($fileMetadata, [
        'mimeType' => $fileMimeType,
        'uploadType' => 'resumable'
    ]);

    $media = new \Google\Http\MediaFileUpload($client, $request, $fileMimeType, null, true, $chunkSize);
    $media->setFileSize($fileSize);

    $status = false;
    while (!$status && !feof($handle)) {
        $chunk = fread($handle, $chunkSize);
        $status = $media->nextChunk($chunk);
    }

    fclose($handle);
    $client->setDefer(false);

    if ($status != false) {
        $sonuc_cikti_mesaji[] = [
            'status' => 'success',
            'message' => "<span style='color:green;'>Google Drive Api Sunucusuna Dosya Başarıyla Yüklendi: </span> $fileName"
        ];
        return true;
    } else {
        $error = $client->getHttpClient()->getLastResponse()->getBody()->getContents();
        $errorData = json_decode($error, true);

        if (isset($errorData['error']['message']) && strpos($errorData['error']['message'], 'quotaExceeded') !== false) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">Depo alanı dolu. Dosya yüklenemedi.</span>'
            ];
            return false;
        } else {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">Dosya yükleme hatası.</span>'
            ];
            return false;
        }
    }
}

function deleteFile($service, $fileId) {
    try {
        $service->files->delete($fileId);
        return true;
    } catch (\Google\Service\Exception $e) {
        return false;
    }
}


// Klasör Yükleme Fonksiyonu
function uploadFolder($service, $folderPath, $parentFolderId = null) {
    global $sonuc_cikti_mesaji;
    $folderInfo = pathinfo($folderPath);
    $folderName = $folderInfo['basename'];

    $folderId = getFolderIdByName($service, $folderName, $parentFolderId); // Buradaki fonksiyon hataya sebep oluyor olabilir.
    if (!$folderId) {
        $folderMetadata = new \Google\Service\Drive\DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => $parentFolderId ? [$parentFolderId] : []
        ]);

        $retryCount = 0;
        $maxRetries = 3;
        $success = false;
        while ($retryCount < $maxRetries && !$success) {
            try {
                $folder = $service->files->create($folderMetadata, [
                    'fields' => 'id'
                ]);
                $folderId = $folder->id;
                $success = true;
            } catch (\Google\Service\Exception $e) {
                if ($e->getCode() == 500 || $e->getCode() == 503) {
                    $retryCount++;
                    sleep(2); // Bekle ve tekrar dene
                } else if ($e->getCode() == 403 && strpos($e->getMessage(), 'quotaExceeded') !== false) {
                    $sonuc_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => '<span style="color:red;">Depo alanı dolu. Klasör oluşturulamadı.</span>'
                    ];
                    return false;
                } else {
                    $sonuc_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => '<span style="color:red;">Klasör oluşturma hatası: </span>' . $e->getMessage()
                    ];
                    return false;
                }
            }
        }

        if (!$success) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">Klasör oluşturma <b>' . $maxRetries . '</b> deneme sonrası başarısız oldu</span>'
            ];
            return false;
        }
    }

    $items = scandir($folderPath);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        $itemPath = $folderPath . '/' . $item;
        if (is_dir($itemPath)) {
            $result = uploadFolder($service, $itemPath, $folderId);
            if (!$result) {
                return false;
            }
        } else {
            $result = uploadFile($service, $itemPath, $folderId);
            if (!$result) {
                return false;
            }
        }
    }

    $sonuc_cikti_mesaji[] = [
        'status' => 'success',
        'message' => "<span style='font-weight: bold;color:green;'>Google Drive Api Sunucusuna Klasör Başarıyla Yüklendi: </span> $folderName"
    ];

    return true;
}

// Yardımcı Fonksiyonlar

// Belirtilen klasörün adını kontrol eden ve ID'sini döndüren fonksiyon
function getFolderIdByName($service, $folderName, $parentFolderId = null) {
    $query = "name='$folderName' and mimeType='application/vnd.google-apps.folder' and trashed=false";
    if ($parentFolderId) {
        $query .= " and '{$parentFolderId}' in parents";
    }

    $response = $service->files->listFiles([
        'q' => $query,
        'fields' => 'files(id, name)',
    ]);

    return count($response->files) > 0 ? $response->files[0]->id : null;
}

// Yol boyunca klasörleri oluşturan fonksiyon
function getFolderIdByPath($service, $path) {
    $parts = explode('/', trim($path, '/'));
    $parentId = 'root';
    foreach ($parts as $part) {
        $response = $service->files->listFiles([
            'q' => "name='$part' and mimeType='application/vnd.google-apps.folder' and '{$parentId}' in parents and trashed=false",
            'fields' => 'files(id, name)',
        ]);
        if (count($response->files) == 0) {
            $parentId = createFolder($service, $part, $parentId); // Klasör yoksa oluştur
        } else {
            $parentId = $response->files[0]->id;
        }
    }
    return $parentId;
}

// Klasör oluşturma fonksiyonu
function createFolder($service, $folderName, $parentFolderId = null) {
    $folderMetadata = new \Google\Service\Drive\DriveFile([
        'name' => $folderName,
        'mimeType' => 'application/vnd.google-apps.folder',
        'parents' => $parentFolderId ? [$parentFolderId] : []
    ]);

    $folder = $service->files->create($folderMetadata, [
        'fields' => 'id'
    ]);
    return $folder->id;
}

// Belirtilen dosyanın adını kontrol eden ve ID'sini döndüren fonksiyon
function getFileIdByName($service, $fileName, $parentFolderId = null) {
    $query = "name='$fileName' and trashed=false";
    if ($parentFolderId) {
        $query .= " and '{$parentFolderId}' in parents";
    }

    $response = $service->files->listFiles([
        'q' => $query,
        'fields' => 'files(id, name)',
    ]);

    return count($response->files) > 0 ? $response->files[0]->id : null;
}

###############################################################################################################
###############################################################################################################


if (!function_exists('uzakGoogleSunucuyaYedekle')) {
function uzakGoogleSunucuyaYedekle($islemi_yapan, $dosya_adi_yolu, $yedekleme_gorevi, $silinecek_dosya_tipi, $uzak_sunucu_ici_dizin_adi, $google_sunucu_korunacak_yedek, $secilen_yedekleme_oneki){

    // Google Drive API istemcisi oluşturma
    $client = getClient();
    $service = new \Google\Service\Drive($client);

    $rootFolderId = 'root'; // Root klasörünün ID'si 'root' olarak belirlenir
    $sonuc_cikti_mesaji = [];

    // Eğer uzaktan sunucu içi dizin adı belirtildiyse, bu dizini oluştur veya ID'yi kontrol et
    if ($uzak_sunucu_ici_dizin_adi) {
        $parentFolderId = getFolderIdByPath($service, $uzak_sunucu_ici_dizin_adi);
    } else {
        $parentFolderId = $rootFolderId; // Eğer belirtilmediyse root klasörü kullanılır
    }

    // Dosya veya klasör yedekleme işlemi
    if (is_dir($dosya_adi_yolu)) {
        // Eğer dosya bir klasörse, klasörü yedekle
        $result = uploadFolder($service, $dosya_adi_yolu, $parentFolderId);
    } elseif (file_exists($dosya_adi_yolu)) {
        // Eğer dosya mevcutsa, dosyayı yedekle
        $result = uploadFile($service, $dosya_adi_yolu, $parentFolderId);
    } else {
        // Dosya veya dizin bulunamadı hatası
        $sonuc_cikti_mesaji[] = [
            'status' => 'error',
            'message' => '<span style="color:red;">Dosya veya dizin bulunamadı: </span>' . $dosya_adi_yolu
        ];
        return $sonuc_cikti_mesaji;
    }


    // Yükleme başarılı olduysa
    if ($result === true) {
        if($islemi_yapan){
            $sonuc_cikti_mesaji[] = [
                'status' => 'success',
                'message' => '<span style="color:green;">Google Drive Api Sunucusuna Başarıyla Yüklendi</span>'
            ];
        }else{
            $sonuc_cikti_mesaji[] = [
                'status' => 'success',
                'message' => '<span style="color:green;">Google Drive Api Sunucusuna Başarıyla Yüklendi: </span>' . basename($dosya_adi_yolu)
            ];
        }

    } else {
        // Yükleme hatası varsa sonucu döndür
        return $result;
    }

    // Sonuçları JSON formatında döndür
    return $sonuc_cikti_mesaji;
} // function uzakGoogleSunucuyaYedekle($service, $dosya_adi_yolu, $uzak_sunucu_ici_dizin_adi){
} // if (!function_exists('uzakGoogleSunucuyaYedekle')) {

##################################################################################################################################
##################################################################################################################################
##################################################################################################################################
##################################################################################################################################

// Her yerel alandan googla yükleme kodu
if(isset($_POST['googla_yukle']) && $_POST['googla_yukle'] == '1' && isset($_POST['yerel_den_secilen_dosya']) && !empty($_POST['yerel_den_secilen_dosya']) && isset($_POST['google_drive_dan_secilen_dosya_id']) && !empty($_POST['google_drive_dan_secilen_dosya_id'])) {

ob_start();
    $islemi_yapan = false;
    $yedekleme_gorevi = false;
    $silinecek_dosya_tipi = "";
    $google_sunucu_korunacak_yedek = "";
    $secilen_yedekleme_oneki = "";

    $dosya_adi_yolu = $_POST['yerel_den_secilen_dosya'];
    if($_POST['google_drive_dan_secilen_dosya_id'] == 'root'){
        $uzak_sunucu_ici_dizin_adi = "";
    }else{
        $uzak_sunucu_ici_dizin_adi = $_POST['google_drive_dan_secilen_dosya_id'];
    }


// Elle işlem
try {

     $sonuc_cikti_mesaji = uzakGoogleSunucuyaYedekle($islemi_yapan, $dosya_adi_yolu, $yedekleme_gorevi, $silinecek_dosya_tipi, $uzak_sunucu_ici_dizin_adi, $google_sunucu_korunacak_yedek, $secilen_yedekleme_oneki);

    echo json_encode($sonuc_cikti_mesaji, JSON_UNESCAPED_UNICODE);  // Mesajı JSON olarak gönder
} catch (\Google\Service\Exception $e) {
    $sonuc_cikti_mesaji[] = [
        'status' => 'error',
        'message' => '<span style="color:red;">Hata7:</span> ' . $e->getMessage()
    ];
    echo json_encode($sonuc_cikti_mesaji, JSON_UNESCAPED_UNICODE);  // Mesajı JSON olarak gönder
} catch (Exception $e) {
    $sonuc_cikti_mesaji[] = [
        'status' => 'error',
        'message' => '<span style="color:red;">Hata8:</span> ' . $e->getMessage()
    ];
    echo json_encode($sonuc_cikti_mesaji, JSON_UNESCAPED_UNICODE);  // Mesajı JSON olarak gönder
}

ob_flush();
flush();
}

function isDriveId($drivePath) {
    // Google Drive ID'si genellikle 28-34 karakter uzunluğunda alfanümerik bir dizgedir
    return preg_match('/^[a-zA-Z0-9_-]{28,34}$/', $drivePath);
}

?>