<?php 
// Bismillahirrahmanirrahim
require_once('if-loggedin.php');

class Login {

    private     $PDOdb                      = null;                     // veritabanı bağlantısı
    private     $user_email                 = "";                       // user's name
    private     $user_name   = "";
    private     $ss_id                      = "";
    private     $ss_code                    = "";
    private     $password                   = "";
    private     $confirm_password           = "";                       // kullanıcının karma ve gizli şifresi
    private     $user_password_hash         = "";                       // kullanıcının karma tekrarlama ve gizli şifresi
    private     $user_group                 = "";                       // kullanıcının grup ID
    private     $user_is_logged_in          = false;                    // giriş durumu 

    public      $errors                     = array();                  // hata mesajlarının toplanması
    public      $messages                   = array();                  // başarı / tarafsız mesajların toplanması
    public      $on_icerik_oku_ust          = "";
    public      $on_icerik_oku_orta         = "";
    public      $on_icerik_oku_alt          = "";
    public      $misafir_bayi_username      = "";
    public      $misafir_bayi_password      = "";
    public      $misafir_bayi_bilgileri_goster  = "";
    public      $misafir_bayi_izni          = "";

    public function __construct() {
        
        // session oluştur veya oku
    if(!isset($_SESSION))
    {
    session_start();
    }

        // kullanıcı oturumu kapatmayı denediyse
        if (isset($_POST["logout"])) {
        
            $this->doLogout();
        
        // kullanıcının sunucuda aktif bir oturumu varsa            
        }elseif (!empty($_SESSION['user_email']) && ($_SESSION['user_is_logged_in'] == 1)) {
        
            $this->loginWithSessionData();                
        
        // kullanıcı bir giriş formu gönderdiyse
        }elseif (isset($_POST["csrf_token"])) {
        
            $this->loginWithPostData();
            
        }elseif (isset($_POST['ss_code']) AND isset($_POST['ss_id'])){

            $this->UserPasswordReset();

        }

    } //public function __construct() {


        // Session(oturum) veri ile giriş
        private function loginWithSessionData() {
        
            // giriş durumunu true olarak ayarlayın, çünkü bunu az önce kontrol ettik:
            // !empty($_SESSION['user_email']) && ($_SESSION['user_logged_in'] == 1)
            // when we called this method (in the constructor)
            $this->user_is_logged_in = true;
            
        }

