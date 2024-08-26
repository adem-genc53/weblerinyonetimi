<?php 
    //echo '<pre>' . print_r($_POST, true) . '</pre>';
    if(isset($_POST['mobil_pc'])){
        header("location: /");
        if(isset($_POST['gecis']) && $_POST['gecis'] == 2){
            setcookie("mobil_pc_gecis", 'mobil', time() + 3600 , "/", ".antenfiyati.com");
        }else{
            setcookie("mobil_pc_gecis", 'pc', time() + 3600 , "/", ".antenfiyati.com");
        }
    }

    require(dirname(dirname(__FILE__))."/MobileDetect.php");
    $mobilversiyon = false;
    $detect = new \Detection\MobileDetect;
    $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
    // Mobil veya Tabletten masaüstü versiyon görüntülerken ve mobil_pc_gecis cookie belirlenmemiş ise Mobil versiyona yönlendir
    if( ($deviceType=='tablet' OR $deviceType=='phone') && !isset($_COOKIE['mobil_pc_gecis']) ){
        //header("Location: https://m.webyonetimi.antenfiyati.com");
    }else // Masaüstü bilgisayarda masaüstü versiyon görüntülerken Mobil versiyona geç kutu seçildiğinde Mobil versiyona yönlendir
          // Bu durum masaüstü bilgisayardan Mobil versiyona geçmek istenildiğinde
    if( (isset($_COOKIE['mobil_pc_gecis']) && $_COOKIE['mobil_pc_gecis']=='mobil') ){
        //header("Location: https://m.webyonetimi.antenfiyati.com");
    }
    
    if( (isset($_COOKIE['mobil_pc_gecis']) && $_COOKIE['mobil_pc_gecis']=='pc') ){
        $mobilversiyon = true;
    }
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Web Siteler Dizinleri ve Veritabanıları Yedekleme Yönetimi</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">

    <!-- Google Font: Source Sans Pro -->

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="css/adminlte.min.css">
    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-1.9.1.js"></script>
    <link rel="stylesheet" type="text/css" href="jswindow/jswindow.css"/>
<?php 
    if($mobilversiyon){
        echo "<script>$(document).ready(function () { $('#mobil-versiyon').show(); });</script>";
    }else{
        echo "<script>$(document).ready(function () { $('#mobil-versiyon').hide(); });</script>";
    }
?>
</head>
<!--
`body` tag options:

  Apply one or more of the following classes to to the body tag
  to get the desired effect

  * sidebar-collapse
  * sidebar-mini
-->

<body class="sidebar-mini control-sidebar-slide-open text-sm" style="height: auto;">
<!--<body class="sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed control-sidebar-slide-open dark-mode text-sm" style="height: auto;">-->
    <div class="wrapper">
        <div id="jswindow_website_cerceve">