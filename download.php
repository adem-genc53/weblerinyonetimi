<?php
// Bismillahirrahmanirrahim
session_start();
ini_set('memory_limit', '-1');
require_once('includes/connect.php');
//require_once('check-login.php');
require_once("includes/turkcegunler.php");

    if(isset($_GET['file']))
    {
    //dosya adını oku
    $filename = $_GET['file'];
    if(substr(strrchr($filename,'.'),1) == "zip"){
        $dosyaveyolu = ZIPDIR.$filename;
    }elseif(substr(strrchr($filename,'.'),1) == "sql"){
        $dosyaveyolu = BACKUPDIR."/".$filename;
    }elseif(substr(strrchr($filename,'.'),1) == "gz"){
        $dosyaveyolu = BACKUPDIR."/".$filename;
    }
    //Dosyanın var olup olmadığını kontrol edin
    if(file_exists($dosyaveyolu)) {

    //Define header information
    //Başlık bilgilerini tanımla
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    //header("Content-Transfer-Encoding: Binary");
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: 0");
    header('Content-Disposition: attachment; filename="'.basename($filename).'"');
    header('Content-Length: ' . filesize($dosyaveyolu));
    header('Pragma: public');

    //Clear system output buffer
    //Sistem çıkış tamponunu temizle
    //flush();

    //Read the size of the file
    //Dosyanın boyutunu oku
    readfile($dosyaveyolu);

    //Terminate from the script
    //Komut dosyasından sonlandır
    die();
    }
    else{
    echo "Dosya bulunamıyor.";
    }
    }
    else
    echo "Dosya adı tanımlı değil.";
?>