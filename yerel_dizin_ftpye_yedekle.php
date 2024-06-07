<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");
    if (!extension_loaded('ftp')) {
        exit("<div style='font-weight: bold;font-size: 16px;text-align:center;'>PHP.ini de FTP uzantısı etkinleştirilmedi.</div>");
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
                                <li class="breadcrumb-item active">Dizin/Zİp FTP'ye Yedekle</li>
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
<p>Buradan web dizinleri sıkıştırmadan dizin olarak uzak FTP hesabına elle yedekleyebileceğiniz gibi dizinler içinde gezinerek özel bir dosyayı da yedekleyebilirsiniz.
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

    <form method="POST">
    <div class="row">
        <div class="col-sm-12 p-3 text-center">
            <div class="p-1 bg-primary text-white"><strong>Yerel Dizini/Dosyayı FTP Sunucuya Yedekleme</strong></div>
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
                <span class="input-group-text" style="width: 170px;">Uzak FTP Hedef</span>
            <input class="form-control" type="text" id="ftp_den_secilen_dosya" name="ftp_den_secilen_dosya" />
                </div>
        </div>
    </div>

    <div class="text-center p-3">
        <button type="button" class="btn btn-success btn-sm" onclick="javascript:uzakSunucuyaYukle();"><i class="fa fa-upload" aria-hidden="true"></i> FTP'ye Yükle </button>
    </div>
    </form>

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

    <div class="row">
        <div class="col-sm-6 p-3"><div class="p-1 bg-primary text-white"><strong>Yerel Web Dizinler</strong></div>
            <div id="yerel_dizin_agac"></div>
            <button type="button" class="btn btn-warning btn-sm" style="margin-top: 15px;" onclick="return yerelOgeleriSil();"><span class="glyphicon glyphicon-trash"></span> Seçilen Öğeyi Sil </button>
        </div>
        <div class="col-sm-6 p-3"><div class="p-1 bg-primary text-white"><strong>Uzak FTP Sunucu</strong></div>
            <div id="ftp_uzaktan_agac"></div>
            <button type="button" class="btn btn-warning btn-sm" style="margin-top: 15px;" onclick="return ftpDenSil();"><span class="glyphicon glyphicon-trash"></span> Seçilen Öğeyi Sil </button>
        </div>
    </div>

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
        var ftp_den_secilen_dosya = $('#ftp_den_secilen_dosya').val();
        var dosyami_dizinmi = ftp_den_secilen_dosya.replace(/^.*?\.([a-zA-Z0-9]+)$/, "$1");

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
        if (ftp_den_secilen_dosya == '') {
            $(function () {
                jw("b olumsuz").baslik("FTP'den Hedef Seçilmedi").icerik("FTP'ye yedeklemek için bir hedef seçmelisiniz").kilitle().en(450).boy(100).ac();
            })
            return false;
        }
        if (dosyami_dizinmi != ftp_den_secilen_dosya) {
            $(function () {
                jw("b olumsuz").baslik("FTP'den Hedef Dizin Seçilmedi").icerik("FTP'ye yedeklemek için dosya değil bir dizin hedef seçmelisiniz").kilitle().en(450).boy(100).ac();
            })
            return false;
        }

        $(function () {
            jw('b secim', ftp_dur).baslik("FTP'ye yedeklemek için Onayla").icerik("Yerelden seçilen dosyayı FTP'ye yedekeleme üzeresiniz<br />Yedeklemek istediğinizden emin misiniz?").en(450).kilitle().ac();
        })

        function ftp_dur(x) {
            if (x == 1) {

                var pen = jw('d').baslik("FTP'ye yedekleme").en(750).boy(550).kucultPasif().acEfekt(2, 1000).kapatEfekt(2, 1000).ac();
                pen.icerikTD.spin(gif);

                $.ajax({
                    type: "POST",
                    url: "gorevle_uzak_ftp_yedekle.php",
                    data: { ftpye_yukle: 1, yerel_den_secilen_dosya: yerel_den_secilen_dosya, ftp_den_secilen_dosya: ftp_den_secilen_dosya },
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

    function ftpDenSil() {
        var ftp_den_secileni_sil = $('#ftp_den_secilen_dosya').val();

        if( ftp_den_secileni_sil == '' ){
            $(function(){
            jw("b olumsuz").baslik("FTP'den Dosya Seçilmedi").icerik("FTP'den yedek silmek için bir dosya seçmelisiniz").kilitle().en(450).boy(100).ac();
            })
            return false;
        }

            $(function()
              {
                jw('b secim',ftp_dur).baslik("FTP'den Silmeyi Onayla").icerik("FTP'den seçilen yedek silmek istediğinizden emin misiniz?").en(450).kilitle().ac();
              })
              
        function ftp_dur(x){
            if(x==1){

            var bekleme = jw("b bekle").baslik("FTP'deki Yedek(ler) siliniyor...").en(300).boy(10).kilitle().akilliKapatPasif().ac();

        $.ajax({
            url: "elle_uzak_ve_yerel_sunucudan_dosyalari_sil.php",
            type: "POST",
            dataType: "json",
            data: { ftpden_sil: 1, ftp_den_secileni_sil : ftp_den_secileni_sil },
            success: function (data) {
                bekleme.kapat();
                jw("b olumlu").baslik("FTP'den Silme Sonucu").icerik(data.mesaj).en(500).boy(10).kilitle().akilliKapatPasif().kapaninca(function(){ ftpSatirSil(data.li_sil_adi); }).ac(); 
            }
        });

        }
        }
    }

    function ftpSatirSil(dosya) {
        //console.log("Dosya " + dosya);
    $('ul#ftp_uzak li a.aktif').each(function() {
        //console.log("Adi " + $(this).attr('adi'));
        if($.trim($(this).attr('adi'))==$.trim(dosya)) {
            $(this).closest('li').remove();
        }
    });
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function yerelOgeleriSil() {
        var yerel_den_secilen_dosya = $('#yerel_den_secilen_dosya').val();

        if(yerel_den_secilen_dosya==''){
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
            if($.trim($(this).attr('adi'))==$.trim(dosya) && $.trim(dosya)!='<?php echo DIZINDIR; ?>') {
                $(this).closest('li').remove();
            }else if($.trim(dosya)=='<?php echo DIZINDIR; ?>'){
                $('ul#yerel li').each(function() {
                    $(this).closest('li').remove();
                });
                $("#yerel li:first-child").before('<li class="yerel_home pointer"><a rel="<?php echo DIZINDIR; ?>">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
            }
        });
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(document).ready( function() {

	$( '#yerel_dizin_agac' ).html( '<ul class="filetree start"><li class="wait" style="padding-left: 20px;">' + 'Yerel klasör ağacı oluşturuluyor...' + '<li></ul>' );
	
	getfilelist( $('#yerel_dizin_agac') , '<?php echo DIZINDIR; ?>' );

	function getfilelist( cont, root ) {
	
		$( cont ).addClass( 'wait' );
			
		$.post( 'yerel_web_dizin_agac.php', { dir: root }, function( data ) {

			$( cont ).find( '.start' ).html( '' );
			$( cont ).removeClass( 'wait' ).append( data );
			if( '<?php echo DIZINDIR; ?>' == root ) {
				$( cont ).find('UL:hidden').show();
                $("#yerel li:first-child").before('<li class="yerel_home pointer"><a rel="<?php echo DIZINDIR; ?>">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
            } else {
				$( cont ).find('UL:hidden').slideDown({ duration: 500, easing: null });
            }
		});
	}

	$( '#yerel_dizin_agac' ).on('click', 'LI A', function() {
        var entry = $(this).parent();
        if(entry.hasClass('yerel_home') && '<?php echo DIZINDIR; ?>' == $(this).attr('rel') )
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

$(document).ready( function() {

	$( '#ftp_uzaktan_agac' ).html( '<ul class="filetree start"><li class="wait" style="padding-left: 20px;">' + 'FTP içerik ağacı oluşturuluyor...' + '<li></ul>' );
	
	getfilelist( $('#ftp_uzaktan_agac') , '/' );
	
	function getfilelist( cont, root ) {
	
		$( cont ).addClass( 'wait' );
			
		$.post( 'ftp_uzaktan_agac.php', { dir: root }, function( data ) {
	
			$( cont ).find( '.start' ).html( '' );
			$( cont ).removeClass( 'wait' ).append( data );


			
            if( '/' == root ) {
            if(data=='<ul id="ftp_uzak" class="filetree" style="display: none;"></ul>'){
                $( cont ).find('UL:hidden').show();
                $("#ftp_uzak").append('<li class="uzak_home pointer"><a rel="/">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
            }else{
				$( cont ).find('UL:hidden').show();
                $("#ftp_uzak li:first-child").before('<li class="uzak_home pointer"><a rel="/">Ana Dizin<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>');
            }
            } else {
				$( cont ).find('UL:hidden').slideDown({ duration: 500, easing: null });
            }
			
		});
	}
	
	$( '#ftp_uzaktan_agac' ).on('click', 'LI A', function() {
		var entry = $(this).parent();
        if(entry.hasClass('uzak_home') && '/' == $(this).attr('rel') )
        {
            $('#ftp_den_secilen_dosya').val( $(this).attr('rel') );
            $('#ftp_uzaktan_agac a').removeClass("aktif");
            $(this).addClass("aktif");
            $( '.expanded' ).find('UL').slideUp({ duration: 500, easing: null });
            $( '.expanded' ).removeClass('expanded').addClass('collapsed');
        }
        else
        {

        $('#ftp_uzaktan_agac a').removeClass("aktif");
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
			$('#ftp_den_secilen_dosya').val($.trim($(this).attr('rel')));
			
		} else {
			$( '#ftp_den_secilen_dosya' ).val($.trim($(this).attr('rel')));
		}
	return false;
    }
	});
	
});

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

</script>