<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;

error_reporting(E_ALL);

if(isset($_GET['do'])){
  unset($_SESSION['folder'],$_SESSION['testmode'],$_SESSION['test'],$_SESSION['secilen_veritabani_id']);
}

    // Yedeklenecek dizin yoksa oluştur
    if(!file_exists(BACKUPDIR)){
        if (!mkdir(BACKUPDIR, 0777, true)) {
            die('Failed to create folder' .BACKUPDIR);
        }
    }
###########################################################################################################################################

  // Sayfa ilk geldiğinde veya yükleme modu değiştirdiğinde
    if(isset($_POST['testmode']) && $_POST['testmode'] == 1){ // Elle yükleme seçilirse sessiona al
      $_SESSION['testmode'] = 1;
    }elseif(isset($_POST['testmode']) && $_POST['testmode'] == 0){
      $_SESSION['testmode'] = 0;
    }

    // Sayfaya yeni gelindi session yoksa TEST MODU evet yapıyoruz
    $testmode = isset($_SESSION['testmode']) && $_SESSION['testmode'] == 0 ? $_SESSION['testmode'] : 1;
    
    // Radio duğmeleri sayfa yenilense bile seçili kalması için
    $yes = $testmode == 1 ? ' checked' : '';
    $no = $testmode == 0 ? ' checked' : '';

    // Yükleme modu ne olduğunu göstermek için büyük harf ve renkli mesaj gösterme
    if($testmode == 1){
        $mod_durum = '<td colspan="3" style="font-size: 16px;text-align: center;color: blue;font-weight: bold;">DENEME YÜKLEME MODUNDASINIZ</td>';
    }elseif($testmode == 0){
        $mod_durum = '<td colspan="3" style="font-size: 16px;text-align: center;color: red;font-weight: bold;">GERÇEK YÜKLEME MODUNDASINIZ</td>';
    }else{
        $mod_durum = '<td colspan="3" style="font-size: 16px;text-align: center;color: grey;font-weight: bold;">YÜKLEME MODU SEÇİLMEDİ</td>';
    }

###########################################################################################################################################

    $secili_dizin = ""; // Select optionda seçilen dizin yoksa
    $altdizin = ""; // Select optionda seçilin dizin yoksa alt dizin boş
    // Select option ile alt dizin seçildiğinde
    if(isset($_POST['folder'])){
        $secili_dizin = $_POST['folder'];
        $_SESSION['folder'] = $_POST['folder'];
        $altdizin = "/".$_POST['folder'];
    }elseif(isset($_SESSION['folder'])){
        $secili_dizin = $_SESSION['folder'];
        $altdizin = "/".$_SESSION['folder'];
    }

###########################################################################################################################################

    // Select option için Dizinleri listeliyoruz
    $files = array();
    $i = 0;
    foreach (new DirectoryIterator(BACKUPDIR) AS $file) {
        if ($file->isDir() && $file->getFilename() != '.' && $file->getFilename() != '..') {
          $files['3-'.$file->getCTime().'-'.$i] = $file->getFilename();
        }
    $i++;
    }
    krsort($files);

###########################################################################################################################################
#########################################################################################################################################
    // Ajax ile veritabanı ID geliyormu, geliyorsa hem değişkene hemde sessiona ata
    // Gelmiyorsa else den sesiiondan kullan
    // POST ile veritabanı id
    if(isset($_POST['veritabani_id']) && $_POST['veritabani_id'] > 0){
        unset($_SESSION['secilen_veritabani_id']);
        $veritabani_id = $_POST['veritabani_id'];
        $_SESSION['secilen_veritabani_id'] = $_POST['veritabani_id'];
    }else{
        $veritabani_id = isset($_SESSION['secilen_veritabani_id']) ? $_SESSION['secilen_veritabani_id'] : "";
    }
#########################################################################################################################################
    $db_yok = false;
    $db_name = "";
