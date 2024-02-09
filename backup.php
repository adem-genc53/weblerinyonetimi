<?php 
// Bismillahirrahmanirrahim
//echo '<pre>' . print_r($_POST, true) . '</pre>';
//exit;

ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);
require('includes/connect.php');
require_once("includes/turkcegunler.php");

//Bu tarih yedek dosya adı ve klasör adı için kullanıcak.
// !! BOŞLUKSUZ !!
	define('datetime',date('Y-m-d-H-i-s')); /* Date fortmat. See: http://tr1.php.net/manual/en/function.date.php */
#########################################################################################################################################
    // Ajax ile veritabanı ID geliyormu, geliyorsa hem değişkene hemde sessiona ata
    // Gelmiyorsa else den sesiiondan kullan
    if(isset($_POST['veritabani_id']) && $_POST['veritabani_id'] > 0){
        //unset($_SESSION['secili_veritabani_id']);
        //$_SESSION['secili_veritabani_id'] = $_POST['veritabani_id'];
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
/*
$dosya = fopen ("backup.txt" , "a"); //dosya oluşturma işlemi 
$yaz = "Varsayılan veritabanı: ".$varsayilan['db_name']."\n".print_r($_POST, true); // Yazmak istediginiz yazı 
fwrite($dosya,$yaz); fclose($dosya);

    $jsonData = array("basarili"=>"Veritabanı Başarıyla Yedeklendi", "dosya_adi"=>'deneme');
    echo "<span>".json_encode($jsonData)."</span>";
    exit;
*/
################################################################################
// Yedekleme dizi yoksa dizin oluşturuyoruz
if(!file_exists(BACKUPDIR)){
    if (!mkdir(BACKUPDIR, 0777, true)) {
        die('Failed to create folders...');
    }
}
// Yedekleme dizinin içine kimse ulaşamasın diye .htaccess oluşturuyoruz ve içine 'deny from all' yazıyoruz
$content = 'deny from all';
$file = new SplFileObject(BACKUPDIR . '/.htaccess', "w") ;
$file->fwrite($content);
################################################################################
// yedeklemede önek belirlenmiş mi
// belirlenmiş ise onek ile datetime zamanı ilave ediyoruz
// belirlenmedi ise sadece datetime zamanı ekliyoruz
if(isset($_POST['onek']) && !empty($_POST['onek'])){
$onek = $_POST['onek']."-";
$tarih_onek = $onek.datetime;
}else{
$tarih_onek = datetime;    
}
################################################################################
// Tablolar dizi başlatıyoruz
$tables = array();
################################################################################
// combine 1 ise Tam tek dosya olarak yedekleme
// "-Tam" metin ekliyoruz
// Tüm tablolar için "*" yıldız belirliyoruz
if(isset($_POST['combine']) && $_POST['combine']=='1'){
$tabloadi='-Tam'; 
$tables = '*';
}
################################################################################
// combine 2 ise Tabloları Ayrı Ayrı yedekleme
// Tüm tablolar için "*" yıldız belirliyoruz
// Tabloların oluşturulacağı alt-dizin oluşturuyoruz
if(isset($_POST['combine']) && $_POST['combine']=='2'){
$tables = '*';
define('SUBBACKUPDIR', BACKUPDIR.'/'.$tarih_onek ) ;
if(!file_exists(SUBBACKUPDIR)){
    if (!mkdir(SUBBACKUPDIR, 0777, true)) {
        die('Failed to create folders...');
    }
}
// Yedekleme alt-dizinin içine kimse ulaşamasın diye .htaccess oluşturuyoruz ve içine 'deny from all' yazıyoruz
$content = 'deny from all';
$file = new SplFileObject(SUBBACKUPDIR . '/.htaccess', "w") ;
$file->fwrite($content) ;
}

################################################################################
// combine 3 ise Tabloları elle seçildi ve elle 1 seçeği tabloları birleştirip tek dosya olarak yedekleyecek
if(isset($_POST['combine']) && $_POST['combine']=='3' && isset($_POST['elle']) && $_POST['elle']=='1'){
$toplam_tablo = count($_POST['tablolar']);
if($toplam_tablo==1){
$tables = $_POST['tablolar'];
sort($tables);
$tabloadi="-".$tables[0]; // Seçilen bir tablo ise tablo adını dosyaya ekliyoruz
}else{
$tables = $_POST['tablolar'];
sort($tables);
$tabloadi='-Elle'; // Seçilen birden fazla tablo ise Elle metni dosyaya ekliyoruz
}
}
################################################################################
// combine 3 ise Tabloları elle seçildi ve elle 2 seçeneği seçilen tabloları ayrı ayrı alt-klasöre yedekleyecek
// Tabloların oluşturulacağı alt-dizin oluşturuyoruz
if(isset($_POST['combine']) && $_POST['combine']=='3' && isset($_POST['elle']) && $_POST['elle']=='2'){
define('SUBBACKUPDIR', BACKUPDIR.'/'.$tarih_onek ) ;
if(!file_exists(SUBBACKUPDIR)){
    if (!mkdir(SUBBACKUPDIR, 0777, true)) {
        die('Failed to create folders...');
    }
}
// Yedekleme alt-dizinin içine kimse ulaşamasın diye .htaccess oluşturuyoruz ve içine 'deny from all' yazıyoruz
$content = 'deny from all';
$file = new SplFileObject(SUBBACKUPDIR . '/.htaccess', "w") ;
$file->fwrite($content) ;
$tables = $_POST['tablolar'];
sort($tables);
}
################################################################################
// grup 1 geldi ise yedekleme başlayabilir
if(isset($_POST['grup']) && $_POST['grup']=='1'){
// Satır başlangıcı
$return = null;
// Yedek dosyanın başına sunucu depolama gibi temel bilgileri yazmak için
$mysql_version = $PDOdbsecilen->query('select version()')->fetchColumn();
$mysqlcharacter = $PDOdbsecilen->query("SHOW VARIABLES LIKE 'character_set_connection'");
$mysql_character = $mysqlcharacter->fetchColumn(1);
// fonksiyon ile başlıkları istediğimde dosyanın başına ekliyoruz
function db_genel_bilgi(){
    GLOBAL $mysql_character, $mysql_version, $db_name, $return;
$return .= "\n-- Karakter Seti: {$mysql_character}\n";
$return .= "-- PHP Sürümü: ".phpversion()."\n";
$return .= "-- Sunucu sürümü: {$mysql_version}\n";
$return .= "-- Anamakine: ".$_SERVER['HTTP_HOST']."\n";
$return .= '-- Üretim Zamanı: ' . date_tr('j F Y, H:i', time() ) . "\n";
$return .= "-- Veritabanı: {$db_name}\n";
$return .= "--\n";
$return .= "-- --------------------------------------------------------\n";
$return .= 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";' ."\n" ;
$return .= 'SET AUTOCOMMIT = 0;' ."\n";
$return .= 'START TRANSACTION;' ."\n" ;
$return .= 'SET time_zone = "+00:00";' ."\n" ;
$return .="-- --------------------------------------------------------\n\n";

$return .="--\n";
$return .="-- Veritabanı: `{$db_name}`\n"; // Karşılaştırmada dosyanın hangi veritabanı olduğunu buradan bakacak
$return .="--\n\n";
}
// Başlık bilgileri fonksiyondan buraya çağırıyoruz
echo db_genel_bilgi();
/*
$lock_write = 'LOCK TABLES';
$lock_read = 'LOCK TABLES';
// BASE TABLE SAVE  
// get all of the tables 
*/
// Eğer yıldız ile tüm tablo isteniyorsa
if($tables == '*'){

$tables = array();

$tables = $PDOdbsecilen->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
/*
while($row = $result->fetch(PDO::FETCH_NUM)){
$tables[] = $row[0];
$lock_write .= ' '.$row[0].' WRITE,';
$lock_read .= ' '.$row[0].' READ,';
}
*/
// Eğer yıldız değil ise virgülle ayrılmış tabloları dizi oluşturuyoruz
}else{
$tables = is_array($tables) ? $tables : explode(',',$tables);
/*
foreach ($tables AS $table){
$lock_write .= ' '.$table.' WRITE,';
$lock_read .= ' '.$table.' READ,';
}
*/
}
    // Yedek sonuna veri eklemek için tablo sayısını alıyoruz
    $tablosayisi = count($tables);

##############################################################################################################################

	// Repair & Optimize Tables
	function repairTables($PDOdbsecilen, &$tables)
	{
		foreach ($tables AS $table){

            // Tablo onarımda tabloların kilitlenmesi belirlendi ise kilitliyoruz
            if ($_POST['lock']=='1')
            {
                $PDOdbsecilen->query('LOCK TABLES `'.$table.'` WRITE');
            }

			// Check Table
			$check = $PDOdbsecilen->query(' CHECK TABLE `'.$table.'` ')->fetch(PDO::FETCH_NUM);

            // Repair Table
            $repair = $PDOdbsecilen->query(' REPAIR TABLE `'.$table.'` ')->fetch(PDO::FETCH_BOTH);

            // Optimize Table
            $optimize = $PDOdbsecilen->query(' OPTIMIZE TABLE `'.$table.'` ')->fetch(PDO::FETCH_BOTH);


            // Tablo onarımda tabloların kilitlenmesi belirlendi ise kilitlenen tabloların kilidini açıyoruz ki veri dökümü yapılabilsin
            if ($_POST['lock']=='1')
            {
                $PDOdbsecilen->query('UNLOCK TABLES;');
            }

    } // foreach ($tables AS $table)

	}
    // Bakım seçildi ise önce tablolara bakım yapıyoruz
    if($_POST['bakim']=='1'){
        repairTables($PDOdbsecilen, $tables);
    }

##############################################################################################################################

    //sadece sayı türlerin dizisi
    // sayı olan verilere tırnak işareti koymamak içindir
    $numtypes = array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'float', 'double', 'decimal', 'real', 'int unsigned');

$handle = "";
$basarili = false;
$t = 0;
foreach($tables as $table){
$t++;
// İlk sutuna göre sıralamak için sutun adlarını alıyoruz
// Sutun adlarını tek diziye diziyoruz
$tablonun_sutun_adlari = $PDOdbsecilen->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_COLUMN);

$sutun_sayisi = $PDOdbsecilen->query(" SELECT * FROM `$table` ORDER BY $tablonun_sutun_adlari[0] ASC ");
$num_fields =  $sutun_sayisi->columnCount();
$numrow = $sutun_sayisi->rowCount();

################################################################################

// Tabloları ayrı ayrı yedekleme yolu ve dosya adı
if($_POST['combine']=='2' OR $_POST['combine']=='3' AND $_POST['elle']=='2'){
$handle = fopen(SUBBACKUPDIR.'/'.trim($table).'.sql','a');
// GZip için dosya yolu ve dosya adı
$dosya = SUBBACKUPDIR.'/'.trim($table).'.sql';

if($t>1){
echo db_genel_bilgi();
}
}

// Tek dosyada yedekleme yolu ve dosya adı
if($_POST['combine']=='1' OR $_POST['combine']=='3' AND $_POST['elle']!='2'){
$handle = fopen(BACKUPDIR.'/'.$tarih_onek.$tabloadi.'.sql','a');
// GZip için dosya yolu ve dosya adı
$dosya = BACKUPDIR.'/'.$tarih_onek.$tabloadi.'.sql';
}

$type = array();

################################################################################
// Tablo yapısının üstündeki verileri dosyaya ekliyoruz
$return .= "--\n" ;
$return .= "-- Tablonun yapısı `{$table}`\n" ;
$return .= "--\n\n";
// Tablo yapısının başına "DROP TABLE IF EXISTS tabloadı" ekliyoruz ki geri yüklerken tablo varsa önce silsin diye
$return .= "DROP TABLE IF EXISTS {$table};";

$pstm2 = $PDOdbsecilen->query("SHOW CREATE TABLE `{$table}` ");
$row2 = $pstm2->fetch(PDO::FETCH_NUM);
$ifnotexists = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $row2[1]);
// Tablo oluşturma başlığı ve tablo yapısını dosyaya ekliyoruz
$return .= "\n{$ifnotexists};\n";
$return .= "\n--\n" ;
$return .= "-- Tablonun veri dökümü `{$table}`\n" ;
$return .= "--\n\n" ;

