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
                            <h1 class="m-0">Website Klasörlerin Yönetimi</h1>
                        </div><!-- / <div class="col-sm-6"> -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                                <li class="breadcrumb-item active">Web Sayfa Dizinleri</li>
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
                                Website Dizinleri Yönetimi Hakkında Bilmeniz Gerekenler !
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                            <p>Burada sunucunuzun ana klasördeki web sitelerinize ait klasörleri listeleniyor</p>
                            <p>Web sitelerinizi güncelleme öncesi veya herhangi bir sebepten dolayı değişiklik öncesi ZIP formatında yedeklediğiniz dosyaların yedekleme zamanı eklenmiş olarak listeleniyor</p>
                            <p>Aynı web sitenin farklı tarihlere ait dosyalar bulunuyorsa eski zamana ait dosyaları ve artık yedeklemeye gerek kalmayan dosyaları silebilirsiniz</p>
                            <p>Yedekleme eksiksiz olup olmadığını kontrol etmek için klasör sayfadaki dosya sayısı ile buradaki ZIP dosyanın içindeki dosya sayılarının eşit olup olmadığını kontrol edebilirsiniz</p>
                            <p><b>NOT:</b> Web Sayfa Klasörleri alanında bir klasörü zip yaparken dosya adına tarih ekleniyor, ancak zip dosyasından dosyaları <i>çıkarırken</i> zip yaparken klasör adı ne idi ise aynı klasör adı oluşturarak dosyaları çıkaracaktır, ancak zipten çıkarırken klasör adını değiştirebilirsiniz.</p>
                            <b>Veritabanı yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(BACKUPDIR)); ?></span><br />
                            <b>Web site zip yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(ZIPDIR)); ?></span><br />
                            Aşağıdaki klasörler <span id="yol"><b><?php echo strtolower(htmlpath(DIZINDIR)); ?></b></span> klasörden listeleniyor</p>
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
                            <br />Klasörler Yükleniyor...
                        </div>
                <form name="dizinler" method="POST" onsubmit="return false;" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <table id="dizinlistesi" class="table table-sm table-striped table-hover" style="min-width: 1000px;">
                        <colgroup span="5">
                            <col style="width:45%"></col>
                            <col style="width:15%"></col>
                            <col style="width:15%"></col>
                            <col style="width:15%"></col>
                            <col style="width:10%"></col>
                        </colgroup>
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
    var satir = '';
    var query = '';
    var tarih = '';
    var firma = '';
</script>
<?php 
include('includes/footer.php');
?>

<script type="text/javascript">

function sikistir(dizinadi) {

    var pencere = jw('b secim',OK).baslik('Klasörü Zip Arşivi Oluştur').akilliKapatPasif().kapatPasif()
    .icerik("Zİp Arşivi oluşturulacak klasörün adı:<b>" + dizinadi + "</b><br /><br />Zip Arşivin dosya adını değiştirebilirsiniz<br /><br /><b id='dizinbos' style='color:blue;'></b><input id='ziparsivadi' value=" + dizinadi + " size='60' /><br /><br />Web klasörü Zip Arşivi oluşturmak istediğinizden emin misiniz?").en(450).ac();

    const myTimeout = setTimeout(idver, 1);
    function idver() {
        var i=0;
        $('.jw-t-standart').each(function(){
            i++;
            var newID='button_'+i;
            $(this).attr('id',newID);
        });
    }

    $("#ziparsivadi").on("keypress keyup input", function(event) {

        var dizinadi = document.getElementById('ziparsivadi').value;
        if(dizinadi==''){
            $("#dizinbos").html("ZİP DOSYA ADI GİRİLMESİ ZORUNLUDUR<br /><br />");
            $("#button_1").hide();
        }else{
            $("#dizinbos").html("");
            $("#button_1").show();
        }

        var englishAlphabetAndWhiteSpace = /[A-Za-z0-9-_.]/g;
        var key = String.fromCharCode(event.which);
            if (event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 39 || englishAlphabetAndWhiteSpace.test(key)) {
                return true;
            }
            return false;
    });

function OK(x){
    var ziparsivadi = document.getElementById('ziparsivadi').value;
    var dizindir = '<?php echo DIZINDIR; ?>';
    if(x==1){

        var bekleme = jw("b bekle").baslik("Zip Arşivi Oluşturuluyor...").icerik("Lutfen bekleyin...",function(){ xhr.abort(); bekleme.kapat(); }).en(350).boy(10).kilitle().akilliKapatPasif().ac();
            $.post('zipyap.php', {zipyap: "1", dizinadi: dizinadi, ziparsivadi: ziparsivadi, dizindir: dizindir }, 
            function (gelen_cevap) {
            bekleme.kapat();
            jw("b olumlu").baslik("Zip Arşivleme Sonucu").icerik(gelen_cevap).en(350).boy(10).kilitle().akilliKapatPasif().ac();
        });

    } else {
        pencere.kapat();
     }
}

}
</script>

