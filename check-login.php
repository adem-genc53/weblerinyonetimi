<?php 
// Bismillahirrahmanirrahim
require_once('includes/connect.php');
if(!isset($_SESSION)) 
{
session_start();
}
		
    $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
		$site_url = $protocol."://".$_SERVER["SERVER_NAME"];
		$last_link = "?last=".$_SERVER['REQUEST_URI'];
/*
if(time() - $_SESSION['last_login'] >= 1800){
    session_destroy(); // destroy session.
    header("Location: logout.php");
    die(); // See https://thedailywtf.com/articles/WellIntentioned-Destruction
    //redirect if the page is inactive for 30 minutes
}
else {        
   $_SESSION['last_login'] = time();
   // update 'last_login' to the last time a page containing this code was accessed.
}

*/
if(!empty($_SESSION['user_group'])){
  $grup = $_SESSION['user_group'];
  }else{
    $grup = 0;
  }

    // Giriş yapan kullanıcı giriş kontrol edilecek alanlarda oturumlarından herhangi biri eşleşmiyorsa
    // Tüm oturumları silerek tekrar login sayfasına yönlendirilecektir
    // Bu ayrıca kullanıcı profilinde bilgileri değiştirdiğinde yeni bilgilerle oturum açmasını sağlayacaktır
    if(isset($_SESSION['user_is_logged_in']) && $_SESSION['user_is_logged_in'] == '1'){
        $sorgu = $PDOdb->prepare("SELECT * FROM uyeler WHERE user_id=? AND user_group=? AND user_email=? AND user_name=? ");
        $sorgu->execute([$_SESSION['user_id'],$_SESSION['user_group'],$_SESSION['user_email'],$_SESSION['user_name']]);
        $user_oku = $sorgu->fetch();
            if($sorgu->rowCount() > 0){
                $_SESSION['user_id']                      = $user_oku['user_id'];
                $_SESSION['user_group']                   = $user_oku['user_group'];
                $_SESSION['user_email']                 = $user_oku['user_email'];
                $_SESSION['user_name']                      = $user_oku['user_name'];
            }else{
            unset($_SESSION['user_id'],
                $_SESSION['user_group'],
                $_SESSION['user_email'],
                $_SESSION['user_name'],
                $_SESSION['user_is_logged_in']);
            }
    }

if(isset($_SESSION['user_is_logged_in']) && ($_SESSION['user_is_logged_in'] == true) ){
	//header("location: /");
}else{
	// kullanıcıyı giriş sayfasına yönlendir
	header("location: $site_url/login.php");
	exit;
}

if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])){
	//header("location: /");
}else{
	// kullanıcıyı giriş sayfasına yönlendir
	header("location: $site_url/login.php");
	exit;
}

if(isset($_SESSION['last_login']) && !empty($_SESSION['last_login'])){
	//header("location: /");
}else{
	// kullanıcıyı giriş sayfasına yönlendir
	header("location: $site_url/login.php");
	exit;
}

if(isset($_SESSION['user_email']) && !empty($_SESSION['user_email'])){
	//header("location: /");
}else{
	// kullanıcıyı giriş sayfasına yönlendir
	header("location: $site_url/login.php");
	exit;
}

?>