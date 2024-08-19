<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;
##########################################################################################################

    //$dizin_array = array_map('basename', glob(DIZINDIR.'*', GLOB_ONLYDIR));
    //echo '<pre>' . print_r(array_map('basename', glob(DIZINDIR.'*', GLOB_ONLYDIR)), true) . '</pre>';

    if (is_dir(DIZINDIR)) {
        if ($dit = opendir(DIZINDIR)) {
            while (($dosya = readdir($dit)) !== false) {
                if($dosya != "." && $dosya != ".." && $dosya != ".htaccess"){
                    $dizin_array[] = $dosya;
                }
            }
            closedir();
        }
    }
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                //echo '<pre>' . print_r($_POST, true) . '</pre>';
                //exit;
            }
##########################################################################################################
    // Seçili veritabanı karakter seti belirlemek için
    if( isset($_POST['karakter_seti']) )
    {
    try {
    $sorgu = "UPDATE genel_ayarlar SET
            karakter_seti=?
            LIMIT 1 ";

                $stmt= $PDOdb->prepare($sorgu);
                $stmt->execute([$_POST['karakter_seti']]);
            if ($stmt->rowCount() > 0) {
                $messages[] = "Veritabanı Karakter Seti Başarıyla Güncellendi.";
                header("Refresh: 2; url=".htmlspecialchars($_SERVER["PHP_SELF"])."?");
            } else {
                $errors[] = "Karakter Seti Bir Hatadan Dolayı Güncelleme Başarısız Oldu.<br />Hiçbir değişiklik yapmadan güncelleme yapıyor olabilirsiniz";
            }
        
        } catch (PDOException $e) {
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                $errors[] = "Güncellemeye çalıştığınız Karakter Seti veritabanında zaten kayıtlıdır";
            } else {
                throw $e;
                $errors[] = $e->getMessage();
            }
        }

    }
##########################################################################################################

##########################################################################################################
    if(isset($_POST['zaman_dilimi'])){

    try {
    $sorgu = "UPDATE genel_ayarlar SET
            zaman_dilimi=?
            LIMIT 1 ";

                $stmt= $PDOdb->prepare($sorgu);
                $stmt->execute([$_POST['zaman_dilimi']]);
            if ($stmt->rowCount() > 0) {
                $messages[] = "Zaman Dilimi Başarıyla Güncellendi.";
                header("Refresh: 2; url=".htmlspecialchars($_SERVER["PHP_SELF"])."?");
            } else {
                $errors[] = "Zaman Dilimi Bir Hatadan Dolayı Güncelleme Başarısız Oldu.<br />Hiçbir değişiklik yapmadan güncelleme yapıyor olabilirsiniz";
            }
        
        } catch (PDOException $e) {
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                $errors[] = "Güncellemeye çalıştığınız Zaman Dilimi veritabanında zaten kayıtlıdır";
            } else {
                throw $e;
                $errors[] = $e->getMessage();
            }
        }
    }
##########################################################################################################

##########################################################################################################
    if(isset($_POST['dizinler'])){

    $secilendizinler = json_encode($_POST['dizinler'], JSON_UNESCAPED_UNICODE);
    try {
    $sorgu = "UPDATE genel_ayarlar SET
            haric_dizinler=?
            LIMIT 1 ";

                $stmt= $PDOdb->prepare($sorgu);
                $stmt->execute([$secilendizinler]);
            if ($stmt->rowCount() > 0) {
                $messages[] = "Dizin Adları Başarıyla Güncellendi.";
                header("Refresh: 2; url=".htmlspecialchars($_SERVER["PHP_SELF"])."?");
            } else {
                $errors[] = "Dizin Adları Güncelleme Bir Hatadan Dolayı Güncelleme Başarısız Oldu.<br />Hiçbir değişiklik yapmadan güncelleme yapıyor olabilirsiniz";
            }
        
        } catch (PDOException $e) {
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                $errors[] = "Güncellemeye çalıştığınız Dizin adı veritabanında zaten kayıtlıdır";
            } else {
                throw $e;
                $errors[] = $e->getMessage();
            }
        }
    }
##########################################################################################################

