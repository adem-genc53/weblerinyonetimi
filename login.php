<?php 
require_once('./includes/connect.php');

class SecureLogin {
    private $PDOdb;
    private $user_email;
    private $password;
    private $remember_me;
    private $csrf_token;

    public function __construct(PDO $PDOdb, ) {
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
        $this->setCSRFToken();
    }

    public function login(string $user_email, string $password, string $csrf_token, $remember_me = null): bool {

        if(empty($user_email)){
            $this->errors[] = "E-Posta alanı Zorunludur";
            return false;
        }else if(!filter_var($user_email, FILTER_VALIDATE_EMAIL)){
            $this->errors[] = "E-Posta geçersiz";
            return false;
        }else if(empty($password)){
            $this->errors[] = "Şifre alanı Zorunludur";
            return false;
        }

            // Giriş Kullanıcı Bilgilerini Kontrol Edin
            $sql = "SELECT * FROM uyeler ";
            if(filter_var($user_email, FILTER_VALIDATE_EMAIL)){
                $sql .= "WHERE user_email=?";
            }
            $result = $this->PDOdb->prepare($sql);
            $result->execute(array($user_email));
            $count = $result->rowCount();
            $res = $result->fetch(PDO::FETCH_ASSOC);
            if($count == 1){
                // Girilen şifre ile veritabanındaki şifreyi karşılaştır
                if(password_verify($password, $res['user_password_hash'])){

                        session_destroy();

                        $_SESSION['user_is_logged_in']          = true;
                        $_SESSION['user_id']                    = $res['user_id'];
                        $_SESSION['user_group']                 = $res['user_group'];
                        $_SESSION['user_email']                 = $res['user_email'];
                        $_SESSION['user_name']                  = $res['user_name'];
                        $_SESSION['start_time']                 = time();

                    if(isset($csrf_token) && $remember_me !== null){
                        $beni_token = $csrf_token;
                        $userId = $res['user_id'];
                        $expiry = time() + (86400 * 30); // 30 gün
                        $defaultScheme = isset($_SERVER["HTTPS"]) ? 'https' : 'http';

                        if($defaultScheme == 'https'){
                            setcookie('beni_hatirla', "$userId:$beni_token", $expiry, "/", "", true, true);
                        }else{
                            setcookie('beni_hatirla', "$userId:$beni_token", $expiry, "/", "", false, true);
                        }
                        $stmt = $this->PDOdb->prepare("UPDATE uyeler SET remember_me_token = ?, token_expiry = ? WHERE user_id = ?");
                        $stmt->execute([$beni_token, $expiry, $userId]);
                    }

                        // Kullanıcının ID'sini oturumdan alın
                        $user_id = $_SESSION['user_id'];
                        $log_in_from = "Giriş ile";
                        $current_time = time(); // Unix zaman damgası
                        $logins_sql = "INSERT INTO user_logins (user_id, log_in_from, login_time) VALUES (:user_id, :log_in_from, :login_time)";
                        $logins = $this->PDOdb->prepare($logins_sql);
                        $logins->bindParam(':user_id', $user_id);
                        $logins->bindParam(':log_in_from', $log_in_from);
                        $logins->bindParam(':login_time', $current_time);
                        $logins->execute();

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
                }else{
                    $this->errors[] = "E-posta veya Şifre Hatalı";
                }
            }else{
                $this->errors[] = "E-posta veya Şifre Hatalı";
            }
    return false;
    }