##############################################################################################
        //Tablonun sutün tiplerini alıyoruz ki sayı formatlı mı metin formatlı mı diye
        // Aşağıdaki foreach döngüde kullanacağız
        if ($numrow) {
            $pstm3 = $PDOdbsecilen->query("SHOW COLUMNS FROM `$table` ");
            $type = array();

            while ($rows = $pstm3->fetch(PDO::FETCH_NUM)) {
                if (stripos($rows[1], '(')) {
                    $type[$table][] = stristr($rows[1], '(', true);
                } else {
                    $type[$table][] = $rows[1];
                }
            }
        }
##############################################################################################

##############################################################################################
$sutunozellikleri = $PDOdbsecilen->query(" SHOW COLUMNS FROM `{$table}` ");
$sutun_ozellikleri = $sutunozellikleri->fetchAll(PDO::FETCH_NUM);
##############################################################################################
// Foreach döngü ile veri satırları dosyaya ekliyoruz
@set_time_limit(0);
$s = 0;
while($satirlardizi = $sutun_sayisi->fetch(PDO::FETCH_NUM)){
    $s++;
    $return .= 'INSERT INTO `' . trim($table) . '` VALUES(';
        foreach($satirlardizi AS $key => $value){

            // Veri olup olmadığını kontrol ediyoruz
            if (strlen((string) $value)>0) {
                // Sutün tipi sayı formatlı ise '15', gibi yerine kesmeyi kaldırıp 15, sadece sayı değeri ekle
                if ((in_array($type[$table][$key], $numtypes)) && (strlen((string) $value)>0)) {
                    $return .= $value;
                } else {
                    $return .= $PDOdbsecilen->quote($value); // Sutün tipi sayı formatlı olmadığı için 'veri' gibi veriyi kesme içine alarak ekle
                }
            } else {
                if( $sutun_ozellikleri[$key][2] == 'YES' && empty($sutun_ozellikleri[$key][4]) ){ // Veri yok ve "Tanımlandığı gibi" de yok ise NULL ekle
                    $return .= 'NULL';
                }else{ // Veri yok ve "Tanımlandığı gibi" de veri var ise ekle
                    if ((in_array($type[$table][$key], $numtypes))) {
                        $return .= $sutun_ozellikleri[$key][4];
                    } else {
                        // Sutün tipi NOT NULL olduğu halde veri yoksa burada hata verecektir.
                        // Çözümü, sutün tipi NOT NULL ise boş olmayacak, boş olacaksa DEFAULT NULL olacak
                        if(empty($sutun_ozellikleri[$key][4])){
                            $return .= '\'\'';
                        }else{
                            $return .= $PDOdbsecilen->quote( $sutun_ozellikleri[$key][4] );
                        }
                    }
                }
            }

            if ($key < ($num_fields - 1)) {
                $return .= ', ' ;
            }
        }
        $return .= ");\n";
        // Tablonun verileri dosyaya eklendikten sonra altına karşılaştırmada kullanılacak tablo adı ve kaç satır veri var ekliyoruz
        // Karşılaştırmada kaynak ile yedek karşılaştırırken yedekteki tablo adı ve satır sayısı için buraya bakacak
        if ( $s == ( $numrow - 0 ) ){
        $return .="\n--\n";
        $return .="-- TABLO_ADI {$table} {$numrow}\n";
        $return .="--\n";

        $return .="\n-- --------------------------------------------------------\n\n";
        }

        // Okunan veriyi dosyaya yazıyoruz
        if(fwrite($handle, $return) === FALSE){
            echo "Veri satırı dosyaya yazılamıyor";
            exit;
        }
            $return=null;
        
}

        // Tabloda veri yoksa sadece tablo yapısını dosyaya yazıyoruz
        if($numrow == '0'){
        $return .="--\n";
        $return .="-- TABLO_ADI {$table} {$numrow}\n";
        $return .="--\n";

        $return .="\n-- --------------------------------------------------------\n\n";
        if(fwrite($handle, $return) === FALSE){
            echo "Veri satırı dosyaya yazılamıyor";
            exit;
        }
            $return=null;
        }
       