##########################################################################################################
    if(isset($_POST['sunucu'])){
        $sunucu     = $_POST['sunucu']      ?? null;
        $port       = $_POST['port']        ?? null;

        if(isset($_POST['username']) && empty($_POST['username'])){
        $ftpuser = "";
        }else{
        $username      = $hash->make(trim($_POST['username']));
        $ftpuser = 'username=:username,';
        }

        if(isset($_POST['password']) && empty($_POST['password'])){
        $ftppassword  = "";
        }else{
        $password  = $hash->make(trim($_POST['password']));
        $ftppassword  = 'password=:password,';
        }

        $path       = $_POST['path']       ?? "/";

    try {
    $sorgu = "UPDATE genel_ayarlar SET
            sunucu=:sunucu,
            port=:port,
            $ftpuser
            $ftppassword
            path=:path
            LIMIT 1 ";

                $stmt= $PDOdb->prepare($sorgu);

                $stmt->bindParam(':sunucu', $sunucu, PDO::PARAM_STR);
                $stmt->bindParam(':port', $port, PDO::PARAM_INT);

                if(isset($_POST['username']) && !empty($_POST['username'])){
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                }

                if(isset($_POST['password']) && !empty($_POST['password'])){
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                }
                $stmt->bindParam(':path', $path, PDO::PARAM_STR);

                $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $messages[] = "FTP Bilgileri Başarıyla Güncellendi.";
                header("Refresh: 2; url=".htmlspecialchars($_SERVER["PHP_SELF"])."?");
            } else {
                $errors[] = "FTP Bilgileri Bir Hatadan Dolayı Güncelleme Başarısız Oldu.<br />Hiçbir değişiklik yapmadan güncelleme yapıyor olabilirsiniz";
            }
        
        } catch (PDOException $e) {
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                $errors[] = "Güncellemeye çalıştığınız FTP Bilgileri veritabanında zaten kayıtlıdır";
            } else {
                throw $e;
                $errors[] = $e->getMessage();
            }
        }
    }
##########################################################################################################

##########################################################################################################
    if(isset($_POST['zip_tercihi'])){
        $zip_tercihi = $_POST['zip_tercihi'] ?? 1;

    try {
    $sorgu = "UPDATE genel_ayarlar SET
            zip_tercihi=?
            LIMIT 1 ";

                $stmt= $PDOdb->prepare($sorgu);
                $stmt->execute([$zip_tercihi]);
            if ($stmt->rowCount() > 0) {
                $messages[] = "ZİP Arşivleme Tercihi Başarıyla Güncellendi.";
                header("Refresh: 2; url=".htmlspecialchars($_SERVER["PHP_SELF"])."?");
            } else {
                $errors[] = "ZİP Arşivleme Tercihi Bir Hatadan Dolayı Güncelleme Başarısız Oldu.<br />Hiçbir değişiklik yapmadan güncelleme yapıyor olabilirsiniz";
            }
        
        } catch (PDOException $e) {
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                $errors[] = "Güncellemeye çalıştığınız ZİP Arşivleme Tercihi veritabanında zaten kayıtlıdır";
            } else {
                throw $e;
                $errors[] = $e->getMessage();
            }
        }
    }
##########################################################################################################

##########################################################################################################
    if(isset($_POST['gorevi_calistir'])){
        $gorevi_calistir = $_POST['gorevi_calistir'] ?? 1;

    try {
    $sorgu = "UPDATE genel_ayarlar SET
            gorevi_calistir=?
            LIMIT 1 ";

                $stmt= $PDOdb->prepare($sorgu);
                $stmt->execute([$gorevi_calistir]);
            if ($stmt->rowCount() > 0) {
                $messages[] = "ZİP Arşivleme Tercihi Başarıyla Güncellendi.";
                header("Refresh: 2; url=".htmlspecialchars($_SERVER["PHP_SELF"])."?");
            } else {
                $errors[] = "ZİP Arşivleme Tercihi Bir Hatadan Dolayı Güncelleme Başarısız Oldu.<br />Hiçbir değişiklik yapmadan güncelleme yapıyor olabilirsiniz";
            }
        
        } catch (PDOException $e) {
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                $errors[] = "Güncellemeye çalıştığınız ZİP Arşivleme Tercihi veritabanında zaten kayıtlıdır";
            } else {
                throw $e;
                $errors[] = $e->getMessage();
            }
        }
    }
##########################################################################################################

if(isset($_POST['dosya_kilidi_ac'])){
    $lock_file = '/tmp/gorev.lock';
    if (file_exists($lock_file)) {
        unlink($lock_file);
        $messages[] = "gorev.php dosyanın başarıyla kilidi serbest bırakıldı.";
    }else{
        $errors[] = "gorev.php dosyası kilitli değil.";
    }
}

##########################################################################################################

