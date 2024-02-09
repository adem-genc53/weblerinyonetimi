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

    //echo '<pre>' . print_r($_POST, true) . '</pre>';
    //exit;

    function showSize($size_in_bytes) {
        if ($size_in_bytes >= 1073741824) {
            $size_in_bytes = number_format($size_in_bytes / 1073741824, 2) . ' GB';
        } elseif ($size_in_bytes >= 1048576) {
            $size_in_bytes = number_format($size_in_bytes / 1048576, 2) . ' MB';
        } elseif ($size_in_bytes >= 1024) {
            $size_in_bytes = number_format($size_in_bytes / 1024, 2) . ' KB';
        } elseif ($size_in_bytes > 1) {
            $size_in_bytes = $size_in_bytes . ' Bayt';
        } elseif ($size_in_bytes == 1) {
            $size_in_bytes = $size_in_bytes . ' Bayt';
        } else {
            $size_in_bytes = '0 Bayt';
        }
        return $size_in_bytes;
    }

    $folderId = isset($_POST['dir']) ? $_POST['dir'] : 'root';
    $results = $service->files->listFiles(array(
        'q' => "'$folderId' in parents",
        'orderBy' => 'name'
    ));

    $drive_dizinler_arr = [];
    $drive_dosyalar_arr = [];
    foreach ($results->getFiles() as $file) {
        $optpParams = array('fields' => "size");
        $response = $service->files->get($file->getId(), $optpParams);
        if($file->getMimeType() == 'application/vnd.google-apps.folder'){
            $drive_dizinler_arr[ $file->getId() ][ showSize($response->size) ] = $file->getName();
        }elseif($file->getMimeType() != 'application/vnd.google-apps.folder'){
            $drive_dosyalar_arr[ $file->getId() ][ showSize($response->size) ] = $file->getName();
        }
    }

    function emptyDir($dirid, $service) {
        $results = $service->files->listFiles(array(
            'q' => "'$dirid' in parents"
        ));
        return count($results->getFiles());
    }

    $list = '<ul id="uzak" class="filetree" style="display: none;">';
    // Önce klasörleri gruplandıralım
    foreach( $drive_dizinler_arr AS $id => $arr_devam ) {
        foreach($arr_devam AS $boyutu => $dizin_adi){
            if( emptyDir($id, $service) == '0' ){ // Dizin boşmu değilmi
                $list .= '<li class="folder collapsed"><a href="#" rel="' . $id . '" adi="' . $dizin_adi . '">' . $dizin_adi . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
            }else{
                $list .= '<li class="folder_plus collapsed"><a href="#" rel="' . $id . '" adi="' . $dizin_adi . '">' . $dizin_adi . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
            }
        }
    }

    // Sonra tüm dosyaları gruplandıralım
    foreach( $drive_dosyalar_arr AS $id => $devam_arr ) {
        foreach($devam_arr AS $boyutu => $dosya_adi){
            $ext = preg_replace('/^.*\./', '', $dosya_adi);
            $list .= '<li class="file ext_' . $ext . '"><a href="#" rel="' . $id . '" adi="' . $dosya_adi . '">' . $dosya_adi . '<span style="float: right;color: black;padding-right: 10px;">'.$boyutu.'</span></a></li>';
        }
    }

    $list .= '</ul>';	
    echo $list;

?>