####################################################################################################
    // Eğer tablonun denetim iz kayıtları tetikleyici varsa onuda buradan dosyaya ekliyoruz
    // "table_create", "table_update", "table_delete" tablo yapıları yedekler
    $trigger = $PDOdbsecilen->query(" SELECT * FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = '{$db_name}' AND EVENT_OBJECT_TABLE = '{$table}' ");
    $trigger_dizi = $trigger->fetchAll(PDO::FETCH_ASSOC);

        if(count($trigger_dizi)>0){
            $tri = 1;
            foreach($trigger_dizi AS $trigger){
            if($tri == 1){
                $return .= "--\n";
                $return .= "-- Tetikleyiciler `".$trigger['EVENT_OBJECT_TABLE']."`"; // Tetikleyici tablo adı
                $return .= "\n--\n\n";
            }
                $return .= "DROP TRIGGER IF EXISTS `".$trigger['TRIGGER_NAME']."`;\n"; // Aynı tablo varsa silerek drop komutu
                $return .= "DELIMITER $$\n";
                $return .= 'CREATE TRIGGER `'.$trigger['TRIGGER_NAME'].'` '.$trigger['ACTION_TIMING'].' '.$trigger['EVENT_MANIPULATION'].' ON `'.$trigger['EVENT_OBJECT_TABLE'].'` FOR EACH ROW ';
                $return .= $trigger['ACTION_STATEMENT'];
                $return .= "\n$$\n";
                $return .= "DELIMITER ;\n";
            $tri++;
            }
                $return .= "COMMIT;\n\n";

            // Okunan veriyi dosyaya yazıyoruz
            if(fwrite($handle, $return) === FALSE){
                echo "Veri satırı dosyaya yazılamıyor";
                exit;
            }
                $return=null;
        }
