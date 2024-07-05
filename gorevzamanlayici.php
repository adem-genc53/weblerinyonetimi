<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
//require_once __DIR__ . '/includes/turkcegunler.php';

//echo '<pre>' . print_r($_POST, true) . '</pre>';
##########################################################################################################
    if(isset( $_POST['veri_del'] ) ){
        $sil = $PDOdb->prepare("DELETE FROM zamanlanmisgorev WHERE id = ?");

            $sil->execute([$_POST['veri_del']]);
            if($sil->rowCount()){
                $messages[] = $_POST['veri_del']." ID nolu görev başarıyla silindi<br />";
            }else{
                $errors[] = $_POST['veri_del']." ID nolu görev bir hatadan dolayı silinemedi<br />";
            }
            header("Refresh: 2; url=".htmlspecialchars($_SERVER["PHP_SELF"])."?");
    }
##########################################################################################################

$edit_secili_secilen_yedekleme = 0;

if($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['gorev_ekle']) || isset($_POST['gorevi_duzelt']))){

#########################################################################################################################
########################################### CRON ZAMANLAYICI BAŞLANGICI #################################################
#########################################################################################################################

include("cron_zamanlayici.php");

#########################################################################################################################
########################################### CRON ZAMANLAYICI SONU #######################################################
#########################################################################################################################

    //echo '<pre>' . print_r($_POST, true) . '</pre>';
    //exit;
    // Ekleme ve güncelleme için
    $haftadizi = array(1,2,3,4,5,6,7);
    if(is_array($_POST['haftanin_gunu']) AND array_intersect($haftadizi, $_POST['haftanin_gunu'])){$gun = '-1';}
    if(is_array($_POST['haftanin_gunu']) AND in_array('-1', $_POST['haftanin_gunu'])){$gun = $gun;}
    $gorev_adi                      = $_POST['gorev_adi'];
    $dosya_adi                      = $_POST['dosya_adi'];

    $haftanin_gunu                  = implode(",", $_POST['haftanin_gunu']);

    $saat                           = $_POST['saat'];
    $dakika                         = $_POST['dakika'];
    $aktif                          = $_POST['aktif'];
    $gunluk_kayit                   = $_POST['gunluk_kayit'];

    $gz                             = isset($_POST['gz']) ? $_POST['gz'] : '-1';
    $dbbakim                        = isset($_POST['dbbakim']) ? $_POST['dbbakim'] : '-1';
    $dblock                         = isset($_POST['dblock']) ? $_POST['dblock'] : '-1';
    $combine                        = isset($_POST['combine']) ? $_POST['combine'] : '-1';
    $elle                           = isset($_POST['elle']) ? $_POST['elle'] : '-1';
    if(isset($_POST['tablolar']) && is_array($_POST['tablolar'])){
    $tablolar                       = implode(",", $_POST['tablolar']);
    $tablo_guncelmi_denetle         = isset($_POST['tablo_guncelmi_denetle']) ? $_POST['tablo_guncelmi_denetle'] : 0;
    }else{
    $tablolar                       = NULL;
    $tablo_guncelmi_denetle         = 0;
    }
    $duzeltilecek_id                = isset($_POST['duzelt_id']) ? $_POST['duzelt_id'] : null;
    $ozel_onek                      = 0;

    ###########################################################################################################################
    if(isset($_POST['gorev_nedir']) && $_POST['gorev_nedir'] == 1)
    {
        $yedekleme_gorevi               = $_POST['gorev_nedir'];
        // select
        $secilen_yedekleme              = isset($_POST['veritabani_secilen_yedekleme']) ? $_POST['veritabani_secilen_yedekleme'] : null;

        // Veritabanı yedekleme öneki veritabanı adı veya özel belirlenen önek adı
        if(isset($_POST['veritabani_dosya_adi_degistir']) && $_POST['veritabani_dosya_adi_degistir'] == 1) {
            $secilen_yedekleme_oneki        = isset($_POST['veritabani_secilen_yedekleme_oneki']) ? trim($_POST['veritabani_secilen_yedekleme_oneki']) : null;
            $ozel_onek                      = 1;
        }else{
            $secilen_yedekleme_oneki        = isset($_POST['hide_veritabani_secilen_yedekleme']) ? trim($_POST['hide_veritabani_secilen_yedekleme']) : null;
            $ozel_onek                      = 0;
        }

        $google_sunucu_korunacak_yedek  = isset($_POST['veritabani_google_korunacak_yedek']) ? $_POST['veritabani_google_korunacak_yedek'] : '-1';
        $ftp_sunucu_korunacak_yedek     = isset($_POST['veritabani_ftp_korunacak_yedek']) ? $_POST['veritabani_ftp_korunacak_yedek'] : '-1';
        $yerel_korunacak_yedek          = isset($_POST['veritabani_korunacak_yedek']) ? $_POST['veritabani_korunacak_yedek'] : '-1';
        // dizin yolunun başında ve veya sonunda / eğik çizgi varsa kaldırır
        if(isset($_POST['veritabani_ftp_ici_dizin_adi']) && !empty($_POST['veritabani_ftp_ici_dizin_adi'])){
            $uzak_sunucu_ici_dizin_adi  = preg_replace('/^\/+|\/+$/', '', $_POST['veritabani_ftp_ici_dizin_adi']); // dizin yolunun başında ve veya sonunda / eğik çizgi varsa kaldırır
        }else{
            $uzak_sunucu_ici_dizin_adi  = null;
        }
        $ftp_yedekle                    = isset($_POST['veritabani_ftp_yedekle']) ? $_POST['veritabani_ftp_yedekle'] : 0;
        $google_yedekle                 = isset($_POST['veritabani_google_yedekle']) ? $_POST['veritabani_google_yedekle'] : 0;
    }
    elseif(isset($_POST['gorev_nedir']) && $_POST['gorev_nedir'] == 2)
    {
        $yedekleme_gorevi               = $_POST['gorev_nedir'];

        // select
        $secilen_yedekleme              = isset($_POST['dizin_secilen_yedekleme']) ? $_POST['dizin_secilen_yedekleme'] : null;

        // Dizin yedeklemede zip dosya öneki özel belirlendi ise
        if(isset($_POST['zip_dosya_adi_degistir']) && $_POST['zip_dosya_adi_degistir'] == 1) {
            $secilen_yedekleme_oneki        = isset($_POST['dizin_secilen_yedekleme_oneki']) ? trim($_POST['dizin_secilen_yedekleme_oneki']) : null;
            $ozel_onek                      = 1;
        }else{ // Dizin yedeklemede zip dosya öneki secilen ile aynı ise
            $secilen_yedekleme_oneki        = isset($_POST['hide_dizin_secilen_yedekleme']) ? $_POST['hide_dizin_secilen_yedekleme'] : null;
            $ozel_onek                      = 0;
        }

        $google_sunucu_korunacak_yedek  = isset($_POST['dizin_google_korunacak_yedek']) ? $_POST['dizin_google_korunacak_yedek'] : '-1';
        $ftp_sunucu_korunacak_yedek     = isset($_POST['dizin_ftp_korunacak_yedek']) ? $_POST['dizin_ftp_korunacak_yedek'] : '-1';
        $yerel_korunacak_yedek          = isset($_POST['dizin_korunacak_yedek']) ? $_POST['dizin_korunacak_yedek'] : '-1';
        // dizin yolunun başında ve veya sonunda / eğik çizgi varsa kaldırır
        if(isset($_POST['dizin_ftp_ici_dizin_adi']) && !empty($_POST['dizin_ftp_ici_dizin_adi'])){
            $uzak_sunucu_ici_dizin_adi  = preg_replace('/^\/+|\/+$/', '', $_POST['dizin_ftp_ici_dizin_adi']); // dizin yolunun başında ve veya sonunda / eğik çizgi varsa kaldırır
        }else{
            $uzak_sunucu_ici_dizin_adi  = null;
        }
        $ftp_yedekle                    = isset($_POST['dizin_ftp_yedekle']) ? $_POST['dizin_ftp_yedekle'] : 0;
        $google_yedekle                 = isset($_POST['dizin_google_yedekle']) ? $_POST['dizin_google_yedekle'] : 0;
    }
    elseif(isset($_POST['gorev_nedir']) && $_POST['gorev_nedir'] == 3)
    {
        $yedekleme_gorevi               = $_POST['gorev_nedir'];
        $secilen_yedekleme              = null;
        $secilen_yedekleme_oneki        = null;
        $google_sunucu_korunacak_yedek  = '-1';
        $ftp_sunucu_korunacak_yedek     = '-1';
        $yerel_korunacak_yedek          = '-1';
        $uzak_sunucu_ici_dizin_adi      = null;
        $ftp_yedekle                    = 0;
        $google_yedekle                 = 0;
    }
###########################################################################################################################

    }
    if(isset($_POST['gorev_ekle'])){

    try {
        $ftvtk = $PDOdb->prepare("INSERT INTO zamanlanmisgorev (
        gorev_adi, 
        dosya_adi, 
        sonraki_calisma,
        haftanin_gunu,
        gun, 
        saat, 
        dakika, 
        aktif, 
        gunluk_kayit,
        yedekleme_gorevi, 
        ftp_yedekle,
        google_yedekle,
        uzak_sunucu_ici_dizin_adi,
        google_sunucu_korunacak_yedek,
        ftp_sunucu_korunacak_yedek,
        secilen_yedekleme_oneki,
        yerel_korunacak_yedek,
        gz,
        dbbakim,
        dblock,
        combine,
        elle,
        tablolar,
        tablo_guncelmi_denetle,
        secilen_yedekleme,
        ozel_onek)
            VALUES (
        :gorev_adi, 
        :dosya_adi,  
        :sonraki_calisma,
        :haftanin_gunu,
        :gun, 
        :saat, 
        :dakika, 
        :aktif, 
        :gunluk_kayit, 
        :yedekleme_gorevi,
        :ftp_yedekle,
        :google_yedekle,
        :uzak_sunucu_ici_dizin_adi,
        :google_sunucu_korunacak_yedek,
        :ftp_sunucu_korunacak_yedek,
        :secilen_yedekleme_oneki,
        :yerel_korunacak_yedek,
        :gz,
        :dbbakim,
        :dblock,
        :combine,
        :elle,
        :tablolar,
        :tablo_guncelmi_denetle,
        :secilen_yedekleme,
        :ozel_onek)");

            $ftvtk->bindValue(':gorev_adi', $gorev_adi, PDO::PARAM_STR);
            $ftvtk->bindValue(':dosya_adi', $dosya_adi, PDO::PARAM_STR);
            $ftvtk->bindValue(':sonraki_calisma', $sonraki_calisma, PDO::PARAM_INT);
            $ftvtk->bindValue(':haftanin_gunu', $haftanin_gunu, PDO::PARAM_STR);
            $ftvtk->bindValue(':gun', $gun, PDO::PARAM_STR);
            $ftvtk->bindValue(':saat', $saat, PDO::PARAM_STR);
            $ftvtk->bindValue(':dakika', $dakika, PDO::PARAM_STR);
            $ftvtk->bindValue(':aktif', $aktif, PDO::PARAM_STR);
            $ftvtk->bindValue(':gunluk_kayit', $gunluk_kayit, PDO::PARAM_STR);
            $ftvtk->bindValue(':yedekleme_gorevi', $yedekleme_gorevi, PDO::PARAM_INT);
            $ftvtk->bindValue(':ftp_yedekle', $ftp_yedekle, PDO::PARAM_INT);
            $ftvtk->bindValue(':google_yedekle', $google_yedekle, PDO::PARAM_INT);
            $ftvtk->bindValue(':uzak_sunucu_ici_dizin_adi', $uzak_sunucu_ici_dizin_adi, PDO::PARAM_INT);
            $ftvtk->bindValue(':google_sunucu_korunacak_yedek', $google_sunucu_korunacak_yedek, PDO::PARAM_INT);
            $ftvtk->bindValue(':ftp_sunucu_korunacak_yedek', $ftp_sunucu_korunacak_yedek, PDO::PARAM_INT);
            $ftvtk->bindValue(':secilen_yedekleme_oneki', $secilen_yedekleme_oneki, PDO::PARAM_STR);
            $ftvtk->bindValue(':yerel_korunacak_yedek', $yerel_korunacak_yedek, PDO::PARAM_INT);
            $ftvtk->bindValue(':gz', $gz, PDO::PARAM_INT);
            $ftvtk->bindValue(':dbbakim', $dbbakim, PDO::PARAM_INT);
            $ftvtk->bindValue(':dblock', $dblock, PDO::PARAM_INT);
            $ftvtk->bindValue(':combine', $combine, PDO::PARAM_INT);
            $ftvtk->bindValue(':elle', $elle, PDO::PARAM_INT);
            $ftvtk->bindValue(':tablolar', $tablolar, PDO::PARAM_STR);
            $ftvtk->bindValue(':tablo_guncelmi_denetle', $tablo_guncelmi_denetle, PDO::PARAM_INT);
            $ftvtk->bindValue(':secilen_yedekleme', $secilen_yedekleme, PDO::PARAM_INT);
            $ftvtk->bindValue(':ozel_onek', $ozel_onek, PDO::PARAM_INT);
            $ftvtk->execute();

        if($PDOdb->lastInsertId()){
            $messages[] = "Görev Başarıyla Eklendi"; 
        }else{
            $errors[] = "Görev Ekleme Başarısız Oldu";
        }

        } catch (PDOException $e) {
        $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
        if (strpos($e->getMessage(), $existingkey) !== FALSE) {
        $errors[] = "Eklemeye çalıştığınız <strong>$gorev_adi</strong> görev veritabanında zaten kayıtlıdır";
        } else {
        throw $e;
        }
        }

    }elseif(isset($_POST['gorevi_duzelt'])){

    try {
        $ftvtk = $PDOdb->prepare("UPDATE zamanlanmisgorev SET 
        gorev_adi                       = :gorev_adi, 
        dosya_adi                       = :dosya_adi,  
        sonraki_calisma                 = :sonraki_calisma,
        haftanin_gunu                   = :haftanin_gunu,
        gun                             = :gun, 
        saat                            = :saat, 
        dakika                          = :dakika, 
        aktif                           = :aktif, 
        gunluk_kayit                    = :gunluk_kayit, 
        yedekleme_gorevi                = :yedekleme_gorevi,
        ftp_yedekle                     = :ftp_yedekle,
        google_yedekle                  = :google_yedekle,
        uzak_sunucu_ici_dizin_adi       = :uzak_sunucu_ici_dizin_adi,
        google_sunucu_korunacak_yedek   = :google_sunucu_korunacak_yedek,
        ftp_sunucu_korunacak_yedek      = :ftp_sunucu_korunacak_yedek,
        secilen_yedekleme_oneki         = :secilen_yedekleme_oneki,
        yerel_korunacak_yedek           = :yerel_korunacak_yedek,
        gz                              = :gz,
        dbbakim                         = :dbbakim,
        dblock                          = :dblock,
        combine                         = :combine,
        elle                            = :elle,
        tablolar                        = :tablolar,
        tablo_guncelmi_denetle          = :tablo_guncelmi_denetle,
        secilen_yedekleme               = :secilen_yedekleme,
        ozel_onek                       = :ozel_onek
        WHERE
        id                              = :id");

        $ftvtk->bindValue(':gorev_adi', $gorev_adi, PDO::PARAM_STR);
        $ftvtk->bindValue(':dosya_adi', $dosya_adi, PDO::PARAM_STR);
        $ftvtk->bindValue(':sonraki_calisma', $sonraki_calisma, PDO::PARAM_INT);
        $ftvtk->bindValue(':haftanin_gunu', $haftanin_gunu, PDO::PARAM_STR);
        $ftvtk->bindValue(':gun', $gun, PDO::PARAM_STR);
        $ftvtk->bindValue(':saat', $saat, PDO::PARAM_STR);
        $ftvtk->bindValue(':dakika', $dakika, PDO::PARAM_STR);
        $ftvtk->bindValue(':aktif', $aktif, PDO::PARAM_STR);
        $ftvtk->bindValue(':gunluk_kayit', $gunluk_kayit, PDO::PARAM_STR);
        $ftvtk->bindValue(':yedekleme_gorevi', $yedekleme_gorevi, PDO::PARAM_INT);
        $ftvtk->bindValue(':ftp_yedekle', $ftp_yedekle, PDO::PARAM_INT);
        $ftvtk->bindValue(':google_yedekle', $google_yedekle, PDO::PARAM_INT);
        $ftvtk->bindValue(':uzak_sunucu_ici_dizin_adi', $uzak_sunucu_ici_dizin_adi, PDO::PARAM_INT);
        $ftvtk->bindValue(':google_sunucu_korunacak_yedek', $google_sunucu_korunacak_yedek, PDO::PARAM_INT);
        $ftvtk->bindValue(':ftp_sunucu_korunacak_yedek', $ftp_sunucu_korunacak_yedek, PDO::PARAM_INT);
        $ftvtk->bindValue(':secilen_yedekleme_oneki', $secilen_yedekleme_oneki, PDO::PARAM_STR);
        $ftvtk->bindValue(':yerel_korunacak_yedek', $yerel_korunacak_yedek, PDO::PARAM_INT);
        $ftvtk->bindValue(':gz', $gz, PDO::PARAM_INT);
        $ftvtk->bindValue(':dbbakim', $dbbakim, PDO::PARAM_INT);
        $ftvtk->bindValue(':dblock', $dblock, PDO::PARAM_INT);
        $ftvtk->bindValue(':combine', $combine, PDO::PARAM_INT);
        $ftvtk->bindValue(':elle', $elle, PDO::PARAM_INT);
        $ftvtk->bindValue(':tablolar', $tablolar, PDO::PARAM_STR);
        $ftvtk->bindValue(':tablo_guncelmi_denetle', $tablo_guncelmi_denetle, PDO::PARAM_INT);
        $ftvtk->bindValue(':secilen_yedekleme', $secilen_yedekleme, PDO::PARAM_INT);
        $ftvtk->bindValue(':ozel_onek', $ozel_onek, PDO::PARAM_INT);
        $ftvtk->bindValue(':id', $duzeltilecek_id, PDO::PARAM_INT);
        $ftvtk->execute();

        if($ftvtk->rowCount() > 0 ){
            $messages[] = "Görev Başarıyla Güncellendi";
        }else{
            $errors[] = "Görev Güncelleme Başarısız Oldu";
        }

        } catch (PDOException $e) {
        $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
        if (strpos($e->getMessage(), $existingkey) !== FALSE) {
        $errors[] = "Güncellemeye çalıştığınız <strong>$gorev_adi</strong> görev veritabanında zaten kayıtlıdır";
        } else {
        throw $e;
        }
        }
    }

    if(isset($_GET['edit'])){

        $editresult = $PDOdb->prepare("SELECT * FROM zamanlanmisgorev WHERE id=? ");
        $editresult->execute([$_GET['edit']]);
        $editrow = $editresult->fetch();

        $gorev_aktif        = "";
        $gorev_pasif        = "";
        $gunluk_aktif       = "";
        $gunluk_pasif       = "";
        $gz_aktif           = "";
        $gz_pasif           = "";
        $dbbakim_aktif      = "";
        $dbbakim_pasif      = "";
        $dblock_aktif       = "";
        $dblock_pasif       = "";
        $combine_bir        = "";
        $combine_iki        = "";
        $combine_uc         = "";
        $elle_bir           = "";
        $elle_iki           = "";
        $secilen_tablolar   = "";

        //Görev aktif mi
        if($editrow['aktif'] == "Aktif"){
            $gorev_aktif = 'checked="checked"';
        }elseif($editrow['aktif'] == "Pasif"){
            $gorev_pasif = 'checked="checked"';
        }

        //Günlük aktif mi
        if($editrow['gunluk_kayit'] == "Aktif"){
            $gunluk_aktif = 'checked="checked"';
        }elseif($editrow['gunluk_kayit'] == "Pasif"){
            $gunluk_pasif = 'checked="checked"';
        }

        // Veritabanı bilgileri
        if($editrow['yedekleme_gorevi'] == '1'){
            $veritabani_aktif = 'checked="checked"';
        }elseif($editrow['yedekleme_gorevi'] == '0'){
            $veritabani_aktif = '';
        }

        $secilen_yedekleme_oneki = $editrow['secilen_yedekleme_oneki'];
        $uzak_sunucu_ici_dizin_adi = $editrow['uzak_sunucu_ici_dizin_adi'];

        if($editrow['gz'] == '1'){
            $gz_aktif = 'checked="checked"';
        }elseif($editrow['gz'] == '0'){
            $gz_pasif = 'checked="checked"';
        }

        if($editrow['dbbakim'] == '1'){
            $dbbakim_aktif = 'checked="checked"';
        }elseif($editrow['gz'] == '0'){
            $dbbakim_pasif = 'checked="checked"';
        }

        if($editrow['dblock'] == '1'){
            $dblock_aktif = 'checked="checked"';
        }elseif($editrow['gz'] == '0'){
            $dblock_pasif = 'checked="checked"';
        }

        if($editrow['combine'] == '1'){
            $combine_bir = 'checked="checked"';
        }elseif($editrow['combine'] == '2'){
            $combine_iki = 'checked="checked"';
        }elseif($editrow['combine'] == '3'){
            $combine_uc = 'checked="checked"';
        }

        if($editrow['elle'] == '1'){
            $elle_bir = 'checked="checked"';
        }elseif($editrow['elle'] == '2'){
            $elle_iki = 'checked="checked"';
        }

        if($editrow['ozel_onek'] == '1'){
            $ozel_onek_sec = 'checked="checked"';
            $ozel_onek_disabled = '';
        }elseif($editrow['ozel_onek'] == '0'){
            $ozel_onek_sec = '';
            $ozel_onek_disabled = 'disabled';
        }

        $secilen_tablolar = $editrow['tablolar'];

        if(!empty($editrow['secilen_yedekleme'])){
            $edit_secili_secilen_yedekleme = $editrow['secilen_yedekleme'];
        }

    $gorevler = $PDOdb->prepare("SELECT * FROM zamanlanmisgorev WHERE id=? ");
    $gorevler->execute([$_GET['edit']]);

    }else{ // if(isset($_GET['edit'])){

    $gorevler = $PDOdb->prepare("SELECT * FROM zamanlanmisgorev ");
    $gorevler->execute();

    }
###########################################################################################################################

    $web_dizinler = [];
    $dizinler_arr = json_decode($genel_ayarlar['haric_dizinler'], true);
    $total_size = 0;
    $total_files = 0;
    $dizinler_dizi = array_filter(glob(DIZINDIR.'*'), 'is_dir');
    natcasesort($dizinler_dizi);
    //echo '<pre>' . print_r($dizinler_dizi, true) . '</pre>';
    foreach($dizinler_dizi AS $dizinler){
        if(!in_array(basename($dizinler), $dizinler_arr)){
            $web_dizinler[] = basename($dizinler);
        }
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
                                <li class="breadcrumb-item active">Görev Zamanlayıcı</li>
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
                                Websiteleri Yedekleme Yönetimi Hakkında Bilmeniz Gerekenler!
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <p>Buradan görevleri zamanlayabilirsiniz.
                                </p>
                                <p>Bu script ile veritabanı yedekleme, web site dizinleri zip formatında yedekleme, ve TCMB dan döviz kuru güncelleme için üç dosyaya sahiptir.
                                </p>
                                <p><strong>backup.php</strong> veritabanı yedekler
                                </p>
                                <p><strong>zipyap.php</strong> web dizinleri zip formatında yedekler
                                </p>
                                <p><strong>doviz_kurlar.php</strong> TCMB dan döviz kuru günceller
                                </p>
                                <p><strong>https://alan.com/dosya.php</strong> tam url ve GET parametre(isteğe bağlı) girip dosyayı çalıştırabilirsiniz. Sayfanıza bu <b>&lt;span style=&quot;display: none;&quot; id=&quot;tamurl&quot;&gt;sonuç mesajı&lt;/span&gt;</b> etiketi yerleştirip sonuç mesajı buradan alabilir ve günlüğe yazabilir.
                                </p>
                                <p>Eğer PHP kodu yazma bilginiz varsa kendize özel görevi yerine getirecek script yazabilirsiniz. Örnek <b>test_gorev.php</b> dosyaya bakınız.
                                </p>
                                <p>Buradan veritabanı yedekleme, web site dizin yedekleme, ve döviz kuru güncelleme görevleri ekleyebilir ve yönetebilirsiniz.
                                </p>
                                <p>Eklenmiş görevleri zamanı gelmeden dahi elle yürütme imkanı mevcuttur.
                                </p>
                                <p>Haftanın günlerinde hem * yıldız hemde haftanın günü veya günleri aynı anda seçilemez * yıldız seçildiğinde haftanın günleri devre dışı kalır, haftanın günü veya günleri seçildiğinde ayın günleri devre dışı kalır seçilen haftanın gün vey günlerinde çalışıyor. Bu seçenek mesai günlerinde veya mesai dışı günlerinde çalıştırma imkanı sağlar.
                                </p>
                                <p>Tablo sütunların açıklamaları.<br />
                                <strong>Çalışacağı Zaman</strong> Görevin ne zaman çalışacağını gösterir<br />
                                <strong>Haftanın Günü</strong> -1 ise haftanın günü devre dışı demektir, gün adı yazıyorsa haftanın günü aktif ve çalışacağı günü gösterir.<br />
                                <strong>Gün</strong> -1 ise her gün çalışacak, -1/2,-1/3,-1/4 biri ise -1/x gün ara ile çalışacak, normal gün sayısı varsa her ay o gün çalışacak demektir.<br />
                                <strong>Saat</strong> -1 ise her saat, -1/0.30 ise her yarım saatte, -1/12 ise gece 00:00 ve veya gündüz 12:00 saatlerde yani 12 saatte bir, -1/2,-1/3,-1/4,-1/6 biri ise -/x saat ara ile, normal saat ise o saatte çalışacak demektir<br />
                                <strong>Dakika</strong> -1 ise her dakika, -1/2,-1/3,-1/4,-1/5,-1/10,-1/15 biri ise -/x dakika ara ile, normal dakika ise o dakikada çalışacak demektir<br />
                                <strong>Görev</strong> Aktif olduğunda görev çalışır. Pasif olduğunda görev çalışmaz.<br />
                                <strong>Günlük</strong> Aktif olduğunda görev sonucu günlüğe yazar. Pasif olduğunda görev sonucu günlüğe yazmaz.<br />
                                <strong>Yürüt</strong> Çalışacak zamanı gelmezse bile elle görevi çalıştırabilirsiniz
                                </p>
                                <b>Veritabanı yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(BACKUPDIR)); ?></span><br />
                                <p><b>Web site zip yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(ZIPDIR)); ?></span></p>

                                <p><strong>NOT:</strong> Eğer cPanel > Cron'dan bu "<u>/usr/local/bin/php /home/user/alan_adiniz.com/gorev.php >/dev/null 2>&1</u>" komutu ekleyip dakika başı çalışmasını ayarlarsanız web sitenizde belirleyeceğiniz tüm görevler tam zamanında çalışacaktır.</p>
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

    <table class="table table-sm table-striped table-hover" style="min-width: 1000px;">

      <colgroup span="12">
        <col style="width:10%"></col>
        <col style="width:7%"></col>
        <col style="width:3%"></col>
        <col style="width:3%"></col>
        <col style="width:3%"></col>
        <col style="width:10%"></col>
        <col style="width:3%"></col>
        <col style="width:3%"></col>
        <col style="width:10%"></col>
        <col style="width:3%"></col>
        <col style="width:2%"></col>
        <col style="width:2%"></col>
      </colgroup>

    <thead>
        <tr class="bg-primary" style="line-height: 1.2;font-size: 1rem;">
            <th>Çalışacağı Zaman</th>
            <th>Haftanın Günü</th>
            <th>Gün</th>
            <th>Saat</th>
            <th>Dakika</th>
            <th>Dosya</th>
            <th>Görev</th>
            <th>Günlük</th>
            <th>Görev Adı</th>
            <th style="text-align:center;">Düzelt</th>
            <th style="text-align:center;">Sil</th>
            <th style="text-align:center;">Yurut</th>
        </tr>
    </thead>

    <tbody id="satirlar">
        <tr>
            <td class="ilk-yukleniyor" colspan="12">&nbsp;</td>
        </tr>
    </tbody>
