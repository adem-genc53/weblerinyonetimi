<?php 
// Bismillahirrahmanirrahim
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");
##########################################################################################################
if(isset($_GET['sil']) == 1){
  unset($_SESSION['altdizin'],$_SESSION['folder'],$_SESSION['test']);
  }
  
  if(!empty($_GET['test'])){
  $_SESSION['test'] = $_GET['test'];
  }
  //define ('TESTMODE', $_SESSION['test']);
  $test = isset($_SESSION['test']) ? $_SESSION['test'] : '';
  
  $yes = $test == 'true' ? ' checked' : '';
  $no = $test == 'false' ? ' checked' : '';
  
  if(isset($_GET['test'])) { 
  $_SESSION['testt'] = $_GET['test'] == 'true' ? true : false;
  }
  $testt = isset($_SESSION['testt']) ? $_SESSION['testt'] : '';
  define('TESTMODE', $testt);

      if(!file_exists(BACKUPDIR)){
          if (!mkdir(BACKUPDIR, 0777, true)) {
              die('Failed to create folder' .BACKUPDIR);
          }
      }

      $secili_dizin = "";

      if(isset($_GET['folder'])){
      $secili_dizin = $_GET['folder'];
      $_SESSION['folder'] = $_GET['folder'];
      }elseif(isset($_SESSION['folder'])){
          $secili_dizin = $_SESSION['folder'];
      }
      $slash = !empty($secili_dizin) == 'gz' ? '/' : '';

    // Dizinleri listeliyoruz
    $files = array();
    $i = 0;
    foreach (new DirectoryIterator(BACKUPDIR) AS $file) {
        if ($file->isDir() && $file->getFilename() != '.' && $file->getFilename() != '..') {
          $files['3-'.$file->getCTime().'-'.$i] = $file->getFilename();
        }
    $i++;
    }
    krsort($files);

    if($test == "true"){
        $mod_durum = '<div style="font-size: 18px;text-align: center;color: blue;font-weight: bold;">DENEME YÜKLEME MODUNDASINIZ</div>';
    }elseif($test == "false"){
        $mod_durum = '<div style="font-size: 18px;text-align: center;color: red;font-weight: bold;">GERÇEK YÜKLEME MODUNDASINIZ</div>';
    }else{
        $mod_durum = '<div style="font-size: 18px;text-align: center;color: grey;font-weight: bold;">YÜKLEME MODU SEÇİLMEDİ</div>';
    }
##########################################################################################################
include('includes/header.php');
include('includes/slidebar_menu.php');
?>

<!-- GÖVDE BAŞLAMA-->
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row" style="margin-bottom: 25px;">
                <div class="col-lg-12">

                    <h3 class="page-header">Veri Tabanı Geri Yükleme</h3>

<form method="GET" action="<?php echo ($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
    <div class="panel panel-default" id="yedekler-listesi">
        <div class="panel-heading">
            <b>Yedeklenen Veri Tabanları Listesi</b>
        </div>
        <!-- /.panel-heading -->
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-bordered" style="min-width: 1000px;">

<colgroup span="6">
  <col style="width:20%"></col>
  <col style="width:15%"></col>
  <col style="width:65%"></col>
</colgroup>

