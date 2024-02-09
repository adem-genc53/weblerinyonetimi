<?php 
// Bismillahirrahmanirrahim
session_start();


ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);

if (function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get"))
  @date_default_timezone_set(@date_default_timezone_get());

require_once('check-login.php');
require_once("includes/turkcegunler.php");
/*
$dosya = fopen ("zipcikar.txt" , "a"); //dosya oluşturma işlemi 
$yaz = "zip çıkar\n".print_r($_POST, true); // Yazmak istediginiz yazı 
fwrite($dosya,$yaz); fclose($dosya);
//exit;
*/
  //echo '<pre>' . print_r($_POST, true) . '</pre>';
  //exit;


if(isset($_POST['grup'])){

  $result       = "";
  $starttime    = microtime(true);
  $simdizaman   = date("Y-m-d-H-i-s",time());
  $zipdosya     = $_POST['zipdosya']; // ../WEBZIPLER/klasor-2023-11-21-10-34-35.zip
  $dizinyolu    = $_POST['dizinyolu']; // /home/user/


  $zip = new ZipArchive;
/*
  $archive = $zipdosya;
  $res = $zip->open($archive);
  // Zip File Name
  if($res == ZipArchive::ER_OPEN){
      echo "$archive dosya açılamıyor<br />";
      $result = false;
  }else{
      // Unzip Path
      $zip->extractTo($dizinyolu);
      $zip->close();
      $result = true;
  }
  */

  if ($zip->open($zipdosya) === TRUE) {
      // Unzip Path
      $zip->extractTo($dizinyolu);
      $zip->close();
      $result = true;
  } else {
      $result = false;
  }

  $endtime = microtime(true);

  function formatPeriod($starttime, $endtime) {
      $duration = $endtime - $starttime;
      $hours = floor($duration / 60 / 60);
      $minutes = floor(($duration / 60) - ($hours * 60));
      $seconds = floor($duration - ($hours * 60 * 60) - ($minutes * 60));
      $milliseconds = ($duration - floor($duration)) * 1000;
    return sprintf('%02d:%02d:%02d:%05.0f', $hours,$minutes,$seconds,$milliseconds);
  }

  if ($result) {
    echo "<b>".basename($zipdosya)."</b> Dosyası <b><br />".$dizinyolu."</b> klasörüne başarıyla çıkarıldı<br />";
    echo '<br />Zip ten Çıkarma Süresi: '.formatPeriod($starttime, $endtime).'';
  } else {
    echo "<b style='color: red'>ZIP TEN ÇIKARMA BAŞARISIZ OLDU</b>";
  }

}

?>