<tfoot>
    <tr>
    <td colspan="12"><div style="width:50%; display:inline-block;"><div id="linkler"></div></div><div style="width:50%; display:inline-block;">
<div style="float:right;">

<input type="text" autocomplete="off" id="search" list="aranacaklar" placeholder="İçerik Ara / Çift Tıkla"> Sayfada
    
    <select name="sayfada" id="sayfada">
        <option value="5">5</option>
        <option value="15">15</option>
        <option value="25" selected>25</option>
        <option value="50">50</option>
        <option value="100">100</option>
        <option value="250">250</option>
        <option value="500">500</option>
        <option value="999">999</option>
        <option value="-1">Hepsi</option>
    </select> Satır Göster
    </div></div></td>
    </tr>
    <tr>
            <td colspan="12" style="text-align: center;">
                <button id="eklebuton" class="btn btn-success btn-sm"> <span class="glyphicon glyphicon-plus"></span> Yeni Zamanlanmış Görev Ekle </button>
            </td>
    </tr>
</tfoot>
</table>


                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->


  <!-- // ÜRÜNLERİ SAYFALAMA KODU  -->
<?php    
if(isset($_GET['edit'])){
    if(!empty($veritabani_aktif)){
        echo '<script>jQuery(function($) { $("#veritabani_tablo").show(); $("#eklebuton").hide(); });</script>';
        echo '<script>$("#gorev_zamanlayici select").trigger("change");</script>';
        echo '<script>$("#haftanin_gunu").trigger("change");</script>';
    }
    if(!empty($secilen_tablolar)){
        echo '<script>jQuery(function($) { tablolariYukle(\''.$edit_secili_secilen_yedekleme.'\',\''.$secilen_tablolar.'\',\'TABLE_NAME ASC\');});</script>';
        //echo 'change(\'input[name="combine"]\');';
        //echo '<script>tablolariYukle();</script>';
    }
?>

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

<form name="gorev_zamanlayici" id="gorev_zamanlayici" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<a name="a" id="a"></a>

<input type="hidden" value="<?php echo $editrow['id']; ?>" name="duzelt_id">

<table class="table" style="min-width: 1000px;">

    <colgroup span="8">
        <col style="width:20%"></col>
        <col style="width:8%"></col>
        <col style="width:5%"></col>
        <col style="width:8%"></col>
        <col style="width:5%"></col>
        <col style="width:5%"></col>
        <col style="width:5%"></col>
        <col style="width:60%"></col>
    </colgroup>
<thead>
    <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
        <th style="text-align:center;" colspan="8">Zamanlanmış Görevi Düzelt</th>
    </tr>
</thead>
<tbody>
    <tr>
        <td colspan="8">Düzenlemek İstediğiniz Görev Zaman Bilgilerini Giriniz</td>
    </tr>

    <tr>
        <td colspan="2">Görev Adı</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="gorev_adi" id="gorev_adi" value="<?php echo $editrow['gorev_adi']; ?>" /></td>
        <td colspan="2">Görevi tanımlayan kısa bir tanım giriniz</td>
    </tr>

    <tr>
        <td colspan="2">Lokal yolu ve dosya adı veya tam URL. https://...</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="dosya_adi" id="dosya_adi" value="<?php echo $editrow['dosya_adi']; ?>" /></td>
        <td colspan="2">Dahili dosya veya tam URL. https://domain.com/dosya.php<b>?key=value&key2=value2</b> GET isteğe bağlıdır</td>
    </tr>

    <tr>
        <td colspan="2" rowspan="2" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">Haftanın Günü seçildiğinde 'ayın günü' dikkate almaz)<br /><br /><br />1 den fazla Gün seçmek için klavyenizde Ctrl tuşuna basılı tutarak seçiniz.</td>
        <td colspan="2" rowspan="2" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select size="8" name="haftanin_gunu[]" id="haftanin_gunu" class="form-control" style="width: 150px;" multiple>
            <?php
                $haftanin_gunleri = array(-1 => '*', 1 => "Pazartesi","Salı","Çarşamba","Perşembe","Cuma","Cumartesi","Pazar");
                $secili = explode(",", $editrow['haftanin_gunu']);
                foreach($haftanin_gunleri as $value => $haftanin_gunu)
                {
                $id = $value;
                $selected = in_array($id, $secili) ? ' selected="selected"' : '';
                echo "<option value=\"$id\" $selected>$haftanin_gunu</option>\n";
                }
                ?>
        </select>
        </td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">* Yıldız seçeneği haftayı devre dışı bırakır.</td>
    </tr>

    <tr>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><b>Çalışacak zaman:</b> <span id="scheduledTime" style="font-weight: bold;color:red;"></span><br />Zamanlama seçeneklerine göre çalışacağı zaman örnekte gösteriliyor.<br />Formu gönderdiğinizde o anki geçerli zamana göre oluşturulacağı için değişebilir.</td>
    </tr>

    <tr>
        <td colspan="2">Gün</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="gun" id="gun" style="min-width: 70px;max-width:350px;">
              <?php 
                $gun_options = array(
                    array("-1", "Her Gün(*)"),
                    array("-1/2", "Her İki Günde(*/2)"),
                    array("-1/3", "Her Üç Günde(*/3)"),
                    array("-1/4", "Her Dört Günde(*/4)")
                );

                foreach ($gun_options as $option) {
                    $value = $option[0];
                    $label = $option[1];
                    $selected = $editrow['gun'] == $value ? "selected" : "";
                    echo "<option value=\"$value\" $selected>$label</option>\n";
                }

              for ($i = 1; $i <= 31; $i++){
                $formatted_day = sprintf("%02d", $i);
                if($editrow['gun'] == $i){
                    echo "<option value=\"$formatted_day\" selected>$i</option>\n";
                }else{
                    echo "<option value=\"$formatted_day\">$i</option>\n";
                }
              }     
              ?>
        </select>
        </td>
        <td colspan="2">Görevin çalışacağı günü veya gün aralıkları belirleyin</td>
    </tr>

    <tr>
        <td colspan="2">Saat</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="saat" id="saat" style="min-width: 70px;max-width:350px;">
                <?php
                $options = array(
                    array("-1", "Her saat(*)"),
                    array("-1/0.30", "Her Yarım Saatte(*/0.30)"),
                    array("-1/2", "Her İki Saatte(*/2)"),
                    array("-1/3", "Her Üç Saatte(*/3)"),
                    array("-1/4", "Her Dört Saatte(*/4)"),
                    array("-1/6", "Her Altı Saatte(*/6)"),
                    array("-1/12", "Her Oniki Saatte(0,12)")
                );

                foreach ($options as $option) {
                    $value = $option[0];
                    $label = $option[1];
                    $selected = $editrow['saat'] == $value ? "selected" : "";
                    echo "<option value=\"$value\" $selected>$label</option>\n";
                }

                for ($i = 0; $i <= 23; $i++) {
                    if($editrow['saat'] == $i){
                    echo "<option value=\"$i\" selected>$i</option>\n";
                }else{
                    echo "<option value=\"$i\">$i</option>\n";
                }
                }
                ?>
        </select>
        </td>
        <td colspan="2">Görevin çalışacağı saat veya saat aralıkları belirleyin</td>
    </tr>

    <tr>
        <td colspan="2">Dakika</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="dakika" id="dakika" style="min-width: 70px;max-width:350px;">
              <?php 
                $dakika_options = array(
                    array("-1", "Her Dakika(*)"),
                    array("-1/2", "Her İki Dakikada(*/2)"),
                    array("-1/3", "Her Üç Dakikada(*/3)"),
                    array("-1/4", "Her Dört Dakikada(*/4)"),
                    array("-1/5", "Her Beş Dakikada(*/5)"),
                    array("-1/10", "Her On Dakikada(*/10)"),
                    array("-1/15", "Her Onbeş Dakikada(*/15)")
                );

                foreach ($dakika_options as $option) {
                    $value = $option[0];
                    $label = $option[1];
                    $selected = $editrow['dakika'] == $value ? "selected" : "";
                    echo "<option value=\"$value\" $selected>$label</option>\n";
                }

              for ($i = 0; $i <= 59; $i++){
                if($editrow['dakika'] == $i){
                    echo "<option value=\"$i\" selected>$i</option>\n";
                }else{
                    echo "<option value=\"$i\">$i</option>\n";
                }
              }    
              ?>
        </select>
        </td>
        <td colspan="2">Görevin çalışacağı dakika veya dakika aralıkları belirleyin</td>
    </tr>

    <tr>
        <td>Görev Aktif/Pasif</td>
        <td style="text-align:right;">Aktif:</td>
        <td><input type="radio" name="aktif" value="Aktif" <?php echo $gorev_aktif; ?> /></td>
        <td style="text-align:right;">Pasif:</td>
        <td><input type="radio" name="aktif" value="Pasif" <?php echo $gorev_pasif; ?> /></td>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td>Günlük Aktif/Pasif</td>
        <td style="text-align:right;">Aktif:</td>
        <td><input type="radio" name="gunluk_kayit" value="Aktif" <?php echo $gunluk_aktif; ?> /></td>
        <td style="text-align:right;">Pasif:</td>
        <td><input type="radio" name="gunluk_kayit" value="Pasif" <?php echo $gunluk_pasif; ?> /></td>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td>Bu Görev Nedir</td>
        <td style="text-align:right;">DB Yedekleme</td>
        <td>
        <?php 
            if(!empty($editrow['yedekleme_gorevi']) && $editrow['yedekleme_gorevi'] == 1){
                echo "<input type='radio' name='gorev_nedir' value='1' checked/>";
            }else{
                echo "<input type='radio' name='gorev_nedir' value='1' />";
            }
        ?>
        </td>
        <td style="text-align:right;">Dizin Yedekleme</td>
        <td>
        <?php 
            if(!empty($editrow['yedekleme_gorevi']) && $editrow['yedekleme_gorevi'] == 2){
                echo "<input type='radio' name='gorev_nedir' value='2' checked/>";
            }else{
                echo "<input type='radio' name='gorev_nedir' value='2' />";
            }
        ?>
        </td>
        <td style="text-align:right;">Diğer</td>
        <td colspan="2">
        <?php 
            if(!empty($editrow['yedekleme_gorevi']) && $editrow['yedekleme_gorevi'] == 3){
                echo "<input type='radio' name='gorev_nedir' value='3' checked/>";
            }else{
                echo "<input type='radio' name='gorev_nedir' value='3' />";
            }
        ?>
        </td>
    </tr>

    <tbody id="dizin_ziple_tablo" style="display:none;">
    <!-- /  / -->
    <tr>
        <td colspan="2"><span style="font-weight: bold; color: red;">Göreve Eklediğiniz Web Dizini Seçiniz</span></td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="dizin_secilen_yedekleme" id="dizin_secilen_yedekleme" size="1" style="width:350px;">
            <option value="0">&nbsp;</option>
<?php 
    foreach($web_dizinler AS $dizinler){
        if($editrow['secilen_yedekleme_oneki'] == $dizinler){
            echo "<option value='{$dizinler}' selected>{$dizinler}</option>\n";
        }else{
            echo "<option value='{$dizinler}'>{$dizinler}</option>\n";
        }
    }
?>
        </select>
        </td>
        <td colspan="2"><span style="font-weight: bold; color: red;">Göreve Eklediğiniz Web Dizinin doğru seçtiğinizden emin olunuz</span></td>
    </tr>

    <tr>
        <td>Uzağa Yedekle</td>
        <td style="text-align:right;">FTP</td>
        <td>
        <?php 
            if(!empty($editrow['ftp_yedekle']) && $editrow['ftp_yedekle'] == 1){
                echo "<input type='checkbox' name='dizin_ftp_yedekle' value='1' checked/>";
            }else{
                echo "<input type='checkbox' name='dizin_ftp_yedekle' value='1' />";
            }
        ?>
        </td>
        <td style="text-align:right;">Google Drive</td>
        <td>
        <?php 
            if(!empty($editrow['google_yedekle']) && $editrow['google_yedekle'] == 1){
                echo "<input type='checkbox' name='dizin_google_yedekle' value='1' checked/>";
            }else{
                echo "<input type='checkbox' name='dizin_google_yedekle' value='1' />";
            }
        ?>
        </td>
        <td>&nbsp;</td>
        <td colspan="2">Dizin yedeklendikten sonra uzak sunucu FTP ve veya Google Drive otomatikman yedekle</td>
    </tr>

    <tr>
        <td colspan="2">Uzak Sunucuda Ön Dizin(ler)</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" value="<?php echo $editrow['uzak_sunucu_ici_dizin_adi']; ?>" name="dizin_ftp_ici_dizin_adi" id="dizin_ftp_ici_dizin_adi" style="width:350px;" /></td>
        <td colspan="2">Uzak Sunucuda örnek <u>gorev/database/xxx_web</u> veya <u>gorev/xxx_web</u> gibi ön dizinler ekeyebilir veya boş birakabilirsiniz</td>
    </tr>

    <tr>
        <td colspan="2">Google Sunucudaki Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="dizin_google_korunacak_yedek" id="dizin_google_korunacak_yedek" style="width:350px;">
            <option value="-1">Hiç Birini Silme</option>
                <?php
                for ($x = 1; $x < 11; $x++)
                {
                    if($editrow['google_sunucu_korunacak_yedek'] == $x){
                        echo "<option value=\"$x\" selected>Son $x yedeği koru</option>\n";
                    }else{
                        echo "<option value=\"$x\">Son $x yedeği koru</option>\n";
                    }
                }
                ?>
            </select>
        </td>
        <td colspan="2">Otomatik yedeklenen web dizin son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    <tr>
        <td colspan="2">FTP Sunucudaki Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="dizin_ftp_korunacak_yedek" id="dizin_ftp_korunacak_yedek" style="width:350px;">
            <option value="-1">Hiç Birini Silme</option>
                <?php
                for ($x = 1; $x < 11; $x++)
                {
                    if($editrow['ftp_sunucu_korunacak_yedek'] == $x){
                        echo "<option value=\"$x\" selected>Son $x yedeği koru</option>\n";
                    }else{
                        echo "<option value=\"$x\">Son $x yedeği koru</option>\n";
                    }
                }
                ?>
            </select>
        </td>
        <td colspan="2">Otomatik yedeklenen web dizin son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    <tr>
        <td>Zip Arşivin dosya adını değiştirebilirsiniz</td>
        <td style="text-align:right;">
            <div class="form-check form-check-inline" style="margin-right: 0;display: inline-block;">
                <label class="form-check-label">Değiştir</label>
                <input class="form-check-input" type="checkbox" name="zip_dosya_adi_degistir" id="zip_dosya_adi_degistir" value="1" <?php echo $ozel_onek_sec ?> style="margin-right: 0;">
            </div>
        </td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" value="<?php echo $editrow['secilen_yedekleme_oneki']; ?>" id="dizin_secilen_yedekleme_oneki" name="dizin_secilen_yedekleme_oneki" style="width:350px;" <?php echo $ozel_onek_disabled ?> disabled /></td>
        <td colspan="2"><input type="hidden" name="hide_dizin_secilen_yedekleme" id="hide_dizin_secilen_yedekleme">Yedeklenecek web dizin adıdır. Zip çıkarırken buradaki dizin adı ile çıkarılacaktır</td>
    </tr>

    <tr>
        <td colspan="2">Yerelde Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
            <select class="form-control" name="dizin_korunacak_yedek" style="width:350px;">
            <option value="-1">Hiç Birini Silme</option>
                <?php
                for ($x = 1; $x < 11; $x++)
                {
                    if($editrow['yerel_korunacak_yedek'] == $x){
                        echo "<option value=\"$x\" selected>Son $x yedeği koru</option>\n";
                    }else{
                        echo "<option value=\"$x\">Son $x yedeği koru</option>\n";
                    }
                }
                ?>
            </select>
        </td>
        <td colspan="2">Otomatik yedeklenen web dizin son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    </tbody>

    <tbody id="veritabani_tablo" style="display:none;">
    <!-- /  / -->
    <tr>
        <td colspan="2"><span style="font-weight: bold; color: red;">Göreve Eklediğiniz Veritabanı Seçiniz</span></td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="veritabani_secilen_yedekleme" id="veritabani_secilen_yedekleme" size="1" style="width:350px;">
<?php 
    foreach($veritabanlari_arr AS $id => $veritabani){
        if($editrow['secilen_yedekleme'] == $id){
            echo "<option value='{$id}' selected>{$veritabani}</option>\n";
        }else{
            echo "<option value='{$id}'>{$veritabani}</option>\n";
        }
    }
?>
        </select>
        </td>
        <td colspan="2"><span style="font-weight: bold; color: red;">Göreve Eklediğiniz Veritabanı doğru seçtiğinizden emin olunuz</span></td>
    </tr>

    <tr>
        <td>Uzağa Yedekle</td>
        <td style="text-align:right;">FTP</td>
        <td>
        <?php 
            if(!empty($editrow['ftp_yedekle']) && $editrow['ftp_yedekle'] == 1){
                echo "<input type='checkbox' name='veritabani_ftp_yedekle' value='1' checked />";
            }else{
                echo "<input type='checkbox' name='veritabani_ftp_yedekle' value='1' />";
            }
        ?>
        </td>
        <td style="text-align:right;">Google Drive</td>
        <td>
        <?php 
            if(!empty($editrow['google_yedekle']) && $editrow['google_yedekle'] == 1){
                echo "<input type='checkbox' name='veritabani_google_yedekle' value='1' checked />";
            }else{
                echo "<input type='checkbox' name='veritabani_google_yedekle' value='1' />";
            }
        ?>
        </td>
        <td>&nbsp;</td>
        <td colspan="2">Dizin yedeklendikten sonra uzak sunucu FTP ve veya Google Drive otomatikman yedekle</td>
    </tr>

    <tr>
        <td colspan="2">Uzak Sunucuda Ön Dizin(ler)</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="veritabani_ftp_ici_dizin_adi" id="veritabani_ftp_ici_dizin_adi" value="<?php echo $uzak_sunucu_ici_dizin_adi; ?>" style="width:350px;" /></td>
        <td colspan="2">Uzak Sunucuda örnek <u>gorev/database/xxx_web</u> veya <u>gorev/xxx_web</u> gibi ön dizinler ekeyebilir veya boş birakabilirsiniz</td>
    </tr>

    <tr>
        <td colspan="2">Google Sunucudaki Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="veritabani_google_korunacak_yedek" id="veritabani_google_korunacak_yedek" style="width:350px;">
            <?php 
            $sayidizi = array(-1,1,2,3,4,5,6,7,8,9,10);
            $secili = $editrow['google_sunucu_korunacak_yedek'];
            foreach($sayidizi AS $x)
            {
            $selected = $x == $secili ? ' selected="selected"' : '';
            $xx = $x == '-1' ? 'Hiç Birini Silme' : "Son $x yedeği koru";
            echo "<option value=\"$x\"$selected>$xx</option>\n";
            }
            ?>
        </select>
        </td>
        <td colspan="2">Otomatik yedeklenen veritabanının son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    <tr>
        <td colspan="2">FTP Sunucudaki Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="veritabani_ftp_korunacak_yedek" id="veritabani_ftp_korunacak_yedek" style="width:350px;">
            <?php 
            $sayidizi = array(-1,1,2,3,4,5,6,7,8,9,10);
            $secili = $editrow['ftp_sunucu_korunacak_yedek'];
            foreach($sayidizi AS $x)
            {
            $selected = $x == $secili ? ' selected="selected"' : '';
            $xx = $x == '-1' ? 'Hiç Birini Silme' : "Son $x yedeği koru";
            echo "<option value=\"$x\"$selected>$xx</option>\n";
            }
            ?>
        </select>
        </td>
        <td colspan="2">Otomatik yedeklenen veritabanının son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    <tr>
        <td>Veri tabanı dosya adını değiştirebilirsiniz</td>
        <td style="text-align:right;">
            <div class="form-check form-check-inline" style="margin-right: 0;display: inline-block;">
                <label class="form-check-label">Değiştir</label>
                <input class="form-check-input" type="checkbox" name="veritabani_dosya_adi_degistir" id="veritabani_dosya_adi_degistir" value="1" <?php echo $ozel_onek_sec ?> style="margin-right: 0;">
            </div>
        </td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="veritabani_secilen_yedekleme_oneki" id="veritabani_secilen_yedekleme_oneki" value="<?php echo $secilen_yedekleme_oneki; ?>" style="width:350px;" <?php echo $ozel_onek_disabled ?> disabled /></td>
        <td colspan="2"><input type="hidden" name="hide_veritabani_secilen_yedekleme" id="hide_veritabani_secilen_yedekleme">Yedeklenecek veri tabanına boşluksuz sadece Latin karakter ile bir isim vermelisiniz</td>
    </tr>

    <tr>
        <td colspan="2">Yerelde Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="veritabani_korunacak_yedek" id="veritabani_korunacak_yedek" style="width:350px;">
            <?php 
            $sayidizi = array(-1,1,2,3,4,5,6,7,8,9,10);
            $secili = $editrow['yerel_korunacak_yedek'];
            foreach($sayidizi AS $x)
            {
            $selected = $x == $secili ? ' selected="selected"' : '';
            $xx = $x == '-1' ? 'Hiç Birini Silme' : "Son $x yedeği koru";
            echo "<option value=\"$x\"$selected>$xx</option>\n";
            }
            ?>
        </select>
        </td>
        <td colspan="2">Otomatik yedeklenen veritabanının son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    <tr>
        <td>Veri Tabanını GZip ile sıkıştırarak yedekle</td>
        <td style="text-align:right;">Evet:</td>
        <td><input type="radio" name="gz" value="1" <?php echo $gz_aktif; ?> /></td>
        <td style="text-align:right;">Hayır:</td>
        <td><input type="radio" name="gz" value="0" <?php echo $gz_pasif; ?> /></td>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td>Tabloları Yedeklemeden Önce Kilitle</td>
        <td style="text-align:right;">Evet:</td>
        <td><input type="radio" name="dblock" id="dblock" value="1" <?php echo $dblock_aktif; ?> /></td>
        <td style="text-align:right;">Hayır:</td>
        <td><input type="radio" name="dblock" id="dblock" value="0" <?php echo $dblock_pasif; ?> /></td>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td>Tabloları Yedeklemeden Önce Bakım Yap</td>
        <td style="text-align:right;">Evet:</td>
        <td><input type="radio" name="dbbakim" id="dbbakim" value="1" <?php echo $dbbakim_aktif; ?> /></td>
        <td style="text-align:right;">Hayır:</td>
        <td><input type="radio" name="dbbakim" id="dbbakim" value="0" <?php echo $dbbakim_pasif; ?> /></td>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="2">Birleştirerek Tek Dosya Olarak Yedekle</td>
        <td><input type="radio" name="combine" id="combine" value="1" onclick="return radioEvet();" <?php echo $combine_bir; ?> /></td>
        <td colspan="6">Bu seçenek veritabanının tüm tabloları tek dosya olarak yedekler</td>
    </tr>

    <tr>
        <td colspan="2">Klasöre Tablo Tablo Yedekle</td>
        <td><input type="radio" name="combine" id="combine" value="2" onclick="return radioEvet();" <?php echo $combine_iki; ?> /></td>
        <td colspan="6">Bu seçenek bir klasör oluşturarak her tabloyu ayrı dosya olarak yedekler</td>
    </tr>

    <tr>
        <td colspan="2">Sadece Seçilen Tabloları Yedekle</td>
        <td><input type="radio" name="combine" id="combine3" value="3" style="display: inline;" class="checkbox" <?php echo $combine_uc; ?> onclick="return tablolariYukle();" /></td>
        <td colspan="6">Bu seçenek ile veritabanında seçeceğiniz tabloları yedekler</td>
    </tr>

    <tr class="uyeler" style="display: none;">
        <td colspan="2">Klasöre Tablo Tablo Yedekle</td>
        <td><input type="radio" name="elle" value="2" <?php echo $elle_iki; ?> /></td>
        <td colspan="6">Bu seçenek bir klasör oluşturarak her tabloyu ayrı dosya olarak yedekler</td>
    </tr>

    <tr class="uyeler" style="display: none;">
        <td colspan="2">Birleştirerek Tek Dosya Olarak Yedekle</td>
        <td><input type="radio" name="elle" value="1" <?php echo $elle_bir; ?> /></td>
        <td colspan="6">Bu seçenek veritabanında seçilen tabloları tek dosya olarak yedekler</td>
    </tr>
</tbody>
<tfoot>
    <tr>
        <td colspan="8" style="text-align: center;">
            <button type="submit" class="btn btn-success btn-sm" name="gorevi_duzelt" accesskey="s" onclick="return GorevEkle();" /><span class="glyphicon glyphicon-repeat"></span> Değişiklikleri Güncelle </button>
            <button type="reset" class="btn btn-warning btn-sm" value="" accesskey="r" /><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
            <button type="submit" class="btn btn-danger btn-sm" value="" accesskey="s" onclick="return hide();" /><span class="glyphicon glyphicon-off"></span> Vazget </button>
        </td>
    </tr>
</tfoot>
</table>

                                <div id="showTablolarYedekler" style="display:none;margin-top: 0px;">
                                <a name="tbliste" id="tbliste" style="scroll-margin-top: 50px;"></a>
                                        <div style="text-align:center;border-top: 1px solid #dee2e6;padding:10px;color:red;">
                                            <b>NOT:</b> En son Çalışacağı Zamanından sonra değişiklik olmayan tablo(lar) yedeklenmeyecektir.
                                            <br />
                                            <?php 
                                                if(isset($editrow['tablo_guncelmi_denetle']) && $editrow['tablo_guncelmi_denetle'] == 1){
                                                    echo "<input type='checkbox' name='tablo_guncelmi_denetle' value='1' checked >";
                                                }else{
                                                    echo "<input type='checkbox' name='tablo_guncelmi_denetle' value='1'>";
                                                }
                                            ?>
                                            Eğer tablo(lar) güncellenmezse bile yedeklemek istiyorsanız kutuyu seçiniz.
                                        </div>
                                            <div id="loading" style='text-align: center;'>
                                                <img src="images/ajax-loader.gif" alt="Yükleniyor..." />
                                                <br />Veritabanı Tabloları Yükleniyor...
                                            </div>
                                                <table id="sortliste" class="table table-hover" style="min-width: 1000px;">
                                                    <colgroup span="7">
                                                        <col style="width:40%"></col>
                                                        <col style="width:10%"></col>
                                                        <col style="width:10%"></col>
                                                        <col style="width:10%"></col>
                                                        <col style="width:10%"></col>
                                                        <col style="width:10%"></col>
                                                        <col style="width:10%"></col>
                                                    </colgroup>
                                                </table>
                                <div style="text-align:center;border-top: 1px solid #dee2e6;padding-top:20px;">
                                    <div style="text-align:center;">
                                        <button type="submit" class="btn btn-success btn-sm" name="gorevi_duzelt" accesskey="s" onclick="return GorevEkle();" /><span class="glyphicon glyphicon-repeat"></span> Değişiklikleri Güncelle </button>
                                        <button type="reset" class="btn btn-warning btn-sm" value="" accesskey="r" /><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
                                        <button type="submit" class="btn btn-danger btn-sm" value="" accesskey="s" onclick="return hide();" /><span class="glyphicon glyphicon-off"></span> Vazget </button>
                                    </div>
                                </div>
                                <br />

                                </div>
                                <!-- /showTablolarYedekler -->
 </form>
                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->

<?php 

    }else{

    } // şimdi get else