<script type="text/javascript">

  $(function() {
       $("#loading").show();
       $.post('dizinlistele.php', 
       function (gelen_cevap) {
       $("#dizinlistesi").html(gelen_cevap);
       $("#loading").hide();          
       });
  });

</script>

<script>
function dizinadidegistir(dizinadi) {
    var pencere = jw('b secim',OK).baslik('Klasör adını değiştir').akilliKapatPasif().kapatPasif().icerik("Değiştireceğiniz klasör adı:<b>" + dizinadi + "</b><br /><br />Yeni klasör adını giriniz<br /><br /><b id='yenidizinbos' style='color:blue;'></b><input id='yenidizinadi' value=" + dizinadi + " size='60' /><br /><br />Klasör adını değiştirmek istediğinizden emin misiniz?").en(450).ac();

    const myTimeout = setTimeout(idver, 1);
    function idver() {
        var i=0;
        $('.jw-t-standart').each(function(){
            i++;
            var newID='button_'+i;
            $(this).attr('id',newID);
        });
        $("#button_1").hide();
    }

    $("#yenidizinadi").on("keypress keyup input", function(event) {

        var girilendizinadi = document.getElementById('yenidizinadi').value;
        if(dizinadi==girilendizinadi){
            $("#yenidizinbos").html("AYNI KLASÖR ADI BELİRLİYORSUNUZ<br /><br />");
            $("#button_1").hide();
            console.log("aa");
        }else if(dizinadi != girilendizinadi && girilendizinadi != ''){
            $("#button_1").show();
            $("#yenidizinbos").html("");
        }else if(girilendizinadi==''){
            $("#yenidizinbos").html("KLASÖR ADI GİRİLMESİ ZORUNLUDUR<br /><br />");
            $("#button_1").hide();
        }else{
            $("#yenidizinbos").html("");
            //$("#button_1").show();
        }

        var englishAlphabetAndWhiteSpace = /[A-Za-z0-9-_.]/g;
        var key = String.fromCharCode(event.which);
            if (event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 39 || englishAlphabetAndWhiteSpace.test(key)) {
                return true;
            }
            return false;
    });

function OK(x){
    var yeniadi = document.getElementById('yenidizinadi').value;
    if(x==1){

        var bekleme = jw("b bekle").baslik("Klasör Adı Değiştiriliyor...").icerik("<b>" + dizinadi + "</b> klasör adı <b>" + yeniadi + "</b> adı<br />olarak değiştiriliyor. Lutfen bekleyin...",function(){ xhr.abort(); bekleme.kapat(); }).en(450).boy(10).kilitle().akilliKapatPasif().ac();
            $.post('dizinadidegistir.php', {grup: "1", eskidizinadi: dizinadi, yenidizinadi: yeniadi}, 
            function (gelen_cevap) {
            bekleme.kapat();
            jw("b olumlu").baslik("Klasör Adı Değiştirme Sonucu").icerik(gelen_cevap).en(350).boy(10).kilitle().akilliKapatPasif().kapaninca(function(){ window.location.href=window.location.href }).ac();
        });

    } else {
        pencere.kapat();
     }
}

}
// JavaScript Prompt // açılan pencere içine input ekler

</script>