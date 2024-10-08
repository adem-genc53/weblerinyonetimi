<?php 
// Bismillahirrahmanirrahim
header('Connection: Keep-Alive');
header('Keep-Alive: timeout=5, max=100');
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); // 7200 saniye 120 dakikadır, 3600 1 saat

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    //echo '<pre>' . print_r($_POST, true) . '</pre>';
    //exit;
}

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
                                <li class="breadcrumb-item active">Veritabanı Google'la Yedekle</li>
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
                                <p>Buradan veritabanı yedekler dizinden uzak Google Drive sunucuya veritabanı yedekleri elle yedekyebilirsiniz.
                                </p>
                                <p>İster tek dosya olarak ister dizin seçilerek içindeki tüm yedekleri uzak Google Drive hesabına elle yedekleyebilirsiniz
                                </p>
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
<?php 
    $error = false;
    if (!(PHP_VERSION_ID >= 80100)) {
        echo ("<div style='font-weight: bold;font-size: 16px;text-align:center;font-family: Arial, Helvetica, sans-serif;'>Google Drive Kütüphanesi En Düşük \">= 8.1.0\" PHP sürümünü gerektirir. Siz " . PHP_VERSION . " Çalıştırıyorsunuz.</div>");
        $error = true;
    }
    if (!file_exists(AUTHCONFIGPATH)) {
        echo 'Hata: AuthConfig dosyası bulunamadı.';
        die('Hata: AuthConfig dosyası bulunamadı.');
    }

    if(!$error){
?>
    <form method="POST">
    <div class="row">
        <div class="col-sm-12 p-3 text-center">
            <div class="p-1 bg-primary text-white"><strong>Yerel Veritabani Dizini/Dosyayı Google Drive Sunucuya Yedekleme</strong></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 p-3">
                <div class="input-group">
                <span class="input-group-text" style="width: 170px;">Yerel Veritabani Kaynak</span>
            <input class="form-control" type="text" id="yerel_den_secilen_dosya" name="yerel_den_secilen_dosya" />
                </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 p-3">
                <div class="input-group">
                <span class="input-group-text" style="width: 170px;">Uzak Google Drive Hedef</span>
            <input class="form-control" type="text" id="google_drive_dan_secilen_dosya_adini_goster" disabled style="background-color: #fff;" />
            <input type="hidden" id="google_drive_dan_secilen_dosya_id" name="google_drive_dan_secilen_dosya_id" />
            <input type="hidden" id="google_drive_dan_secilen_dosya_id_sil" name="google_drive_dan_secilen_dosya_id_sil" />
                </div>
        </div>
    </div>

    <div class="text-center p-3">
        <button type="button" class="btn btn-success btn-sm" onclick="javascript:uzakSunucuyaYukle();"><i class="fa fa-upload" aria-hidden="true"></i> Google Drive'a Yükle </button>
    </div>
    </form>
<?php } ?>
                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->


    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">
<?php 
if(!$error){
?>
    <div class="row">
        <div class="col-sm-6 p-3"><div class="p-1 bg-primary text-white"><strong>Yerel Veritabani Yedekleri</strong></div>
            <div id="yerel_dizin_agac"></div>
            <button type="button" class="btn btn-warning btn-sm" style="margin-top: 15px;" onclick="return yerelOgeleriSil();"><span class="glyphicon glyphicon-trash"></span> Seçilen Öğeyi Sil </button>
        </div>
        <div class="col-sm-6 p-3"><div class="p-1 bg-primary text-white"><strong>Uzak Google Drive Sunucu</strong></div>
            <div id="google_drive_uzaktan_agac"></div>
            <button type="button" class="btn btn-warning btn-sm" style="margin-top: 15px;" onclick="return googleDriveSil();"><span class="glyphicon glyphicon-trash"></span> Seçilen Öğeyi Sil </button>
        </div>
    </div>
<?php } ?>
                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->


<br />
        </div><!-- / <div class="content-wrapper"> -->

        

<?php 
include('includes/footer.php');
?>
<link rel="stylesheet" href="css/filetree.css" type="text/css" >