####################################################################################################

            // Dosyanın en sonuna ekliyoruz
            if ( ($t == ( $tablosayisi - 0 ) AND $tablosayisi > 1 AND $_POST['combine']=='1') OR ($_POST['combine']=='2' OR @$_POST['elle']=='2') ){
            $return .= "\n";
            $return .= 'SET FOREIGN_KEY_CHECKS = 1 ; '  . "\n" ; 
            $return .= 'COMMIT ; '  . "\n" ;
            $return .= 'SET AUTOCOMMIT = 1 ; ' . "\n"  ;
            if(fwrite($handle, $return) === FALSE){
                echo "Veri satırı dosyaya yazılamıyor";
                exit;
            }
                $return=null;
            }

            // GZip olmayan Açılmış dosyayı kapatıyoruz          
            if($_POST['gz']=='0'){
                fclose($handle);
            }

            // Alt-klasöre Tablo Tablo yedekleri GZip ile sıkıştırır
            if($_POST['gz']=='1' && ($_POST['combine']=='2' || $_POST['combine']=='3' && isset($_POST['elle']) && $_POST['elle'] == '2')){
            fclose($handle);
            $input = $dosya;
            $output = $input.".gz";
            $basarili = file_put_contents("compress.zlib://$output", file_get_contents($input));
            // Sıkıştırma başarılı ise 
            if($basarili){
                @unlink($dosya);
            }
            }

}//foreach($tables as $table){
##############################################################################################################################################################
            // Tam tek dosya olarak GZip yok
            if($_POST['gz'] == '0' && $_POST['combine']=='1'){
                $goreve_gidecek = $dosya;
            }
            // Elle tabloları seçerek GZip yok ve birleştirilmiş tablolar tek dosya
            if($_POST['gz'] == '0' && $_POST['combine']=='3' && isset($_POST['elle']) && $_POST['elle'] == '1'){
                $goreve_gidecek = $dosya;
            }
