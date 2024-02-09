<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");


foreach ($_POST AS $key => $value){
if($key == "eskidizinadi"){
$eskidizinadi = $value;
}
if($key == "yenidizinadi"){
$yenidizinadi = $value;
}
if($key == "grup"){
$grup = $value;
}
}
if($grup == 1){

if (!file_exists(DIZINDIR.$eskidizinadi)) {
echo "<b style='color: red'>".strtoupper($eskidizinadi)."</b> BÖYLE BİR KLASÖR YOK";
exit;
} elseif(file_exists(DIZINDIR.$yenidizinadi)) {
echo "<b style='color: red'>".strtoupper($yenidizinadi)."</b> ADINDA BİR KLASÖR ZATEN MEVCUT";
exit;
} elseif(is_writable(DIZINDIR.$yenidizinadi)) {
echo "<b style='color: red'>".strtoupper($eskidizinadi)."</b> BU KLASÖRDE YAZMA İZNİ YOK";
exit;
} else {
$degistimi = rename(DIZINDIR.$eskidizinadi, DIZINDIR.$yenidizinadi) ? true : false;
}
//sleep(100);
$klasor_adi = DIZINDIR.$yenidizinadi;
 
if(file_exists($klasor_adi) && $degistimi)
{
echo "Eski adı <b>".strtoupper($eskidizinadi)."</b> olan klasör<br />Yeni adı <b>".strtoupper($yenidizinadi)."</b> olarak başarıyla değiştirilmiştir";
}
else
{
echo "Klasörün yeni <b>".strtoupper($yenidizinadi)."</b> adı değiştirme sırasında herhangi bir sorunla karşılaşılmadı ancak yeni adı ile klasör bulunamadı";
}
}
?>