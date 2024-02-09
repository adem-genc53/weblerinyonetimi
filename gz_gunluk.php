<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");
##########################################################################################################
    if(isset( $_POST['silid'] ) ){

        $sil = $PDOdb->prepare("DELETE FROM zamanlanmisgorev_gunluk WHERE id = ?");
        foreach($_POST['silid'] AS $silid){
            $sil->execute([$silid]);
            if($sil->rowCount()){
                $messages[] = $silid." ID nolu günlük başarıyla silindi<br />";
            }else{
                $errors[] = $silid." ID nolu günlük bir hatadan dolayı silinemedi<br />";
            }
            header("Refresh: 2; url=".htmlspecialchars($_SERVER["PHP_SELF"])."?");
        }
    }
##########################################################################################################
  $gunluk_ilk_son = $PDOdb->prepare("SELECT calisma_zamani FROM zamanlanmisgorev_gunluk WHERE calisma_zamani>'0' ");
  $gunluk_ilk_son->execute();
  $gunluktarih = [];
  while($row = $gunluk_ilk_son->fetch()){
    $gunluktarih[] = date_tr('Y-m-d', $row['calisma_zamani']);
  }
    $gunluk_tarihi = "";
    $gunluktarih = array_unique($gunluktarih);
    $keys = array_keys($gunluktarih);
    $last = end($keys);
  foreach($gunluktarih AS $key=>$tarih){
      if($last != $key){
        $gunluk_tarihi .= '"'.$tarih.'", ';
      }else{
          $gunluk_tarihi .= '"'.$tarih.'"';
      }
  }
##########################################################################################################
include('includes/header.php');
include('includes/navigation.php');
include('includes/sub_navbar.php');
?>

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
                                <li class="breadcrumb-item active">Görev Günlükleri</li>
                            </ol>
                        </div><!-- / <div class="col-sm-6"> -->
                    </div><!-- / <div class="row mb-2"> -->
                </div><!-- / <div class="container-fluid"> -->
            </div><!-- / <div class="content-header"> -->

<?php 
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                //echo '<pre>' . print_r($_POST, true) . '</pre>';
            }
            if (isset($errors)) {
                echo "<div class='uyari'>";
                echo "<span title='Kapat' class='closebtn'>&times;</span>";
                echo "<span class='baslik'><span class='glyphicon glyphicon-ban-circle'></span></span><br />";
                foreach ($errors AS $error) {
                    echo $error."<br />";
                }
                echo "</div>";
            }
            if (isset($messages)) {
                echo "<div class='uyari success'>";
                echo "<span title='Kapat' class='closebtn'>&times;</span>";
                echo "<span class='baslik'><span class='glyphicon glyphicon-ok'></span></span><br />";                
                foreach ($messages AS $message) {
                    echo $message."</strong>";
                }
                    echo "</div>";
            }
?>

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
                                Websiteleri Yedekleme Yönetimi Hakkında Bilmeniz Gerekenler !
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
<p>Buradan günlüğü etkinleştirilen zamanlanmış görevlerin yerine getirilmesinin sonucunu ve görevin ne zaman yerine getirildiğini ve ne kadar zaman aldığını görmenizi sağlar.
</p>
<p>Bu sayede zamanlanmış görevlerin sonuçlarını takip edebilirsiniz.
</p>
<p>Veritabanı boyutunun artması sorun teşkil ediyorsa zaman zaman eski günlükleri sile bilirsiniz.
</p>
                                <b>Veritabanı yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath('./'.BACKUPDIR)); ?></span><br />
                                <p><b>Web site zip yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath('./'.ZIPDIR)); ?></span></p>
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

                                <form method="POST" id="gonder">            
                                            <table class="table table-sm table-striped table-hover yukleniyor" id="gvUsers" style="min-width: 1000px;">
                                            <colgroup span="6">
                                                <col style="width:15%"></col>
                                                <col style="width:25%"></col>
                                                <col style="width:15%"></col>
                                                <col style="width:25%"></col>
                                                <col style="width:15%"></col>
                                                <col style="width:5%"></col>
                                            </colgroup>
                                                 <thead>
                                                    <tr class="bg-primary" style="line-height: 1.2;font-size: 1rem;">
                                                        <th>Çalıştığı Zaman</th>
                                                        <th>Görev Adı</th>
                                                        <th>Çalıştırılan Dosya</th>
                                                        <th>Çalışma Sonucu</th>
                                                        <th>Çalışma Süresi (S-D-S-m)</th>
                                                        <th style="text-align:right;">Sil <input type="checkbox" title="Tümünü seçmek için tıklayın" onclick="javascript:tumunu_sec(this);" /></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="satirlar">
                                                    <tr>
                                                        <td class="ilk-yukleniyor" colspan="6">&nbsp;</td>
                                                    </tr>
                                                </tbody>
                                            <tfoot>
                                                <tr>
                                                <td colspan="6"><div style="width:50%; display:inline-block;"><div id="linkler"></div></div><div style="width:50%; display:inline-block;">
                                            <div style="float:right;">
                                            
                                            <span class="takvim" data-allow-input="false" data-wrap="true" data-click-opens="true" style="float:left; padding-right: 10px;">
                                                    <a href="javascript:void(0)" data-clear="" title="Tarihi Temizle"><i class="fa fa-times" aria-hidden="true"></i></a>&nbsp;&nbsp;
                                                    <a href="javascript:void(0)" data-toggle="" title="Bir Tarih Seç"><i class="fa fa-calendar" aria-hidden="true"></i></a>&nbsp;
                                                <input data-input="" title="Bir Tarih Seç" type="text" id="tarih" style="width: 80px;" placeholder="Tarih Seç">
                                            </span>

                                        <input type="text" autocomplete="off" id="search" list="aranacaklar" placeholder="İçerik Ara / Çift Tıkla"> Sayfada
                                                
                                                <select name="sayfada" id="sayfada">
                                                    <option value="5">5</option>
                                                    <option value="15" selected>15</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                    <option value="250">250</option>
                                                    <option value="500">500</option>
                                                    <option value="999">999</option>
                                                    <option value="-1">Hepsi</option>
                                                </select> Satır Göster | Tümünü Seç <input type="checkbox" title="Tümünü seçmek için tıklayın" onclick="javascript:tumunu_sec(this);" />
                                                </div></div></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" style="text-align:center;">
                                                        <button class="btn btn-warning btn-sm" name="gunluk_sil" value="1" id="sil" /><span class="glyphicon glyphicon-trash"></span> Seçilen zipli dosya(ları) sil </button>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                            </table>
                                        </form>
 
                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->



        </div><!-- / <div class="content-wrapper"> -->
        