if(isset($_POST['veritabani_id']) && $_POST['veritabani_id'] > 0 || isset($_SESSION['secilen_veritabani_id']) && $_SESSION['secilen_veritabani_id'] > 0){
    // Seçilen veritabanı 
    $default = $PDOdb->prepare("SELECT * FROM veritabanlari WHERE id=? LIMIT 1");
    $default->execute([$veritabani_id]);
    $varsayilan = $default->fetch(PDO::FETCH_ASSOC);


    // Seçilen veritabanı varsa bağlantı oluşturuyoruz
    $secilen = "mysql:host=".$varsayilan['database_host'].";dbname=".$varsayilan['db_name'].";charset=".CHARSET.";port=".PORT."";
    try {
    $PDOdbsecilen = new PDO($secilen, $hash->take($varsayilan['database_user']), $hash->take($varsayilan['database_password']), $options);
    $PDOdbsecilen->exec("set names ".CHARSET);
    $PDOdbsecilen->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
        die($e->getMessage());
    }
    $db_yok = true;
    $db_name = $varsayilan['db_name'];

}else{
    $PDOdbsecilen = $PDOdb;
    $db_name = DB_NAME;
}
#########################################################################################################################################
if($db_yok){
    $varsayilan_karakter_set_adi = $PDOdbsecilen->query("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
    FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name';")->fetch(PDO::FETCH_ASSOC);
}

###########################################################################################################################################

// Bağlantı karakter seti, döküm dosyası karakter seti ile aynı olmalıdır (utf8, latin1, cp1251, koi8r vb.)
// Tam liste için https://dev.mysql.com/doc/refman/5.0/en/charset-charsets.html adresine bakın.
// Latin olmayan harflerle ilgili sorunlarınız varsa bunu değiştirin

$db_connection_charset = $genel_ayarlar['karakter_seti'];

// İSTEĞE BAĞLI AYARLAR

$filename           = '';     // Yüklenecek yedek dosyanın adını giriniz
$ajax               = true;   // AJAX modu: web sitesi yenilenmeden içe aktarma yapılacaktır
$linespersession    = 300;   // Bir seferde içe aktarmada yürütülecek satır sayısı
$delaypersession    = 0;      // Her oturumdan sonra uyku süresini milisaniye cinsinden belirtebilirsiniz.
                              // Yalnızca JavaScript etkinleştirildiğinde çalışır. Sunucu taşmasını azaltmak için kullanın

// CSV ile ilgili ayarlar (yalnızca bir CSV dökümü kullanıyorsanız)

$csv_insert_table   = '';     // CSV dosyaları için hedef tablosu
$csv_preempty_table = false;  // true: işlemeden önce $csv_insert_table içinde belirtilen tablodaki tüm girişleri silin
$csv_delimiter      = ',';    // CSV dosyasında alan sınırlayıcı
$csv_add_quotes     = true;   // CSV verilerinizde zaten her alanın etrafında tırnak işaretleri varsa, bunu false olarak ayarlayın
$csv_add_slashes    = true;   // CSV verilerinizin ' ve " önünde eğik çizgiler zaten varsa, bunu false olarak ayarlayın

// İzin verilen yorum işaretçileri: bu dizelerle başlayan satırlar BigDump tarafından yok sayılır

$comment[]='#';                       // Standart yorum satırları varsayılan olarak bırakılır
$comment[]='-- ';
$comment[]='DELIMITER';               // Geçerli bir SQL ifadesi olmadığı için DELIMITER anahtarını yok sayın
// $comment[]='---';                  // Eski mysqldump tarafından oluşturulmuş tescilli döküm kullanılıyorsa bu satırın yorumunu kaldırın
// $comment[]='CREATE DATABASE';      // Dökümünüz, yok saymak için veritabanı oluşturma sorguları içeriyorsa, bu satırın açıklamasını kaldırın
$comment[]='/*!';                     // Veya diğer tescilli şeyleri dışarıda bırakmak için kendi dizinizi ekleyin

// Ön sorgular: Her içe aktarma oturumunun başında yürütülecek SQL sorguları

$pre_query[]='SET foreign_key_checks = 0';
// $pre_query[]='İsterseniz buraya ek sorgular ekleyin';

// Varsayılan sorgu sınırlayıcı: Satır sonundaki bu karakter, Bigdump'a bir SQL ifadesinin nerede bittiğini söyler
// Döküm dosyasındaki DELIMITER ifadesi ile değiştirilebilir (normalde prosedürler/işlevler tanımlanırken kullanılır)

$delimiter = ';';

// Dize tırnak karakteri

$string_quotes = '\'';                  // Döküm dosyanız dizeler için çift tırnak kullanıyorsa '"' olarak değiştirin

// Kaç satır bir sorgu olarak kabul edilebilir (metin satırları hariç)

$max_query_lines = 300;

// Yükleme dosyalarının nereye yerleştirileceği (varsayılan: bigdump klasörü)
 $upload_dir = BACKUPDIR.$altdizin;

// *******************************************************************************************
// PHP'ye aşina değilseniz lütfen bu satırın altındaki hiçbir şeyi değiştirmeyin
// *******************************************************************************************

if ($ajax)
  ob_start();

define ('VERSION','0.36b');
define ('DATA_CHUNK_LENGTH',16384);  // Bir seferde kaç karakter okunur
define ('TESTMODE',$testmode);          // Dosyayı gerçekten veritabanına erişmeden işlemek için true olarak ayarlayın
define ('BIGDUMP_DIR',BACKUPDIR.$altdizin);
define ('PLUGIN_DIR',BIGDUMP_DIR.'/plugins/');

//header("Expires: Mon, 1 Dec 2003 01:00:00 GMT");
//header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
//header("Cache-Control: no-store, no-cache, must-revalidate");
//header("Cache-Control: post-check=0, pre-check=0", false);
//header("Pragma: no-cache");

@ini_set('auto_detect_line_endings', true);
@set_time_limit(0);

if (function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get"))
  @date_default_timezone_set(@date_default_timezone_get());

// Kullanıcının girdisinden istemediğimiz her şeyi temizleyin ve çıkarın [0.27b]

foreach ($_REQUEST as $key => $val) 
{
  $val = preg_replace("/[^_A-Za-z0-9-\.&= ;\$]/i",'', $val);
  $_REQUEST[$key] = $val;
}

  function showSize($size_in_bytes) {
    $value = 0;
    if ($size_in_bytes >= 1073741824) {
        $value = round($size_in_bytes/1073741824*10)/10;
        return  (@$round) ? round($value) . 'GB' : "{$value} GB";
    } else if ($size_in_bytes >= 1048576) {
        $value = round($size_in_bytes/1048576*10)/10;
        return  (@$round) ? round($value) . 'MB' : "{$value} MB";
    } else if ($size_in_bytes >= 1024) {
        $value = round($size_in_bytes/1024*10)/10;
        return  (@$round) ? round($value) . 'KB' : "{$value} KB";
    } else {
        return "$size_in_bytes Bayt";
    }
  }

do_action('header');

do_action('head_meta');

include('includes/header.php');
include('includes/navigation.php');
include('includes/sub_navbar.php');
?>

<style type="text/css">

#bartablo {
  width:750px;
  border-collapse: collapse;
  border: 2px solid #ddd;
  padding: 5px;
}
p.centr
{ 
  text-align:center;
}

p.smlcentr
{ font-size:12px;
  line-height:14px;
  text-align:center;
}

p.error
{ color:#FF0000;
  font-weight:bold;
  text-align:center;
}

p.success
{ color:#00DD00;
  font-weight:bold;
  text-align:center;
}

p.successcentr
{ /*color:#00DD00;
  background-color:#DDDDFF;*/
/*  font-weight:bold;*/
  text-align:center;
}

td.bg3
{ /*background-color:#EEEE99;*/ /* yükleme istatistik değerler alanı */
  text-align:left;
  vertical-align:top;
  width:20%;
  border-collapse: collapse;
  border: 2px solid #ddd;
  padding: 5px;
}

th.bg4
{ /*background-color:#EEAA55;*/ /** sol üst alanlar */
  text-align:left;
  vertical-align:top;
  width:20%;
  border-collapse: collapse;
  border: 2px solid #ddd;
  padding: 5px;
  background-color: #f5f5f5;
}

td.bgpctbar
{ /*background-color:#EEEEAA;*/ /** bar arka plan rengi */
  text-align:left;
  vertical-align:middle;
  width:80%;
}
div tt {
  background-color:#808080;
  color: #FFFFFF;
}

.center {
  margin-left: auto;
  margin-right: auto;
}

<?php do_action('head_style'); ?>

</style>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Web Siteler Yönetimi</h1>
                        </div><!-- / <div class="col-sm-6"> -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                                <li class="breadcrumb-item active">Veritabanı Geri Yükleme</li>
                            </ol>
                        </div><!-- / <div class="col-sm-6"> -->
                    </div><!-- / <div class="row mb-2"> -->
                </div><!-- / <div class="container-fluid"> -->
            </div><!-- / <div class="content-header"> -->


    <!-- Bilgilendirme Satırı Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
                    <!-- Bilgilendirme bölümü -->
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                            <h5 class="m-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                Veritabanı Geri Yükleme Hakkında Bilmeniz Gerekenler !
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <p>Buradan daha önce yedeklediğiniz veri tabanı veya tabloları geri yükleyebilirsiniz</p>
                                <p>Tüm web siteler için geçerli olan herhangi bir saldırı veya yanlışlıkla verilerin silinmesi durumlarda web sitenin geri getirilmesi için zaman zaman belirli aralıklarda veri tabanı yedeklenmesi gerekir.</p>
                                <p>Bu script hem veri tabanı yedekleme hem de geri yükleme imkanı sağlamaktadır.</p>
                                <p>Yönetim panelinde ayarları değiştirme ve veya ürünleri ekleme ve veya silme gibi çalışmalara başlamadan önce veri tabanını yedeklemenizi öneririz hatta tabloları ayrı ayrı yedekleme seçeneğini kullanarak yedeklemenizi öneririz</p>
                                <p>Tabloları ayrı ayrı yedeklemenin avantajı tüm veri tabını geri yükleme yerine geri getirmek istediğiniz bir veya birden fazla tabloları ayrı ayrı geri yükleme imkanı sağlamasıdır.</p>
                                <p>Buradan yedeklerinizi geri yükleme yapabileceğiniz gibi DENEME MODUNDA yükleme yaparak alınan yedeğin geri yüklemede herhangi bir sorun çıkarıp çıkarmayacağını yani yedeklemenin sağlıklı yapılıp yapılmadığını da test etmiş olursunuz (yedeğin sorunsuz olduğunu garantilemez).</p>
                                <p><b>Not:</b> Buradan geri yükleme yapıldığında yedeğin içinde mevcut tablo adı ile sunucudaki veri tabanındaki tablo adı ile eşleşenler silinerek yerine yedekten geri yüklenecektir. Eşleşmeyen tablolar ise silinmeden kalacaktır.</p>
                                <b>Veritabanı yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(BACKUPDIR)); ?></span><br />
                                <p><b>Web site zip yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(ZIPDIR)); ?></span></p>
                            </div>
                            </div>
                        </div><!-- / <div class="card"> -->
                    </div><!-- / <div id="accordion"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Bilgilendirme Satırı Sonu -->


    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

