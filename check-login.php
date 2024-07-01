<?php 
// Bismillahirrahmanirrahim
require_once('includes/connect.php');
#####################################################################################################################################

#####################################################################################################################################

class BeniHatirla {
    private $PDOdb;

    public function __construct(PDO $PDOdb ) {
        $this->PDOdb = $PDOdb;

        // Oturumu başlat
        if(session_status() == PHP_SESSION_NONE && !headers_sent())
        {
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
session_name(str_replace('.','_',$serverName)); // Bu oturum name oturum_guncelle.php deki ile aynı olması gerekiyor
            session_start();
            session_regenerate_id(true);
        }

        // Oturum çalıntısı için CSRF koruması sağla
        //$this->setCSRFToken();
    }

// COOKIE VARSA USER ID İLE TOKENİ AYIRIYORUZ
    public function checkRememberMe(): bool {
        if (isset($_COOKIE['beni_hatirla'])) {
            list($userId, $token) = explode(':', $_COOKIE['beni_hatirla']);
            // COOKIE DEKİ TOKEN İLE VERİTABANI TOKEN AYNI İSE
            if ($this->validateRememberMeToken($userId, $token)) {
                // KULLANICININ SESSION OTURUMU OLUŞTUR
                $this->refreshUserSession($userId);
                $this->updateRememberMeToken($userId);
                return true;
            } else {
                // Geçersiz çerez olduğunda temizle
                $this->logout();
            }
        }
        return false;
    }

// COOKIE DEKİ TOKEN İLE VERİTABANI TOKEN AYNI MI KONTROL EDİYORUZ
    private function validateRememberMeToken($userId, $token): bool {
        $stmt = $this->PDOdb->prepare("SELECT user_id FROM uyeler WHERE user_id = ? AND remember_me_token = ? AND token_expiry > NOW()");
        $stmt->execute([$userId, $token]);
        return $stmt->fetchColumn();
    }

// KULLANICI COOKIE İLE OTURUM AÇTIĞINDA COOKIE TOKENİ GÜNCELLE
    private function updateRememberMeToken($userId): bool {
        $newToken = bin2hex(random_bytes(16));
        $newExpiry = time() + (86400 * 30); // 30 gün
        $defaultScheme = isset($_SERVER["HTTPS"]) ? 'https' : 'http';

        if($defaultScheme == 'https'){
            setcookie('beni_hatirla', "$userId:$newToken", $newExpiry, "/", "", true, true);
        }else{
            setcookie('beni_hatirla', "$userId:$newToken", $newExpiry, "/", "", false, true);
        }
        $this->saveRememberMeTokenToDatabase($userId, $newToken, $newExpiry);
    return true;
    }

// COOKIE TEOKENİ KULLANICI HESABINA KAYDET
    private function saveRememberMeTokenToDatabase($userId, $token, $expiry) {
        $stmt = $this->PDOdb->prepare("UPDATE uyeler SET remember_me_token = ?, token_expiry = FROM_UNIXTIME(?) WHERE user_id = ?");
        $stmt->execute([$token, $expiry, $userId]);
    }

// KULLANICI OTURUMU OTOMATİKMAN AÇ
    public function refreshUserSession($userId): bool {

// BENİ HATIRLA COOKIE'Sİ MEVCUT OLAN KULLANICININ ID'Sİ İLE KULLANICI BİLGİLERİNİ SAĞLA
            $sql = "SELECT * FROM uyeler ";
            $sql .= "WHERE user_id=?";
            $result = $this->PDOdb->prepare($sql);
            $result->execute(array($userId));
            $count = $result->rowCount();
            $user = $result->fetch(PDO::FETCH_ASSOC);
        if($count == 1){

// BENİ HATIRLA COOKIE MEVCUT OLDUĞUNDAN OTOMATİKMAN OTURUM SESSION'LARI OLUŞTURARAK OTURUMU AÇ
            $_SESSION['user_id']            = $user['user_id'];
            $_SESSION['user_group']         = $user['user_group'];
            $_SESSION['user_email']         = $user['user_email'];
            $_SESSION['user_name']          = $user['user_name'];
            $_SESSION['user_is_logged_in']  = true;
            $_SESSION['start_time']         = time();

            // Kullanıcının ID'sini oturumdan alın
            $user_id = $_SESSION['user_id'];
            $log_in_from = "Hatırla ile";
            $current_time = time(); // Unix zaman damgası

            // Son giriş zamanını kontrol etme
            $last_login_sql = "SELECT login_time FROM user_logins WHERE user_id = :user_id ORDER BY login_time DESC LIMIT 1";
            $last_login_stmt = $this->PDOdb->prepare($last_login_sql);
            $last_login_stmt->bindParam(':user_id', $user_id);
            $last_login_stmt->execute();
            $last_login = $last_login_stmt->fetch(PDO::FETCH_ASSOC);

            if ($last_login) {
                $last_login_time = (int)$last_login['login_time']; // Unix zaman damgası olarak al
                // Eğer son giriş zamanı ile şimdiki zaman arasında 60 saniyeden az fark varsa yeni giriş eklemeyin
                if (($current_time - $last_login_time) > 60) {
                    // Yeni giriş kaydı ekleme
                    $logins_sql = "INSERT INTO user_logins (user_id, log_in_from, login_time) VALUES (:user_id, :log_in_from, :login_time)";
                    $logins = $this->PDOdb->prepare($logins_sql);
                    $logins->bindParam(':user_id', $user_id);
                    $logins->bindParam(':log_in_from', $log_in_from);
                    $logins->bindParam(':login_time', $current_time);
                    $logins->execute();
                    //echo "Giriş kaydedildi.";
                } else {
                    //echo "Aynı dakika içinde birden fazla giriş yapılamaz.";
                }
            } else {
                // Kullanıcının hiç giriş kaydı yoksa ilk kaydı ekleyin
                $logins_sql = "INSERT INTO user_logins (user_id, log_in_from, login_time) VALUES (:user_id, :log_in_from, :login_time)";
                $logins = $this->PDOdb->prepare($logins_sql);
                $logins->bindParam(':user_id', $user_id);
                $logins->bindParam(':log_in_from', $log_in_from);
                $logins->bindParam(':login_time', $current_time);
                $logins->execute();
                //echo "Giriş kaydedildi.";
            }

            // Kullanıcının toplam giriş sayısını kontrol etme
            $count_sql = "SELECT COUNT(*) FROM user_logins WHERE user_id = :user_id";
            $count_stmt = $this->PDOdb->prepare($count_sql);
            $count_stmt->bindParam(':user_id', $user_id);
            $count_stmt->execute();
            $total_logins = $count_stmt->fetchColumn();

            // Eğer toplam giriş sayısı 10'dan büyükse eski kayıtları sil
        if ($total_logins > 10) {
            // Eski giriş kayıtlarını silmek için SQL sorgusu
            $delete_sql = "DELETE FROM user_logins WHERE user_id = :user_id AND id NOT IN (
                SELECT id FROM (
                    SELECT id FROM user_logins WHERE user_id = :uid ORDER BY login_time DESC LIMIT 10
                ) as temp
            )";
            $delete = $this->PDOdb->prepare($delete_sql);
            $delete->bindParam(':user_id', $user_id);
            $delete->bindParam(':uid', $user_id);
            $delete->execute();
        }

        return true;

        }

    }

    private function logout(): void {
        // Tüm oturum değişkenlerini temizle
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
    }

}

    // Sınıf örneğini oluştur
    $benihatirla = new BeniHatirla($PDOdb);

    // COOKIE MEVCUT İSE
    if (isset($_COOKIE['beni_hatirla']) && !isset($_SESSION['user_is_logged_in'])) {

        // FONKSİYONU ÇAĞIR VE COOKIE TOKENİN GEÇERLİ OLUP OLMADIĞINI KONTROL EDİYORUZ
        $benihatirla->checkRememberMe();

        // COOKIE MEVCUT DEĞİL ANCAK SESSION MEVCUT İSE 
    } elseif (!isset($_COOKIE['beni_hatirla']) && isset($_SESSION['user_is_logged_in']) && $_SESSION['user_is_logged_in'] == true) {
        $userId = $_SESSION['user_id'] ?? null;
        // KULLANICININ SESSION OTURUMU OLUŞTUR
        $benihatirla->refreshUserSession($userId);

        // COOKIE VE SESSION MEVCUT DEĞİL LOGIN.PHP SAYFASINA YÖNLENDİRİYORUZ
    } elseif (!isset($_COOKIE['beni_hatirla']) && !isset($_SESSION['user_is_logged_in'])) {

        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $site_url = $protocol."://".$_SERVER["SERVER_NAME"];
        $last_link = "?last=".$_SERVER['REQUEST_URI'];

        // KULLANICIYI GİRİŞ SAYFASINA YÖNLENDİR
        header("Location: $site_url/login.php".$last_link);

    }  
?>