$charsets = [
    'armscii8' => 'ARMSCII-8 Armenian - armscii8_general_ci',
    'ascii' => 'US ASCII - ascii_general_ci',
    'big5' => 'Big5 Traditional Chinese - big5_chinese_ci',
    'binary' => 'Binary pseudo charset - binary',
    'cp1250' => 'Windows Central European - cp1250_general_ci',
    'cp1251' => 'Windows Cyrillic - cp1251_general_ci',
    'cp1256' => 'Windows Arabic - cp1256_general_ci',
    'cp1257' => 'Windows Baltic - cp1257_general_ci',
    'cp850' => 'DOS West European - cp850_general_ci',
    'cp852' => 'DOS Central European - cp852_general_ci',
    'cp866' => 'DOS Russian - cp866_general_ci',
    'cp932' => 'SJIS for Windows Japanese - cp932_japanese_ci',
    'dec8' => 'DEC West European - dec8_swedish_ci',
    'eucjpms' => 'UJIS for Windows Japanese - eucjpms_japanese_ci',
    'euckr' => 'EUC-KR Korean - euckr_korean_ci',
    'gb18030' => 'China National Standard GB18030 - gb18030_chinese_ci',
    'gb2312' => 'GB2312 Simplified Chinese - gb2312_chinese_ci',
    'gbk' => 'GBK Simplified Chinese - gbk_chinese_ci',
    'geostd8' => 'GEOSTD8 Georgian - geostd8_general_ci',
    'greek' => 'ISO 8859-7 Greek - greek_general_ci',
    'hebrew' => 'ISO 8859-8 Hebrew - hebrew_general_ci',
    'hp8' => 'HP West European - hp8_english_ci',
    'keybcs2' => 'DOS Kamenicky Czech-Slovak - keybcs2_general_ci',
    'koi8r' => 'KOI8-R Relcom Russian - koi8r_general_ci',
    'koi8u' => 'KOI8-U Ukrainian - koi8u_general_ci',
    'latin1' => 'cp1252 West European - latin1_swedish_ci',
    'latin2' => 'ISO 8859-2 Central European - latin2_general_ci',
    'latin5' => 'ISO 8859-9 Turkish - latin5_turkish_ci',
    'latin7' => 'ISO 8859-13 Baltic - latin7_general_ci',
    'macce' => 'Mac Central European - macce_general_ci',
    'macroman' => 'Mac West European - macroman_general_ci',
    'sjis' => 'Shift-JIS Japanese - sjis_japanese_ci',
    'swe7' => '7bit Swedish - swe7_swedish_ci',
    'tis620' => 'TIS620 Thai - tis620_thai_ci',
    'ucs2' => 'UCS-2 Unicode - ucs2_general_ci',
    'ujis' => 'EUC-JP Japanese - ujis_japanese_ci',
    'utf16' => 'UTF-16 Unicode - utf16_general_ci',
    'utf16le' => 'UTF-16LE Unicode - utf16le_general_ci',
    'utf32' => 'UTF-32 Unicode - utf32_general_ci',
    'utf8mb3' => 'UTF-8 Unicode - utf8mb3_general_ci',
    'utf8mb4' => 'UTF-8 Unicode - utf8mb4_0900_ai_ci'
];

##########################################################################################################