<form method="POST" action="<?php echo ($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">

      <table class="table" style="min-width: 1000px;">
        <colgroup span="3">
          <col style="width:20%"></col>
          <col style="width:20%"></col>
          <col style="width:60%"></col>
        </colgroup>
        <thead>
          <tr class="bg-primary">
            <th colspan="3" style="text-align:center;line-height: .30;font-size: 1rem;">Yedeklenen Veri Tabanları Listeleme & Geri Yükleme</th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td>Geri Yüklenecek Veritabanı Seç:</td>
            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
            <select class="form-control" name="veritabani_id" id="ekle_veritabani_id" size="1" onChange='this.form.submit();'>
              <option value="0">&nbsp;</option>
                <?php 
                    foreach($veritabanlari_arr AS $id => $veritabani){
                        if(isset($_POST['veritabani_id']) && $_POST['veritabani_id'] == $id || isset($_SESSION['secilen_veritabani_id']) && $_SESSION['secilen_veritabani_id'] == $id){
                            echo "<option value='{$id}' selected>{$veritabani}</option>\n";
                        }else{
                            echo "<option value='{$id}'>{$veritabani}</option>\n";
                        }
                    }
                ?>
            </select>
            </td>
            <td>Geri yüklenecek veritabanı seçiniz</td>
          </tr>
          <tr>
            <td>Yedeklerin bulunduğu dizin:</td>
            <td colspan="2"><span id="yol"><?php echo strtolower(htmlpath(BACKUPDIR)); ?></span></td>
          </tr>

          <tr>
            <td>Deneme Modu Etkinleştir:</td>
            <td>
            Evet: <input onChange='this.form.submit();' type="radio" value="1" name="testmode" <?php echo $yes; ?> />
            Hayır: <input onChange='this.form.submit();' type="radio" value="0" name="testmode" <?php echo $no; ?> />
            </td>
            <td>Deneme modu <strong>Hayır</strong> seçilirse <strong>gerçek</strong> geri yükleme yapar. <strong>Evet</strong> seçilirse gerçek geri yükler <strong>gibi</strong> yedeğin kontrolu yapabilirsniz</td>
          </tr>

          <tr>
            <?php echo $mod_durum; ?>
          </tr>
<?php 
  if(!isset($_GET['start'])){
?>
        <tr>
            <td>Alt dizinden bir klasör seçin:</td>
            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
              <select size="1" name="folder" class="form-control" onchange="this.form.submit()">
                <?php 
                  echo "<option value=''>&nbsp;</option>\n                ";
                  foreach($files AS $key => $value){
                      $selected = $value == $secili_dizin ? ' selected="selected"' : '';
                      echo "<option value='{$value}'{$selected}>{$value}</option>\n                ";
                    }
            ?>
              </select>
            </td>
            <td>Eğer veri tabnının tümünü geri yüklemek yerine belirli tabloları ayrı ayrı geri yüklemek istiyorsanız burada bir klasör seçin</td>
        </tr>
        <tr>
          <td>Seçili Alt-Dizindeki tabloları birleştir</td>
          <td><button type="button" id="merge" class="btn btn-success btn-sm" title="Seçili Alt-Dizindeki Tabloları Birleştir"><i class="fas fa-object-ungroup"></i> Seçili Alt-Dizindeki Tabloları Birleştir </button></td>
          <td>Eğer Alt-Dizindeki tüm tabloları geri yüklemek istiyorsanız tek tek tabloları yüklemek yerine önce tabloları birleştirin ve sonra birleştirilen dosyayı geri yükle</td>
        </tr>
<?php } ?>

        </tbody>
          </table>

                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->

        <a name="tbl" id="tbl" style="scroll-margin-top: 50px;"></a>

<?php

function skin_open() 
{
  echo ('<div class="skin1">');
}

function skin_close() 
{
  echo ('</div>');
}
/*
skin_open();
echo ('<h1>BigDump: Staggered MySQL Dump Importer v'.VERSION.'</h1>');
skin_close();
*/
do_action('after_headline');

$error = false;
$file  = false;

// Check PHP version
// PHP versiyon kontrolu
if (!$error && !function_exists('version_compare'))
{ echo ("<p class='error'>BigDump'ın devam etmesi için PHP sürüm 4.1.0 gereklidir. PHP ".phpversion()." yüklediniz. Üzgünüm!</p>\n");
  $error=true;
}
/*
// mysql eklentisinin mevcut olup olmadığını kontrol edin
if (!$error && !function_exists('mysqli_connect'))
{ echo ("<p class='error'>PHP kurulumunuzda MySQLi uzantısı bulunamadı. PHP'niz MySQL uzantısını destekliyorsa daha eski bir Bigdump sürümünü kullanabilirsiniz.</p>\n");
  $error=true;
}
*/
// PHP maksimum yükleme boyutunu hesaplama (10M veya 100K gibi ayarları ele al)
if (!$error)
{ $upload_max_filesize=ini_get("upload_max_filesize");
  if (preg_match("/([0-9]+)K/i",$upload_max_filesize,$tempregs)) $upload_max_filesize=$tempregs[1]*1024;
  if (preg_match("/([0-9]+)M/i",$upload_max_filesize,$tempregs)) $upload_max_filesize=$tempregs[1]*1024*1024;
  if (preg_match("/([0-9]+)G/i",$upload_max_filesize,$tempregs)) $upload_max_filesize=$tempregs[1]*1024*1024*1024;
}

  
do_action ('script_runs');

// Veritabanına bağlanın, karakter kümesini ayarlayın ve ön sorguları yürütün
if (!$error && !TESTMODE) {

/*
  if (mysqli_connect_error()) 
  { echo ("<p class='error'>".mysqli_connect_error()." nedeniyle veritabanı bağlantısı başarısız oldu.</p>\n");
    echo ("<p>BigDump yapılandırmasında veritabanı ayarlarını düzenleyin veya veritabanı sağlayıcınızla iletişime geçin.</p>\n");
    $error=true;
  }
*/

  if (!$error && $db_connection_charset!=='')
    if($db_yok){
      $PDOdbsecilen->query("SET NAMES $db_connection_charset");
    }


  if (!$error && isset ($pre_query) && sizeof ($pre_query)>0)
  { reset($pre_query);

    foreach ($pre_query as $pre_query_value)
    {	
      if (!$PDOdbsecilen->query($pre_query_value))
        { echo ("<p class='error'>Ön sorguda hata.</p>\n");
          echo ("<p>Query: ".trim(nl2br(htmlentities($pre_query_value)))."</p>\n");
          echo ("<p>MySQL: ".$PDOdbsecilen->error."</p>\n");
          $error=true;
          break;
     }

    }
  }
}
else
{ 
  $dbconnection = false;
}

do_action('database_connected');

// TANI
// echo("<h1>Kontrol Noktası!</h1>");
// Yüklenen dosyaları çoklu dosya modunda listele