?>

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content" id="ekle" style="display:none;">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

<form name="gorev_zamanlayici" id="gorev_zamanlayici" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<table class="table" style="min-width: 1000px;">
    <colgroup span="8">
        <col style="width:20%"></col>
        <col style="width:8%"></col>
        <col style="width:5%"></col>
        <col style="width:8%"></col>
        <col style="width:5%"></col>
        <col style="width:5%"></col>
        <col style="width:5%"></col>
        <col style="width:60%"></col>
    </colgroup>
<thead>
    <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
        <th style="text-align:center;" colspan="8">Yeni Görev Zamanla & Ekle</th>
    </tr>
</thead>
<tbody>
    <tr>
        <td colspan="8">Eklemek İstediğiniz Görev Zaman Bilgilerini Giriniz</td>
    </tr>

    <tr>
        <td colspan="2">Görev Adı</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="gorev_adi" id="gorev_adi" /></td>
        <td colspan="2">Görevi tanımlayan kısa bir tanım giriniz</td>
    </tr>

    <tr>
        <td colspan="2">Lokal yolu ve dosya adı veya tam URL. https://...</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="dosya_adi" id="dosya_adi" /></td>
        <td colspan="2">Dahili dosya veya tam URL. https://domain.com/dosya.php<b>?key=value&key2=value2</b> GET isteğe bağlıdır</td>
    </tr>

    <tr>
        <td colspan="2" rowspan="2">Haftanın Günü (Not: bu seçenek 'ayın günü' dikkate almaz)<br /><br /><br />1 den fazla Gün seçmek için klavyenizde Ctrl tuşuna basılı tutarak seçiniz.</td>
        <td colspan="2" rowspan="2" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
            <select size="8" name="haftanin_gunu[]" id="haftanin_gunu" style="width: 150px;" class="form-control" multiple>  
                <option value="-1" selected="selected">*</option>
                <option value="1">Pazartesi</option>
                <option value="2">Salı</option>
                <option value="3">Çarşamba</option>
                <option value="4">Perşembe</option>
                <option value="5">Cuma</option>
                <option value="6">Cumartesi</option>
                <option value="7">Pazar</option>		
            </select>
        </td>
        <td colspan="4">* Yıldız seçeneği haftayı devre dışı bırakır.</td>
    </tr>

    <tr>
        <td colspan="4"><b>Çalışacak zaman:</b> <span id="scheduledTime" style="font-weight: bold;color:red;"></span><br />Zamanlama seçeneklerine göre çalışacağı zaman örnekte gösteriliyor.<br />Formu gönderdiğinizde o anki geçerli zamana göre oluşturulacağı için değişebilir.</td>
    </tr>

    <tr>
        <td colspan="2">Gün</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="gun" id="gun" style="min-width: 70px;max-width:350px;">
              <?php 
                $gun_options = array(
                    array("-1", "Her Gün(*)"),
                    array("-1/2", "Her İki Günde(*/2)"),
                    array("-1/3", "Her Üç Günde(*/3)"),
                    array("-1/4", "Her Dört Günde(*/4)")
                );

                foreach ($gun_options as $option) {
                    $value = $option[0];
                    $label = $option[1];
                    echo "<option value=\"$value\">$label</option>\n";
                }

              for ($i = 1; $i <= 31; $i++){
                $formatted_day = sprintf("%02d", $i);
                    echo "<option value=\"$formatted_day\">$i</option>\n";
              }     
              ?>
        </select>
        </td>
        <td colspan="2">Görevin çalışacağı günü veya gün aralıkları belirleyin</td>
    </tr>

    <tr>
        <td colspan="2">Saat</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="saat" id="saat" style="min-width: 70px;max-width:350px;">
                <?php
                $options = array(
                    array("-1", "Her saat(*)"),
                    array("-1/0.30", "Her Yarım Saatte(*/0.30)"),
                    array("-1/2", "Her İki Saatte(*/2)"),
                    array("-1/3", "Her Üç Saatte(*/3)"),
                    array("-1/4", "Her Dört Saatte(*/4)"),
                    array("-1/6", "Her Altı Saatte(*/6)"),
                    array("-1/12", "Her Oniki Saatte(0,12)")
                );

                foreach ($options as $option) {
                    $value = $option[0];
                    $label = $option[1];
                    echo "<option value=\"$value\">$label</option>\n";
                }

                for ($i = 0; $i <= 23; $i++) {
                    echo "<option value=\"$i\">$i</option>\n";
                }
                ?>
        </select>
        </td>
        <td colspan="2">Görevin çalışacağı saat veya saat aralıkları belirleyin</td>
    </tr>

    <tr>
        <td colspan="2">Dakika</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="dakika" id="dakika" style="min-width: 70px;max-width:350px;">
              <?php 
                $dakika_options = array(
                    array("-1", "Her Dakika(*)"),
                    array("-1/2", "Her İki Dakikada(*/2)"),
                    array("-1/3", "Her Üç Dakikada(*/3)"),
                    array("-1/4", "Her Dört Dakikada(*/4)"),
                    array("-1/5", "Her Beş Dakikada(*/5)"),
                    array("-1/10", "Her On Dakikada(*/10)"),
                    array("-1/15", "Her Onbeş Dakikada(*/15)")
                );

                foreach ($dakika_options as $option) {
                    $value = $option[0];
                    $label = $option[1];
                    echo "<option value=\"$value\">$label</option>\n";
                }

              for ($i = 0; $i <= 59; $i++){
                    echo "<option value=\"$i\">$i</option>\n";
              }    
              ?>
        </select>
        </td>
        <td colspan="2">Görevin çalışacağı dakika veya dakika aralıkları belirleyin</td>
    </tr>

    <tr>
        <td>Görev Aktif/Pasif</td>
        <td style="text-align:right;">Aktif:</td>
        <td><input type="radio" name="aktif" value="Aktif" /></td>
        <td style="text-align:right;">Pasif:</td>
        <td><input type="radio" name="aktif" value="Pasif" /></td>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td>Günlük Aktif/Pasif</td>
        <td style="text-align:right;">Aktif:</td>
        <td><input type="radio" name="gunluk_kayit" value="Aktif" /></td>
        <td style="text-align:right;">Pasif:</td>
        <td><input type="radio" name="gunluk_kayit" value="Pasif" /></td>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td>Bu Görev Nedir</td>
        <td style="text-align:right;">DB Yedekleme</td>
        <td><input type="radio" name="gorev_nedir" value="1" /></td>
        <td style="text-align:right;">Dizin Yedekleme</td>
        <td><input type="radio" name="gorev_nedir" value="2" /></td>
        <td style="text-align:right;">Diğer</td>
        <td><input type="radio" name="gorev_nedir" value="3" checked /></td>
        <td>&nbsp;</td>
    </tr>

    <tbody id="dizin_ziple_tablo" style="display:none;">
    <!-- /  / -->
    <tr>
        <td colspan="2"><span style="font-weight: bold; color: red;">Göreve Eklediğiniz Web Dizini Seçiniz</span></td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="dizin_secilen_yedekleme" id="dizin_secilen_yedekleme" size="1" style="width:350px;">
            <option value="0">&nbsp;</option>