##############################################################################################################################################################
                // Tek dosyada yedeği GZip ile sıkıştırır
                if($handle && $_POST['gz']=='1' && ($_POST['combine']=='1' || $_POST['combine']=='3' && isset($_POST['elle']) && $_POST['elle'] == '1')){
                fclose($handle); // burası hata veriyor
                $input = $dosya;
                $output = $input.".gz";
                $basarili = file_put_contents("compress.zlib://$output", file_get_contents($input));                        
                if($basarili){
                    @unlink($dosya);
                }
                }
##############################################################################################################################################################
            // Tam tek dosya olarak yedeklendiğin GZip var dosya adı
            if($_POST['gz'] == '1' && $_POST['combine']=='1'){
                $goreve_gidecek = $output;
            // Tabloları Ayrı Ayrı yedeklendiğinde dizin adı alıyoruz GZip var yok fark etmez dizin adı önemlidir
            }else if($_POST['combine']=='2'){
                $goreve_gidecek = SUBBACKUPDIR;
            // Elle tablolar seçiliyor ve tablolar Ayrı Ayrı alt-dizine yedeklendiğinde dizin adı alıyoruz GZip var yok fark etmez dizin adı önemlidir
            }else if($_POST['combine']=='3' && isset($_POST['elle']) && $_POST['elle'] == '2'){
                $goreve_gidecek = SUBBACKUPDIR;
            // Elle tablolar seçiliyor ve tablolar birleştirilecek tek dosya ve GZip var
            }else if($_POST['gz'] == '1' && $_POST['combine']=='3' && isset($_POST['elle']) && $_POST['elle'] == '1' ){
                $goreve_gidecek = $output;
            }
