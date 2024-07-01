<?php
if(session_status() == PHP_SESSION_NONE && !headers_sent()){
// Oturum adını belirleyin
session_name(str_replace('.','_',$_SERVER["SERVER_NAME"]));
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

// mevcut sayfayı kontrol et
// echo basename($_SERVER['PHP_SELF']);
if((basename($_SERVER['PHP_SELF']) == 'login.php')){

	if( (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) || 
		(isset($_SESSION['user_group']) && !empty($_SESSION['user_group'])) || 
		(isset($_SESSION['user_is_logged_in']) && !empty($_SESSION['user_is_logged_in'])) || 
		(isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) || 
		(isset($_SESSION['last_login']) && !empty($_SESSION['last_login'])) || 
		(isset($_SESSION['user_email']) && !empty($_SESSION['user_email'])) ){
		// kontrol paneli sayfasına yönlendirme
		header("location: /");
	}
	$session = array("last_login","user_name","user_email","user_group","user_id","user_is_logged_in");

foreach ($_SESSION AS $key => $value){
    if (in_array($key, $session)) {
      unset($_SESSION[$key]);
    }
  }
}
?>