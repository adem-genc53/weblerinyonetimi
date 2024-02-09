<?php 
// Bismillahirrahmanirrahim
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Connection: close");

session_start();
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");
################################################################################
    //sleep(5);
    // echo '<pre>' . print_r($_POST, true) . '</pre>';

################################################################################

        $post_sqlsec = isset($_POST['sqlsec']) ? $_POST['sqlsec'] : '';
        $klasorsec = isset($_POST['klasorsec']) ? $_POST['klasorsec'] : '';
        $dizin = BACKUPDIR;
        $uzantilar = array("sql","gz"); //hangi uzantılar?
        $gzuzanti = array("gz"); //hangi uzantı?
        $sqluzanti = array("sql"); //hangi uzantı?
        $database = 'Veritabanı: `';   // Veritabanı adı için yedek içinde aranacak kelime
        $search = 'TABLO_ADI ';       // Tablo adı ve satır sayısı için yedek içinde aranacak kelime
        $insert = 'INSERT INTO `';
        $completed = 'COMPLETED SUCCESSFULLY'; // Başarılı yedeklendiğini göstermek için aranacak kelime
        $manuel = 'MANUEL'; // Tablolar elle seçilerek yedeklendiğini göstermek için aranacak kelime
        $found = false;
        $bulundu = false;
        $databasename = "";
        $manuelbulundu = 0;
  
        function ext($text)  {
        $text = strtolower(pathinfo($text, PATHINFO_EXTENSION));
        return $text;  
        }
        function uzanti($text)  {
        $text = strtolower(pathinfo($text, PATHINFO_EXTENSION));
        return $text;  
        } 

   $yedektablo = array();
   $veritabitabloadi = array();
################################################################################

// Klasor içindeki tabloları array() dizi oluşturur
$veritabanitablolistesi = [];
if(!empty($klasorsec)){
$alt_klasor_yolu = $klasorsec."/";

if ($handle = opendir("$alt_klasor_yolu") or die ("Dizin açılamadı!")) {         
        $gzdizi = array();
        $sqldizi = array();
        $klasordizi = array();
        $klasor = array();
        $open = opendir($alt_klasor_yolu); // klasör aç
        while($q = readdir($open)) {                      
            $filetype = ext($q);
            if ($q != "." && $q != "..") {            
               if(!is_file("$alt_klasor_yolu/$q")){
                   $klasordizi[] = $q;                   
               }               
               if(in_array($filetype, $gzuzanti)){
                   $gzdizi[] = $q;
               }
               if(in_array($filetype, $sqluzanti)){
                   $sqldizi[] = $q;
               }
            }
        }       
        asort($klasordizi);
        asort($gzdizi);
        asort($sqldizi);
        $tumdizi = array();
        $tumdizi = array_merge_recursive($klasordizi, $gzdizi, $sqldizi);
        asort($tumdizi);       
        foreach($tumdizi AS $yedek) {
                
        $klasorise = is_dir($alt_klasor_yolu."/".$yedek) ? ''.$yedek.'' : '';
        unset($klasor);
        $ac = opendir($alt_klasor_yolu.'/'.$klasorise); // klasör aç        
        while($qq = readdir($ac)) {
        $tip = ext($qq);
        if ($qq != "." && $qq != "..") {
        if(in_array($tip, $uzantilar)){
        $klasor[] = $qq;
        }
        }
        }           
        if($klasorise==false){
        $veritabanitablolistesi[] = $alt_klasor_yolu.$yedek;
        }
        }
        }
        }

    // Sadece klasör içindeki yedekler
    //echo '<pre>Dizindekiler: ' . print_r($veritabanitablolistesi, true) . '</pre>';
    //exit;
################################################################################
      if(isset($_POST['yinede'])){
      $break = true;
      $true = true;
      }else{
      $break = false;
      $true = false;
      }
// array() olarak gelen klasör içindeki tablolar veya tam yedeklenmiş veritabanini satır satır okumaya başlıyor
if($post_sqlsec != "-1" OR !empty($veritabanitablolistesi)){

if(!empty($post_sqlsec)){
    $veritabanitablolistesi = array($post_sqlsec);
}

    // Klasör veya tek dosya yedek
    //echo '<pre>Dizindekiler: ' . print_r($veritabanitablolistesi, true) . '</pre>';
    //exit;
if (is_array($veritabanitablolistesi) || is_object($veritabanitablolistesi)){

foreach($veritabanitablolistesi as $key=>$fileName) {

// Gelen tablo veya veritabani uzantısını kontrol ediyor
    $dosyatipi = uzanti($fileName);
    if($dosyatipi=='gz'){
        $file = gzopen($fileName,'r');
    }else{
        $file = fopen($fileName,'r');
    } 
$count = 0;
// Satır satır okuyor
while(!feof($file)){

    echo fgets($file); // Dosyayı satır satır okuyor

}

    if(!empty($klasorsec) AND $key !== array_key_last($veritabanitablolistesi)){
        echo "\n/************************************************************************************/\n";
        echo "/*************************** SONRAKİ TABLONUN BAŞLANGICI ****************************/\n";
        echo "/************************************************************************************/\n";
    }
fclose($file);

} // foreach($veritabanitablolistesi as $fileName) {

}
}
?>