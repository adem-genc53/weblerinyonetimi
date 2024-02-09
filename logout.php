<?php
session_start();
//session_destroy();

$session = array("last_login","user_name","user_email","user_group","user_id","user_is_logged_in");

foreach ($_SESSION AS $key => $value){
    if (in_array($key, $session)) {
      unset($_SESSION[$key]);
    }
  }
header("location: /");
?>