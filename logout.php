<?php
if(session_status() == PHP_SESSION_NONE && !headers_sent()) {
  session_start();
}

//session_destroy();

    $defaultScheme = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
    if($defaultScheme == 'https'){
        setcookie('webyonetimi_beni', '', time() - 3600, "/", "", true, true);
    }else{
        setcookie('webyonetimi_beni', '', time() - 3600, "/", "", false, true);
    }

$session = array("last_login","user_name","user_email","user_group","user_id","user_is_logged_in");

foreach ($_SESSION AS $key => $value){
    if (in_array($key, $session)) {
      unset($_SESSION[$key]);
    }
  }
header("location: /");
?>