<?php 
if(session_status() == PHP_SESSION_NONE && !headers_sent()){
// Oturum adını belirleyin
if (isset($_SERVER['SERVER_NAME'])) {
    $serverName = $_SERVER['SERVER_NAME'];
} elseif (isset($_SERVER['HTTP_HOST'])) {
    $serverName = $_SERVER['HTTP_HOST'];
} elseif (getenv('SERVER_NAME') !== null) {
    $serverName = getenv('SERVER_NAME');
}else{
    $serverName = "webleryonetimi";
}
session_name(str_replace('.','_',$serverName));
// Oturumu başlatın
session_start();
}

// Oturum kimliğini yenileme süresini belirleyin (örneğin 15 dakika)
$regenerate_time = 15 * 60; // 15 dakika
// Şu anki zaman ve oturum başlangıç zamanını kontrol edin
if (isset($_SESSION['start_time']) && (time() - $_SESSION['start_time'] > $regenerate_time) && isset($_SESSION)) {
    // Oturum kimliğini yenileyin
    session_regenerate_id(true);
    // Yeni başlangıç zamanını ayarlayın
    $_SESSION['start_time'] = time();
}
?>