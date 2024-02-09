<?php 
// Bismillahirrahmanirrahim
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");

ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); //7200 saniye 120 dakikadır, 3600 1 saat

//echo '<pre>' . print_r($_POST, true) . '</pre>';
//exit;
$basarili = [];
$basarisiz = [];
if(isset($_POST['delete_veritabaniyedek']) AND $_POST['grup'] == "sqlyedeksil"){ // delete_veritabaniyedek[]
    if (!function_exists('delete_directory')){
        // Klasörü silecek fonksiyon
        function delete_directory($dir){
            if(is_dir($dir)){
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach($files as $file){
                    if ($file->isDir()){
                        rmdir($file->getRealPath());
                    }else{
                        unlink($file->getRealPath());
                    }
                }
                // Seçilen dizinide silmek içindir. Yukarısı içeriği siler
                if(rmdir($dir)){
                    return $basarili[] = "<b>".basename($dir)."</b> Dizin Başarıyla Silindi<br />";
                }else{
                    return $basarisiz[] = "<b style='color: red;'>".basename($dir)."</b> Dizin Bir Hatadan Dolayı Silinemedi<br />";
                }
            }else{ // if(is_dir($dir)){
                if(unlink($dir)){
                    return $basarili[] = "<b>".basename($dir)."</b> Dosya Başarıyla Silindi<br />";
                }else{
                    return $basarisiz[] = "<b style='color: red;'>".basename($dir)."</b> Dosya Bir Hatadan Dolayı Silinemedi<br />";
                }
            }
        }
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
foreach($_POST['delete_veritabaniyedek'] AS $zipli_dosya_adi){
    print_r(delete_directory(BACKUPDIR."/".$zipli_dosya_adi));
}

}
############################################################################################################################################
############################################################################################################################################
############################################################################################################################################
if(isset($_POST['delete_ziplidizinler']) AND $_POST['grup'] == "ziplidizinsil"){ // delete_ziplidizinler[]
    if (!function_exists('delete_directory')){
        // Klasörü silecek fonksiyon
        function delete_directory($dir){
            if(is_dir($dir)){
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach($files as $file){
                    if ($file->isDir()){
                        rmdir($file->getRealPath());
                    }else{
                        unlink($file->getRealPath());
                    }
                }
                // Seçilen dizinide silmek içindir. Yukarısı içeriği siler
                if(rmdir($dir)){
                    return $basarili[] = "<b>".basename($dir)."</b> Dizin Başarıyla Silindi<br />";
                }else{
                    return $basarisiz[] = "<b style='color: red;'>".basename($dir)."</b> Dizin Bir Hatadan Dolayı Silinemedi<br />";
                }
            }else{ // if(is_dir($dir)){
                if(unlink($dir)){
                    return $basarili[] = "<b>".basename($dir)."</b> Dosya Başarıyla Silindi<br />";
                }else{
                    return $basarisiz[] = "<b style='color: red;'>".basename($dir)."</b> Dosya Bir Hatadan Dolayı Silinemedi<br />";
                }
            }
        }
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
foreach($_POST['delete_ziplidizinler'] AS $zipli_dosya_adi){
    print_r(delete_directory(rtrim(ZIPDIR,'/')."/".$zipli_dosya_adi));
}

}

/*
################### zipli dizinlerin silme kodu başlangıcı #####################
if(isset($_POST['delete_ziplidizinler']) AND $_POST['grup'] == "ziplidizinsil"){
$tumzipdosyalar = array();
$silinmeyendosyalar = array();
$silinendosyalar = array();
$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($_POST['delete_ziplidizinler']));

foreach($iterator as $key => $value) {
          $tumzipdosyalar[] = $value;
    if(is_file("./".ZIPDIR."/".$value) AND file_exists("./".ZIPDIR."/".$value)){

         if (!unlink("./".ZIPDIR."/".$value)) {
            $silinmeyendosyalar[] = $value;
        } else {
            $silinendosyalar[] = $value;
       }
    
    } //if(is_file("./".ZIPDIR."/".$value) AND file_exists("./".ZIPDIR."/".$value)){
 } //foreach($iterator as $key => $value) {
 
      $fark = array_diff($tumzipdosyalar , $silinendosyalar);
              if(count($fark) == 0){
                if(count($tumzipdosyalar) > 1){
                 echo "Zipli Dosyalar Başarıyla Silindi";
                } else {
                echo "Zipli Dosya Başarıyla Silindi";
                } 
              } else {
      foreach($fark AS $silinmedi){
      
       echo "<b style='color: red'>Bu dosya silinemedi:</b> ".$silinmedi."<br />";
       
      } //foreach($fark AS $silinmedi){       
              } 
} //if(isset($_POST['delete_ziplidizinler']) AND $_POST['grup'] == "ziplidizinsil"){
################### zipli dizinlerin silme kodu sonu ###########################




################## MysQSL yedek dosyaları silme kodu başlangıcı ################
if(isset($_POST['delete_veritabaniyedek']) AND $_POST['grup'] == "sqlyedeksil"){ // delete_veritabaniyedek[]
$tumdizinler = array();
$tumdosyalar = array();
$silinmeyendosyalar = array();
$silinendosyalar = array();
$silinenklasorler = array();
$silinmeyenklasorler = array();
$birdizin = false;
$birdosya = false;
$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($_POST['delete_veritabaniyedek']));
    foreach($iterator as $key => $value) {

    if(is_dir("./".BACKUPDIR."/".$value) AND file_exists("./".BACKUPDIR."/".$value)){
              $tumdizinler[] = $value;
              $birdizin = true;
          $silinecekklasor = "./".BACKUPDIR."/".$value; //silinecek klasörün adı
          KlasorSil($silinecekklasor) ? $silinenklasorler[] = $value : $silinmeyenklasorler[] = $value;         
    
    }elseif(is_file("./".BACKUPDIR."/".$value) AND file_exists("./".BACKUPDIR."/".$value)){
         $tumdosyalar[] = $value;
         $birdosya = true;
         if (!unlink("./".BACKUPDIR."/".$value)) {
            $silinmeyendosyalar[] = $value;
        } else {
            $silinendosyalar[] = $value;
       }
    
    }
} //foreach($iterator as $key => $value) {

    if($birdizin){
      $fark = array_diff($tumdizinler , $silinenklasorler);
              if(count($fark) == 0){
                if(count($tumdizinler) > 1){
                 echo "Veritabanı Yedek Klasörler Başarıyla Silindi";
                } else {
                echo "Veritabanı Yedek Klasör Başarıyla Silindi";
                } 
              } else {
      foreach($fark AS $silinmedi){
      
       echo "<b style='color: red'>Bu klasör silinemedi:</b> ".$silinmedi."<br />";
       
      } //foreach($fark AS $silinmedi){       
              } // else
    } //if($birdizin){
    
            if($birdizin AND $birdosya){
                echo "<br />";
            } 
    if($birdosya){
       $fark = array_diff($tumdosyalar , $silinendosyalar);
              if(count($fark) == 0){
                if(count($tumdosyalar) > 1){
                 echo "Veritabanı Yedek Dosyalar Başarıyla Silindi";
                } else {
                echo "Veritabanı Yedek Dosya Başarıyla Silindi";
                } 
              } else {
      foreach($fark AS $silinmedi){
      
       echo "<b style='color: red'>Bu dosya silinemedi:</b> ".$silinmedi."<br />";
       
      } //foreach($fark AS $silinmedi){       
              } // else
    } //if($birdizin){    
             
} //if(isset($_POST['delete_ziplidizinler']) AND $_POST['grup'] == "sqlyedeksil"){              

              
function KlasorSil($dir) {
if (substr($dir, strlen($dir)-1, 1)!= '/')
$dir .= '/';
//echo $dir; //silinen klasörün adı
if ($handle = opendir($dir)) {
	while ($obj = readdir($handle)) {
		if ($obj!= '.' && $obj!= '..') {
			if (is_dir($dir.$obj)) {
				if (!KlasorSil($dir.$obj))
					return false;
				} elseif (is_file($dir.$obj)) {
					if (!unlink($dir.$obj))
						return false;
					}
			}
	}
		closedir($handle);
		if (!@rmdir($dir))
		return false;
		return true;
	}
return false;
}
*/

################## MysQSL yedek dosyaları silme kodu sonu ######################


?>