    public function logout(): void {
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

    public function isLoggedIn(): bool {
        // Oturumda kullanıcı kimliği var mı kontrol et
        return isset($_SESSION['user_id']);
    }

    private function setCSRFToken(): void {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function getCSRFToken(): string {
        return $_SESSION['csrf_token'];
    }

    public function validateCSRFToken(string $token): bool {
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function getErrors(): array {
        return $this->errors;
    }

}

    // Sınıf örneğini oluştur
    $secureLogin = new SecureLogin($PDOdb);

    // Oturum kontrolü yap
    if ($secureLogin->isLoggedIn()) {
                // Kullanıcı oturumda değilse, giriş sayfasına yönlendir
                //header('Location: login.php');
                //exit();
        // Kullanıcı girişi başarılı ise son sayfasına yönlendir
        if(isset($_GET['last']) && !empty($_GET['last'])){
            header("location: ".$_GET['last']." ");
        }else{
            header("location: /");
        }
    }elseif (isset($_COOKIE['beni_hatirla'])) {
        require_once("check-login.php");
        if(isset($_GET['last']) && !empty($_GET['last'])){
            header("location: ".$_GET['last']." ");
        }else{
            header("location: /");
        }
    }


// Kullanıcı girişini kontrol etmek için
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ÇIKIŞ POST GÖNDERİLDİĞİNDE OTURUMU SONLANDIRIR
    if (isset($_POST["logout"])) {
        $secureLogin->logout();
    } else {

        require_once('./recaptcha.php');
        if(empty($robotdegil)){

            $errors[] = '"Ben Robot Değilim" Kutuyu Seçmelisiniz.';

        }else
        if(empty(filter_input(INPUT_POST, 'user_email'))){

            $errors[] = 'E-Posta alanı Zorunludur';

        }else
        if(!filter_var(filter_input(INPUT_POST, 'user_email'), FILTER_VALIDATE_EMAIL)){

            $errors[] = 'E-Posta geçersiz';

        }else
        if(empty(filter_input(INPUT_POST, 'user_password'))){

            $errors[] = 'Şifre alanı Zorunludur';

        }else{

            $user_email         = $_POST['user_email'];
            $password           = $_POST['user_password'];
            $remember_me        = $_POST['remember_me'] ?? "";
            $csrf_token         = $_SESSION['csrf_token'];

        // Kullanıcı giriş işlemini gerçekleştir
        if ($secureLogin->login($user_email, $password, $csrf_token, $remember_me)) {
            //echo "Giriş başarılı!";
            $errors[] = 'Giriş başarılı!';
            // Kullanıcı girişi başarılı ise son sayfasına yönlendir
            if(isset($_GET['last']) && !empty($_GET['last'])){
                header("location: ".$_GET['last']." ");
            }else{
                header("location: /");
            }
        } else {
            // Hata mesajlarını almak için:
            $errors = $secureLogin->getErrors();
        }
    }
    }
} // if ($_SERVER['REQUEST_METHOD'] === 'POST') {

##########################################################################################################

##########################################################################################################
include('includes/header.php');
?>
<div class="hold-transition login-page">
<div class="login-box">
<!-- /.login-logo -->
<div class="card">
    <div class="card-body login-card-body">
    <p class="login-box-msg">Giriş Yap</p>
        <?php
            if(!empty($errors)){
                echo "<div class='alert alert-danger'>";
                foreach ($errors as $error) {
                    echo "<span class='glyphicon glyphicon-remove'></span>&nbsp;".$error."<br>";
                }
                echo "</div>";
            }
        ?>
    <form role="form" method="post">
        <input type="hidden" name="csrf_token" value="<?php //echo $token; ?>">
        <div class="input-group mb-3">
        <input class="form-control" placeholder="E-POSTA ADRESİ" name="user_email" type="email" autofocus value="<?php if(isset($_POST['user_email'])){ echo $_POST['user_email']; } ?>" required />
        <div class="input-group-append">
            <div class="input-group-text">
            <span class="fas fa-envelope"></span>
            </div>
        </div>
        </div>

        <div class="input-group mb-3">
        <input class="form-control" id="user_password" placeholder="ŞİFRE" name="user_password" type="password" autocomplete="off" required />
        <div class="input-group-append">
            <div class="input-group-text">
            <span class="input-group-addon"><span toggle="#user_password" class="fa fa-fw fa-eye field-icon toggle-password" title="Şifreyi göster"></span></span>
            </div>
            <div class="input-group-text">
            <span class="fas fa-lock"></span>
            </div>
        </div>
        </div>

        <div class="input-group mb-3">
        <div class="g-recaptcha" data-sitekey="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"></div>
        </div>

        <div class="row">
        <div class="col-8">
            <div class="icheck-primary">
            <input type="checkbox" id="remember" name="remember_me" value="1">
            <label for="remember">
                Beni Hatırla
            </label>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
        </div>
        <!-- /.col -->
        </div>
    </form>

    <p class="mb-1">
        <a href="forgot-password.php">Şifremi Unuttum</a>
    </p>
    <p class="mb-0">
        <a href="register.php" class="text-center">Yeni üyelik kaydı</a>
    </p>
    </div>
    <!-- /.login-card-body -->
</div>
</div>
<!-- /.login-box -->
</div></div>


<script>
$(document).ready(function () {        
    var width = $('.g-recaptcha').parent().width();
    //console.log(width);
    //if (width < 322) {
        var scale = width / 302;
        $('.g-recaptcha').css('transform', 'scale(' + scale + ')');
        $('.g-recaptcha').css('-webkit-transform', 'scale(' + scale + ')');
        $('.g-recaptcha').css('transform-origin', '0 0');
        $('.g-recaptcha').css('-webkit-transform-origin', '0 0');
    //}
});

    $(".toggle-password").click(function() {
    $(this).toggleClass("fa-eye fa-eye-slash");
    var input = $($(this).attr("toggle"));
    if (input.attr("type") == "password") {
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
    });
</script>
<script type="text/javascript">
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "gorev.php", true);
    xhr.send();
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
