<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';

class RememberMe {
    private $PDOdb;

    public function __construct($PDOdb) {
        $this->pdo = $PDOdb;
    }

    // COOKIE VARSA USER ID İLE TOKENİ AYIRIYORUZ
    public function checkRememberMe() {
        if (isset($_COOKIE['beni_hatirla'])) {
            list($userId, $token) = explode(':', $_COOKIE['beni_hatirla']);
            // COOKIE DEKİ TOKEN İLE VERİTABANI TOKEN AYNI İSE
            if ($this->validateRememberMeToken($userId, $token)) {
                // KULLANICININ SESSION OTURUMU OLUŞTUR
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
            // Geçersiz çerez olduğunda temizle
            $this->logout();
        }
    }

    // COOKIE VE SESSION GEÇERLİ DEĞİL İSE COOKIE VE SESSIONU TEMİZLEYİP LOGIN.PHP SAYFASINA YÖNLENDİRİYORUZ
    public function logout() {
        $defaultScheme = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        if($defaultScheme == 'https'){
            setcookie('beni_hatirla', '', time() - 3600, "/", "", true, true);
        }else{
            setcookie('beni_hatirla', '', time() - 3600, "/", "", false, true);
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
    if (isset($_COOKIE['beni_hatirla'])) {

        // FONKSİYONU ÇAĞIR VE COOKIE TOKENİN GEÇERLİ OLUP OLMADIĞINI KONTROL EDİYORUZ
        $rememberMe->checkRememberMe();

        // COOKIE MEVCUT DEĞİL ANCAK SESSION MEVCUT İSE 
    } elseif (!isset($_COOKIE['beni_hatirla']) && isset($_SESSION['user_is_logged_in']) && $_SESSION['user_is_logged_in'] == true) {
        $userId = $_SESSION['user_id'] ?? null;
        // KULLANICININ SESSION OTURUMU OLUŞTUR
        $rememberMe->refreshUserSession($userId);

        // COOKIE VE SESSION MEVCUT DEĞİL LOGIN.PHP SAYFASINA YÖNLENDİRİYORUZ
    } else {
        // KULLANICIYI GİRİŞ SAYFASINA YÖNLENDİR
        header("Location: $site_url/login.php".$last_link);
        exit;
    }

/************************************************************************************************************************************************* */

?>