<?php 
    foreach($web_dizinler AS $dizinler){
        echo "<option value='{$dizinler}'>{$dizinler}</option>\n";
    }
?>
        </select>
        </td>
        <td colspan="2"><span style="font-weight: bold; color: red;">Göreve Eklediğiniz Web Dizinin doğru seçtiğinizden emin olunuz</span></td>
    </tr>

    <tr>
        <td>Uzağa Yedekle</td>
        <td style="text-align:right;">FTP</td>
        <td><input type="checkbox" name="dizin_ftp_yedekle" id="dizin_ftp_yedekle" value="1" /></td>
        <td style="text-align:right;">Google Drive</td>
        <td><input type="checkbox" name="dizin_google_yedekle" id="dizin_google_yedekle" value="1" /></td>
        <td>&nbsp;</td>
        <td colspan="2">Dizin yedeklendikten sonra uzak sunucu FTP ve veya Google Drive otomatikman yedekle</td>
    </tr>

    <tr>
        <td colspan="2">Uzak Sunucuda Ön Dizin(ler)</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="dizin_ftp_ici_dizin_adi" id="dizin_ftp_ici_dizin_adi" style="width:350px;" /></td>
        <td colspan="2">Uzak Sunucuda örnek <u>gorev/database/xxx_web</u> veya <u>gorev/xxx_web</u> gibi ön dizinler ekeyebilir veya boş birakabilirsiniz</td>
    </tr>

    <tr>
        <td colspan="2">Google Sunucudaki Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="dizin_google_korunacak_yedek" id="dizin_google_korunacak_yedek" style="width:350px;">
            <option value="-1">Hiç Birini Silme</option>
                <?php
                for ($x = 1; $x < 11; $x++)
                {
                echo "<option value=\"$x\">Son $x yedeği koru</option>\n";
                }
                ?>
            </select>
        </td>
        <td colspan="2">Otomatik yedeklenen web dizin son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    <tr>
        <td colspan="2">FTP Sunucudaki Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="dizin_ftp_korunacak_yedek" id="dizin_ftp_korunacak_yedek" style="width:350px;">
            <option value="-1">Hiç Birini Silme</option>
                <?php
                for ($x = 1; $x < 11; $x++)
                {
                echo "<option value=\"$x\">Son $x yedeği koru</option>\n";
                }
                ?>
            </select>
        </td>
        <td colspan="2">Otomatik yedeklenen web dizin son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    <tr>
        <td>Zip Arşivin dosya adını değiştirebilirsiniz</td>
        <td style="text-align:right;">
            <div class="form-check form-check-inline" style="margin-right: 0;display: inline-block;">
                <label class="form-check-label">Değiştir</label>
                <input class="form-check-input" type="checkbox" name="zip_dosya_adi_degistir" id="zip_dosya_adi_degistir" value="1" style="margin-right: 0;">
            </div>
        </td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
            <input type="text" class="form-control" id="dizin_secilen_yedekleme_oneki" name="dizin_secilen_yedekleme_oneki" style="width:350px;" disabled required /></td>
        <td colspan="2"><input type="hidden" name="hide_dizin_secilen_yedekleme" id="hide_dizin_secilen_yedekleme">Yedeklenecek web dizin adıdır. Zip çıkarırken buradaki dizin adı ile çıkarılacaktır</td>
    </tr>

    <tr>
        <td colspan="2">Yerelde Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
            <select class="form-control" name="dizin_korunacak_yedek" style="width:350px;">
            <option value="-1">Hiç Birini Silme</option>
                <?php
                for ($x = 1; $x < 11; $x++)
                {
                echo "<option value=\"$x\">Son $x yedeği koru</option>\n";
                }
                ?>
            </select>
        </td>
        <td colspan="2">Otomatik yedeklenen web dizin son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    </tbody>

    <tbody id="veritabani_tablo" style="display:none;">
    <!-- /  / -->
    <tr>
        <td colspan="2"><span style="font-weight: bold; color: red;">Göreve Eklediğiniz Veritabanı Seçiniz</span></td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="veritabani_secilen_yedekleme" id="veritabani_secilen_yedekleme" size="1" style="width:350px;">
            <option value='0'>&nbsp;</option>