$timezones = [
    'UTC' => 'Eşgüdümlü Evrensel Zaman (UTC ±0)',
    'Europe/Istanbul' => 'Türkiye Standart Zamanı (UTC +3)',
    'America/New_York' => 'Doğu Standart Zamanı (UTC -5)',
    'America/Chicago' => 'Merkezi Standart Zamanı (UTC -6)',
    'America/Denver' => 'Dağ Standart Zamanı (UTC -7)',
    'America/Los_Angeles' => 'Pasifik Standart Zamanı (UTC -8)',
    'Asia/Tokyo' => 'Japonya Standart Zamanı (UTC +9)',
    'Asia/Shanghai' => 'Çin Standart Zamanı (UTC +8)',
    'Asia/Kolkata' => 'Hindistan Standart Zamanı (UTC +5:30)',
    'Australia/Sydney' => 'Avustralya Doğu Standart Zamanı (UTC +10)',
    'Europe/London' => 'Greenwich Ortalama Zamanı (UTC ±0)',
    'Europe/Paris' => 'Orta Avrupa Zamanı (UTC +1)',
    'Europe/Berlin' => 'Orta Avrupa Zamanı (UTC +1)',
    'Europe/Moscow' => 'Moskova Standart Zamanı (UTC +3)',
    'America/Sao_Paulo' => 'Brezilya Zamanı (UTC -3)',
    'Africa/Johannesburg' => 'Güney Afrika Standart Zamanı (UTC +2)',
    'Asia/Dubai' => 'Basra Körfezi Standart Zamanı (UTC +4)',
    'Asia/Seoul' => 'Kore Standart Zamanı (UTC +9)',
    'Asia/Bangkok' => 'Indochina Zamanı (UTC +7)',
    'Pacific/Auckland' => 'Yeni Zelanda Standart Zamanı (UTC +12)'
];

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
                            <h1 class="m-0">Web Siteler Yönetimi Genel Ayarlar</h1>
                        </div><!-- / <div class="col-sm-6"> -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                                <li class="breadcrumb-item active">Genel Ayarlar</li>
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
                                Websiteleri Yedekleme Yönetimi Genel Ayarlar Hakkında Bilmeniz Gerekenler !
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <p>Buradan web siteler yönetimi sitenin ayarlarını yapabilirsiniz.
                                </p>
                                <p><strong>Tarih ve Saat için Yerel Zaman Dilimi Ayarı</strong> alanında yedeklenen dosyaların ve günlükleri zamanları ve görevlerin gerçek zamanında görevleri yerine getirlmesi ve tarihlerin doğru gösterilmesi için yerel zamanınızı ayarlamanız gerekmektedir.
                                </p>
                                <p><strong>Veritabanı Geri Yükleme MySQL Karakter Seti Belirleme</strong> veritabanı yedekleme ve geri yüklemede sorun yaşamamak için veritabanınızın karakter kodunu ne ise burada da aynısını belirlemeniz gerekir ki latin olmayan karakterlerde sorun yaşamayasınız.
                                </p>
                                <p><strong>Web Dizin Listelemede Harıç Tutulacak Dizinleri ve Dosyaları Seçiniz</strong> web site dizinleri, veritabanı yedekler dizini, web site zip yedekler dizini listelerken hosting için gerekli olan dosyalar ve dizinleri hariç tutulacakları seçebilirsiniz bu sayede daha hızlı listelenecektir.
                                </p>
                                <p><strong>FTP Bilgileri</strong> bu alana uzak sunucuda bir FTP hesabınız varsa bilgilerini giriniz. Eğer uzak sunucuda FTP hesabınız yoksa hata mesajı vermemesi için bu hosting alanınızda bir dizin &quot;FTP_dizin&quot; oluşturarak bir FTP hesabı oluşturup bilgileri bu alana giriniz. Görev zamanlamada FTP ye yedekle seçeneği seçmeyerek aynı yedeği bir hostin alanında iki yerde yedeklenmesini önleyebilirsiniz.
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

                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                                                <table class="table" style="min-width: 1000px;">
                                                <colgroup span="5">
                                                    <col style="width:25%"></col>
                                                    <col style="width:25%"></col>
                                                    <col style="width:5%"></col>
                                                    <col style="width:10%"></col>
                                                    <col style="width:10%"></col>
                                                </colgroup>
                                                    <thead>
                                                        <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
                                                            <th colspan="5" style="text-align: center;">Tarih ve Saat için Yerel Zaman Dilimi Ayarı</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="5">
                                                                Sunucu tarihi ve saati ile yerel tarihi ve saati farklılık gösterebilir. Web sitenizdeki tarihi ve saati bölgenize göre ayarlamak için aşağıdaki bölge ayarı yapınız.
                                                                Bu sayede tarih ve saat gösteren tüm alanlarınız yerel zamanı gösterecektir.
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Sunucu Tarihi & Saati</td>
                                                            <td colspan="4"><strong><?php echo $sunucu_tarihi."</strong> Varsayılan Sunucu Zaman Dilimi: <strong>".$varsayilan_zaman_dilimi; ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Yerel Tarihi & Saati</td>
                                                            <td colspan="4"><strong><?php echo $yerel_tarihi."</strong> Yerel Zaman Dilimi: <strong>".date_default_timezone_get(); ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Yerel Tarihi & Saati için bölgenizi seçin</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;min-width: 200px;">

                                                                <div class="dropdown">
                                                                    <button class="btn btn-primary dropdown-toggle  d-flex justify-content-between align-items-center" type="button" id="dropdownTimezoneButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <?php echo isset($genel_ayarlar['zaman_dilimi']) ? $timezones[$genel_ayarlar['zaman_dilimi']] : 'Zaman Dilimi Seç'; ?>
                                                                    </button>
                                                                    <ul class="dropdown-menu" aria-labelledby="dropdownTimezoneButton" style="width:520px;">
                                                                        <div class="modal-scrollbar">
                                                                        <?php foreach($timezones as $key => $value): ?>
                                                                            <li>
                                                                                <a class="dropdown-item <?php echo $genel_ayarlar['zaman_dilimi'] == $key ? 'selected' : ''; ?>" href="#" data-key="<?php echo $key; ?>" data-value="<?php echo $value; ?>">
                                                                                    <?php echo $value; ?>
                                                                                    <span class="badge bg-primary rounded-pill"><?php echo $key; ?></span>
                                                                                </a>
                                                                            </li>
                                                                        <?php endforeach; ?>
                                                                        </div>
                                                                    </ul>
                                                                    <input type="hidden" id="selectedTimezone" name="zaman_dilimi" value="<?php echo $genel_ayarlar['zaman_dilimi']; ?>">
                                                                </div>

                                                            </td>
                                                            <td colspan="3">Seçilen zaman dilimi: <span style="font-weight: bold;" id="zaman_dilimi_display"><?php echo $genel_ayarlar['zaman_dilimi']; ?></span></td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="5" style="text-align:center;">
                                                                <button type="submit" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-repeat"></span> Değişiklikleri Kaydet </button> 
                                                                <button type="reset" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
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

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                                                <table class="table" style="min-width: 1000px;">
                                                <colgroup span="5">
                                                    <col style="width:25%"></col>
                                                    <col style="width:25%"></col>
                                                    <col style="width:5%"></col>
                                                    <col style="width:10%"></col>
                                                    <col style="width:10%"></col>
                                                </colgroup>
                                                    <thead>
                                                        <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
                                                            <th colspan="5" style="text-align: center;">Veritabanı Geri Yükleme MySQL Karakter Seti Belirleme</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="5">
                                                                Latin olmayan karakterlerde sorun yaşamamak için yedek veritabanı dosyanız hangi karakter seti ile yedeklendi ise geri yükleme de aynı karakter seti seçmelisiniz
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>MySQL Geri Yükleme için Karakter Seti</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;min-width: 200px;">

                                                                <div class="dropdown">
                                                                    <button class="btn btn-primary dropdown-toggle  d-flex justify-content-between align-items-center" type="button" id="dropdownCharsetButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <?php echo isset($genel_ayarlar['karakter_seti']) ? $charsets[$genel_ayarlar['karakter_seti']] : 'Karakter Seti Seç'; ?>
                                                                    </button>
                                                                    <ul class="dropdown-menu" aria-labelledby="dropdownCharsetButton" style="width:520px;">
                                                                        <div class="modal-scrollbar">
                                                                        <?php foreach($charsets as $key => $value): ?>
                                                                            <li>
                                                                                <a class="dropdown-item <?php echo $genel_ayarlar['karakter_seti'] == $key ? 'selected' : ''; ?>" href="#" data-key="<?php echo $key; ?>" data-value="<?php echo $value; ?>">
                                                                                    <span class="file-name"><?php echo $value; ?></span>
                                                                                    <span class="badge bg-primary rounded-pill"><?php echo $key; ?></span>
                                                                                </a>
                                                                            </li>
                                                                        <?php endforeach; ?>
                                                                        </div>
                                                                    </ul>
                                                                    <input type="hidden" id="selectedCharset" name="karakter_seti" value="<?php echo $genel_ayarlar['karakter_seti']; ?>">
                                                                </div>

                                                            </td>
                                                            <td colspan="3">Seçilen karakter seti: <span style="font-weight: bold;" id="karakter_seti_display"><?php echo $genel_ayarlar['karakter_seti']; ?></span></td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="5" style="text-align:center;">
                                                                <button type="submit" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-repeat"></span> Değişiklikleri Kaydet </button> 
                                                                <button type="reset" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
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
<style>
    .dropdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dropdown-item .file-name {
        flex: 1;
    }
    .dropdown-item .badge {
        margin-left: 1rem;
        white-space: nowrap;
        font-size: 95%;
    }
    .dropdown-item .icon {
        margin-right: 0.5rem;
    }
    .dropdown-toggle {
        text-align: left;
        width: 100%;
    }
    .dropdown-toggle::after {
        margin-left: auto; /* Select ikonu sağ tarafa hizalar */
    }
    .dosya_adi {
        margin-left: 20px; /* Dosya adlarına girinti ekler */
    }
    .dropdown-item.selected {
        background-color: #E0E0E6; /* Seçili öğe için vurgu rengi */
        color: black;
    }

