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

//echo '<pre>' . print_r($_POST, true) . '</pre>';
//exit;
    $yedektablo_adi = [];
    $yedektablo = array();
    $veritabitabloadi = array();
#########################################################################################################################################
    // POST ile veritabanı id
    if(isset($_POST['veritabani_id']) && $_POST['veritabani_id'] > 0){
        $veritabani_id = $_POST['veritabani_id'];
    }else{
        $veritabani_id = 0;
    }
#########################################################################################################################################
    // Seçilen veritabanı 
    $default = $PDOdb->prepare("SELECT * FROM veritabanlari WHERE id=? LIMIT 1");
    $default->execute([$veritabani_id]);
    $varsayilan = $default->fetch(PDO::FETCH_ASSOC);

    // Seçilen veritabanı varsa bağlantı oluşturuyoruz
    $secilen = "mysql:host=".$varsayilan['database_host'].";dbname=".$varsayilan['db_name'].";charset=".CHARSET.";port=".PORT."";

    $PDOdbsecilen = new PDO($secilen, $hash->take($varsayilan['database_user']), $hash->take($varsayilan['database_password']), $options);
    $PDOdbsecilen->exec("set names utf8");
    $PDOdbsecilen->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_name = $varsayilan['db_name'];
#########################################################################################################################################
################################################################################

    $post_sqlsec = isset($_POST['sqlsec']) ? $_POST['sqlsec'] : '';
    $klasorsec = isset($_POST['klasorsec']) ? $_POST['klasorsec'] : '';
    $dizin = BACKUPDIR;
    $uzantilar = array("sql","gz"); //hangi uzantılar?
    $gzuzanti = array("gz"); //hangi uzantı?
    $sqluzanti = array("sql"); //hangi uzantı?
    $database = 'Veritabanı: `';   // Veritabanı adı için yedek içinde aranacak kelime
    $tabloadi = 'CREATE TABLE IF NOT EXISTS '; //'TABLO_ADI ';       // Tablo adı ve satır sayısı için yedek içinde aranacak kelime
    $found = false;
    $databasename = "";

    function ext($text)  {
    $text = strtolower(pathinfo($text, PATHINFO_EXTENSION));
    return $text;  
    }
    function uzanti($text)  {
    $text = strtolower(pathinfo($text, PATHINFO_EXTENSION));
    return $text;  
    } 

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

foreach($veritabanitablolistesi as $fileName) {

// Gelen tablo veya veritabani uzantısını kontrol ediyor
    $dosyatipi = uzanti($fileName);
    if($dosyatipi=='gz'){
        $file = gzopen($fileName,'r');
    }else{
        $file = fopen($fileName,'r');
    } 

// Satır satır okuyor
$count = 1;
unset($yedektablo_adi);
$dizisatir = [];
while(!feof($file)){
//while (($row = fgets($file, 4096)) !== false) {

    $row = fgets($file); // Dosyayı satır satır okuyor
    $dizisatir[] = $row;

  if(strpos($row, $database) !== false){ // Yedek içinde veritabanı adını alıyoruz
   $raw_data = explode("\n", $row);
   $dat = explode("`", $raw_data[0]);
                   array_pop($dat);
   $databasename = array_pop($dat);

  } // if(strpos($row, $database) !== false){

    if($databasename != $db_name){ // Karşılaştırılacak veritabanı aynı değil ise döngüyü durdur
        $break;
    } // if($databasename != $db_name){

  if(strpos($row, $tabloadi) !== false){  // Yedek içinden tablo adlarını alıyoruz

   $found = true;
   $rawdata = explode("\n", $row);
   $data = explode('`', $rawdata[0]);

   $yedektablo_adi[] = $data[1];

    //echo '<pre>Yedek: ' . print_r($yedektablo_adi, true) . '</pre>';

    //$yedektablo[$tablename] = $count; // Tablo adlarını ve satır sayılarını array() oluşturuyor
    //$yedektablo_adi[] = $tablename;

   } // if(strpos($row, $tabloadi) !== false){

}
fclose($file);

    //echo '<pre>Yedek: ' . print_r($dizisatir, true) . '</pre>';


    // Bulunan tablonun INSERT INTO satırı sayarak satır sayısını alıyoruz
    foreach($yedektablo_adi AS $tabname){
        $item1 = 'INSERT INTO `'.$tabname.'`';
        $count = count(
            preg_grep(
                '/'.preg_quote($item1).'/i',
                $dizisatir
            )
        );
        $yedektablo[$tabname] = $count;
    }
    unset($yedektablo_adi);

} // foreach($veritabanitablolistesi as $fileName) {

}

}