<?php 
    foreach($veritabanlari_arr AS $id => $veritabani){
        echo "<option value='{$id}'>{$veritabani}</option>\n";
    }
?>
        </select>
        </td>
        <td colspan="2"><span style="font-weight: bold; color: red;">Göreve Eklediğiniz Veritabanı doğru seçtiğinizden emin olunuz</span></td>
    </tr>

    <tr>
        <td>Uzağa Yedekle</td>
        <td style="text-align:right;">FTP</td>
        <td><input type="checkbox" name="veritabani_ftp_yedekle" id="veritabani_ftp_yedekle" value="1" /></td>
        <td style="text-align:right;">Google Drive</td>
        <td><input type="checkbox" name="veritabani_google_yedekle" id="veritabani_google_yedekle" value="1" /></td>
        <td>&nbsp;</td>
        <td colspan="2">Dizin yedeklendikten sonra uzak sunucu FTP ve veya Google Drive otomatikman yedekle</td>
    </tr>

    <tr>
        <td colspan="2">Uzak Sunucuda Ön Dizin(ler)</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="veritabani_ftp_ici_dizin_adi" id="veritabani_ftp_ici_dizin_adi" style="width:350px;" /></td>
        <td colspan="2">Uzak Sunucuda örnek <u>gorev/database/xxx_web</u> veya <u>gorev/xxx_web</u> gibi ön dizinler ekeyebilir veya boş birakabilirsiniz</td>
    </tr>

    <tr>
        <td colspan="2">Google Sunucudaki Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="veritabani_google_korunacak_yedek" id="veritabani_google_korunacak_yedek" style="width:350px;">
            <option value="-1">Hiç Birini Silme</option>
                <?php
                for ($x = 1; $x < 11; $x++)
                {
                echo "<option value=\"$x\">Son $x yedeği koru</option>\n";
                }
                ?>
            </select>
        </td>
        <td colspan="2">Otomatik yedeklenen veritabanının son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    <tr>
        <td colspan="2">FTP Sunucudaki Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
        <select class="form-control" name="veritabani_ftp_korunacak_yedek" id="veritabani_ftp_korunacak_yedek" style="width:350px;">
            <option value="-1">Hiç Birini Silme</option>
                <?php
                for ($x = 1; $x < 11; $x++)
                {
                echo "<option value=\"$x\">Son $x yedeği koru</option>\n";
                }
                ?>
            </select>
        </td>
        <td colspan="2">Otomatik yedeklenen veritabanının son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    <tr>
        <td>Veri tabanı dosya adını değiştirebilirsiniz</td>
        <td style="text-align:right;">
            <div class="form-check form-check-inline" style="margin-right: 0;display: inline-block;">
                <label class="form-check-label">Değiştir</label>
                <input class="form-check-input" type="checkbox" name="veritabani_dosya_adi_degistir" id="veritabani_dosya_adi_degistir" value="1" style="margin-right: 0;">
            </div>
        </td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="veritabani_secilen_yedekleme_oneki" id="veritabani_secilen_yedekleme_oneki" style="width:350px;" required disabled /></td>
        <td colspan="2"><input type="hidden" name="hide_veritabani_secilen_yedekleme" id="hide_veritabani_secilen_yedekleme">Yedeklenecek veri tabanına boşluksuz sadece Latin karakter ile bir isim vermelisiniz</td>
    </tr>

    <tr>
        <td colspan="2">Yerelde Eski Yedeği Silerken Son Kaç Yedek Korunacak</td>
        <td colspan="4" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
            <select class="form-control" name="veritabani_korunacak_yedek" id="veritabani_korunacak_yedek" style="width:350px;">
            <option value="-1">Hiç Birini Silme</option>
                <?php
                for ($x = 1; $x < 11; $x++)
                {
                echo "<option value=\"$x\">Son $x yedeği koru</option>\n";
                }
                ?>
            </select>
        </td>
        <td colspan="2">Otomatik yedeklenen veritabanının son kaç yedeği silmekten korunacak? "<b>Hiç Birini Silme</b>" seçeneği silme işlemi gerçekleştirmez.</td>
    </tr>

    <tr>
        <td>Veri Tabanını GZip ile sıkıştırarak yedekle</td>
        <td style="text-align:right;">Evet:</td>
        <td><input type="radio" name="gz" value="1"></td>
        <td style="text-align:right;">Hayır:</td>
        <td><input type="radio" name="gz" value="0"></td>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td>Tabloları Yedeklemeden Önce Kilitle</td>
        <td style="text-align:right;">Evet:</td>
        <td><input type="radio" name="dblock" id="dblock" value="1"></td>
        <td style="text-align:right;">Hayır:</td>
        <td><input type="radio" name="dblock" id="dblock" value="0"></td>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td>Tabloları Yedeklemeden Önce Bakım Yap</td>
        <td style="text-align:right;">Evet:</td>
        <td><input type="radio" name="dbbakim" id="dbbakim" value="1"></td>
        <td style="text-align:right;">Hayır:</td>
        <td><input type="radio" name="dbbakim" id="dbbakim" value="0"></td>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="2">Birleştirerek Tek Dosya Olarak Yedekle</td>
        <td><input type="radio" name="combine" id="combine" value="1" onclick="return radioEvet();"></td>
        <td colspan="6">Bu seçenek veritabanının tüm tabloları tek dosya olarak yedekler</td>
    </tr>

    <tr>
        <td colspan="2">Klasöre Tablo Tablo Yedekle</td>
        <td><input type="radio" name="combine" id="combine" value="2" onclick="return radioEvet();"></td>
        <td colspan="6">Bu seçenek bir klasör oluşturarak her tabloyu ayrı dosya olarak yedekler</td>
    </tr>

    <tr>
        <td colspan="2">Sadece Seçilen Tabloları Yedekle</td>
        <td><input type="radio" name="combine" id="combine3" value="3" onclick="return tablolariYukle();" /></td>
        <td colspan="6">Bu seçenek veritabanında seçilen tabloları yedekler</td>
    </tr>

    <tr class="uyeler" style="display: none;">
        <td colspan="2">Klasöre Tablo Tablo Yedekle</td>
        <td><input type="radio" name="elle" value="2"></td>
        <td colspan="6">Bu seçenek bir klasör oluşturarak her tabloyu ayrı dosya olarak yedekler</td>
    </tr>

    <tr class="uyeler" style="display: none;">
        <td colspan="2">Birleştirerek Tek Dosya Olarak Yedekle</td>
        <td><input type="radio" name="elle" value="1"></td>
        <td colspan="6">Bu seçenek veritabanında seçilen tabloları tek dosya olarak yedekler</td>
    </tr>
