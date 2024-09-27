<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

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
                            <h1 class="m-0">Website Klasörlerin Yedeklenmiş Zip Dosyalar Yönetimi</h1>
                        </div><!-- / <div class="col-sm-6"> -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                                <li class="breadcrumb-item active">Zipli Web Sayfa Dizinleri</li>
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
                                Zipli Dosyaları Açma Hakkında Bilmeniz Gerekenler !
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <p>Burada sunucunuzun ana klasördeki ZIP formatındaki dosyalar listelenir</p>
                                <p>Web sitelerinizi güncelleme öncesi veya herhangi bir sebepten dolayı değişiklik öncesi ZIP formatında yedeklediğiniz dosyaların yedekleme zamanı eklenmiş olarak listeleniyor</p>
                                <p>Aynı web sitenin farklı tarihlere ait dosyalar bulunuyorsa eski zamana ait dosyaları ve artık yedeklemeye gerek kalmayan dosyaları silebilirsiniz</p>
                                <p>Yedekleme eksiksiz olup olmadığını kontrol etmek için klasör sayfadaki dosya sayısı ile buradaki ZIP dosyanın içindeki dosya sayılarının eşit olup olmadığını kontrol edebilirsiniz</p>
                                <p>Sıkıştırılan web dizinlerin <b>.zip</b> uzantılı dosyaların adını tıklayarak bilgisayarınıza indirebilirsiniz.</p>
                                <p><b>NOT:</b> Web Sayfa Klasörleri alanında bir klasörü zip yaparken dosya adına tarih ekleniyor, ancak zip dosyasından dosyaları <i>çıkarırken</i> zip yaparken klasör adı ne idi ise aynı klasör adı oluşturarak dosyaları çıkaracaktır, ancak zipten çıkarırken klasör adını değiştirebilirsiniz.</p>
                                <p><b>üstüne yazma riski yok</b> mesajı, zip açılacağı klasör adında dizinde aynı klasörün olmadığını belirtiyor</p>
                                <p><b>DİKKAT! Ana dizine açıyorsunuz!</b> mesajı, zip dosyanın içeriği ana dizinine açılacağını belirtiyor aynı isimli dosyalar olması durumunda üstüne yazılacaktır ve <b>kesinlikle tavsiye edilmez</b></p>
                                <p><b>DİKKAT! Ana dizin yolu bozuldu</b> mesajı, ana dizin yolunun bozulduğunu belirtiyor, zip açılması sonucu yolun başlangıcı geçersiz ise bu scriptin içine açacaktır, eğer yolun başlangıcı geçerli ise geçerli olan yollardan devam ederek olmayan dizin ise oluşturarak açacaktır ve <b>kesinlikle tavsiye edilmez</b></p>
                                <b>Veritabanı yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(BACKUPDIR)); ?></span><br />
                                <b>Aşağıdaki zipli dosyalar </b><span id="yol"><?php echo strtolower(htmlpath(ZIPDIR)); ?></span> klasörden listeleniyor</p>
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
                        <div id="loading" style='text-align: center;'>
                            <img src="images/ajax-loader.gif" alt="Yükleniyor..." />
                            <br />Zİpli Dosyalar Yükleniyor...
                        </div>
                    <form name="teklifsil" id="gvUsers" method="POST" onsubmit="return false;" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <table id="ziplistesi" class="table table-sm table-striped table-hover" style="min-width: 1000px;">
                                <colgroup span="5">
                                    <col style="width:45%"></col>
                                    <col style="width:15%"></col>
                                    <col style="width:10%"></col>
                                    <col style="width:10%"></col>
                                    <col style="width:10%"></col>
                                    <col style="width:10%"></col>
                                </colgroup>
                            </table>
                            <input type="hidden" id="onceki" value="1">
                        <div>
                            <div>
                                <div style="text-align: center;padding: 0 15px 15px 0;">
                                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirmDel();" /><span class="glyphicon glyphicon-trash"></span> Seçilen zipli dosya(ları) sil </button>
                                </div>
                            </div>
                        </div>
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
    var satir = '';
    var query = '';
    var tarih = '';
    var firma = '';
</script>
<?php 
include('includes/footer.php');
?>

<script type="text/javascript">

    $(function() {
        $("#loading").show();
        $.post('ziplistele.php', 
        function (gelen_cevap) {
            $("#ziplistesi").html(gelen_cevap);
            $("#loading").hide();
        });
    });

</script>

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
        chkB.parentElement.parentElement.style.backgroundColor='#FFEB90';
        chkB.parentElement.parentElement.style.borderBottom='thin solid';
        chkB.parentElement.parentElement.style.color='';
    }else{
        chkB.parentElement.parentElement.style.backgroundColor='';
        chkB.parentElement.parentElement.style.borderBottom='';
        chkB.parentElement.parentElement.style.color='';
    }
}
</script>

