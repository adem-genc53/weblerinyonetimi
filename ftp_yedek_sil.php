<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
//require_once('check-login.php');
require_once("includes/turkcegunler.php");

//ob_start();
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); //7200 saniye 120 dakikadır, 3600 1 saat


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ftp_yedekle']) && $_POST['ftp_yedekle'] == 1 && isset($_POST['dosya_adi_yolu']) && strlen($_POST['dosya_adi_yolu']) > 1){

    $gorevler = $PDOdb->prepare(" SELECT * FROM zamanlanmisgorev WHERE id = ? "); 
    $gorevler->execute([$_POST['id']]);
    $row = $gorevler->fetch();

    $uzantilar = ["zip","sql","gz","rar","tar"];

    $yuklenecek_dizin_veya_dosya = isset($_POST['dosya_adi_yolu']) ? $_POST['dosya_adi_yolu'] : ""; //"../yeni-webyonetimi"; //Bu, tüm alt klasörleri ve dosyalarıyla birlikte yüklemek istediğiniz klasördür veya dosya adıdır.

    $ftpsunucu      = $genel_ayarlar['sunucu']; //ftp domain name
    $ftpusername    = $genel_ayarlar['username']; //ftp user name 
    $ftppass        = $genel_ayarlar['password']; //ftp passowrd

    // ftp bağlantısı kurma
    $ftp = @ftp_ssl_connect($ftpsunucu)
        or die($ftpsunucu  . " sunucuya bağlanamadı");

    if($ftp) {
        //echo "FTP sunucusuna başarıyla bağlanıldı!";
        
        // Kurulan bağlantıya ftp kullanıcı adı şifresi ile giriş yapıyoruz
        $login_result = ftp_login($ftp, $ftpusername, $ftppass);
        ftp_pasv($ftp, true);

        if($login_result){
            //echo "<br>FTP girişi başarılı<br><br>";
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function deleteDirectoryRecursive($directory, $ftp) {
    // Fonksiyonla gelen dosya ise siliyoruz, eğer dizin ise bir aşağı fonksiyona geçiyoruz
    if (@ftp_delete($ftp, $directory)) {
        return;
        //echo "Silindi: ".$directory."<br>";
    }
    // Burada dizini silmeye çalışıyoruz dizin içi boş değil ise devam ediyoruz ve dizin içindekilerini siliyoruz
    if( !@ftp_rmdir($ftp, $directory) ) {
        // Dizin içindeki dosyaları listeliyoruz
        if ($files = @ftp_nlist ($ftp, $directory)) {
            foreach ($files as $file){
                // Dizideki . ve .. ile dizinleri gösterenleri parçıyoruz ve dizideki son öğeyi alıyoruz
                $haric = explode("/", $file);
                // Satırlarında . ve .. olanları hariç tutuyoruz
                if(end($haric)!='.' && end($haric)!='..'){
                    // fonsiyona tekrar gönderip en baştaki ftp_delete() ile dosyaları siliyoruz
                    deleteDirectoryRecursive( $file, $ftp);
                }
            }
        }
    }
    // Dosyalar silinip dizin boş kaldığında dizinide siliyoruz
    @ftp_rmdir($ftp, $directory);
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $file_list = [];
    //while ($row = $gorevler->fetch()) {
    // '/^\/+|\/+$/'

        if($row['yedekleme_gorevi'] == 1){
            $sil_uzantilar = ["sql","gz"];
        }elseif($row['yedekleme_gorevi'] == 2){
            $sil_uzantilar = ["zip"];
        }
        if(!empty($row['uzak_sunucu_ici_dizin_adi']) && strlen($row['uzak_sunucu_ici_dizin_adi'])>2){
            $uzak_sunucu_ici_dizin_adi = ltrim(rtrim($row['uzak_sunucu_ici_dizin_adi'],'/'),'/'); //preg_replace('/\/+$/', '', $row['uzak_sunucu_ici_dizin_adi']); // dizin yolunun başında ve veya sonunda / eğik çizgi varsa kaldırır. Elle ekledik
        }else{
            $uzak_sunucu_ici_dizin_adi = "";
        }
    if($row['ftp_sunucu_korunacak_yedek'] != '-1'){
        $file_list = ftp_mlsd($ftp, $uzak_sunucu_ici_dizin_adi);

        $ftpdeki_dosyalar = [];
        $ftpdeki_dizinler = [];
        if(is_array($file_list) || is_object($file_list)){

    foreach($file_list as $file_list_arr) {

    if(!in_array($file_list_arr['type'], array("pdir","cdir")) && stripos($file_list_arr['name'], $row['secilen_yedekleme_oneki']) !== false){
        if($file_list_arr['type'] == 'file' && in_array(pathinfo($file_list_arr['name'], PATHINFO_EXTENSION), $sil_uzantilar)){
            $ftpdeki_dosyalar[$file_list_arr['modify']][] = $uzak_sunucu_ici_dizin_adi."/".$file_list_arr['name'];
        //echo "<b style='color:blue;'>Dosya: </b>"."/".$uzak_sunucu_ici_dizin_adi."/".$file_list_arr['name']."<br>";
        }elseif($file_list_arr['type'] == 'dir'){
            $ftpdeki_dizinler[$file_list_arr['modify']][] = $uzak_sunucu_ici_dizin_adi."/".$file_list_arr['name'];
        //echo "<b style='color: red;'>Klasör: </b>"."/".$uzak_sunucu_ici_dizin_adi."/".$file_list_arr['name']."<br>";
        }
    }
        } // foreach($file_list as $file_list_arr) {
        } // if(is_array($file_list) || is_object($file_list)){

    if(isset($ftpdeki_dosyalar) && count($ftpdeki_dosyalar)>0) {
    krsort($ftpdeki_dosyalar);
    $ftpdeki_dosyalar = call_user_func_array('array_merge', $ftpdeki_dosyalar);
    }
    if(isset($ftpdeki_dizinler) && count($ftpdeki_dizinler)>0) {
    krsort($ftpdeki_dizinler);
    $ftpdeki_dizinler = call_user_func_array('array_merge', $ftpdeki_dizinler);
    }

    if (!function_exists('validateDate')) {
        function validateDate($date, $format = 'Y-m-d-H-i-s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }
    }

/*
        $dosya = fopen ("ftp_yedek_sil_dizi.txt" , "a"); //dosya oluşturma işlemi 
        $yaz = print_r($ftpdeki_dizinler, true); // Yazmak istediginiz yazı 
        fwrite($dosya,$yaz); fclose($dosya);
*/

    if(count($ftpdeki_dosyalar)>0){
        while (count($ftpdeki_dosyalar) > $row['ftp_sunucu_korunacak_yedek']){
            $silinendosya = array_pop($ftpdeki_dosyalar);
            $dosya_tarihi = substr($silinendosya, strpos($silinendosya, $row['secilen_yedekleme_oneki']."-") + strlen($row['secilen_yedekleme_oneki']."-"), 19);
            if(validateDate($dosya_tarihi)){
                deleteDirectoryRecursive( $silinendosya, $ftp);
                //echo "<b style='color: red;'>Temsili Silinen dosya: </b>".$silinendosya."<br>";
            }
        }
    }

    if(count($ftpdeki_dizinler)>0){
        while (count($ftpdeki_dizinler) > $row['ftp_sunucu_korunacak_yedek']){
            $silinendizin = array_pop($ftpdeki_dizinler);
            $dizin_tarihi = substr($silinendizin, -19);
            if(validateDate($dizin_tarihi)){
                deleteDirectoryRecursive( $silinendizin, $ftp);
                //echo "<b style='color: blue;'>Temsili Silinen klasör: </b>".$silinendizin."<br>";
            }
        }
    }

    //echo '<pre>Dosyalar: '.$row['ftp_sunucu_korunacak_yedek'].'<br>' . print_r($ftpdeki_dosyalar, true) . '</pre>';
    //echo '<pre>Dizinler: '.$row['ftp_sunucu_korunacak_yedek'].'<br>' . print_r($ftpdeki_dizinler, true) . '</pre>';
    } // if($row['ftp_sunucu_korunacak_yedek'] != '-1'){

        //} // while ($row = $gorevler->fetch()) {
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else { // if($login_result){
            //echo "<br>FTP giriş hatası oluştu!";
        }

        // echo ftp_get_option($ftp, 1);
        // Bağlantıyı kapatıyoruz
        if(ftp_close($ftp)) {
            //echo "<br>FTP Bağlantısı Başarıyla Kapatıldı";
        }
    } // if($ftp) {

} // if($_SERVER['REQUEST_METHOD']
?>