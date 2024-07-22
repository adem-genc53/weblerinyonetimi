<?php 
    if($_POST)
    {
        if(isset($_POST["g-recaptcha-response"])){
            $response = $_POST["g-recaptcha-response"];
            $secret = "6LcjqxUqAAAAACa0U5IPYI8M71d7EEmRWKsKVrIf";
            $remoteip = $_SERVER["REMOTE_ADDR"];
            $captcha = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$response&remoteip=$remoteip");

            $result = json_decode($captcha);
            if($result->success == 1)
            {
            //$messages[] = 'Doğrulama tamamlandı.';
            $robotdegil = true;
            }
            else {
            //$errors[] = '"Ben Robot Değilim" Kutuyu Seçmelisiniz.';
            }
        }
    }
?>