<script>
          
function zipcikar(zipadi, dizinadi, dizinvarmi) {
    
    $(document).ready(function () {
        $(document).on("keyup input", "#yenidizinyolu", function () {
            var dizinyolu = document.getElementById('yenidizinyolu').value;
            var dizinyolu = dizinyolu.toLowerCase(); // Tümü küçük harf yap
            //var dizinyolu = dizinyolu.replace(/\/$/, ''); // Yolunda sonunda slah varsa kaldır null dönüş için
            var name = $(this).val().match(/([^\/]*)\/*$/)[1]; // input içindeki son klasör adı / eğik için
            var name = name.match(/([^\\]*)\\*$/)[1]; // input içindeki son klasör adı \ eğik için
            var name = name.toLowerCase(); // Tümü küçük harf yap
            $("#canli").text( name ); // popup pencereye klasör adını yaz

            var yol = "<?php echo KOKYOLU; ?>";
            var yol = yol.toLowerCase(); // Tümü küçük harf yap
            var anadizinbozuldumu = dizinyolu.match(yol); // Kokyolu ile input içindeki kokyolu eşleşiyormu
            var kokarr = yol.split('/'); // yolu / ile parçalayarak diziye al
            var kokarray = kokarr.map(kokarr => kokarr); // açılacak klasör kokyol içinde varmı

            <?php // input içinde dizin adı yoksa Evet butonu gizle ki devam edilemesin ?>
            if(dizinyolu.length==0){
                $('#button_1').hide();
            }else{
                $('#button_1').show();
            }

        });
    });


    var grup = "1";
    var yol = "<?php echo KOKYOLU; ?>";
    if(dizinadi){ // zip yorumda dosya adı varsa
        yenidizinadi = dizinadi;
    }else{
        yenidizinadi = zipadi.substring(0, zipadi.lastIndexOf('.')).split('/').reverse()[0]; // dosya adı için uzantıyı kaldır. slah tan sonraki dosya adını al
    }

    var isim = yol + yenidizinadi;
    var isimname = isim.match(/([^\/]*)\/*$/)[1];
    var pencere = jw('b secim',OK).baslik("ZIP \'ten Çıkar!").akilliKapatPasif().kapatPasif()
    .icerik("<p>ZIP \'ten çıkarılacak dosya adı: <b>" + zipadi.split('/').reverse()[0] + "</b></p> <p id='dizinvarmi'></p> <p><div class='editable' data-placeholder='" + yol + "'><input type='text' value='" + yenidizinadi +"' id='yenidizinyolu' /></div></p><div style='padding-bottom:5px;'>ZIP 'ten çıkarılacağı klasörün adı: <b><span id='canli'>" + isimname +"</span></b></div><div id='geridizin' style='display:none;color:blue;padding-bottom:5px;font-weight: bold;'></div><div>Zipli dosyayı çıkarmak istediğinizden emin misiniz?</div>")
    .en(650).ac();

    // Zip dosyalar listelenirken açılacak klasör adı dizin varmı yok mu mesajı
    if(dizinvarmi==1){
        $("#dizinvarmi").html("<span style='font-size: 16px;color:blue;'><b>DİKKAT!</b></span> Bu <b style='font-size: 12px;color:blue;'>" + dizinadi + "</b> klasör dizinde mevcut. Eğer buraya açarsanız dosyaların üzerine yazılacaktır.");
    }else{
        $("#dizinvarmi").html("Bu <b style='font-size: 12px;color:blue;'>" + dizinadi + "</b> klasör dizinde mevcut değil, üstüne yazma riski yok. Açabilirsiniz.");
    }

    <?php // Popup penceredeki butonları kontrol etmek için ID ekliyoruz. setTimeout() ile bekletiyoruz ki popup pencere açılsın butonlar oluşsun ?>
    const myTimeout = setTimeout(idver, 1);
    function idver() {
        var i=0;
        $('.jw-t-standart').each(function(){
            i++;
            var newID='button_'+i;
            $(this).attr('id',newID);
        });
        $('#button_1').html('Evet, Zipten Çıkar');
    }
        
    $("#yenidizinyolu").on("keypress keyup input", function(event) {

        var dizinyolu = document.getElementById('yenidizinyolu').value;
        var yol = "<?php echo KOKYOLU; ?>";
        var anadizinbozuldumu = dizinyolu.match(yol);
        showResult(dizinyolu)

        var englishAlphabetAndWhiteSpace = /[.A-Za-z0-9-_/]/g;
        var key = String.fromCharCode(event.which);
            if (event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 39 || englishAlphabetAndWhiteSpace.test(key)) {
                return true;
            }
            return false;
    });

function showResult(str) {

  var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if (this.readyState==4 && this.status==200) {
      document.getElementById("dizinvarmi").innerHTML=this.responseText;
      //document.getElementById("dizinvarmi").style.border="1px solid #A5ACB2";
    }
  }
  xmlhttp.open("GET","dizin_varmi.php?dizin="+str,true);
  xmlhttp.send();
} 

function OK( x, geridizinac ){
    if(x==1){
        var dizinyolu = document.getElementById('yenidizinyolu').value;
        var yol = "<?php echo KOKYOLU; ?>";
        var anadizinbozuldumu = dizinyolu.match(yol);
        var hamyol = parseInt(yol.length);
        var yeniyol = parseInt((dizinyolu.length));

    if( dizinyolu=="" && geridizinac==undefined) {
        pencere.kapat();
        jw("b olumsuz").baslik("Klasör Adını Girmediniz").icerik("Zipin açılacağı klasör adını girmediniz<br /><br />Zipin açılacağı klasör adı belirlenmelidir.").kilitle().en(450).boy(100).ac();
    } else {
        pencere.kapat();
        var bekleme = jw("b bekle").baslik("ZIP \'ten Çıkarılıyor...").icerik("<b>" + zipadi.split('/').reverse()[0] + "</b><br /><br />Dosyası çıkarılıyor. Lutfen bekleyin...").en(450).boy(10).kilitle().akilliKapatPasif().ac();
        $.post('zipcikar.php', {grup: 1, zipdosya: zipadi, dizinyolu: yol + dizinyolu},
        function (gelen_cevap) {
		   bekleme.kapat();
       jw("b olumlu").baslik("ZIP \'ten Çıkarma Sonucu").icerik(gelen_cevap).en(450).boy(10).kilitle().akilliKapatPasif().ac();    
       });
    }
    } else {
        pencere.kapat();
     }
}
  getTextWidth()
}
// JavaScript Prompt
</script>


<script language="javascript">
function confirmDel() {

	var inputElems = document.querySelectorAll('input[name="delete_ziplidizinler[]"]:checked'), count = 0;
  
	for (var i=0; i<inputElems.length; i++) {
		if (inputElems[i].type === 'checkbox' && inputElems[i].checked === true) {
			count++;
		}
	}

if (count<1){
  $(function(){
	   jw("b olumsuz").baslik("Silinecek dosya(lar) seçilmemiş").icerik("Silinecek zipli dosya(lar) seçmediniz!").kilitle().en(450).boy(100).ac();
  })  
  return false;
  }
  
if (count>0){
  $(function()
  {
	   jw('b secim',OK).baslik("Zipli Dosyaları Silmeyi Onayla").icerik("Zipli dosyaları silmek istediğinizden emin misiniz?").en(450).kilitle().ac();
  })
  return false;
}

function OK(x){
	    if(x==1){
	var bekleme = jw("b bekle").baslik("Zipli Dosyalar siliniyor...").en(450).boy(10).kilitle().akilliKapatPasif().ac();
    var str = 'grup=ziplidizinsil';
    var t = $('#gvUsers').serialize();
    (t !='')? str += '&'+t :'';    
    xhr = $.ajax({
       type: "POST",
       url: "dosyasil.php",
       data: str,
       success: function(veriler){       
		   bekleme.kapat();
       jw("b olumlu").baslik("Zipli Dosyalar Silme Sonucu").icerik(veriler).en(450).boy(10).kilitle().akilliKapatPasif().kapaninca(function(){ window.location.href='<?php echo $_SERVER['REQUEST_URI']; ?>' }).ac();
       }
      });             
     } //if(x==1){
    } //function DUR(x){
}
</script>

<style>
    .editable
        {
            position: relative;
            /*border: 1px solid gray;*/
            padding-top: 1px;
            /*background-color: white;*/
            box-shadow: rgba(0,0,0,0.4) 2px 2px 2px inset;
        }

    .editable > input
        {
            /*position: relative;
            z-index: 1;*/
            border-color: white;
            /*background-color: transparent;
            box-shadow: none;*/
            width: 100%;
            /*padding-left: 40px;*/
        }

    .editable::before
        {
            position: absolute;
            left: 4px;
            top: 5px;
            content: attr(data-placeholder);
            pointer-events: none;
            opacity: 1;
            z-index: 1;
        }
</style>

<script type="text/javascript">
    function getTextWidth() {
        inputText = "<?php echo KOKYOLU; ?>";
        font = "14px Helvetica Neue";

        canvas = document.createElement("canvas");
        context = canvas.getContext("2d");
        context.font = font;
        width = context.measureText(inputText).width;
        formattedWidth = Math.ceil(width) + "px";
        $('.editable').css('padding-left', formattedWidth);
    }
</script>