        private function loginWithPostData() {
                
            if(isset($_POST) & !empty($_POST)){

                include("recaptcha.php");
                if(empty($robotdegil)){
                    $this->errors[] = '"Ben Robot Değilim" Kutuyu Seçmelisiniz.';
                }

                // PHP Form Doğrulamaları
                if(empty(filter_input(INPUT_POST, 'user_email'))){ 
                    $this->errors[] = "E-Posta alanı Zorunludur";
                }
                if(empty(filter_input(INPUT_POST, 'user_password'))){ 
                    $this->errors[] = "Şifre alanı Zorunludur";
                }
                // CSRF Token Doğrulaması
                if(isset($_POST['csrf_token'])){
                    if($_POST['csrf_token'] === $_SESSION['csrf_token']){
                    }else{
                        $this->errors[] = "CSRF Token Doğrulamasında Sorun";
                    }
                }
                //CSRF Token Zaman Doğrulaması
                $max_time = 60*60*24; // saniye içinde
                if(isset($_SESSION['csrf_token_time']) && is_numeric($_SESSION['csrf_token_time'])){
                    $token_time = $_SESSION['csrf_token_time'];
                    if(($token_time + $max_time) >= time() ){
                    }else{
                        $this->errors[] = "CSRF Token Süresi Doldu";
                        unset($_SESSION['csrf_token']);
                        unset($_SESSION['csrf_token_time']);
                    }
                }
            
                if(empty($this->errors)){
                    require_once('./includes/connect.php');

                    // Giriş Kullanıcı Bilgilerini Kontrol Edin
                    $sql = "SELECT * FROM uyeler WHERE ";
                    if(filter_var(filter_input(INPUT_POST, 'user_email'), FILTER_VALIDATE_EMAIL)){
                        $sql .= "user_email=?";
                    }
                    $result = $PDOdb->prepare($sql);
                    $result->execute(array(filter_input(INPUT_POST, 'user_email')));
                    $count = $result->rowCount();
                    $res = $result->fetch(PDO::FETCH_ASSOC);
                    if($count == 1){
                        // Girilen şifre ile veritabanındaki şifreyi karşılaştır
                        if(password_verify($_POST['user_password'], $res['user_password_hash'])){
                            
                            // session id yeniden oluştur
$session = array("last_login","user_name","user_email","user_group","user_id","user_is_logged_in","proje_son_kayit_id","proje");

foreach ($_SESSION AS $key => $value){
    if (in_array($key, $session)) {
    unset($_SESSION[$key]);
    }
}
                            require_once("includes/turkcegunler.php");
                            $giris_zamani = strtotime(date_tr('Y-m-d H:i:s', time()));

                            session_regenerate_id();
                            $_SESSION['user_is_logged_in']          = true;
                            $_SESSION['user_id']                    = $res['user_id'];
                            $_SESSION['user_group']                 = $res['user_group'];
                            $_SESSION['user_email']                 = $res['user_email'];
                            $_SESSION['user_name']                  = $res['user_name'];
                            $_SESSION['last_login']                 = $giris_zamani;

                            //$date = new DateTime();
                            //$TimeNow = $date->getTimestamp();
                            $sonlogin = $res['last_login']; //değeri 1
                            if($sonlogin == 11){ //indis 11 ise 1 olsun dedik
                            $sonlogin = 1;
                            }  
                            $loginsutunu="login".$sonlogin; // login1
                            $kaydet = $PDOdb->prepare("UPDATE uyeler SET $loginsutunu=? WHERE user_id=? ");
                            $kaydet->execute([$giris_zamani, $res['user_id']]);

                            if($kaydet->rowCount() > 0){
                            $sonlogin++; //son logini 1 arttırdık
                            $guncelle = $PDOdb->prepare("UPDATE uyeler SET last_login=? WHERE user_id=? ");
                            $guncelle->execute([$sonlogin, $res['user_id']]);
                            }
                            
                            // Kullanıcı girişi başarılı x sayfaya yönlendir
                            if(isset($_GET['last']) && !empty($_GET['last'])){
                                header("location: ".$_GET['last']." ");
                            }else{
                                header("location: /");
                            }
                        
                        }else{
                            $this->errors[] = "E-posta veya Şifre Hatalı";
                        }
                    }else{
                        $this->errors[] = "E-posta veya Şifre Hatalı";
                    }
                }
            }

        } // private function loginWithPostData() {


        // Çıkış, oturumu sonlandırma

    public function doLogout() {
    session_start();
    session_destroy();
    header("location: login.php");
        }

        public function isUserLoggedIn() {
        
            return $this->user_is_logged_in;
        
        }

} //class Login {

    $login = new Login();

// 1. CSRF Token oluşturun
$token = md5(uniqid(rand(), TRUE));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();

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
            if($login->errors){
                echo "<div class='alert alert-danger'>";
                foreach ($login->errors as $error) {
                    echo "<span class='glyphicon glyphicon-remove'></span>&nbsp;".$error."<br>";
                }
                echo "</div>";
            }
        ?>
    <form role="form" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
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
        <div class="g-recaptcha" data-sitekey="6Le6jL0UAAAAAGd8kRl9RSkMl82ERek090TOODEG"></div>
        </div>

        <div class="row">
        <div class="col-8">
            <div class="icheck-primary">
            <input type="checkbox" id="remember">
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
    navigator.sendBeacon("gorev.php");
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