################################################################################
   if($databasename == $db_name OR $true){  // Kaynak ile Yedek Veritabanı eşit ise listelemeye başla

    $result = $PDOdbsecilen->query("
    SELECT TABLE_SCHEMA, 
    TABLE_NAME, TABLE_ROWS
    FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = '$db_name'
    ORDER BY TABLE_NAME ASC");
     
    $ttablo = 0;
    while($table = $result->fetch()) {
    $veritabitabloadi[$table['TABLE_NAME']] = $table['TABLE_ROWS'];
    $ttablo ++;
    } 

    ksort($yedektablo);
    ksort($veritabitabloadi);
    //echo '<pre>Yedek: ' . print_r($yedektablo_adi, true) . '</pre>';
    //echo '<pre>Yedek: ' . print_r($yedektablo, true) . '</pre>';
    //echo '<pre>Kaynak: ' . print_r($veritabitabloadi, true) . '</pre>';
    //exit;
########################################################################################################################################################################################################################    
// İki veritabanı diziyi birleştiriyoruz ve kaynak veritabanına col1 adını yedek veritabanına col2 adını ekliyoruz.
// Kaynak veritabanı işlemi
$newArray = array(); 
foreach( $veritabitabloadi as $key => $value ) { 
$newArray [ $key ][ 'col1' ] = $value ; 
} 

// Yedek veritabanı işlemi
foreach( $yedektablo as $key => $value ) { 
$newArray [ $key ][ 'col2' ] = $value ; 
}     
ksort($newArray);   
#######################################################################################################################################################################################################################              
?>
  <table class="table table-bordered table-sm" style="min-width: 1000px;">
    <colgroup span="7">
        <col style="width:39%"></col>
        <col style="width:5%"></col>
        <col style="width:1%"></col>
        <col style="width:5%"></col>
        <col style="width:39%"></col>
        <col style="width:5%"></col>
        <col style="width:1%"></col>
    </colgroup>
  <tr class="bg-primary">    
    <th style="padding:3px 5px 3px 5px;">Kaynak Tablolar</th>
		<th style="padding:3px 5px 3px 5px;">Satır</th>
    <th style="padding:3px 5px 3px 5px;">Sonuç</th>
    <th style="border-bottom: 1px solid white;border-top: 1px solid white;padding:3px 5px 3px 5px;background:#FFFFFF;">&nbsp;</th>
		<th style="padding:3px 5px 3px 5px;">Yedek Tablolar</th>
		<th style="padding:3px 5px 3px 5px;">Satır</th>
    <th style="padding:3px 5px 3px 5px;">Sonuç</th>
  </tr>
    <tr>
      <td colspan="3" style="text-align:center;background:#F0F1F9;padding:1px 5px 1px 5px;"><b>Kaynak Veritabanı: </b><?php echo $db_name; ?></td>
      <td rowspan="<?php echo count($newArray)+1; ?>" style="border-bottom: 1px solid white;padding:1px 5px 1px 5px;display: table-cell; vertical-align: middle;text-align:center;"><img border="0" src="images/diff.png" width="48" height="45"></td>
      <td colspan="3" style="text-align:center;background:#F0F1F9;padding:1px 5px 1px 5px;"><b>Yedek Veritabanı: </b><?php echo $databasename; ?></td>
    </tr>
    <?php
$k = 1;
$satir = 0;
$sol = 0;
$sag = 0;
$sorunsuz = 0;  
$r = 0;  
foreach( $newArray as $key => $data ) {
       if($r%2) {
       $bgcolor = "#F0F1F9";
       }else{
       $bgcolor = "#F4F4F4";
       }
if(!isset( $data [ 'col2' ])) {
$sol ++;
$kaynaktavaryedekteyok = "1"; 
// Kaynak veritabanında tablo var ancak yedek veritabanında yok, olmayan tablonun zemin rengi kırmızı, sağ tarafı boş
  echo '
    <tr>
      <td style="text-align:left;color:white;background:#FF3333;padding:1px 5px 1px 5px;">'.$key.'</td>
      <td style="text-align:right;color:white;background:#FF3333;padding:1px 5px 1px 5px;">'.$data['col1'].'</td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;"><i class="fa fa-plus" aria-hidden="true" style="color:red;"></i></td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;">&nbsp;</td>
      <td style="text-align:right;background:'.$bgcolor.';padding:1px 5px 1px 5px;">&nbsp;</td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;"><i class="fa fa-minus" aria-hidden="true" style="color:red;"></i></td>                 
    </tr>';  
} 
elseif(!isset( $data [ 'col1' ])) {
$sag ++;
$yedektevarkaynaktayok = "1";  
// Yedek veritabanında tablo var ancak kaynak veritabanında yok, olmayan tablonun zemin rengi kırmızı, sol tarafı boş
  echo '
    <tr>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;">&nbsp;</td>
      <td style="text-align:right;background:'.$bgcolor.';padding:1px 5px 1px 5px;">&nbsp;</td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;"><i class="fa fa-minus" aria-hidden="true" style="color:red;"></i></td>
      <td style="text-align:left;color:white;background:#FF3333;padding:1px 5px 1px 5px;">'.$key.'</td>
      <td style="text-align:right;color:white;background:#FF3333;padding:1px 5px 1px 5px;">'.$data['col2'].'</td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;"><i class="fa fa-plus" aria-hidden="true" style="color:red;"></i></td>                 
    </tr>';
} 
elseif( $data [ 'col1' ] != $data [ 'col2' ]) {
$verisatirlariesitdegil = "1";
$satir ++; 
// Kaynak veritabanında ve yedek veritabanında bazi tabloların veri satır sayıları eşit değil, sayıların zemini rengi kırmızı
  echo '
    <tr>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;">'.$key.'</td>
      <td style="text-align:right;color:white;background:#FF3333;padding:1px 5px 1px 5px;">'.$data['col1'].'</td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;"><i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i></td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;">'.$key.'</td>
      <td style="text-align:right;color:white;background:#FF3333;padding:1px 5px 1px 5px;">'.$data['col2'].'</td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;"><i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i></td>                 
    </tr>';  
} 
else {
$sorunsuz ++;
$basariliyedeklendi = "1"; 
// Kaynak veritabanında ve yedek veritabanında tablor var ve veri satır sayıları aynı, sorunsuz yedeklenen tablolar
  echo '
    <tr>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;">'.$key.'</td>
      <td style="text-align:right;background:'.$bgcolor.';padding:1px 5px 1px 5px;">'.$data['col1'].'</td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;">'.$key.'</td>
      <td style="text-align:right;background:'.$bgcolor.';padding:1px 5px 1px 5px;">'.$data['col2'].'</td>
      <td style="text-align:left;background:'.$bgcolor.';padding:1px 5px 1px 5px;"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></td>                 
    </tr>'; 
}
$r++;
unset($key,$data['col1'],$data['col2']); 
}    
?>
  <tr>    
    <th colspan="7" style="padding:3px 5px 3px 5px;text-align:left;border-bottom: 1px solid white;border-left: 1px solid white;border-right: 1px solid white;">Kaynağa göre toplam <?=$ttablo?> tablo mevcut.</th>   
  </tr>   
</table>
<?php

echo '<table class="table table-sm" style="min-width: 1000px;">';
if(isset($basariliyedeklendi) && $basariliyedeklendi == "1" && isset($verisatirlariesitdegil) && $verisatirlariesitdegil == "1" || 
    isset($yedektevarkaynaktayok) && $yedektevarkaynaktayok == "1" || 
    isset($kaynaktavaryedekteyok) && $kaynaktavaryedekteyok == "1"){
  echo '
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td style="text-align:left;font-size:14px;">Veritabanının toplam <b>'.$ttablo.'</b> tablodan sadece <b>'.$sorunsuz.'</b> tablosu ve veri satırları başarılı biçimde yedeklendi</td>
    </tr>';
}
if(isset($kaynaktavaryedekteyok) && $kaynaktavaryedekteyok == "1" AND isset($basariliyedeklendi) && $basariliyedeklendi == "1"){
  echo '
    <tr>
      <td><i class="fa fa-plus" aria-hidden="true" style="color:red;"></i></td>
      <td><i class="fa fa-minus" aria-hidden="true" style="color:red;"></i></td>
      <td style="text-align:left;font-size:14px;">Kaynak veritabanında olan <b>'.$sol.'</b> tablo yedek veritabanında yok. <b>Yani yedeklenmeyen tablolar mevcut</b></td>
    </tr>';
}
if(isset($yedektevarkaynaktayok) && $yedektevarkaynaktayok == "1" AND isset($basariliyedeklendi) && $basariliyedeklendi == "1"){
  echo '
    <tr>
      <td><i class="fa fa-plus" aria-hidden="true" style="color:red;"></i></td>
      <td><i class="fa fa-minus" aria-hidden="true" style="color:red;"></i></td>
      <td style="text-align:left;font-size:14px;">Yedek veritabanında olan <b>'.$sag.'</b> tablo kaynak veritabanında yok. <b>Karşılaştırdığınız veritabanı aynı olduğundan emin olun</b></td>
    </tr>';
}
if(isset($verisatirlariesitdegil) && $verisatirlariesitdegil == "1"){
  echo '
    <tr>
      <td><i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i></td>
      <td>&nbsp;</td>
      <td style="text-align:left;font-size:14px;">Kaynak ve yedek veritabanındaki <b>'.$satir.'</b> tablonun veri satırları eşit değil. <b>Yani yedeklenmeyen veri satırları olabilir. <br />NOT:</b> Kaynak veritabanında yedekleme sonrasında çerezler, oturumlar ve etiketler gibi vs vs az önemli satırlar eklenmiş veya silinmiş olabilir.<br />Önemli veri satırlarında eksik yedekleme olup olmadığına dikkat etmeniz önerilir.</td>
    </tr>';
}
if(isset($basariliyedeklendi) && $basariliyedeklendi == "1" AND isset($verisatirlariesitdegil) && $verisatirlariesitdegil != "1" AND isset($yedektevarkaynaktayok) && $yedektevarkaynaktayok != "1" AND isset($kaynaktavaryedekteyok) && $kaynaktavaryedekteyok != "1"){
  echo '
    <tr>
      <td><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></td>
      <td>&nbsp;</td>
      <td style="text-align:left;font-size:14px;"><b>Veritabanının tamamı <b>'.$sorunsuz.'</b> tablo ve veri satırları başarılı biçimde yedeklendi.</b><br />Eğer bu karşılaştırma yedeğin sorunsuz olmadığından şüpheniz varsa<br /><b>Veritabanı Geri Yükle</b> alanından <b>DENEME</b> modunda yükleme yaparak kontrol edebilirsiniz.</td>
    </tr>';
}
if(isset($basariliyedeklendi) && $basariliyedeklendi != "1" AND isset($verisatirlariesitdegil) && $verisatirlariesitdegil != "1" AND isset($yedektevarkaynaktayok) && $yedektevarkaynaktayok == "1" AND isset($kaynaktavaryedekteyok) && $kaynaktavaryedekteyok == "1"){
  echo '
    <tr>
      <td><i class="fa fa-plus" aria-hidden="true" style="color:red;"></i></td>
      <td><i class="fa fa-minus" aria-hidden="true" style="color:red;"></i></td>
      <td style="text-align:left;font-size:14px;"><b>Karşılaştırılan veritabanları aynı ise tüm tabloların ön ekleri farklı olduğundan tablo adları eşleşmiyor olabilir</b></td>
    </tr>';
}
//echo '</table>';
} // if($databasename == $db_name){        
################################################################################
$dbaynidegil = true;    
if($databasename != $db_name && !empty($veritabanitablolistesi)){
    //echo '<table class="table table-sm" style="min-width: 1000px;">';
        echo '<tr><td style="font-size:14px;color:#FF3333;" colspan="4"><b>Kaynak Veritabanı ile Yedek Veritabanı aynı değil</b></td></tr>';
        echo '<tr><td style="text-align:left;font-size:14px;color:blac;" colspan="4"><b>Kaynak Veritabanı Adı: </b>'.$db_name.'</td></tr>';
        echo '<tr><td style="text-align:left;font-size:14px;color:blac;" colspan="4"><b>Yedek Veritabanı Adı: </b>'.$databasename.'</td></tr>';
    //echo '</table>';
    $dbaynidegil = false;
}

if( ($databasename != $db_name && !empty($veritabanitablolistesi)) || ($databasename == $db_name OR $true) ){
    echo '</table>';
}

?>