<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';

class RememberMe {
    private $PDOdb;

    public function __construct($PDOdb) {
        $this->pdo = $PDOdb;
    }

    // COOKIE VARSA USER ID İLE TOKETİ AYIRIYORUZ
    public function checkRememberMe() {
        if (isset($_COOKIE['webyonetimi_beni'])) {
            list($userId, $token) = explode(':', $_COOKIE['webyonetimi_beni']);
            if ($this->validateRememberMeToken($userId, $token)) {
                //$_SESSION['user_id'] = $userId;
                //$_SESSION['user_is_logged_in'] = true;
                $this->refreshUserSession($userId);
                return true;
            } else {
                // Geçersiz çerez olduğunda temizle
                $this->logout();
            }
        }
        return false;
    }

    // COOKIE DEKİ TOKEN İLE VERİTABANI TOKEN AYNI MI KONTROL EDİYORUZ
    private function validateRememberMeToken($userId, $token) {
        $stmt = $this->pdo->prepare("SELECT user_id FROM uyeler WHERE user_id = ? AND remember_me_token = ? AND token_expiry > NOW()");
        $stmt->execute([$userId, $token]);
        return $stmt->fetchColumn();
    }

    // COOKIE DEKİ TOKEN İLE VERİTABANI TOKEN AYNI OLDUĞUNDAN OTURUMU GÜNCELLİYORUZ
    // VEYA
    // COOKIE MEVCUT DEĞİL AMA SESSİON MEVCUT KULLANICI ID İLE KULLANICI OTURUMUNU GÜNCELLİYORUZ
    public function refreshUserSession($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM uyeler WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id']            = $user['user_id'];
            $_SESSION['user_group']         = $user['user_group'];
            $_SESSION['user_email']         = $user['user_email'];
            $_SESSION['user_name']          = $user['user_name'];
            $_SESSION['user_is_logged_in']  = true;
        } else {
            $this->logout();
        }
    }

    // COOKIE VE SESSION GEÇERLİ DEĞİL İSE COOKIE VE SESSION U TEMİZLEYİ LOGIN.PHP SAYFASINA YÖNLENDİRİYORUZ
    public function logout() {
        $defaultScheme = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        if($defaultScheme == 'https'){
            setcookie('webyonetimi_beni', '', time() - 3600, "/", "", true, true);
        }else{
            setcookie('webyonetimi_beni', '', time() - 3600, "/", "", false, true);
        }
        unset($_SESSION['user_id'], $_SESSION['user_group'], $_SESSION['user_email'], $_SESSION['user_name'], $_SESSION['user_is_logged_in']);
    }
}

/************************************************************************************************************************************************* */

    $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
    $site_url = $protocol."://".$_SERVER["SERVER_NAME"];
    $last_link = "?last=".$_SERVER['REQUEST_URI'];

/************************************************************************************************************************************************* */

    $rememberMe = new RememberMe($PDOdb);

/************************************************************************************************************************************************* */

    // COOKIE MEVCUT İSE
    if (isset($_COOKIE['webyonetimi_beni'])) {

        // FONKSİYONU ÇAĞIR VE COOKIE TOKENİN GEÇERLİ OLUP OLMADIĞINI KONTROL EDİYORUZ
        $rememberMe->checkRememberMe();

        // COOKIE MEVCUT DEĞİL ANCAK SESSION MEVCUT İSE 
    } elseif (!isset($_COOKIE['webyonetimi_beni']) && $_SESSION['user_is_logged_in'] && $_SESSION['user_is_logged_in'] == true) {
        $userId = $_SESSION['user_id'] ?? null;
        $rememberMe->refreshUserSession($userId);

        // COOKIE VE SESSION MEVCUT DEĞİL LOGIN.PHP SAYFASINA YÖNLENDİRİYORUZ
    } else {
        // Kullanıcıyı giriş sayfasına yönlendir
        header("Location: $site_url/login.php".$last_link);
        exit;
    }

/************************************************************************************************************************************************* */

/*
    // Giriş yapan kullanıcı giriş kontrol edilecek alanlarda oturumlarından herhangi biri eşleşmiyorsa
    // Tüm oturumları silerek tekrar login sayfasına yönlendirilecektir
    // Bu ayrıca kullanıcı profilinde bilgileri değiştirdiğinde yeni bilgilerle oturum açmasını sağlayacaktır
    if(isset($_SESSION['user_is_logged_in']) && $_SESSION['user_is_logged_in'] == '1'){
        $sorgu = $PDOdbdb->prepare("SELECT * FROM uyeler WHERE user_id=? AND user_group=? AND user_email=? AND user_name=? ");
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
*/
/*
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
*/
?>