<script language="javascript">
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    var gif =
    {
        lines: 10, // The number of lines to draw
        length: 3, // The length of each line
        width: 7, // The line thickness
        radius: 15, // The radius of the inner circle
        corners: 0.7, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        color: '#BFDBDD', // #rgb or #rrggbb
        speed: 1.2, // Rounds per second
        trail: 40, // Afterglow percentage
        shadow: true, // Whether to render a shadow
        hwaccel: false, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: 'auto', // Top position relative to parent in px
        left: 'auto' // Left position relative to parent in px
    }

    function uzakSunucuyaYukle() {
        var yerel_den_secilen_dosya = $('#yerel_den_secilen_dosya').val();
        var google_drive_dan_secilen_dosya_id = $('#google_drive_dan_secilen_dosya_id').val();
        var google_drive_dan_secilen_dosya_adini_goster = $('#google_drive_dan_secilen_dosya_adini_goster').val();
        var dosyami_dizinmi = google_drive_dan_secilen_dosya_adini_goster.replace(/^.*?\.([a-zA-Z0-9]+)$/, "$1");

        function basename(path) {
            return path.split('/').reverse()[0];
        }

        if (yerel_den_secilen_dosya == '') {
            $(function () {
                jw("b olumsuz").baslik("Yerelden Kaynak Seçilmedi").icerik("Yerelden bir dosya veya dizin kaynak seçmelisiniz").kilitle().en(450).boy(100).ac();
            })
            return false;
        }
        if (basename(yerel_den_secilen_dosya) == '.htaccess') {
            $(function () {
                jw("b olumsuz").baslik("Bu Dosya Yüklenemez").icerik("<b>.htaccess</b> dosya tek başına yüklenemez.<br />Dizin içinde olduğunda dizinle beraber yüklenebilir").kilitle().en(450).boy(100).ac();
            })
            return false;
        }
        if (google_drive_dan_secilen_dosya_id == '') {
            $(function () {
                jw("b olumsuz").baslik("Google Drive'da Hedef Seçilmedi").icerik("Google Drive'a yedeklemek için bir hedef seçmelisiniz").kilitle().en(450).boy(100).ac();
            })
            return false;
        }
        if (dosyami_dizinmi != google_drive_dan_secilen_dosya_adini_goster) {
            $(function () {
                jw("b olumsuz").baslik("Google Drive'da Hedef Dizin Seçilmedi").icerik("Google Drive'a yedeklemek için dosya değil bir dizin hedef seçmelisiniz").kilitle().en(450).boy(100).ac();
            })
            return false;
        }

        $(function () {
            jw('b secim', dur).baslik("Google Drive'a yedeklemek için Onayla").icerik("Yerelden seçilen dosyayı Google Drive'a yedekeleme üzeresiniz<br />Yedeklemek istediğinizden emin misiniz?").en(450).kilitle().ac();
        })

        function dur(x) {
            if (x == 1) {

                //var pen = jw('d').baslik("Google Drive'a yedekleme").en(750).boy(550).kucultPasif().acEfekt(2, 1000).kapatEfekt(2, 1000).ac();
                //pen.icerikTD.spin(gif);
                var bekleme = jw("b bekle").baslik("Google Drive'a yedekleniyor...").en(300).boy(10).kilitle().akilliKapatPasif().ac();
            // İstek başlamadan önceki zamanı al
            const startTime = new Date();
                $.ajax({
                    type: "POST",
                    url: "gorevle_uzak_google_yedekle.php",
                    data: { googla_yukle: 1, yerel_den_secilen_dosya: yerel_den_secilen_dosya, google_drive_dan_secilen_dosya_id: google_drive_dan_secilen_dosya_id, google_drive_dan_secilen_dosya_adini_goster: google_drive_dan_secilen_dosya_adini_goster },
                    timeout: 3600000, // 1 saat = 3600000 ms
                    success: function (msg) {

                    // İstek sonlandığında zamanı al
                    const endTime = new Date();

                    // Geçen süreyi hesapla (milisaniye cinsinden)
                    const elapsedTime = endTime - startTime;

                    // Geçen süreyi saat, dakika, saniye ve milisaniye olarak parçala
                    const hours = Math.floor(elapsedTime / (1000 * 60 * 60));
                    const minutes = Math.floor((elapsedTime % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((elapsedTime % (1000 * 60)) / 1000);
                    const milliseconds = elapsedTime % 1000;

                    // Sonucu uygun formatta göster
                    const formattedTime = 
                        String(hours).padStart(2, '0') + ':' +
                        String(minutes).padStart(2, '0') + ':' +
                        String(seconds).padStart(2, '0') + ':' +
                        String(milliseconds).padStart(3, '0');

            var mesajlar;
            try {
                    var mesajlar = JSON.parse(msg);  // JSON yanıtı bir JavaScript dizisine dönüştür
                    var tumMesajlar = '';  // Tüm mesajları toplamak için bir değişken
                if (Array.isArray(mesajlar)) {
                    // Mesajları ekrana yazdır veya işle
                    mesajlar.forEach(function(mesaj) {
                        if (mesaj.status === 'success') {
                            //console.log('Başarı: ' + mesaj.message);
                            tumMesajlar += mesaj.message + '<br />';  // Mesajları birleştir ve <br /> ile ayır
                        } else if (mesaj.status === 'error') {
                            //console.error('Hata: ' + mesaj.message);
                            tumMesajlar += mesaj.message + '<br />';  // Mesajları birleştir ve <br /> ile ayır
                        }
                    });
                }else{
                    tumMesajlar = mesajlar;
                }
            } catch (e) {
                tumMesajlar = msg;
            }

                    $(function () {
                        //pen.icerik(msg);
                        bekleme.kapat();
                        var pen = jw('d').baslik('Google Drive\'a yedekleme Sonucu').icerik("<b>Yükleme süresi:</b> " + formattedTime + "<br />" + tumMesajlar).en(750).boy(550).kucultPasif().acEfekt(2, 1000).kapatEfekt(2, 1000).kapaninca(function() { loadFtpFileTree(); }).ac();
                    })                    

                    }, // success
                    error: function(xhr, status, error) {
                        bekleme.kapat();
                        $(function(){
                            jw("b olumsuz").baslik("Ajax Sunucu ile iletişimde hata oluştu.").icerik("Durum: " + status + "<br />Hata mesajı: " + error + "<br />Sunucu cevabı: " + xhr.responseText).kilitle().en(450).boy(50).ac();
                        })
                    }
                });

            }
        }
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function googleDriveSil() {

    // Aktif sınıfa sahip öğeyi seç (örneğin class="aktif" olan a tagı)
    var aktifOge = document.querySelector('#uzak .aktif');

    if (aktifOge) {
        // 'adi' özniteliğini al
        var adi = aktifOge.getAttribute('adi');
    }

        var google_drive_dan_secilen_dosya_id = $('#google_drive_dan_secilen_dosya_id').val();
        var google_drive_dan_secilen_dosya_id_sil = $('#google_drive_dan_secilen_dosya_id_sil').val();

        if( google_drive_dan_secilen_dosya_id == '' ){
            $(function(){
            jw("b olumsuz").baslik("Google Drive'dan Dosya Seçilmedi").icerik("Google Drive'dan yedek silmek için bir dosya seçmelisiniz").kilitle().en(350).boy(100).ac();
            })
            return false;
        }

            $(function()
              {
                jw('b secim',dur).baslik("Google Drive'dan Silmeyi Onayla").icerik("Google Drive'da seçilen yedek silmek istediğinizden emin misiniz?").en(450).kilitle().ac();
              })
              
        function dur(x){
            if(x==1){

            var bekleme = jw("b bekle").baslik("Google Drive Hesabından Yedek(ler) siliniyor...").en(300).boy(10).kilitle().akilliKapatPasif().ac();
            // İstek başlamadan önceki zamanı al
            const startTime = new Date();
        $.ajax({
            url: "elle_uzak_ve_yerel_sunucudan_dosyalari_sil.php",
            type: "POST",
            data: { googdan_sil: 1, google_drive_dan_secilen_dosya_id : google_drive_dan_secilen_dosya_id, google_drive_dan_secilen_dosya_id_sil : google_drive_dan_secilen_dosya_id_sil },
            timeout: 3600000, // 1 saat = 3600000 ms
            success: function (msg) {

            // İstek sonlandığında zamanı al
            const endTime = new Date();

            // Geçen süreyi hesapla (milisaniye cinsinden)
            const elapsedTime = endTime - startTime;

            // Geçen süreyi saat, dakika, saniye ve milisaniye olarak parçala
            const hours = Math.floor(elapsedTime / (1000 * 60 * 60));
            const minutes = Math.floor((elapsedTime % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((elapsedTime % (1000 * 60)) / 1000);
            const milliseconds = elapsedTime % 1000;

            // Sonucu uygun formatta göster
            const formattedTime = 
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0') + ':' +
                String(milliseconds).padStart(3, '0');

            var mesajlar;
            try {
                    var mesajlar = JSON.parse(msg);  // JSON yanıtı bir JavaScript dizisine dönüştür
                    var tumMesajlar = '';  // Tüm mesajları toplamak için bir değişken
                if (Array.isArray(mesajlar)) {
                    // Mesajları ekrana yazdır veya işle
                    mesajlar.forEach(function(mesaj) {
                        if (mesaj.status === 'success') {
                            //console.log('Başarı: ' + mesaj.message);
                            tumMesajlar += mesaj.message + '<br />';  // Mesajları birleştir ve <br /> ile ayır
                        } else if (mesaj.status === 'error') {
                            //console.error('Hata: ' + mesaj.message);
                            tumMesajlar += mesaj.message + '<br />';  // Mesajları birleştir ve <br /> ile ayır
                        }
                    });
                }else{
                    tumMesajlar = mesajlar;
                }
            } catch (e) {
                tumMesajlar = msg;
            }

            $(function () {
                //pen.icerik(msg);
                bekleme.kapat();
                var pen = jw('d').baslik("Google Drive'dan Dosya Silme Sonucu").icerik("<b>Silme süresi:</b> " + formattedTime + "<br />" + tumMesajlar).en(750).boy(550).kucultPasif().acEfekt(2, 1000).kapatEfekt(2, 1000).kapaninca(function(){ googleSatirSil(adi); }).ac();
            })                    

            }, // success
            error: function(xhr, status, error) {
                bekleme.kapat();
                $(function(){
                    jw("b olumsuz").baslik("Ajax Sunucu ile iletişimde hata oluştu.").icerik("Durum: " + status + "<br />Hata mesajı: " + error + "<br />Sunucu cevabı: " + xhr.responseText).kilitle().en(450).boy(50).ac();
                })
            }
        });

        }
        }
    }

    function googleSatirSil(dosya) {
    $('ul#uzak li a.aktif').each(function() {
        if($.trim($(this).attr('adi'))==$.trim(dosya) && $.trim(dosya)!='root') {
            $(this).closest('li').remove();
        }else if($.trim(dosya)=='root'){
            $('ul#uzak li').each(function() {
                $(this).closest('li').remove();
            });
            $("#uzak").append('<li class="uzak_home pointer"><a rel="root" adi="root">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
        }
    });
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function yerelOgeleriSil() {

    // Aktif sınıfa sahip öğeyi seç (örneğin class="aktif" olan a tagı)
    var aktifOge = document.querySelector('#yerel .aktif');

    if (aktifOge) {
        // 'adi' özniteliğini al
        var adi = aktifOge.getAttribute('adi');
    }

        var yerel_den_secilen_dosya = $('#yerel_den_secilen_dosya').val();

        if( yerel_den_secilen_dosya == '' ){
            $(function(){
            jw("b olumsuz").baslik("Yerelden Dosya Seçilmedi").icerik("Yerelden dosya silmek için bir dosya seçmelisiniz").kilitle().en(450).boy(100).ac();
            })
            return false;
        }

            $(function()
              {
                jw('b secim',dur).baslik("Yerelden Silmeyi Onayla").icerik("Yerelden dosya silmek istediğinizden emin misiniz?").en(450).kilitle().ac();
              })
              
    function dur(x){
        if(x==1){

        var bekleme = jw("b bekle").baslik("Yerelden dosya siliniyor...").en(300).boy(10).kilitle().akilliKapatPasif().ac();
            // İstek başlamadan önceki zamanı al
            const startTime = new Date();
    $.ajax({
        url: "elle_uzak_ve_yerel_sunucudan_dosyalari_sil.php",
        type: "POST",
        data: { yerelden_sil: 1, yerel_den_secilen_dosya: yerel_den_secilen_dosya },
        timeout: 3600000, // 1 saat = 3600000 ms
        success: function (msg) {

        // İstek sonlandığında zamanı al
        const endTime = new Date();

        // Geçen süreyi hesapla (milisaniye cinsinden)
        const elapsedTime = endTime - startTime;

        // Geçen süreyi saat, dakika, saniye ve milisaniye olarak parçala
        const hours = Math.floor(elapsedTime / (1000 * 60 * 60));
        const minutes = Math.floor((elapsedTime % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((elapsedTime % (1000 * 60)) / 1000);
        const milliseconds = elapsedTime % 1000;

        // Sonucu uygun formatta göster
        const formattedTime = 
            String(hours).padStart(2, '0') + ':' +
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0') + ':' +
            String(milliseconds).padStart(3, '0');

            var mesajlar;
            try {
                    var mesajlar = JSON.parse(msg);  // JSON yanıtı bir JavaScript dizisine dönüştür
                    var tumMesajlar = '';  // Tüm mesajları toplamak için bir değişken
                if (Array.isArray(mesajlar)) {
                    // Mesajları ekrana yazdır veya işle
                    mesajlar.forEach(function(mesaj) {
                        if (mesaj.status === 'success') {
                            //console.log('Başarı: ' + mesaj.message);
                            tumMesajlar += mesaj.message + '<br />';  // Mesajları birleştir ve <br /> ile ayır
                        } else if (mesaj.status === 'error') {
                            //console.error('Hata: ' + mesaj.message);
                            tumMesajlar += mesaj.message + '<br />';  // Mesajları birleştir ve <br /> ile ayır
                        }
                    });
                }else{
                    tumMesajlar = mesajlar;
                }
            } catch (e) {
                tumMesajlar = msg;
            }

        $(function () {
            //pen.icerik(msg);
            bekleme.kapat();
            var pen = jw('d').baslik('Yerelden Dosya Silme Sonucu').icerik("<b>Silme süresi:</b> " + formattedTime + "<br />" + tumMesajlar).en(750).boy(550).kucultPasif().acEfekt(2, 1000).kapatEfekt(2, 1000).kapaninca(function(){ yerelSatirSil(adi); }).ac();
        })                    

        }, // success
        error: function(xhr, status, error) {
            bekleme.kapat();
            $(function(){
                jw("b olumsuz").baslik("Ajax Sunucu ile iletişimde hata oluştu.").icerik("Durum: " + status + "<br />Hata mesajı: " + error + "<br />Sunucu cevabı: " + xhr.responseText).kilitle().en(450).boy(50).ac();
            })
        }
    });

    }
    }
    }

    function yerelSatirSil(dosya) {
    $('ul#yerel li a.aktif').each(function() {
        if($.trim($(this).attr('adi'))==$.trim(dosya) && $.trim(dosya)!='<?php echo BACKUPDIR; ?>') {
            $(this).closest('li').remove();
        }else if($.trim(dosya)=='<?php echo BACKUPDIR; ?>'){
            $('ul#yerel li').each(function() {
                $(this).closest('li').remove();
            });
            $("#yerel li:first-child").before('<li class="yerel_home pointer"><a rel="<?php echo BACKUPDIR; ?>">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
        }
    });
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$( '#yerel_dizin_agac' ).html( '<ul class="filetree start"><li class="wait" style="padding-left: 20px;">' + 'Yerel klasör ağacı oluşturuluyor...' + '<li></ul>' );
	
	getfilelist( $('#yerel_dizin_agac') , '<?php echo BACKUPDIR; ?>' );

	function getfilelist( cont, root ) {
	
		$( cont ).addClass( 'wait' );
			
		$.post( 'yerel_web_dizin_agac.php', { dir: root }, function( data ) {

			$( cont ).find( '.start' ).html( '' );
			$( cont ).removeClass( 'wait' ).append( data );
			if( '<?php echo BACKUPDIR; ?>' == root ) {
				$( cont ).find('UL:hidden').show();
                $("#yerel li:first-child").before('<li class="yerel_home pointer"><a rel="<?php echo BACKUPDIR; ?>">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
            } else {
				$( cont ).find('UL:hidden').slideDown({ duration: 500, easing: null });
            }
		});
	}

	$( '#yerel_dizin_agac' ).on('click', 'LI A', function() {
        var entry = $(this).parent();
        if(entry.hasClass('yerel_home') && '<?php echo BACKUPDIR; ?>' == $(this).attr('rel') )
        {
            $('#yerel_den_secilen_dosya').val($(this).attr('rel'));
            $('#yerel_dizin_agac a').removeClass("aktif");
            $(this).addClass("aktif");
            $( '.yerel_expanded' ).find('UL').slideUp({ duration: 500, easing: null });
            $( '.yerel_expanded' ).removeClass('yerel_expanded').addClass('yerel_collapsed');

        }
        else
        {

        $('#yerel_dizin_agac a').removeClass("aktif");
        $(this).addClass("aktif");

		if( entry.hasClass('folder_plus') || entry.hasClass('folder') ) {
			if( entry.hasClass('yerel_collapsed') ) {

				entry.find('UL').remove();
				getfilelist( entry, escape( $(this).attr('rel') ));
				entry.removeClass('yerel_collapsed').addClass('yerel_expanded');
			} else {
				entry.find('UL').slideUp({ duration: 500, easing: null });
				entry.removeClass('yerel_expanded').addClass('yerel_collapsed');
			}
			$('#yerel_den_secilen_dosya').val($(this).attr('rel'));
		} else {
			$('#yerel_den_secilen_dosya').val($(this).attr('rel'));
		}
	return false;
    }
	});

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Ağacı yükleme fonksiyonu
    function loadFtpFileTree() {
        // Yükleniyor mesajını hemen göster
        $('#google_drive_uzaktan_agac').html('<ul class="filetree start"><li class="wait" style="padding-left: 20px;">Google Drive içerik ağacı oluşturuluyor...<li></ul>');
        
        // Dosya listesini yükle
        getGooglefilelist( $('#google_drive_uzaktan_agac') , 'root' );
    }

    // Sayfa yüklendiğinde yerel ağacını yükle
    $(document).ready(function() {
        loadFtpFileTree();
    });
	
	function getGooglefilelist( cont, root ) {

		$( cont ).addClass( 'wait' );
			
		$.post( 'google_uzak_agac.php', { dir: root }, function( data ) {
	
			$( cont ).find( '.start' ).html( '' );
			$( cont ).removeClass( 'wait' ).append( data );


			
            if( 'root' == root ) {
            if(data=='<ul id="uzak" class="filetree" style="display: none;"></ul>'){
                $( cont ).find('UL:hidden').show();
                $("#uzak").append('<li class="uzak_home pointer"><a rel="root" adi="root">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
            }else{
				$( cont ).find('UL:hidden').show();
                $("#uzak li:first-child").before('<li class="uzak_home pointer"><a rel="root" adi="root">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
            }
            } else {
				$( cont ).find('UL:hidden').slideDown({ duration: 500, easing: null });
            }
			
		});
	}

	$( '#google_drive_uzaktan_agac' ).on('click', 'LI A', function() {
		var entry = $(this).parent();
        if(entry.hasClass('uzak_home') && 'root' == $(this).attr('adi') )
        {
			$('#google_drive_dan_secilen_dosya_adini_goster,#google_drive_dan_secilen_dosya_id_sil').val($.trim($(this).attr('adi')));
            $('#google_drive_dan_secilen_dosya_id').val($.trim($(this).attr('rel')));

            $('#google_drive_uzaktan_agac a').removeClass("aktif");
            $(this).addClass("aktif");
            $( '.expanded' ).find('UL').slideUp({ duration: 500, easing: null });
            $( '.expanded' ).removeClass('expanded').addClass('collapsed');
        }
        else
        {

        $('#google_drive_uzaktan_agac a').removeClass("aktif");
        $(this).addClass("aktif");
		
		if( entry.hasClass('folder_plus') || entry.hasClass('folder') || entry.hasClass('uzak_home') ) {
			if( entry.hasClass('collapsed') ) {
				entry.find('UL').remove();
				getGooglefilelist( entry, escape( $(this).attr('rel') ));
				entry.removeClass('collapsed').addClass('expanded');
			}
			else {
				
				entry.find('UL').slideUp({ duration: 500, easing: null });
				entry.removeClass('expanded').addClass('collapsed');
			}
			$('#google_drive_dan_secilen_dosya_adini_goster,#google_drive_dan_secilen_dosya_id_sil').val($.trim($(this).attr('adi')));
            $('#google_drive_dan_secilen_dosya_id').val($.trim($(this).attr('rel')));
			
		} else {
			$('#google_drive_dan_secilen_dosya_adini_goster,#google_drive_dan_secilen_dosya_id_sil').val($.trim($(this).attr('adi')));
            $('#google_drive_dan_secilen_dosya_id').val($.trim($(this).attr('rel')));
		}
	return false;
    }
	});
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>