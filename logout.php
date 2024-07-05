<?php
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';

    $defaultScheme = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
    if($defaultScheme == 'https'){
        setcookie('beni_hatirla', '', time() - 3600, "/", "", true, true);
    }else{
        setcookie('beni_hatirla', '', time() - 3600, "/", "", false, true);
    }

    // Tüm oturum değişkenlerini temizle
    $_SESSION = [];

    // Oturumu sonlandır
    session_destroy();

header("location: /");
?>