##############################################################################################################################################################
/*
$dosya = fopen ("backup3.txt" , "a"); //dosya oluşturma işlemi 
$yaz = print_r($goreve_gidecek."\n", true); // Yazmak istediginiz yazı 
fwrite($dosya,$yaz); fclose($dosya);
*/
            if($handle != "" OR $basarili){
                unset($PDOdbsecilen);
            // Otomatik yedekleme başarılı olduğundaki mesaj
                if(isset($_POST['oto_yedek']) && $_POST['oto_yedek'] == 1){
                    $jsonData = array("basarili"=>"Veritabanı Başarıyla Yedeklendi", "dosya_adi"=>$goreve_gidecek);
                    echo "<span>".json_encode($jsonData)."</span>";
                }else{
                    echo 'Veritabanı Başarıyla Yedeklendi';
                }
            }else{
            // Otomatik yedekleme başarısız olduğundaki mesaj
                if(isset($_POST['oto_yedek']) && $_POST['oto_yedek'] == 1){
                    $jsonData = array("basarili"=>"Veritabanı Bir Hatadan Dolayı Yedeklenemedi", "dosya_adi"=>$goreve_gidecek);
                    echo "<span>".json_encode($jsonData)."</span>";
                }else{
                    echo 'Veritabanı Bir Hatadan Dolayı Yedeklenemedi';
                }
            }
            //echo $dosyaa; // klasör adı

}//if($_POST['grup']=='1'){
    unset($_POST,$PDOdbsecilen,$PDOdb);
?>