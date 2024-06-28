<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

ob_start();
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); //7200 saniye 120 dakikadır, 3600 1 saat

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
                                <li class="breadcrumb-item active">Dizin/Zİp Google'la Yedekle</li>
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
                                <p>Buradan web dizinlerin zip formatında sıkıştırılarak yedeklenen zipli dosyaların uzak Google Drive hesabına elle yedekleyebilirsiniz
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
            <div class="p-1 bg-primary text-white"><strong>Yerel Dizini/Dosyayı Google Drive Sunucuya Yedekleme</strong></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 p-3">
                <div class="input-group">
                <span class="input-group-text" style="width: 170px;">Yerel Web Dizin Kaynak</span>
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
        <div class="col-sm-6 p-3"><div class="p-1 bg-primary text-white"><strong>Yerel Zipli Web Dizinler</strong></div>
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
            jw('b secim', ftp_dur).baslik("Google Drive'a yedeklemek için Onayla").icerik("Yerelden seçilen dosyayı Google Drive'a yedekeleme üzeresiniz<br />Yedeklemek istediğinizden emin misiniz?").en(450).kilitle().ac();
        })

        function ftp_dur(x) {
            if (x == 1) {

                var pen = jw('d').baslik("Google Drive'a yedekleme").en(750).boy(550).kucultPasif().acEfekt(2, 1000).kapatEfekt(2, 1000).ac();
                pen.icerikTD.spin(gif);

                $.ajax({
                    type: "POST",
                    url: "gorevle_uzak_google_yedekle.php",
                    data: { googla_yukle: 1, yerel_den_secilen_dosya: yerel_den_secilen_dosya, google_drive_dan_secilen_dosya_id: google_drive_dan_secilen_dosya_id, google_drive_dan_secilen_dosya_adini_goster: google_drive_dan_secilen_dosya_adini_goster },
                    success: function (msg) {
                        $(function () {
                            pen.icerik(msg);
                        })
                    }
                });

            }
        }
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function googleDriveSil() {
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
                jw('b secim',ftp_dur).baslik("Google Drive'dan Silmeyi Onayla").icerik("Google Drive'da seçilen yedek silmek istediğinizden emin misiniz?").en(450).kilitle().ac();
              })
              
        function ftp_dur(x){
            if(x==1){

            var bekleme = jw("b bekle").baslik("Google Drive Hesabından Yedek(ler) siliniyor...").en(300).boy(10).kilitle().akilliKapatPasif().ac();

        $.ajax({
            url: "elle_uzak_ve_yerel_sunucudan_dosyalari_sil.php",
            type: "POST",
            dataType: "json",
            data: { googdan_sil: 1, google_drive_dan_secilen_dosya_id : google_drive_dan_secilen_dosya_id, google_drive_dan_secilen_dosya_id_sil : google_drive_dan_secilen_dosya_id_sil },
            success: function (data) {
                bekleme.kapat();
                //alert(data);
                jw("b olumlu").baslik("Google Drive'dan Silme Sonucu").icerik(data.mesaj).en(500).boy(10).kilitle().akilliKapatPasif().kapaninca(function(){ googleSatirSil(data.li_sil_adi); }).ac(); 
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
        var yerel_den_secilen_dosya = $('#yerel_den_secilen_dosya').val();

        if( yerel_den_secilen_dosya == '' ){
            $(function(){
            jw("b olumsuz").baslik("Yerelden Dosya Seçilmedi").icerik("Yerelden dosya silmek için bir dosya seçmelisiniz").kilitle().en(450).boy(100).ac();
            })
            return false;
        }

            $(function()
              {
                jw('b secim',ftp_dur).baslik("Yerelden Silmeyi Onayla").icerik("Yerelden dosya silmek istediğinizden emin misiniz?").en(450).kilitle().ac();
              })
              
    function ftp_dur(x){
        if(x==1){

        var bekleme = jw("b bekle").baslik("Yerelden dosya siliniyor...").en(300).boy(10).kilitle().akilliKapatPasif().ac();

    $.ajax({
        url: "elle_uzak_ve_yerel_sunucudan_dosyalari_sil.php",
        type: "POST",
        dataType: "json",
        data: { yerelden_sil: 1, yerel_den_secilen_dosya: yerel_den_secilen_dosya },
        success: function (data) {
        bekleme.kapat();
            jw("b olumlu").baslik("Yerelden Dosya Silme Sonucu").icerik(data.mesaj).en(500).boy(10).kilitle().akilliKapatPasif().kapaninca(function(){ yerelSatirSil(data.li_sil_adi); }).ac(); 
        }
    });

    }
    }
    }

    function yerelSatirSil(dosya) {
    $('ul#yerel li a.aktif').each(function() {
        if($.trim($(this).attr('adi'))==$.trim(dosya) && $.trim(dosya)!='<?php echo ZIPDIR; ?>') {
            $(this).closest('li').remove();
        }else if($.trim(dosya)=='<?php echo ZIPDIR; ?>'){
            $('ul#yerel li').each(function() {
                $(this).closest('li').remove();
            });
            $("#yerel li:first-child").before('<li class="yerel_home pointer"><a rel="<?php echo ZIPDIR; ?>">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
        }
    });
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$(document).ready( function() {

	$( '#yerel_dizin_agac' ).html( '<ul class="filetree start"><li class="wait" style="padding-left: 20px;">' + 'Yerel klasör ağacı oluşturuluyor...' + '<li></ul>' );
	
	getfilelist( $('#yerel_dizin_agac') , '<?php echo ZIPDIR; ?>' );

	function getfilelist( cont, root ) {
	
		$( cont ).addClass( 'wait' );
			
		$.post( 'yerel_web_zip_dizin_agac.php', { dir: root }, function( data ) {

			$( cont ).find( '.start' ).html( '' );
			$( cont ).removeClass( 'wait' ).append( data );
			if( '<?php echo ZIPDIR; ?>' == root ) {
				$( cont ).find('UL:hidden').show();
                $("#yerel li:first-child").before('<li class="yerel_home pointer"><a rel="<?php echo ZIPDIR; ?>">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
            } else {
				$( cont ).find('UL:hidden').slideDown({ duration: 500, easing: null });
            }
		});
	}

	$( '#yerel_dizin_agac' ).on('click', 'LI A', function() {
        var entry = $(this).parent();
        if(entry.hasClass('yerel_home') && '<?php echo ZIPDIR; ?>' == $(this).attr('rel') )
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
});
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$('#google_drive_uzaktan_agac').html( '<ul class="filetree start"><li class="wait" style="padding-left: 20px;">' + 'Google Drive içerik ağacı oluşturuluyor...' + '<li></ul>' );
	
	getfilelist( $('#google_drive_uzaktan_agac') , 'root' );
	
	function getfilelist( cont, root ) {

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
				getfilelist( entry, escape( $(this).attr('rel') ));
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