</tbody>
<tfoot>
    <tr>
        <td colspan="8" style="text-align: center;">
            <button type="submit" class="btn btn-success btn-sm" name="gorev_ekle" accesskey="s" onclick="return GorevEkle();" /><span class="glyphicon glyphicon-plus"></span> Yeni Görev Ekle </button>
            <button type="reset" class="btn btn-warning btn-sm" value="" accesskey="r" /><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
            <button type="submit" class="btn btn-danger btn-sm" value="" accesskey="s" onclick="return hide();" /><span class="glyphicon glyphicon-off"></span> Vazget </button>
        </td>
    </tr>
</tfoot>
</table>

                <div id="showTablolarYedekler" style="display:none;margin-top: 0px;">
                <a name="tbliste" id="tbliste" style="scroll-margin-top: 50px;"></a>
                        <div style="text-align:center;border-top: 1px solid #dee2e6;padding:10px;color:red;">
                            <b>NOT:</b> En son Çalışacağı Zamanından sonra değişiklik olmayan tablo(lar) yedeklenmeyecektir.
                            <br /><input type="checkbox" name="tablo_guncelmi_denetle" value="1"> Eğer tablo(lar) güncellenmezse bile yedeklemek istiyorsanız kutuyu seçiniz.
                        </div>
                            <div id="loading" style='text-align: center;'>
                                <img src="images/ajax-loader.gif" alt="Yükleniyor..." />
                                <br />Veritabanı Tabloları Yükleniyor...
                            </div>
                                <table id="sortliste" class="table table-hover" style="min-width: 1000px;">
                                    <colgroup span="7">
                                        <col style="width:40%"></col>
                                        <col style="width:10%"></col>
                                        <col style="width:10%"></col>
                                        <col style="width:10%"></col>
                                        <col style="width:10%"></col>
                                        <col style="width:10%"></col>
                                        <col style="width:10%"></col>
                                    </colgroup>
                                </table>

                                <div style="text-align:center;border-top: 1px solid #dee2e6;padding-top:20px;">
                                    <div>
                                        <button type="submit" class="btn btn-success btn-sm" name="gorev_ekle" accesskey="s" onclick="return GorevEkle();" /><span class="glyphicon glyphicon-plus"></span> Yeni Görev Ekle </button>
                                        <button type="reset" class="btn btn-warning btn-sm" value="" accesskey="r" /><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
                                        <button type="submit" class="btn btn-danger btn-sm" value="" accesskey="s" onclick="return hide();" /><span class="glyphicon glyphicon-off"></span> Vazget </button>
                                    </div>
                                </div>
                                <br />

                                </div>
                                <!-- /showTablolarYedekler -->

  </form>

                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->