<script type='text/javascript'>
    var satir = "zamanlanmisgorev_gunluk";
    var query = '';
    var tarih = '';
    var firma = '';
</script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script async>
        flatpickr(".takvim", {
            disableMobile: "true",
            allowInvalidPreload: "true",
            dateFormat: "Y-m-d",
            minDate: "<?php echo current($gunluktarih); ?>",
            enable: [ <?php echo $gunluk_tarihi; ?> ],
            maxDate: "<?php echo end($gunluktarih); ?>",
            locale: {
            firstDayOfWeek: 1,
            weekdays: {
            longhand: ['Pazar', 'Pazartesi','Salı','Çarşamba','Perşembe', 'Cuma','Cumartesi'],
            shorthand: ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt']
            },
            months: {
            longhand: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos', 'Eylül','Ekim','Kasım','Aralık'],
            shorthand: ['Oca','Şub','Mar','Nis','May','Haz','Tem','Ağu','Eyl','Eki','Kas','Ara']
            },
            today: 'Bugün',
            clear: 'Temizle'
            }
        });
	</script>

<?php 
include('includes/footer.php');
?>

<script type="text/javascript"> 
function tumunu_sec(spanChk){       
           var IsChecked = spanChk.checked;
           var Chk = spanChk;
              Parent = document.getElementById('gvUsers');          
              var items = Parent.getElementsByTagName('input');                         
              for(i=0;i<items.length;i++)
              {               
                  if(items[i].id != Chk && items[i].type=="checkbox")
                  {
                      if(items[i].checked!= IsChecked)
                      {    
                          items[i].click();    
                      }
                  }
              }            
       }  
</script>

<script type="text/javascript">  
function renk(chkB){
    var IsChecked = chkB.checked;           
    if(IsChecked){
        chkB.parentElement.parentElement.parentElement.style.backgroundColor='#FFEB90';
        chkB.parentElement.parentElement.parentElement.style.borderBottom='thin solid';
        chkB.parentElement.parentElement.parentElement.style.color='';
    }else{
        chkB.parentElement.parentElement.parentElement.style.backgroundColor='';
        chkB.parentElement.parentElement.parentElement.style.borderBottom='';
        chkB.parentElement.parentElement.parentElement.style.color='';
    }
}
</script>

<script type='text/javascript'>
$( "#sil" ).click(function() {

        var inputElems = document.querySelectorAll('input[name="silid[]"]:checked'), count = 0;
    
        for (var i=0; i<inputElems.length; i++) {
            if (inputElems[i].type === 'checkbox' && inputElems[i].checked === true) {
                count++;
            }
        } 
      if (count < 1){
          $(function(){
            jw("b olumsuz").baslik("Günlük Seçmediniz").icerik("Silinecek günlük(leri) seçmediniz").kilitle().en(350).boy(100).ac();
          })
          return false;
      }

      $(function()
        {
        jw('b secim',sil_dur).baslik("Günlükleri Silmeyi Onayla").icerik("Günlükleri silmek istediğinizden emin misiniz?<br /><br />Veri tabanını yedeklemediniz ise Silmenin geri dönüşü yoktur").en(450).kilitle().ac();
        })
      return false;

      function sil_dur(x){
        if(x==1){
            $("#gonder").submit();
        }
      }
});
</script>