</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

$(document).ready(function() {
    // Zaman Dilimi Seçimi
    $('#dropdownTimezoneButton').siblings('.dropdown-menu').find('.dropdown-item').on('click', function(e) {
        e.preventDefault();
        const key = $(this).data('key');
        const value = $(this).data('value');

        // Seçili olan metni güncelle
        $('#selectedTimezone').val(key);
        $('#zaman_dilimi_display').text(key);
        $('#dropdownTimezoneButton').text(value);

        // Dropdown menüde seçili olan öğeyi vurgula
        $('#dropdownTimezoneButton').removeClass('btn-secondary').addClass('btn-primary');
        $(this).closest('.dropdown-menu').find('.dropdown-item').removeClass('selected');
        $(this).addClass('selected');
    });

    // Karakter Seti Seçimi
    $('#dropdownCharsetButton').siblings('.dropdown-menu').find('.dropdown-item').on('click', function(e) {
        e.preventDefault();
        const key = $(this).data('key');
        const value = $(this).data('value');

        // Seçili olan metni güncelle
        $('#selectedCharset').val(key);
        $('#karakter_seti_display').text(key);
        $('#dropdownCharsetButton').text(value);

        // Dropdown menüde seçili olan öğeyi vurgula
        $('#dropdownCharsetButton').removeClass('btn-secondary').addClass('btn-primary');
        $(this).closest('.dropdown-menu').find('.dropdown-item').removeClass('selected');
        $(this).addClass('selected');
    });
});