<form id="gonder" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="veri_del" id="veri_del">
</form>
<script>

    $(document).on('click', "[id^=veri_sil_]", function(){
        var id = $(this).attr('id');
        id = id.replace("veri_sil_",'');
        $("#veri_del").val(id);
        var veri_adi = $(this).attr('data-name');

      $(function()
        {
        jw('b secim',sil_dur).baslik("Silmeyi Onayla").icerik("<b>" + veri_adi + "</b> görevi silmek istediğinizden emin misiniz?").en(450).kilitle().ac();
        })
      return false;

      function sil_dur(x){
        if(x==1){
            $("#gonder").submit();
        }
      }

    });

</script>

        </div><!-- / <div class="content-wrapper"> -->
<script type='text/javascript'>
    var satir = 'zamanlanmisgorev';
    var query = '';
    var tarih = '';
    var firma = '';
    var bildirgoster = '<?php if(isset($_GET['edit'])){ echo $_GET['edit']; } ?>';
</script>
<?php 
include('includes/footer.php');
?>

<style>
option:disabled {
   background: #ccc;
   width: 500px;
   padding: 5px;
   }
</style>

<script>
    $(document).ready(function(){
        // SAAT seçeneğinin değişiklik olayı
        $('#saat').change(function(){
            var selectedSaat = $(this).val();
            if(selectedSaat.startsWith("-1/")){
                // Eğer saat seçeneğinde ('Her Dakika hariç') 'Her...' ile başlayan bir seçenek seçildiyse, 
                // DAKİKA seçeneğinindeki 'Her...' ile başlayan tüm seçenekleri devre dışı bırak
                $('#dakika option[value^="-1"]').attr('selected',false);
                $('#dakika option[value^="-1"]').prop('disabled', true);
                // DAKİKA seçeneğini "0" olarak seç
                if($('#dakika').val()==null){
                    $('#dakika option[value^="0"]').attr('selected',true);
                }
            } else {
                // Eğer 'Her...' seçeneği seçilmediyse, DAKİKA seçeneğini aktif hale getir
                $('#dakika option[value^="-1"]').prop('disabled', false);
            }
        });
    });
</script>

<script type="text/javascript">
$(document).ready(function() {
    // Select alanı değiştiğinde
    $('#haftanin_gunu, #gun, #saat, #dakika').change(function() {
        var formData = $('#gorev_zamanlayici').serialize(); // Form verilerini al
        var additionalData = { ajaxtan: true }; // Harici post verisi
        formData = formData + '&' + $.param(additionalData); // Serialize edilen veriye ekleyin
        $.post('cron_zamanlayici.php', formData, function(data) { // cron_zamanlayici.php'ye POST gönder
            $('#scheduledTime').text(data);
        });
    });

    // Sayfa yüklendiğinde bir kez tetikle
    $('#gorev_zamanlayici select').trigger('change');
});
</script>

<script>
    $(document).ready(function(){
        $('#haftanin_gunu').change(function(){
            var selectedValue = $(this).val();
            var gunSelect = $('#gun');

            if(selectedValue == -1 || selectedValue.indexOf("-1") !== -1){
                gunSelect.prop('disabled', false);
            } else {
                gunSelect.prop('disabled', true);
            }
        });
        // Sayfa yüklendiğinde bir kez tetikle
        //$('#haftanin_gunu').trigger('change');
    });
</script>

<?php    
    if(isset($_GET['edit'])){
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#gorev_zamanlayici select").trigger("change");
        $("#haftanin_gunu").trigger("change");
    });
</script>
<?php 
    }
?>