if (!$error && !isset($_REQUEST["fn"]) && $filename=="")
{ if ($dirhandle = opendir($upload_dir)) 
  {

    $tum_yedekler_dizisi = array();
    $dizin_yolu = new FilesystemIterator($upload_dir);

    $i=0;
    foreach($dizin_yolu as $dizindeki_dosya) {
        if($dizindeki_dosya->getFilename() != '.htaccess' && $dizindeki_dosya->getFilename() != 'index.html' && !$dizindeki_dosya->isDir() ){
            $tum_yedekler_dizisi[$dizindeki_dosya->getCTime()."-".$i] = $dizindeki_dosya->getFilename(); // Tüm yedek dosyalar
        }
    $i++;
    }
    if(empty($_SESSION['folder'])){
      krsort($tum_yedekler_dizisi); // Birleştirilmiş dosyalar yedekleme tarihine göre sırala
    }else{
      sort($tum_yedekler_dizisi); // Dizin içindeki dosyayı ada göre sırala
    }
    
    //echo '<pre>' . print_r($tum_yedekler_dizisi, true) . '</pre>';

    $dirhead=false;
echo "</table>";
// Geçerli MySQL bağlantı karakter kümesini yazdır
if (!$error && !TESTMODE && !isset($_REQUEST["fn"]))
    {
      $rowkarakter = $varsayilan_karakter_set_adi['DEFAULT_CHARACTER_SET_NAME'] ?? 'geçersiz';
        echo ("<div class='container-fluid'><div class='col-sm-12'><div class='alert alert-warning' style='margin-top:0px;'><div style='text-align: center;'><b>Önemli Not: </b>geri yüklenecek <b><i>".$db_name."</i></b> veri tabanınızın karakter seti <b><i>".$rowkarakter."</i></b> dır.<br />Latin olamayan karakterlerde sorun yaşamamak için yedek veri tabanı dosyanızın <b><i>".$rowkarakter."</i></b> karakter seti ile kodlanmalıdır<br />Yedek dosayı geri yüklerken kullanılacak karakter seti <b><i>".$db_connection_charset."</i></b> dır. Eğer yedek dosyanızın geri yükleme karakter seti yanlış ise <b>Genel Ayarlar</b> bölümünden değiştirebilirsiniz.</div></div></div></div>");
    }

    if (count($tum_yedekler_dizisi)>0)
    {
      $css = 0;
      ?>


    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">


          <table class="table table-sm table-striped table-hover" style="min-width: 1000px;">
            <colgroup span="5">
                <col style="width:50%"></col>
                <col style="width:15%"></col>
                <col style="width:15%"></col>
                <col style="width:10%"></col>
                <col style="width:10%"></col>
            </colgroup>
    
            <thead>
              <tr style="line-height: 1.2;font-size: 1rem;" class="bg-primary">
                <th >Yedek Veri Tabanı Dosya Adı</th>
                <th style="text-align:center;">Boyutu</th>
                <th>Yedekleme/Düzenleme Zamanı</th>
                <th style="text-align:center;">Dosya Tipi</th>
                <th>Yedeği Geri Yükle</th>
              </tr>
            </thead>
            <tbody>
      <?php

      foreach ($tum_yedekler_dizisi as $dirfile)
      { 
        if ($dirfile != "." && $dirfile != ".." && $dirfile!=basename($_SERVER["SCRIPT_FILENAME"]) && preg_match("/\.(sql|gz|csv)$/i",$dirfile))
       
        { 
            if (!$dirhead)
          { 

            $dirhead=true;
          }
          //echo ("<tr><td>$dirfile</td><td class='right'>".filesize($upload_dir.'/'.$dirfile)."</td><td>".date_tr('j F Y, H:i', filemtime($upload_dir.'/'.$dirfile))."</td>");

            if (preg_match("/\.sql$/i",$dirfile))
            $uzanti = "sql";
          elseif (preg_match("/\.gz$/i",$dirfile))
            $uzanti = "gzip";
          elseif (preg_match("/\.csv$/i",$dirfile))
            $uzanti = "CSV";
          else
            $uzanti = "Misc";

          if ((preg_match("/\.gz$/i",$dirfile) && function_exists("gzopen")) || preg_match("/\.sql$/i",$dirfile) || preg_match("/\.csv$/i",$dirfile))
          
          //$iceri_aktar = ("<a class='myButton' href='".$_SERVER["PHP_SELF"]."?start=1&amp;fn=".urlencode($dirfile)."&amp;foffset=0&amp;totalqueries=0&amp;delimiter=".urlencode($delimiter)."'>Geri Yükle</a></td>");
          $iceri_aktar = ("<a class='tikla' href='".$_SERVER["PHP_SELF"]."?start=1&amp;fn=".urlencode($dirfile)."&amp;foffset=0&amp;totalqueries=0&amp;delimiter=".urlencode($delimiter)."#tbl'>Geri Yükle</a>");
          // TODO: echo ("<td><a href='".$_SERVER["PHP_SELF"]."?start=1&amp;fn=".urlencode($dirfile)."&amp;foffset=0&amp;totalqueries=0&amp;delimiter=".urlencode($delimiter)."'>Start Import</a></td>\n <td><a href='".$_SERVER["PHP_SELF"]."?delete=".urlencode($dirfile)."'>Delete file</a></td></tr>\n");
          else
          echo ("&nbsp;");
          ?>
            <tr>
                <td><div class="smallfont"><img src="images/<?php echo $uzanti; ?>.png"> <?php echo $dirfile; ?></div></td>
                <td style="text-align:right;padding-right:70px;"><div class="smallfont"><?php echo showSize(filesize($upload_dir.'/'.$dirfile)); ?></div></td>
                <td style="text-align:right;padding-right:70px;"><div class="smallfont"><?php echo near_date(filemtime($upload_dir.'/'.$dirfile)); ?></div></td>
                <td style="text-align:center;"><div class="smallfont"><?php echo $uzanti; ?></div></td>
                <td style="text-align:center;"><div class="smallfont"><?php echo $iceri_aktar; ?></div></td>
            </tr>
          <?php
          $css++;
        }
      }
    }
echo "</tbody>";
    if ($dirhead) 
      echo ('
            <tfoot>
              <tr>
                <th colspan="5" style="text-align:center;">Veri Tabanı Yedeklemek için "Veri Tabanı Yedekle" Alanında Yedekleyebilirsiniz</th>
              </tr>
            </tfoot>
          </table>
              ');
    else 
      echo ('
    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

          <table class="table table-sm table-striped table-hover" style="min-width: 1000px;">
            <colgroup span="5">
                <col style="width:50%"></col>
                <col style="width:15%"></col>
                <col style="width:15%"></col>
                <col style="width:10%"></col>
                <col style="width:10%"></col>
            </colgroup>
    
            <thead>
              <tr style="line-height: 1.2;font-size: 1rem;" class="bg-primary">
                <th >Yedek Veri Tabanı Dosya Adı</th>
                <th style="text-align:center;">Boyutu</th>
                <th>Yedekleme/Düzenleme Zamanı</th>
                <th style="text-align:center;">Dosya Tipi</th>
                <th>Yedeği Geri Yükle</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th colspan="5" style="text-align:center;">HENÜZ YEDEKLENMİŞ VERİ TABANI YOK</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="5" style="text-align:center;">Veri Tabanı Yedeklemek için "Veri Tabanı Yedekle" Alanında Yedekleyebilirsiniz</th>
              </tr>
            </tfoot>
          </table>
              ');
      //echo ("<p>Çalışma dizinine yüklenmiş SQL, GZ veya CSV dosyası bulunamadı</p>\n");
  }
  else
  { echo ("<p class='error'>Dizin listeleme hatası $upload_dir</p>\n");
    $error=true;
  }
}


// Single file mode
// Tek dosya modu

if (!$error && !isset ($_REQUEST["fn"]) && $filename!="")
{ 
  echo ("<h3 style='text-align: center;'><a class='tikla' href='".$_SERVER["PHP_SELF"]."?start=1&amp;fn=".urlencode($filename)."&amp;foffset=0&amp;totalqueries=0'>Tek Yedek Dosyayı Geri Yükle</a></h3>\n");
}

if (!$error && isset($_REQUEST["start"]))
{ 

// Set current filename ($filename overrides $_REQUEST["fn"] if set)
// Geçerli dosya adını ayarla (ayarlandıysa, $filename $_REQUEST["fn"] öğesini geçersiz kılar)

  if ($filename!="")
    $curfilename=$filename;
  else if (isset($_REQUEST["fn"]))
    $curfilename=urldecode($_REQUEST["fn"]);
  else
    $curfilename="";

// Recognize GZip filename
// GZip dosya adını tanıyın

  if (preg_match("/\.gz$/i",$curfilename)) 
    $gzipmode=true;
  else
    $gzipmode=false;

  if ((!$gzipmode && !$file=@fopen($upload_dir.'/'.$curfilename,"r")) || ($gzipmode && !$file=@gzopen($upload_dir.'/'.$curfilename,"r")))
  { echo ("<p class='error'>İçeri aktarmak için ".$curfilename." dosya açılamıyor</p>\n");
    echo ("<p>Lütfen yedek dosya adınızın yalnızca alfanümerik karakterler içerdiğini kontrol edin ve buna göre yeniden adlandırın, örnek için: $curfilename.".
           "<br>VEYA, tam dosya adını bigdump.php dosyanın içindeki \$filename değişkene tanımlayın. ".
           "<br>VEYA, önce $curfilename dosyayı sunucuya yüklemelisiniz.</p>\n");
    $error=true;
  }

// Get the file size (can't do it fast on gzipped files, no idea how)
// Dosya boyutunu al (gzip'li dosyalarda bunu hızlı yapamazsınız, nasıl yapılacağı hakkında bir fikriniz yok)

  else if ((!$gzipmode && @fseek($file, 0, SEEK_END)==0) || ($gzipmode && @gzseek($file, 0)==0))
  { if (!$gzipmode) $filesize = ftell($file);
    else $filesize = gztell($file);                   // Always zero, ignore // Her zaman sıfır, yoksay
  }
  else
  { echo ("<p class='error'>$curfilename için arama yapamıyorum</p>\n");
    $error=true;
  }

// Stop if csv file is used, but $csv_insert_table is not set
// csv dosyası kullanılıyorsa dur, ancak $csv_insert_table ayarlanmadı

  if (!$error && ($csv_insert_table == "") && (preg_match("/(\.csv)$/i",$curfilename)))
  { echo ("<p class='error'>Bir CSV dosyası kullanırken \$csv_insert_table belirtmelisiniz. </p>\n");
    $error=true;
  }
}


// *******************************************************************************************
// START IMPORT SESSION HERE
// İTHALAT OTURUMUNU BURADAN BAŞLATIN
// *******************************************************************************************

if (!$error && isset($_REQUEST["start"]) && isset($_REQUEST["foffset"]) && preg_match("/(\.(sql|gz|csv))$/i",$curfilename))
{

  do_action('session_start');

// Check start and foffset are numeric values
// Kontrol başlangıcı ve ofset sayısal değerlerdir

  if (!is_numeric($_REQUEST["start"]) || !is_numeric($_REQUEST["foffset"]))
  { echo ("<p class='error'>BEKLENMEYEN: başlangıç ve ofset için sayısal olmayan değerler</p>\n");
    $error=true;
  }
  else
  {	$_REQUEST["start"]   = floor($_REQUEST["start"]);
    $_REQUEST["foffset"] = floor($_REQUEST["foffset"]);
  }

// Set the current delimiter if defined
// Tanımlanmışsa geçerli sınırlayıcıyı ayarla

  if (isset($_REQUEST["delimiter"]))
    $delimiter = $_REQUEST["delimiter"];

// Empty CSV table if requested
// İstenirse CSV tablosunu boşaltın

  if (!$error && $_REQUEST["start"]==1 && $csv_insert_table != "" && $csv_preempty_table)
  { 
    $query = "DELETE FROM `$csv_insert_table`";
    if (!TESTMODE && !$PDOdbsecilen->query(trim($query)))
    { echo ("<p class='error'>$csv_insert_table dan girdiler silinirken hata oluştu.</p>\n");
      echo ("<p>Query: ".trim(nl2br(htmlentities($query)))."</p>\n");
      echo ("<p>MySQL: ".$PDOdbsecilen->error."</p>\n");
      $error=true;
    }
  }
  
// Print start message
// Başlangıç mesajını yazdır
?>
    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">
<?php 

  if (!$error)
  { skin_open();
    if (TESTMODE){
      echo ("<p class='centr' style='color: blue;'><b>DENEME MODU ETKİN</b></p>\n");
    }else{
      echo ("<p class='centr' style='color: red;'><b>GERÇEK YÜKLEME MODU ETKİN</b></p>\n");
    }
    echo ("<p class='centr'>Geri yüklenen veritabanı: <strong>".$curfilename."</strong></p>\n");
    echo ("<p class='smlcentr suan_satir'>İşlenen satır başlangıcı: ".$_REQUEST["start"]."</p>\n");	
    skin_close();
  }

// Check $_REQUEST["foffset"] upon $filesize (can't do it on gzipped files)
// $_REQUEST["foffset"] öğesini $filesize üzerinde kontrol edin (bunu gzip'li dosyalarda yapamazsınız)

  if (!$error && !$gzipmode && $_REQUEST["foffset"]>$filesize)
  { echo ("<p class='error'>BEKLENMEYEN: Dosya işaretçisi dosyanın sonuna ayarlanamıyor</p>\n");
    $error=true;
  }

// Set file pointer to $_REQUEST["foffset"]
// Dosya işaretçisini $_REQUEST["foffset"] olarak ayarla

  if (!$error && ((!$gzipmode && fseek($file, $_REQUEST["foffset"])!=0) || ($gzipmode && gzseek($file, $_REQUEST["foffset"])!=0)))
  { echo ("<p class='error'>BEKLENMEYEN: Dosya işaretçisi ofset olarak ayarlanamıyor: ".$_REQUEST["foffset"]."</p>\n");
    $error=true;
  }

// Start processing queries from $file
// $file'dan sorguları işlemeye başla

  if (!$error)
  { $query="";
    $queries=0;
    $totalqueries=$_REQUEST["totalqueries"];
    $linenumber=$_REQUEST["start"];
    $querylines=0;
    $inparents=false;

// Stay processing as long as the $linespersession is not reached or the query is still incomplete
// $linespersession'a ulaşılmadığı veya sorgu hala eksik olduğu sürece işlemeye devam edin

    while ($linenumber<$_REQUEST["start"]+$linespersession || $query!="")
    {

// Read the whole next line
// Sonraki satırın tamamını oku

      $dumpline = "";
      while (!feof($file) && substr ($dumpline, -1) != "\n" && substr ($dumpline, -1) != "\r")
      { if (!$gzipmode)
          $dumpline .= fgets($file, DATA_CHUNK_LENGTH);
        else
          $dumpline .= gzgets($file, DATA_CHUNK_LENGTH);
      }
      if ($dumpline==="") break;

// Remove UTF8 Byte Order Mark at the file beginning if any
// Varsa dosya başındaki UTF8 Bayt Sıralama İşaretini kaldır

      if ($_REQUEST["foffset"]==0)
        $dumpline=preg_replace('|^\xEF\xBB\xBF|','',$dumpline);

// Create an SQL query from CSV line
// CSV satırından bir SQL sorgusu oluşturun

      if (($csv_insert_table != "") && (preg_match("/(\.csv)$/i",$curfilename)))
      {
        if ($csv_add_slashes)
          $dumpline = addslashes($dumpline);
        $dumpline = explode($csv_delimiter,$dumpline);
        if ($csv_add_quotes)
          $dumpline = "'".implode("','",$dumpline)."'";
        else
          $dumpline = implode(",",$dumpline);
        $dumpline = 'INSERT INTO '.$csv_insert_table.' VALUES ('.$dumpline.');';
      }

// Handle DOS and Mac encoded linebreaks (I don't know if it really works on Win32 or Mac Servers)
// DOS ve Mac kodlu satır kesmeleri işleyin (Win32 veya Mac Sunucularında gerçekten çalışıp çalışmadığını bilmiyorum)

      $dumpline=str_replace("\r\n", "\n", $dumpline);
      $dumpline=str_replace("\r", "\n", $dumpline);
            
// DIAGNOSTIC
// echo ("<p>Line $linenumber: $dumpline</p>\n");

// Recognize delimiter statement

// TANI
// echo ("<p>Line $linenumber: $dumpline</p>\n");

// Ayırıcı deyimi tanıyın

      if (!$inparents && strpos ($dumpline, "DELIMITER ") === 0)
        $delimiter = str_replace ("DELIMITER ","",trim($dumpline));

// Skip comments and blank lines only if NOT in parents
// Yalnızca ebeveynlerde DEĞİLSE yorumları ve boş satırları atla

      if (!$inparents)
      { $skipline=false;
        reset($comment);
        foreach ($comment as $comment_value)
        { 

// DIAGNOSTIC
// TANI
//          echo ($comment_value);
          if (trim($dumpline)=="" || strpos (trim($dumpline), $comment_value) === 0)
          { $skipline=true;
            break;
          }
        }
        if ($skipline)
        { $linenumber++;

// DIAGNOSTIC
// TANI
// echo ("<p>Comment line skipped</p>\n");

          continue;
        }
      }

// Remove double back-slashes from the dumpline prior to count the quotes ('\\' can only be within strings)
// Tırnakları saymadan önce döküm satırından çift ters eğik çizgiyi kaldırın ('\\' yalnızca dizelerin içinde olabilir)

      $dumpline_deslashed = str_replace ("\\\\","",$dumpline);

// Count ' and \' (or " and ') in the dumpline to avoid query break within a text field ending by $delimiter
// $delimiter ile biten bir metin alanı içinde sorgu kırılmasını önlemek için döküm satırında ' ve \' (veya " ve ') sayın

      $parents=substr_count ($dumpline_deslashed, $string_quotes)-substr_count ($dumpline_deslashed, "\\$string_quotes");
      if ($parents % 2 != 0)
        $inparents=!$inparents;

// Add the line to query
// sorgulanacak satırı ekleyin

      $query .= $dumpline;

// Don't count the line if in parents (text fields may include unlimited linebreaks)
// Ebeveynler içindeyse satırı sayma (metin alanları sınırsız satır sonu içerebilir)
      
      if (!$inparents)
        $querylines++;
      
// Stop if query contains more lines as defined by $max_query_lines
// Sorgu, $max_query_lines tarafından tanımlandığı gibi daha fazla satır içeriyorsa dur

      if ($querylines>$max_query_lines)
      {
        echo ("<p class='error'>$linenumber satırında durduruldu. </p>");
        echo ("<p>Bu yerde, geçerli sorgu ".$max_query_lines." den fazla döküm satırı içeriyor. Bu durum, döküm dosyanız her sorgunun sonuna noktalı virgül ve ");
        echo ("ardından satır sonu koymayan bir araç tarafından oluşturulduysa veya dökümünüz genişletilmiş eklemeler veya çok uzun prosedür tanımları ");
        echo ("içeriyorsa olabilir. Daha fazla bilgi için lütfen <a href='https://www.ozerov.de/bigdump/usage/'>BigDump kullanım notlarını</a> ");
        echo ("okuyun. Genişletilmiş ekler içeren döküm dosyalarını işlemek için destek hizmetlerimizi isteyin.</p>\n");
        $error=true;
        break;
      }

// Execute query if end of query detected ($delimiter as last character) AND NOT in parents
// Sorgunun sonu algılanırsa (son karakter olarak $sınırlayıcı) VE ebeveynlerde DEĞİLSE sorguyu çalıştır

// DIAGNOSTIC
// TANI
// echo ("<p>Regex: ".'/'.preg_quote($delimiter).'$/'."</p>\n");
// echo ("<p>In Parents: ".($inparents?"true":"false")."</p>\n");
// echo ("<p>Line: $dumpline</p>\n");

      if ((preg_match('/'.preg_quote($delimiter,'/').'$/',trim($dumpline)) || $delimiter=='') && !$inparents)
      { 

// Cut off delimiter of the end of the query
// Sorgunun sonundaki sınırlayıcıyı kes

        $query = substr(trim($query),0,-1*strlen((string) $delimiter));

// DIAGNOSTIC
// TANI
 // echo ("<p>Query: ".trim(nl2br(htmlentities($query)))."</p>\n");

        if (!TESTMODE && !$PDOdbsecilen->query($query))
        { echo ("<p class='error'>$linenumber satırında hata: ". trim($dumpline)."</p>\n");
          echo ("<p>Query: ".trim(nl2br(htmlentities($query)))."</p>\n");
          echo ("<p>MySQL: ".$PDOdbsecilen->error."</p>\n");
          $error=true;
          break;
        }
        $totalqueries++;
        $queries++;
        $query="";
        $querylines=0;
      }
      $linenumber++;
    }
  }

// Get the current file position
// Geçerli dosya konumunu al

  if (!$error)
  { if (!$gzipmode) 
      $foffset = ftell($file);
    else
      $foffset = gztell($file);
    if (!$foffset)
    { echo ("<p class='error'>BEKLENMEYEN: Dosya işaretçisi ofseti okunamıyor</p>\n");
      $error=true;
    }
  }

// Print statistics
// İstatistikleri yazdır

skin_open();

 echo ("<p class='centr'><img id='yukleniyor' src='images/yukleniyor.gif'><br /><strong>İstatistikler Tablosu</strong></p>\n");

  if (!$error)
  { 
    $lines_this   = $linenumber-$_REQUEST["start"];
    $lines_done   = $linenumber-1;
    $lines_togo   = ' ? ';
    $lines_tota   = ' ? ';
    
    $queries_this = $queries;
    $queries_done = $totalqueries;
    $queries_togo = ' ? ';
    $queries_tota = ' ? ';

    $bytes_this   = $foffset-$_REQUEST["foffset"];
    $bytes_done   = $foffset;
    $kbytes_this  = round($bytes_this/1024,2);
    $kbytes_done  = round($bytes_done/1024,2);
    $mbytes_this  = round($kbytes_this/1024,2);
    $mbytes_done  = round($kbytes_done/1024,2);
   
    if (!$gzipmode)
    {
      $bytes_togo  = $filesize-$foffset;
      $bytes_tota  = $filesize;
      $kbytes_togo = round($bytes_togo/1024,2);
      $kbytes_tota = round($bytes_tota/1024,2);
      $mbytes_togo = round($kbytes_togo/1024,2);
      $mbytes_tota = round($kbytes_tota/1024,2);
      
      $pct_this   = ceil($bytes_this/$filesize*100);
      $pct_done   = ceil($foffset/$filesize*100);
      $pct_togo   = 100 - $pct_done;
      $pct_tota   = 100;

      if ($bytes_togo==0) 
      { $lines_togo   = '0'; 
        $lines_tota   = $linenumber-1; 
        $queries_togo = '0'; 
        $queries_tota = $totalqueries; 
      }

      $pct_bar    = "<div style='text-align: center;font-size: 18px;color:white;height:25px;width:$pct_done%;background-color:#000080;'>$pct_done%</div>";
    }
    else
    {
      $bytes_togo  = ' ? ';
      $bytes_tota  = ' ? ';
      $kbytes_togo = ' ? ';
      $kbytes_tota = ' ? ';
      $mbytes_togo = ' ? ';
      $mbytes_tota = ' ? ';
      
      $pct_this    = ' ? ';
      $pct_done    = ' ? ';
      $pct_togo    = ' ? ';
      $pct_tota    = 100;
      $pct_bar     = "<div style='text-align: center;'>".str_replace(' ','&nbsp;','<tt>[    Gzipli dosyalar i&ccedilin bar kullan&#305;lamaz    ]</tt></div>');
    }
    
    echo ("
    <table id='bartablo' class='center'>
      <tr>
        <th class='bg4'> </th>
        <th class='bg4'>Her Bir Seferde</th>
        <th class='bg4'>Tamamlanan</th>
        <th class='bg4'>Kalan</th>
        <th class='bg4'>Toplam</th>
      </tr>
      <tr>
        <th class='bg4'>Satır sayısı</th>
        <td class='bg3'>$lines_this</td>
        <td class='bg3'>$lines_done</td>
        <td class='bg3'>$lines_togo</td>
        <td class='bg3'>$lines_tota</td>
      </tr>
      <tr>
        <th class='bg4'>Sorgu sayısı</th>
        <td class='bg3'>$queries_this</td>
        <td class='bg3'>$queries_done</td>
        <td class='bg3'>$queries_togo</td>
        <td class='bg3'>$queries_tota</td>
      </tr>
      <tr>
        <th class='bg4'>Bayt</th>
        <td class='bg3'>$bytes_this</td>
        <td class='bg3'>$bytes_done</td>
        <td class='bg3'>$bytes_togo</td>
        <td class='bg3'>$bytes_tota</td>
      </tr>
      <tr>
        <th class='bg4'>KB</th>
        <td class='bg3'>$kbytes_this</td>
        <td class='bg3'>$kbytes_done</td>
        <td class='bg3'>$kbytes_togo</td>
        <td class='bg3'>$kbytes_tota</td>
      </tr>
      <tr>
        <th class='bg4'>MB</th>
        <td class='bg3'>$mbytes_this</td>
        <td class='bg3'>$mbytes_done</td>
        <td class='bg3'>$mbytes_togo</td>
        <td class='bg3'>$mbytes_tota</td>
      </tr>
      <tr>
        <th class='bg4'>%</th>
        <td class='bg3'>$pct_this</td>
        <td class='bg3'>$pct_done</td>
        <td class='bg3'>$pct_togo</td>
        <td class='bg3'>$pct_tota</td>
      </tr>
      <tr>
        <th class='bg4'>% bar</th>
        <td class='bgpctbar' colspan='4'>$pct_bar</td>
      </tr>
    </table>
    \n");

    // Gerçek yüklemede bitti ve betiği yeniden başlat mesajı
    if (!TESTMODE && $linenumber<$_REQUEST["start"]+$linespersession) {
      echo "<br /><div class='alert alert-success'><p class='successcentr'>";
      echo ("Tebrikler, GERÇEK yüklemede herhangi bir sorunla karşılaşmadan yedeğin son satırına ulaşıldı<br />");
      echo ("Buda yedeğin sorunsuz tamamlandığını gösterir<br />");
      echo ("Ancak, yinede ne olur ne olmaz diyerek yüklenen veritabanına ait web sitenin tüm alanlarını kontrol ediniz");
      echo "</p></div>";
      echo '<script>$("#yukleniyor").hide();</script>';
    
      do_action('script_finished');
      $error=true; // This is a semi-error telling the script is finished // Bu, betiğin bittiğini söyleyen bir yarı hatadır

    // DENEME yüklemede bitti ve betiği yeniden başlat mesajı
    }elseif (TESTMODE && $linenumber<$_REQUEST["start"]+$linespersession) {
      echo "<br /><div class='alert alert-success'><p class='successcentr'>";
      echo ("Tebrikler, DENEME yükleme herhangi bir sorunla karşılaşmadan yedeğin son satırına ulaşıldı<br />");
      echo ("Buda yedeğin sorunsuz okunabilir olduğunu gösteriyor<br />");
      echo ("Ancak, GERÇEK yüklemede MySQL sürüme göre değişiklikler veya bazı kısıtlamalara göre yükleme sırasında hatalar alabilirsiniz<br />");
      echo ("Bu tip hataları gidermek için yedeği editörde açıp ilgili alanları düzeltmeniz gerekir");
      echo "</p></div>";
      echo '<script>$("#yukleniyor").hide();</script>';
    
      do_action('script_finished');
      $error=true; // This is a semi-error telling the script is finished // Bu, betiğin bittiğini söyleyen bir yarı hatadır
    }
    else
    { if ($delaypersession!=0)
        echo ("<p class='centr'>Şimdi bir sonraki oturuma başlamadan önce <strong>$delaypersession milisaniye</strong> bekliyorum...</p>\n");
      if (!$ajax) 
        echo ("<script language='JavaScript' type='text/javascript'>window.setTimeout('location.href='".$_SERVER["PHP_SELF"]."?start=$linenumber&fn=".urlencode($curfilename)."&foffset=$foffset&totalqueries=$totalqueries&delimiter=".urlencode($delimiter)."';',500+$delaypersession);</script>\n");

      echo ("<noscript>\n");
      echo ("<p class='centr'><a href='".$_SERVER["PHP_SELF"]."?start=$linenumber&amp;fn=".urlencode($curfilename)."&amp;foffset=$foffset&amp;totalqueries=$totalqueries&amp;delimiter=".urlencode($delimiter)."#tbl'>$linenumber</a> satırından devam edin (Bunu otomatik olarak yapmak için JavaScript'i etkinleştirin)</p>\n");
      echo ("</noscript>\n");
   
      echo ("<p class='centr'>İçe aktarmayı durdurmak için <strong><a class='myButton' href='".$_SERVER["PHP_SELF"]."'>DUR</a></strong> butona basın <strong>VEYA BİTMESİNİ BEKLEYİN!</strong></p>");
    }
  }
  else 
    echo ("<p class='error'>Hatadan dolayı durdu</p>\n");
    skin_close();
  }

  if ($error)
    echo ("<p class='centr'><a class='btn btn-success btn-sm' href='".$_SERVER["PHP_SELF"]."'><span class='glyphicon glyphicon-ok'></span> Başlama Sayfasına Geri Dön</a></p><br />");

if (isset($PDOdbsecilen))

if ($file && !$gzipmode) fclose($file);
else if ($file && $gzipmode) gzclose($file);

?>


<?php do_action('end_of_body'); ?>
</form>

                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->
<br />
        </div><!-- / <div class="content-wrapper"> -->
<script type='text/javascript'>
    var satir = '';
    var query = '';
    var tarih = '';
    var firma = '';
</script>
<?php 
include('includes/footer.php');
?>



<?php

// If error or finished put out the whole output from above and stop
// Eğer hata veya bitmişse, tüm çıktıyı yukarıdan çıkarın ve durdurun

if ($error) 
{
  $out1 = ob_get_contents();
  ob_end_clean();
  echo $out1;
  die;
}

// If Ajax enabled and in import progress creates responses  (XML response or script for the initial page)
// Ajax etkinleştirildiyse ve içe aktarma işlemi devam ediyorsa yanıtlar oluşturur (ilk sayfa için XML yanıtı veya komut dosyası)

if ($ajax && isset($_REQUEST['start']))
{
  if (isset($_REQUEST['ajaxrequest'])) 
  {	ob_end_clean();
      create_xml_response();
      die;
  } 
  else 
    create_ajax_script();	  
}

// Her neyse, çıktıyı yukarıdan çıkar

//ob_flush();

// ANA YAZI BURADA BİTİYOR

// *******************************************************************************************
// Eklenti yönetimi (DENEYSEL)
// *******************************************************************************************

function do_action($tag)
{ global $plugin_actions;
  
  if (isset($plugin_actions[$tag]))
  { reset ($plugin_actions[$tag]);
    foreach ($plugin_actions[$tag] as $action)
      call_user_func_array($action, array());
  }
}

function add_action($tag, $function)
{
    global $plugin_actions;
    $plugin_actions[$tag][] = $function;
}

// *******************************************************************************************
// AJAX yardımcı programları
// *******************************************************************************************

function create_xml_response() 
{
  global $linenumber, $foffset, $totalqueries, $curfilename, $delimiter,
                 $lines_this, $lines_done, $lines_togo, $lines_tota,
                 $queries_this, $queries_done, $queries_togo, $queries_tota,
                 $bytes_this, $bytes_done, $bytes_togo, $bytes_tota,
                 $kbytes_this, $kbytes_done, $kbytes_togo, $kbytes_tota,
                 $mbytes_this, $mbytes_done, $mbytes_togo, $mbytes_tota,
                 $pct_this, $pct_done, $pct_togo, $pct_tota,$pct_bar;

    header('Content-Type: application/xml');
    header('Cache-Control: no-cache');
    
    echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
    echo "<root>";

// veri - hesaplamalar için

    echo "<linenumber>$linenumber</linenumber>";
    echo "<foffset>$foffset</foffset>";
    echo "<fn>$curfilename</fn>";
    echo "<totalqueries>$totalqueries</totalqueries>";
    echo "<delimiter>$delimiter</delimiter>";

// sonuçlar - sayfa güncellemesi için

    echo "<elem1>$lines_this</elem1>";
    echo "<elem2>$lines_done</elem2>";
    echo "<elem3>$lines_togo</elem3>";
    echo "<elem4>$lines_tota</elem4>";
    
    echo "<elem5>$queries_this</elem5>";
    echo "<elem6>$queries_done</elem6>";
    echo "<elem7>$queries_togo</elem7>";
    echo "<elem8>$queries_tota</elem8>";
    
    echo "<elem9>$bytes_this</elem9>";
    echo "<elem10>$bytes_done</elem10>";
    echo "<elem11>$bytes_togo</elem11>";
    echo "<elem12>$bytes_tota</elem12>";
            
    echo "<elem13>$kbytes_this</elem13>";
    echo "<elem14>$kbytes_done</elem14>";
    echo "<elem15>$kbytes_togo</elem15>";
    echo "<elem16>$kbytes_tota</elem16>";
    
    echo "<elem17>$mbytes_this</elem17>";
    echo "<elem18>$mbytes_done</elem18>";
    echo "<elem19>$mbytes_togo</elem19>";
    echo "<elem20>$mbytes_tota</elem20>";
    
    echo "<elem21>$pct_this</elem21>";
    echo "<elem22>$pct_done</elem22>";
    echo "<elem23>$pct_togo</elem23>";
    echo "<elem24>$pct_tota</elem24>";
    echo "<elem_bar>".htmlentities($pct_bar)."</elem_bar>";
                
    echo "</root>";		
}

function create_ajax_script() 
{
  global $linenumber, $foffset, $totalqueries, $delaypersession, $curfilename, $delimiter;
?>

    <script type="text/javascript" language="javascript">			

  // sonraki eylem url'sini oluşturur (yükleme sayfası veya XML yanıtı)
    function get_url(linenumber,fn,foffset,totalqueries,delimiter) {
        return "<?php echo $_SERVER['PHP_SELF'] ?>?start="+linenumber+"&fn="+fn+"&foffset="+foffset+"&totalqueries="+totalqueries+"&delimiter="+delimiter+"&ajaxrequest=true";
    }
    
    // extracts text from XML element (itemname must be unique)
  // metni XML öğesinden çıkarır (öğe adı benzersiz olmalıdır)
    function get_xml_data(itemname,xmld) {
        return xmld.getElementsByTagName(itemname).item(0).firstChild.data;
    }
    
    function makeRequest(url) {
        http_request = false;
        if (window.XMLHttpRequest) { 
        // Mozilla etc.
            http_request = new XMLHttpRequest();
            if (http_request.overrideMimeType) {
                http_request.overrideMimeType("text/xml");
            }
        } else if (window.ActiveXObject) { 
        // IE
            try {
                http_request = new ActiveXObject("Msxml2.XMLHTTP");
            } catch(e) {
                try {
                    http_request = new ActiveXObject("Microsoft.XMLHTTP");
                } catch(e) {}
            }
        }
        if (!http_request) {
                alert("Cannot create an XMLHTTP instance");
                return false;
        }
        http_request.onreadystatechange = server_response;
        http_request.open("GET", url, true);
        http_request.send(null);
    }
    
    function server_response() 
    {

      // doğru yanıt bekleniyor
      if (http_request.readyState != 4)
        return;

      if (http_request.status != 200) 
      {
        alert("Sayfa kullanılamıyor veya yanlış url!")
        return;
      }
        
        // r = xml yanıtı
        var r = http_request.responseXML;
        
        //if received not XML but HTML with new page to show
    // XML değil, gösterilecek yeni sayfayla birlikte HTML alındıysa
        if (!r || r.getElementsByTagName('root').length == 0) 
        {	var text = http_request.responseText;
            document.open();
            document.write(text);		
            document.close();	
            return;		
        }
        
        // " Şimdi işlenen satır başlangıcı:" güncelle
        document.getElementsByClassName('suan_satir')[0].innerHTML = 
            "Şuan işlenen satır başlangıcı: " + 
               r.getElementsByTagName('linenumber').item(0).firstChild.nodeValue;
        
        // tabloyu yeni değerlerle güncelle
        for(i = 1; i <= 24; i++)
            document.getElementById("bartablo").getElementsByTagName("td").item(i-1).firstChild.data = get_xml_data('elem'+i,r);
        
        // renk bar çubuğunu güncelle
        document.getElementById("bartablo").getElementsByTagName("td").item(24).innerHTML = 
            r.getElementsByTagName('elem_bar').item(0).firstChild.nodeValue;
             
        // action url (XML response)
    // eylem url'si (XML yanıtı)
        url_request =  get_url(
            get_xml_data('linenumber',r),
            get_xml_data('fn',r),
            get_xml_data('foffset',r),
            get_xml_data('totalqueries',r),
            get_xml_data('delimiter',r));
        
        // ask for XML response
    // XML yanıtı iste
        window.setTimeout("makeRequest(url_request)",500+<?php echo $delaypersession; ?>);
    }

    // First Ajax request from initial page
  // İlk sayfadan ilk Ajax isteği

    var http_request = false;
    var url_request =  get_url(<?php echo ($linenumber.',"'.urlencode($curfilename).'",'.$foffset.','.$totalqueries.',"'.urlencode($delimiter).'"') ;?>);
    window.setTimeout("makeRequest(url_request)",500+<?php echo $delaypersession; ?>);
    </script>

<?php
}

?>

<script language="javascript" type="text/javascript"> 
      $('.tikla').click(function( e ){
      var url = $(this).attr('href');
      var urlParams = new URLSearchParams(url);
      var yedek = urlParams.get('fn');
      var testmode = $("input[name='testmode']:checked").attr('value');
      var veritabani_id = $('select[name="veritabani_id"] option:selected').val();

    if(veritabani_id=="0") {
        $(function(){
            jw("b olumsuz").baslik("Veritabanı Belirlemediniz!").icerik("Geri yükleyeceğiniz veritabanı seçmelisiniz").kilitle().en(400).boy(100).ac();
        })
        return false;
    }

      if(testmode==undefined) {
      $(function(){
        jw("b olumsuz").baslik("GERİ YÜKLEME MODU SEÇMEDİNİZ").icerik("GERİ YÜKLEME SEÇENEĞİNİ SEÇMEDİNİZ<br /><br />DENEME amaçlı veya GERÇEK yükleme").kilitle().en(350).boy(100).ac();
      })
      return false;
      }
    if(testmode==1){
    var deneme = "<strong>NOT:</strong> Şuanda <strong>DENEME MODUNDA</strong> yükleme yapıyorsunuz<br /><br /><strong>DENEME</strong> Yüklemeye devam etsin mi?";
    } 
    if(testmode==0){
    var deneme = "<strong>NOT:</strong> Şuanda <strong>GERÇEK</strong> yükleme yapıyorsunuz<br /><br /><strong>GERÇEK</strong> Yüklemeye devam etsin mi?";
    }

    $(function()
    {
      jw('b secim',yukle_dur).baslik("Veri Tabanına Geri Yüklemeyi Onayla!").icerik("Geri yüklenecek veri tabanı adı: <strong><?php echo $db_name; ?></strong><br /><br />Veri tabanı veya tablo yedeğin adı: <strong>" + yedek + "</strong><br /><br />" + deneme ).en(450).kilitle().ac();
    })
    function yukle_dur(x){
          if(x==1){
          window.self.location= url ;
          }else{
          return false;
          }
    }
    return false;
    });

$('#merge').click(function( e ){
  var folder = $('select[name="folder"] option:selected').val();
      if(!folder) {
        $(function(){
            jw("b olumsuz").baslik("Önce Alt-Dizin Seçiniz!").icerik("Birleştirmek istediğiniz bir Alt-Dizin seçmelisiniz").kilitle().en(400).boy(100).ac();
        })
        return false;
    }
var bekleme = jw("b bekle").baslik("Veri Tabanı Birleştiriliyor...").en(350).boy(10).kilitle().akilliKapatPasif().ac();
    $.ajax({
        type:'POST',
        url: "yedek_tablolari_birlestir.php",
        data: { folder : folder},
        success: function(msg){
                bekleme.kapat();
                jw("b olumlu").baslik("Veri Tabanı Birleştirme Sonucu").icerik(msg).en(450).boy(10).kilitle().akilliKapatPasif().kapaninca(function(){ window.location.href=window.location.href; <?php unset($_SESSION['folder']) ?> }).ac();     
        }
    });

});
</script>