</script>
    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <table class="table" style="min-width: 1000px;">
                    <colgroup span="5">
                        <col style="width:25%"></col>
                        <col style="width:25%"></col>
                        <col style="width:5%"></col>
                        <col style="width:10%"></col>
                        <col style="width:10%"></col>
                    </colgroup>
                        <thead>
                            <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
                                <th colspan="5" style="text-align: center;">Web Dizin Listelemede Harıç Tutulacak Dizinleri ve Dosyaları Seçiniz</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5">
                                    Web dizinleri listelerken sunucunun sistem dizinleri/dosyalar dahil olmasını istemediğiniz dizin/dosya adlarını seciniz. Burada seçeceğiniz dizin/dosya adları listede gözükmeyecektir.
                                </td>
                            </tr>
                            <tr class="table-light">
                                <td colspan="5" style="min-width: 200px;">
                                <?php 

                                    $haric_dizinler_json = $genel_ayarlar['haric_dizinler'];

                                    // JSON verisi null veya boş mu kontrol edin
                                    if (is_null($haric_dizinler_json) || $haric_dizinler_json === '') {
                                        $gizli_dizinler = []; // Boş dizi olarak ayarlayın
                                    } else {
                                        // JSON verisini decode edin
                                        $gizli_dizinler = json_decode($haric_dizinler_json, true);
                                        
                                        // Decode işlemi başarısız olduysa boş dizi olarak ayarlayın
                                        if (json_last_error() !== JSON_ERROR_NONE) {
                                            $gizli_dizinler = [];
                                        }
                                    }
                                    foreach($dizin_array AS $dizin){
                                        if(in_array($dizin, $gizli_dizinler)){
                                            //echo "<input type='checkbox' name='dizinler[]' value='{$dizin}' checked> {$dizin}\n<br />";
                                            echo "<span style='width: 500px;text-align: left;font-weight: 500;font-size: 14px;' class='badge text-dark'><input type='checkbox' name='dizinler[]' value='{$dizin}' checked> {$dizin}</span>";
                                        }else{
                                            //echo "<input type='checkbox' name='dizinler[]' value='{$dizin}'> {$dizin}\n<br />";
                                            //echo "<span style='width: 500px;text-align: left;font-weight: 400;font-size: 14px;' class='badge text-dark'><input type='checkbox' name='dizinler[]' value='{$dizin}'> {$dizin}</span>";
                                        }
                                    }
                                    foreach($dizin_array AS $dizin){
                                        if(in_array($dizin, $gizli_dizinler)){
                                            //echo "<input type='checkbox' name='dizinler[]' value='{$dizin}' checked> {$dizin}\n<br />";
                                            //echo "<span style='width: 500px;text-align: left;font-weight: 500;font-size: 14px;' class='badge text-dark'><input type='checkbox' name='dizinler[]' value='{$dizin}' checked> {$dizin}</span>";
                                        }else{
                                            //echo "<input type='checkbox' name='dizinler[]' value='{$dizin}'> {$dizin}\n<br />";
                                            echo "<span style='width: 500px;text-align: left;font-weight: 400;font-size: 14px;' class='badge text-dark'><input type='checkbox' name='dizinler[]' value='{$dizin}'> {$dizin}</span>";
                                        }
                                    }
                                ?>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:center;">
                                    <button type="submit" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-repeat"></span> Değişiklikleri Kaydet </button> 
                                    <button type="reset" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
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

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                                                <table class="table" style="min-width: 1000px;">
                                                <colgroup span="5">
                                                    <col style="width:25%"></col>
                                                    <col style="width:25%"></col>
                                                    <col style="width:5%"></col>
                                                    <col style="width:10%"></col>
                                                    <col style="width:10%"></col>
                                                </colgroup>
                                                    <thead>
                                                        <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
                                                            <th colspan="5" style="text-align: center;">FTP Bilgileri</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="5">
                                                                Sunucunuzun hacklenmesi gibi durumlarda web sitenizi veya sitelerinizi geri getirebilmeniz için sunucunuzun dışında başka bir yerde de yedekleme yapılması gerekir.<br />
                                                                FTP iç Yolu: eğer FTP de A Firmaya ait tüm yedekleri sabit bir dizine yedekleme yapmak istiyorsanız "/home/user/a_firma_yedekleri/" gibi belirleyebilirsiniz. Aksi durumda sadece "/" bir slash eğik çizgi girmeniz gerekir.
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Sunucu Adı / IP:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;min-width: 200px;">
                                                                <input class="form-control" type="text" name="sunucu" value="<?php echo $genel_ayarlar['sunucu']; ?>">
                                                            </td>
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Bağlantı Noktası:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                                                                <input class="form-control" type="text" name="port" value="<?php echo $genel_ayarlar['port']; ?>">
                                                            </td>
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Kullanıcı Adı:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                                                                <input class="form-control" type="text" name="username" placeholder="Kullanıcı adı değiştiriyorsanız yeni kullanıcı adını giriniz" />
                                                            </td>
                                                            <td colspan="3">FTP Kullanıcı adı, şifreli kayıt edildiği için burada gösterilmez</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Şifre:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                                                                <input class="form-control" type="text" name="password" placeholder="Şifreyi değiştiriyorsanız yeni şifreyi giriniz" />
                                                            </td>
                                                            <td colspan="3">FTP Şifresi, şifreli kayıt edildiği için burada gösterilmez</td>
                                                        </tr>
                                                        <tr>
                                                            <td>FTP iç Yolu:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                                                                <input class="form-control" type="text" name="path" value="<?php echo $genel_ayarlar['path']; ?>">
                                                            </td>
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="5" style="text-align:center;">
                                                                <button type="submit" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-repeat"></span> Değişiklikleri Kaydet </button> 
                                                                <button type="reset" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
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

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                                                <table class="table" style="min-width: 1000px;">
                                                <colgroup span="5">
                                                    <col style="width:25%"></col>
                                                    <col style="width:25%"></col>
                                                    <col style="width:5%"></col>
                                                    <col style="width:10%"></col>
                                                    <col style="width:10%"></col>
                                                </colgroup>
                                                    <thead>
                                                        <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
                                                            <th colspan="5" style="text-align: center;">Web Dizinlerin Yedeklenmesinde Kullanılacak Zip Arşivi Yöntemi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="5">
                                                                Web dizinlerin zaman zaman yedeklenmesinde faydalı olacağı için elle veya görev zamanlayıcı ile yedeklerken web dizinlerin zip formatında sıkıştırarak yedeklemek gerekiyor.<br />
                                                                Dizinlerin zip formatında sıkıştırmak için iki yöntem bulunmaktadır bunlardan biri <b>PHP</b> kodu kullanarak Zip oluşturmaktır, bu yöntem web sitenizin belleğini ve CPU kullanımı tüketeceği gibi yavaş sıkıştırma olacaktır.<br />
                                                                Diğer ikinci yöntem ise <b>EXEC</b> le <b>Zip</b> komutu kullanımı bu fonksiyon bazı sunucular desteklemeyebilir, ancak destekleyen sunucular için bu fonksiyonun tercih edilmesi öneririz, çünkü bu fonksiyon sistem üzerinden kullanıldığı için hem çok hızlı sıkıştırma yapacağız için PHP zaman aşımı, bellek kullanımı ve CPU kullanımı yapmaz<br />
                                                                <b>EXEC</b> ile <b>Zip</b> komutu sunucunuzda çalışıp çalışmadığını aşağıdaki buton ile test ederek sonuca göre sıkıştırma tercihinizi seçebilirsiniz.
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>PHP ile Zip Arşivi Oluştur:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;min-width: 200px;">
                                                            <?php 
                                                            if(isset($genel_ayarlar['zip_tercihi']) && $genel_ayarlar['zip_tercihi']==1){
                                                                echo "<input type='radio' name='zip_tercihi' value='1' checked>";
                                                            } else if(isset($genel_ayarlar['zip_tercihi']) && $genel_ayarlar['zip_tercihi']==2){
                                                                echo "<input type='radio' name='zip_tercihi' value='1'>";
                                                            }
                                                            ?>
                                                            </td>
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td>EXEC ile Zip Arşivi Oluştur:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                                                            <?php 
                                                            if(isset($genel_ayarlar['zip_tercihi']) && $genel_ayarlar['zip_tercihi']==2){
                                                                echo "<input type='radio' name='zip_tercihi' value='2' checked>";
                                                            } else if(isset($genel_ayarlar['zip_tercihi']) && $genel_ayarlar['zip_tercihi']==1){
                                                                echo "<input type='radio' name='zip_tercihi' value='2'>";
                                                            }
                                                            ?>
                                                            </td>
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Sunucunuzda EXEC ve Zip Komutu Testi Buradan Yapabilirsiniz:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><button type="button" id="execTestButton" class="btn btn-success btn-sm">EXEC ve Zip Komutu Testi</button></td>
                                                            <td colspan="3"><div id="messageContainer"></div></td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="5" style="text-align:center;">
                                                                <button type="submit" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-repeat"></span> Değişiklikleri Kaydet </button> 
                                                                <button type="reset" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
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

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <table class="table" style="min-width: 1000px;">
                    <colgroup span="5">
                        <col style="width:25%"></col>
                        <col style="width:25%"></col>
                        <col style="width:5%"></col>
                        <col style="width:10%"></col>
                        <col style="width:10%"></col>
                    </colgroup>
                        <thead>
                            <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
                                <th colspan="5" style="text-align: center;">Zamanlanmış Görevlerde Kullanılan gorev.php dosyanın kilidini serbest bırakma</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5">
                                    Zamanlanmış görev aracılığıyla görevleri yerine getirmek için gorev.php dosyası çalıştırılır ve bu dosya ilk çalıştırıldığında kilitlenir ve görevlerin bitiminde tekrar kilidi serbest bırakılır.<br />
                                    Ancak, görevlerin yerine getirilmesi sırasında beklenmeyen hatalardan dolayı gorev.php dosyanın sonuna ulaşamadığından gorev.php dosyası kilitli kalabilir ve sonraki görevlerin yerine getirilmesi engellenmiş olur.<br />
                                    Böyle bir durumda aşağıda <span style='color: red;font-weight: bold;'>gorev.php dosyası kilitlidir.</span> Mesajını görürsünüz. Kilidi serbest bırakmak için kutuyu işaretleyerek formu göndermeniz yeterli olacaktır.<br />
                                    <b>NOT:</b> herhangi bir görev yerine getirilirken kilitli görüneceğini unutmayın, normal çalışma sırasındaki kilidi serbest bırakmayınız.<br />
                                    <?php 
                                    // Kilit dosyasının yolu
                                    $lock_file = '/tmp/gorev.lock';

                                    // Eğer kilit dosyası varsa ve dosya halen var ise işlemi sonlandır
                                    if (file_exists($lock_file)) {
                                        echo "<span style='color: red;font-weight: bold;'>gorev.php dosyası kilitlidir.</span>";
                                    }else{
                                        echo "<span style='color: green;font-weight: bold;'>gorev.php dosyası kilitli DEĞİLDİR.</span>";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>gorev.php dosyanın kilidini serbest bırak:</td>
                                <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;min-width: 200px;">
                                    <input type='checkbox' name='dosya_kilidi_ac' value='1'>
                                </td>
                                <td colspan="3">&nbsp;</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:center;">
                                    <button type="submit" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-repeat"></span> Dosya Kilidi Serbest Bırak </button> 
                                    <button type="reset" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
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

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                                                <table class="table" style="min-width: 1000px;">
                                                <colgroup span="5">
                                                    <col style="width:25%"></col>
                                                    <col style="width:25%"></col>
                                                    <col style="width:5%"></col>
                                                    <col style="width:10%"></col>
                                                    <col style="width:10%"></col>
                                                </colgroup>
                                                    <thead>
                                                        <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
                                                            <th colspan="5" style="text-align: center;">Görevi Otomatik Çalıştırma Yöntemini Belirleme</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="5">
                                                                Zamanlanmış görevlerin yerine getirilmesi için <b>gorev.php</b> dosyanın çalıştırılması gerekir.<br />
                                                                &nbsp;1. Yöntem, Plesk Panel & cPanel üzerinden Cron İşlerine komut ekleyerek <b>gorev.php</b> dosyanın çalıştırılmasıdır.<br />
                                                                &nbsp;2. Yöntem, bu script içinde herhangi bir sayfa ziyaret edildiğinde ajax ile <b>gorev.php</b> dosyası tetiklerek çalıştırılmasıdır.<br />
                                                                Bu seçeneklerden önerilen seçenek 1. seçenektir.<br />
                                                                Cron işlerine komut ekleyip <b>gorev.php</b> dosyayı her dakika çalışacak şeklinde ayarladığınızda hem tüm görevlerin tam zamanında yerine getirilmesi sağlanmış olacağı gibi görevlerin yerine getirilirken web sitesi bundan etkilenmeden rahatça gezinebilir ve yapmak istediğiniz işleri kolaylıkla yapabilirsiniz.<br />
                                                                Ancak, Ajax ile veya elle doğrudan <b>https://alanadi.com/gorev.php</b> url çalıştırıldığında görev işlemi bitene kadar sitede herhangi bir işlem yada sayfa değiştirilmez.
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Görevi Cron İşleri İle Çalıştır:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;min-width: 200px;">
                                                            <?php 
                                                            if(isset($genel_ayarlar['gorevi_calistir']) && $genel_ayarlar['gorevi_calistir']==1){
                                                                echo "<input type='radio' name='gorevi_calistir' value='1' checked>";
                                                            } else {
                                                                echo "<input type='radio' name='gorevi_calistir' value='1'>";
                                                            }
                                                            ?>
                                                            </td>
                                                            <td colspan="3">Eğer <b>Cron Job</b> alanına "<b>/usr/local/bin/php <?php echo $_SERVER['DOCUMENT_ROOT']; ?>/gorev.php</b>>/dev/null 2>&1" komutu eklemiş iseniz çalışır.</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Görevi Ajax İle Çalıştır:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                                                            <?php 
                                                            if(isset($genel_ayarlar['gorevi_calistir']) && $genel_ayarlar['gorevi_calistir']==2){
                                                                echo "<input type='radio' name='gorevi_calistir' value='2' checked>";
                                                            } else {
                                                                echo "<input type='radio' name='gorevi_calistir' value='2'>";
                                                            }
                                                            ?>
                                                            </td>
                                                            <td colspan="3">Ajax kodu mevcuttur</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Görevi Ajax & Cron İle Çalıştır:</td>
                                                            <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                                                            <?php 
                                                            if(isset($genel_ayarlar['gorevi_calistir']) && $genel_ayarlar['gorevi_calistir']==3){
                                                                echo "<input type='radio' name='gorevi_calistir' value='3' checked>";
                                                            } else {
                                                                echo "<input type='radio' name='gorevi_calistir' value='3'>";
                                                            }
                                                            ?>
                                                            </td>
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="5" style="text-align:center;">
                                                                <button type="submit" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-repeat"></span> Değişiklikleri Kaydet </button> 
                                                                <button type="reset" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
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


<br />
        </div><!-- / <div class="content-wrapper"> -->
        
<script type='text/javascript'>
    var satir = '';
    var query = '';
    var tarih = '';
    var firma = '';
</script>

<script type='text/javascript'>
    document.getElementById('execTestButton').addEventListener('click', function() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'exec_test.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var messageContainer = document.getElementById('messageContainer');
                messageContainer.innerHTML = '<div style="padding: 1px 0px 1px 10px;" class="alert-' + response.status + '">' + response.message + '</div>';
            }
        };
        xhr.send('exec_test=1');
    });
</script>

<?php 
include('includes/footer.php');
?>
