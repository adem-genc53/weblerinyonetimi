<?php 
// Bismillahirrahmanirrahim
if(session_status() == PHP_SESSION_NONE && !headers_sent()){
// Oturum adını belirleyin
if (isset($_SERVER['SERVER_NAME']) && !is_int($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
    $serverName = str_replace('.','_', $_SERVER['SERVER_NAME']);
} elseif (isset($_SERVER['HTTP_HOST']) && !is_int($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
    $serverName = str_replace('.','_', $_SERVER['HTTP_HOST']);
} elseif (getenv('SERVER_NAME') !== null && !is_int(getenv('SERVER_NAME')) && !empty(getenv('SERVER_NAME'))) {
    $serverName = str_replace('.','_', getenv('SERVER_NAME'));
}else{
    $serverName = str_replace('.','_', 'webleryonetimi');
}
session_name($serverName);
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