<script type="text/javascript">

    function simdiCalistir(gorev_adi, runid){
            $(function()
              {
                jw('b secim',gorev_dur).baslik("Görevi Elle Yürütme").icerik("<u>"+ gorev_adi +"</u> görevi elle yürütmek istediğinizden emin misiniz<br />Bu görevde belirlenen tüm seçeneler uygulanacaktır.<br />Sonraki yürütme zamanı değiştirilmeyecektir").en(450).kilitle().ac();
              })
          function gorev_dur(x){
                if(x==1){
                  var bekleme = jw("b bekle").baslik("Görev yurutuluyor...").en(350).boy(120).kilitle().akilliKapatPasif().ac(); 
                $.ajax({
                type: "POST",
                url: "gorev.php",
                data: { "elle_yurutme" : 1, "gorevid" : runid },
                success: function(veriler){
                  bekleme.kapat();
                  jw("b olumlu", function(){ window.location = "gorevzamanlayici.php"; }).baslik("Görevi Elle Yürütme Sonucu").icerik(veriler).en(450).boy(80).kilitle().akilliKapatPasif().ac();
                }
                });
                }
              }
    }

    $('select[name="veritabani_secilen_yedekleme"]').change(function(){
        $('input[name="veritabani_secilen_yedekleme_oneki"]').val($.trim($('select[name="veritabani_secilen_yedekleme"] option:selected').text()));
        $('input[name="hide_veritabani_secilen_yedekleme"]').val($.trim($('select[name="veritabani_secilen_yedekleme"] option:selected').text()));
        if($('input[name=combine]:checked').val() == 3){
            tablolariYukle($(this).val());
        }
    });

    // DİZİN YEDEKLEMEDE SEÇİLEN DİZİN ADINI DİZİN YEDEKLEME ÖNEKİ GİRİŞ ALANINA EKLİYORUZ
    $('select[name="dizin_secilen_yedekleme"]').change(function(){
        $('input[name="dizin_secilen_yedekleme_oneki"]').val($.trim($('select[name="dizin_secilen_yedekleme"] option:selected').text()));
        $('input[name="hide_dizin_secilen_yedekleme"]').val($.trim($('select[name="dizin_secilen_yedekleme"] option:selected').text()));
    });

    // DİZİN YEDEKLEMEDE KENDİ DİZİN ADI İLE ÖNEKİ YERİNE KUTUYU SEÇEREK ÖZEL ÖNEK BELİRLEME SEÇENEĞİ
    $("#zip_dosya_adi_degistir").change(function() {
        if(this.checked) { // CHECKBOX SEÇİLİ İSE
            $("input[name='dizin_secilen_yedekleme_oneki']").prop('disabled', false); // ÖNEK GİRİŞ ALANI ETKİNLEŞTİR
            $("input[name='hide_dizin_secilen_yedekleme']").prop('disabled', true); // HIDDEN GİRİŞ ALANI DEVRE DIŞI BIRAK ÇÜNKÜ GİRİŞ ALANI ETKİN
            // ELLE GİRİLEN ÖNEKİ VERİTABANINDA ALARAK INPUT GİRİŞE EKLE
            $('input[name="dizin_secilen_yedekleme_oneki"]').val('<?php if(isset($editrow['secilen_yedekleme_oneki'])){ echo $editrow['secilen_yedekleme_oneki']; } ?>');
            // ELLE GİRİLEN ÖNEKİ VERİTABANINDA ALARAK HIDDEN INPUT GİRİŞE EKLE
            $('input[name="hide_dizin_secilen_yedekleme"]').val('<?php if(isset($editrow['secilen_yedekleme_oneki'])){ echo $editrow['secilen_yedekleme_oneki']; } ?>');
        }else{
            // CHECKBOX SEÇİLİ DEĞİL İSE SEÇİLEN SELECT OPTIONDAN SEÇİLİ DİZİN ADI ALARAK INPUT ALANI EKLE
            $('input[name="dizin_secilen_yedekleme_oneki"]').val($.trim($("select[name='dizin_secilen_yedekleme'] option:selected").text()));
            // CHECKBOX SEÇİLİ DEĞİL İSE SEÇİLEN SELECT OPTIONDAN SEÇİLİ DİZİN ADI ALARAK HIDDEN INPUT ALANI EKLE
            $('input[name="hide_dizin_secilen_yedekleme"]').val($.trim($("select[name='dizin_secilen_yedekleme'] option:selected").text()));
            $("input[name='dizin_secilen_yedekleme_oneki']").prop('disabled', true); // SELECT OPTION ALANINDAN DİZİN ADI GİRİLDİĞİ İÇİN DEVRE DIŞI
            $("input[name='hide_dizin_secilen_yedekleme']").prop('disabled', false); // ÖNEK GİRİŞ ALANI DEVRE DIŞI OLDUĞU İÇİN HIDDEN INPUT ALANI ETKİNLEŞTİRİYORUZ
        }
    });

    // VERİTABANI YEDEKLEMEDE KENDİ VERİTABANI ADI İLE ÖNEKİ YERİNE KUTUYU SEÇEREK ÖZEL ÖNEK BELİRLEME SEÇENEĞİ
    $("#veritabani_dosya_adi_degistir").change(function() {
        if(this.checked) { // CHECKBOX SEÇİLİ İSE
            $("input[name='veritabani_secilen_yedekleme_oneki']").prop('disabled', false); // ÖNEK GİRİŞ ALANI ETKİNLEŞTİR
            $("input[name='hide_veritabani_secilen_yedekleme']").prop('disabled', true); // HIDDEN GİRİŞ ALANI DEVRE DIŞI BIRAK ÇÜNKÜ GİRİŞ ALANI ETKİN
            // ELLE GİRİLEN ÖNEKİ VERİTABANINDA ALARAK INPUT GİRİŞE EKLE
            $('input[name="veritabani_secilen_yedekleme_oneki"]').val('<?php if(isset($editrow['secilen_yedekleme_oneki'])){ echo $editrow['secilen_yedekleme_oneki']; } ?>');
            // ELLE GİRİLEN ÖNEKİ VERİTABANINDA ALARAK HIDDEN INPUT GİRİŞE EKLE
            $('input[name="hide_veritabani_secilen_yedekleme"]').val('<?php if(isset($editrow['secilen_yedekleme_oneki'])){ echo $editrow['secilen_yedekleme_oneki']; } ?>');
        }else{
            // CHECKBOX SEÇİLİ DEĞİL İSE SEÇİLEN SELECT OPTIONDAN SEÇİLİ VERİTABANI ADI ALARAK INPUT ALANI EKLE
            $('input[name="veritabani_secilen_yedekleme_oneki"]').val($.trim($("select[name='veritabani_secilen_yedekleme'] option:selected").text()));
            // CHECKBOX SEÇİLİ DEĞİL İSE SEÇİLEN SELECT OPTIONDAN SEÇİLİ VERİTABANI ADI ALARAK HIDDEN INPUT ALANI EKLE
            $('input[name="hide_veritabani_secilen_yedekleme"]').val($.trim($("select[name='veritabani_secilen_yedekleme'] option:selected").text()));
            $("input[name='veritabani_secilen_yedekleme_oneki']").prop('disabled', true); // SELECT OPTION ALANINDAN VERİTABANI ADI GİRİLDİĞİ İÇİN DEVRE DIŞI
            $("input[name='hide_veritabani_secilen_yedekleme']").prop('disabled', false); // ÖNEK GİRİŞ ALANI DEVRE DIŞI OLDUĞU İÇİN HIDDEN INPUT ALANI ETKİNLEŞTİRİYORUZ
        }
    });


    function radioEvet(){
        $("#sortliste").empty();
        $('#showTablolarYedekler').hide();
        $('#showTablolar').hide();
        $("#yedekler-listesi").show();
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

      $(document).ready(function(){

        $("input[name='veritabani_secilen_yedekleme_oneki']").on("keypress", function(event) {
              var englishAlphabetAndWhiteSpace = /[A-Za-z0-9-_.]/g;
              var key = String.fromCharCode(event.which);
              if (event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 39 || englishAlphabetAndWhiteSpace.test(key)) {
                  return true;
              }
              return false;
          });
          $("input[name='veritabani_secilen_yedekleme_oneki']").on("paste", function(e) {
              //e.preventDefault(); // yapıştırmayı engelliyor
          });
        $("input[name='dizin_secilen_yedekleme_oneki']").on("keypress", function(event) {
              var englishAlphabetAndWhiteSpace = /[A-Za-z0-9-_.]/g;
              var key = String.fromCharCode(event.which);
              if (event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 39 || englishAlphabetAndWhiteSpace.test(key)) {
                  return true;
              }
              return false;
          });
          $("input[name='dizin_secilen_yedekleme_oneki']").on("paste", function(e) {
              e.preventDefault();
          });
      });
</script>

<script type="text/javascript">
$(document).ready(function() {
    $('input[name="gorev_nedir"]').on('click', function() {
        if( $(this).val() == 1 ) { // veritabani_
            $('#diizn_secilen_yedekleme').prop('selectedIndex',0);
            $("#dizin_ziple_tablo").hide();
            $("#veritabani_tablo").show();
            $("#dosya_adi").val("backup.php");
            $('input[name="secilen_yedekleme_oneki"]').val("");
            $("input[name='dizin_secilen_yedekleme_oneki']").prop('disabled', true);
            $("input[name='hide_dizin_secilen_yedekleme']").prop('disabled', true);
            $("input[name='veritabani_secilen_yedekleme_oneki']").prop('disabled', false);
            $("input[name='hide_veritabani_secilen_yedekleme']").prop('disabled', false);
        }else if( $(this).val() == 2 ) { // dizin_
            $('#veritabani_secilen_yedekleme').prop('selectedIndex',0);
            $("#veritabani_tablo").hide();
            $("#dizin_ziple_tablo").show();
            $("#dosya_adi").val("zipyap.php");
            $('input[name="secilen_yedekleme_oneki"]').val("");
            $("input[name='dizin_secilen_yedekleme_oneki']").prop('disabled', false);
            $("input[name='hide_dizin_secilen_yedekleme']").prop('disabled', false);
            $("input[name='veritabani_secilen_yedekleme_oneki']").prop('disabled', true);
            $("input[name='hide_veritabani_secilen_yedekleme']").prop('disabled', true);
        } else { // kurlar_
            $('#veritabani_secilen_yedekleme').prop('selectedIndex',0);
            $('#diizn_secilen_yedekleme').prop('selectedIndex',0);
            $("#veritabani_tablo").hide();
            $("#dizin_ziple_tablo").hide();
            $("#dosya_adi").val("<?php if(isset($editrow['dosya_adi']) && ($editrow['dosya_adi'] != 'backup.php' || $editrow['dosya_adi'] != 'zipyap.php')){ echo $editrow['dosya_adi']; }else{ echo "doviz_kurlar.php"; } ?>");
            $('input[name="secilen_yedekleme_oneki"]').val("");
            $("input[name='dizin_secilen_yedekleme_oneki']").prop('disabled', true);
            $("input[name='hide_dizin_secilen_yedekleme']").prop('disabled', true);
            $("input[name='veritabani_secilen_yedekleme_oneki']").prop('disabled', true);
            $("input[name='hide_veritabani_secilen_yedekleme']").prop('disabled', true);
        }
    });
    $('input:radio[name="gorev_nedir"][value="<?php echo isset($editrow['yedekleme_gorevi']) ? $editrow['yedekleme_gorevi'] : 3; ?>"]').attr('checked',true).trigger("click");
});

    // GÖREVİ DÜZENLERKEN SEÇİM KUTUSU SEÇİLİ İSE ELLE GİRİLEN DİZİN ÖNEKİ YÜKLE
    $(document).ready(function(){
        if ($("#zip_dosya_adi_degistir").is(':checked')) { // CHECKBOX SEÇİLİ İSE
            $('input[name="dizin_secilen_yedekleme_oneki"]').val('<?php if(isset($editrow['secilen_yedekleme_oneki'])){ echo $editrow['secilen_yedekleme_oneki']; } ?>');
            $('input[name="hide_dizin_secilen_yedekleme"]').val('<?php if(isset($editrow['secilen_yedekleme_oneki'])){ echo $editrow['secilen_yedekleme_oneki']; } ?>');
        }else{
            $("input[name='dizin_secilen_yedekleme_oneki']").prop('disabled', true);
            $("input[name='hide_dizin_secilen_yedekleme']").prop('disabled', false);
        }
    });

    // GÖREVİ DÜZENLERKEN SEÇİM KUTUSU SEÇİLİ İSE ELLE GİRİLEN VERİTABANI ÖNEKİ YÜKLE
    $(document).ready(function(){
        if ($("#veritabani_dosya_adi_degistir").is(':checked')) { // CHECKBOX SEÇİLİ İSE
            $('input[name="veritabani_secilen_yedekleme_oneki"]').val('<?php if(isset($editrow['secilen_yedekleme_oneki'])){ echo $editrow['secilen_yedekleme_oneki']; } ?>');
            $('input[name="hide_veritabani_secilen_yedekleme"]').val('<?php if(isset($editrow['secilen_yedekleme_oneki'])){ echo $editrow['secilen_yedekleme_oneki']; } ?>');
        }else{
            $("input[name='veritabani_secilen_yedekleme_oneki']").prop('disabled', true);
            $("input[name='hide_veritabani_secilen_yedekleme']").prop('disabled', false);
        }
    });

$('input[name="combine"]').on('change', function() {

  $('.uyeler').toggle(+this.value === 3 && this.checked);

});

function tablolariYukle(db_secildi, tablolar, sort) {
	$(document).ready(function() {
    const element = document.getElementById("tbliste");
    element.scrollIntoView();
    <?php 
    if(empty($secilen_tablolar)){
        echo 'var tablolar = ""; ';
        echo "\n";
    }else{
        echo 'var tablolar = \''.$secilen_tablolar.'\';';
        echo "\n";
    }
    ?>
    if(db_secildi){
        var secilen_yedekleme = db_secildi;
    }else{
        var secilen_yedekleme = "<?php echo $edit_secili_secilen_yedekleme; ?>";
    }

if(secilen_yedekleme>0){
    $(".uyeler").show();

    $("#loading").show();
    $('#showTablolarYedekler').show();
    $('#showTablolar').show();
    $('#bekle-sort').fadeIn('');
    $("#sortliste").empty();

        $.ajax({
            url: "tabloliste.php",
            type: "POST",
            data: { tablolari_listele : 1, secilen_yedekleme : secilen_yedekleme, tablolar : tablolar, sort : sort },
            success: function(ilksayfa){
                //console.log(ilksayfa);
                $("#sortliste").html(ilksayfa);
                $('#bekle-sort').fadeOut('');
                $('#gizle').fadeOut('');
                $("#yedekler-listesi").hide();
                $("#loading").hide();
                element.scrollIntoView();
            }
        });



} // if(secilen_yedekleme>0){

});

}

</script>

<script type="text/javascript">

function GorevEkle() {

  var aktif                     = $("input[name='aktif']:checked").attr('value');
  var gunluk_kayit              = $("input[name='gunluk_kayit']:checked").attr('value');
  var gz                        = $("input[name='gz']:checked").attr('value');
  var dbbakim                   = $("input[name='dbbakim']:checked").attr('value');
  var lock                      = $("input[name='dblock']:checked").attr('value');
  var combine                   = $("input[name='combine']:checked").attr('value');
  var tablolar                  = $('input[id=tablolar]:checked').length;
  var elle                      = $("input[name='elle']:checked").attr('value');
  var gorev_adi                 = $('#gorev_adi').val();
  var dosya_adi                 = $('#dosya_adi').val();
  var secilen_yedekleme_oneki   = $('#secilen_yedekleme_oneki').val();
  var checkbox                  = $("input[name='yedekleme_gorevi']").is(':checked');
  var secilen_yedekleme         = $('select[name="secilen_yedekleme"] option:selected').val();

  if(gorev_adi=="") {
    $(function(){
      jw("b olumsuz").baslik("Görev Adi Girmediniz").icerik("Görevi tanımlayan kısa bir görev adı giriniz").kilitle().en(350).boy(100).ac();
    })
    return false;
  }
  if(dosya_adi=="") {
    $(function(){
      jw("b olumsuz").baslik("Dosya Adi Girmediniz").icerik("Çalıştırılacak dosya adı giriniz").kilitle().en(350).boy(100).ac();
    })
    return false;
  }
  if(aktif==undefined) {
    $(function(){
      jw("b olumsuz").baslik("Yedekme Görevi Aktif/Pasif").icerik("Yedekleme görevi aktif veya pasif olacağını belirlemediniz").kilitle().en(350).boy(100).ac();
    })
    return false;
  }
  if(gunluk_kayit==undefined) {
    $(function(){
      jw("b olumsuz").baslik("Yedekme Görevi Günlüğü Kaydetme").icerik("Yedekleme görevi yerine getirdiğinde sonucu günlüğe kayit edip etmeyeceğini belirlemediniz").kilitle().en(350).boy(100).ac();
    })
    return false;
  }

  if(checkbox>0){

    if(secilen_yedekleme=="") {
        $(function(){
            jw("b olumsuz").baslik("Veritabanı Belirlemediniz!").icerik("Göreve eklenecek veritabanı seçmelisiniz").kilitle().en(400).boy(100).ac();
        })
        return false;
    }
  if(secilen_yedekleme_oneki=="") {
    $(function(){
      jw("b olumsuz").baslik("Yedeğin Önekini Girmediniz!").icerik("Veritabanı yedeğin önekini yada yedeğin adını girmelisiniz").kilitle().en(350).boy(100).ac();
    })
    return false;
  }
  if(gz==undefined) {
    $(function(){
      jw("b olumsuz").baslik("Yedeği GZipleme Belirlemediniz!").icerik("Veritabanı yedeğin GZip ile sıkıştırılıp veya sıkıştırılmayacağını belirlemediniz").kilitle().en(350).boy(100).ac();
    })
    return false;
  }
  if(lock==undefined) {
    $(function(){
      jw("b olumsuz").baslik("Tabları Kilitleme Belirlemediniz!").icerik("Yedeklenmeden önce tabloların kilitlenip kilitlenmeyeceğini belirlemediniz").kilitle().en(350).boy(100).ac();
    })
    return false;
  }
  if(dbbakim==undefined) {
    $(function(){
      jw("b olumsuz").baslik("Tablolara Bakım Belirlemediniz!").icerik("Yedeklenmeden önce tablolara bakım belirlemediniz").kilitle().en(350).boy(100).ac();
    })
    return false;
  }
  if(combine==undefined) {
    $(function(){
      jw("b olumsuz").baslik("Yedekleme Seçeneği Belirlemediniz!").icerik("Veritabanı yedekleme seçeneği belirlemediniz").kilitle().en(350).boy(100).ac();
    })
    return false;
  }
  if (elle==undefined && combine==3) {
    $(function(){
      jw("b olumsuz").baslik("Seçilecek Tabloarın Yedekleme Biçimi Belirlemediniz!").icerik("Seçilecek tabloların yedekleme biçimini seçmediniz").kilitle().en(350).boy(100).ac();
    })
    return false;
  }
  if (tablolar < 1 && combine==3) {
    $(function(){
      jw("b olumsuz").baslik("Tabloları Belirlemediniz!").icerik("Yedeklemek istediğiniz tabloyu veya tabloları seçmediniz").kilitle().en(350).boy(100).ac();
    })
    return false;
    }
  }

}

</script>

<script type="text/javascript">
function week_chck(obj){
  var val=[];
  for(var i=0;i<obj.options.length;i++){
  if(obj.options[i].selected===true){val.push(obj.options[i].value);}
  }
    if(val.length>1 && val[0]=='-1'){
      $(function(){
        jw("b olumsuz").baslik("Yıldız ile beraber haftanın günleri seçilemez").icerik("Aynı zaman içinde <b style='font-size:18px;padding: 0 10px;vertical-align: bottom;'>&#9733;</b> yıldız ile beraber <b>haftanın gün(leri)</b> seçilemez!").kilitle().en(350).boy(100).ac();
      })
      for(var k=1;k<obj.options.length;k++){obj.options[k].selected=false;}
      week_chck(obj);
    }
}

  window.onload=function(){
    document.getElementById('haftanin_gunu').onchange=function(){week_chck(this);}
  }
</script>