<tbody>
<tr>
  <td colspan="3">Buradan daha önce yedeklediğiniz veri tabanı veya tabloları geri yükleyebilirsiniz<br /><br />Tüm web siteler için geçerli olan herhangi bir saldırı veya yanlışlıkla verilerin silinmesi durumlarda web sitenin geri getirilmesi için zaman zaman belirli aralıklarda veri tabanı yedeklenmesi gerekir.<br /><br />Bu script hem veri tabanı yedekleme hem de geri yükleme imkanı sağlamaktadır.<br />Yönetim panelinde ayarları değiştirme ve veya ürünleri ekleme ve veya silme gibi çalışmalara başlamadan önce veri tabanını yedeklemenizi öneririz hatta tabloları ayrı ayrı yedekleme seçeneğini kullanarak yedeklemenizi öneririz.<br /><br />Tabloları ayrı ayrı yedeklemenin avantajı tüm veri tabını geri yükleme yerine geri getirmek istediğiniz bir veya birden fazla tabloları ayrı ayrı yükleme imkanı sağlamasıdır.<br /><br />Buradan yedeklerinizi geri yükleme yapabileceğiniz gibi DENEME MODUNDA yükleme yaparak alınan yedeğin geri yüklemede herhangi bir sorun çıkarıp çıkarmayacağını yani yedeklemenin sağlıklı yapılıp yapılmadığını da test etmiş olursunuz.<br /><br /><strong>Not:</strong> Buradan geri yükleme yapıldığında yedeğin içinde mevcut tablo adı ile sunucudaki veri tabanındaki tablo adı ile eşleşenler silinerek yerine yedekten geri yüklenecektir. Eşleşmeyen tablo adları ise silinmeden kalacaktır.</td>
  </tr>

  <tr>
    <td colspan="3">Veri Tabanı Yedeği Yükleme Modunu Belirle</td>
  </tr>

<tr>
  <td>Deneme Modu Etkinleştir:</td>
  <td>
  Evet: <input onChange='this.form.submit();' type="radio" value="true" id="show" name="test" <?=$yes?> />
  Hayır: <input onChange='this.form.submit();' type="radio" value="false" id="hide" name="test" <?=$no?> />
  </td>
  <td>Deneme modu <strong>Hayır</strong> seçilirse <strong>gerçek</strong> geri yükleme yapar. Veya <strong>Evet</strong> seçilirse gerçek geri yükler gibi yükleme yapar ancak gerçekte geri yüklemez</td>
  </tr>

  <tr>
    <td colspan="3"><?php echo $mod_durum; ?></td>
  </tr>

  <tr>
    <td colspan="3">Alt Dizinden Veri Tabanı Tablo Yükle</td>
  </tr>

<tr>
  <td>Alt dizinden bir klasör seçin:</td>
  <td>
     <select size="1" name="folder" class="icon-menu" onchange="this.form.submit()">
          <?php
            echo '<option value="">&nbsp;</option>';
            foreach($files AS $key => $value){
                $selected = $value == $secili_dizin ? ' selected="selected"' : '';
                echo '<option value="'.$value.'" style="background-image:url(images/klasor.png)" '.$selected.'>'.$value.'</option>';
              }
          ?>      
        </select>
  </td>
  <td>Eğer veri tabnının tümünü geri yüklemek gerine belirli tabloları ayrı ayrı yüklemek istiyorsanız burada bir klasör seçin</td>
  </tr>

  <tr>
    <td align="center" colspan="3"><strong>BigDump: Staggered MySQL Dump Importer v0.36b</strong></td>
  </tr>

                </table>
            </div>
        </div>
        <!-- /.panel-body -->
    </div>
    <!-- /.panel panel-default -->
</form>

<?php 
//ob_end_flush();
$geriyukle = '1';
include "bigdump.php";
?>


                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </div>
    <!-- /#page-wrapper -->
<!-- GÖVDE SONU -->

<?php 
include('includes/footer.php');
?>

<script language="javascript" type="text/javascript"> 
    function dur(url,yedek){
      var test = $("input[name='test']:checked").attr('value');
      if(test==undefined) {
      $(function(){
        jw("b olumsuz").baslik("GERİ YÜKLEME MODU SEÇMEDİNİZ").icerik("GERİ YÜKLEME SEÇENEĞİNİ SEÇMEDİNİZ<br /><br />DENEME amaçlı veya GERÇEK yükleme").kilitle().en(350).boy(100).ac();
      })
      return false;
      }
    if(test == "true"){
    var deneme = "<strong>NOT:</strong> Şuanda <strong>DENEME MODUNDA</strong> yükleme yapıyorsunuz<br /><br /><strong>DENEME</strong> Yüklemeye devam etsin mi?";
    } 
    if(test == "false"){
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
    } 
</script>