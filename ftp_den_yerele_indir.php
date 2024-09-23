<?php 
// Bismillahirrahmanirrahim
header('Connection: Keep-Alive');
header('Keep-Alive: timeout=5, max=100');
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;

ob_start();
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); // 7200 saniye 120 dakikadır, 3600 1 saat

if($_SERVER['REQUEST_METHOD'] == 'POST'){

//echo '<pre>' . print_r($_POST, true) . '</pre>';
//exit;


    echo "<div style='margin: 25px;'>";
    $ftp_server = $genel_ayarlar['sunucu'] ?? '';
    $ftp_username = !empty($genel_ayarlar['username']) ? $hash->take($genel_ayarlar['username']) : '';
    $ftp_password = !empty($genel_ayarlar['password']) ? $hash->take($genel_ayarlar['password']) : '';
    $ftp_path = $genel_ayarlar['path'];

    // FTP Bağlantı türü ve modunu ayarlardan al
    $ftp_mode = $genel_ayarlar['ftp_mode']; // 'active' veya 'passive'
    $ftp_ssl = $genel_ayarlar['ftp_ssl']; // true veya false

        // FTP bağlantısı kur
        if ($ftp_ssl) {
            // SSL bağlantısı kur ve oturumu aç
            $ftp_connect = ftp_ssl_connect($ftp_server);
            if (!$ftp_connect) {
                die("FTP SSL bağlantısı kurulamadı.");
            }
        } else {
            // Standart bağlantı kur ve oturumu aç
            $ftp_connect = ftp_connect($ftp_server);
            if (!$ftp_connect) {
                die("FTP Standart bağlantısı kurulamadı.");
            }
        }

        // Zaman aşımını ayarla (örneğin, 120 saniye)
        ftp_set_option($ftp_connect, FTP_TIMEOUT_SEC, 120);

        if ($ftp_connect) {
            ftp_login($ftp_connect, $ftp_username, $ftp_password);

            // Pasif/Aktif mod ayarı
            if ($ftp_mode) {
                ftp_pasv($ftp_connect, true);
            } else {
                ftp_pasv($ftp_connect, false);
            }
        }else{
            ftp_close($ftp_connect);
            die("FTP oturumu açılamadı.");
        }

if(isset($_POST['ftp_den_secilen_dosya']) && !empty($_POST['ftp_den_secilen_dosya'])){
    $ftp_kaynak = trim($_POST['ftp_den_secilen_dosya']); //  " /dizinsizwebyonetimitablotablo-2023-10-12-00-00/ " // başında ve sonunda eğik çizgi var

    // Başlangıçta değişkeni boş olarak tanımla
    $ftp_secilen_kaynak_indir = '';
    $ftp_hesabindaki_dizini_bosalt = false;
    
    // Genel Ayarlarda $ftp_path FTP Hesap bilgileri ile "FTP iç Yolu:" DİZİN belirlenmedi ise ve / eğik çizgi varsa
    if ($ftp_path === '/') {

        $ftp_secilen_kaynak_indir .= "/"; // eğik / çizgiyi tekrar ekle

    // Genel Ayarlarda $ftp_path FTP hesabında "FTP iç Yolu:" DİZİN var.
    // $ftp_kaynak Ağaçtan Ev seçildi ise / eğik çizgi geliyor
    }elseif($ftp_path != '/' && $ftp_kaynak == '/'){

        // Genel Ayarlarda $ftp_path FTP hesabında DİZİN var.
        // $ftp_kaynak AĞAÇ tan Ev seçilerek / eğik çizgi geldi
        // Bu durumda FTP hesabındaki dizini boşaltacağız
        $ftp_secilen_kaynak_indir .= "/".trim($ftp_path, '/');
        // Bu değişken true gönderek FTP hesab dizini silmesini engelleyecek
        $ftp_hesabindaki_dizini_bosalt = true;

    // Genel Ayarlarda $ftp_path FTP hesabında DİZİN var.
    // $ftp_kaynak Ağaçtan DOSYA veya DİZİN seçildi ise
    }elseif($ftp_path !== '/' && $ftp_kaynak !== '/'){

        // Ağaçta klasör veya dosya seçildi ise seçileni sil
        $ftp_secilen_kaynak_indir .= "/".trim($ftp_path, '/');

    }

    // Ağaçtan Ev seçili DEĞİL İSE
    if ($ftp_kaynak !== '/') {

        // Ağaçtan seçilen dosya veya klasörü sil
        $ftp_secilen_kaynak_indir .= $ftp_kaynak;

    }
    $ftp_secilen_kaynak_indir = str_replace(['//', '\\\\'], '/', $ftp_secilen_kaynak_indir);
}

if(isset($_POST['yerel_den_secilen_dosya']) && !empty($_POST['yerel_den_secilen_dosya'])){
    $path_secilen = str_replace(['//', '\\\\'], '/', $ftp_path.trim($_POST['ftp_den_secilen_dosya']));
    if(!@ftp_chdir($ftp_connect, $path_secilen)){
        $yerel_dizin = trim($_POST['yerel_den_secilen_dosya']); // Dosya indiriliyor
    }else{
        $yerel_dizin = trim($_POST['yerel_den_secilen_dosya']).basename(trim($_POST['ftp_den_secilen_dosya'])); // Klasör indiriliyor
    }
}

// Yerel ve FTP dizinlerini görüntülemek için bilgi mesajı
echo "<b>Yerel:</b> ".$_POST['yerel_den_secilen_dosya']." <b>Dizine:</b><br />";

function ftp_sync($_from = null, $_to = null) {
    global $ftp_connect;
    
    if (!is_null($_from)) {
        $is_dir = @ftp_chdir($ftp_connect, $_from);
        ftp_chdir($ftp_connect, '..'); // Geri dön

        if (!$is_dir) {
            //echo "Dosya olduğu algılandı: $_from<br />";
            $ftp_size = ftp_size($ftp_connect, $_from);

            if ($ftp_size == -1) {
                die("<span style='color: red;'>Dosya mevcut değil veya boyutu alınamadı:</span> $_from");
            }

            $tekdosya = basename($_from);
            if (!is_dir($_to)) mkdir($_to, 0777, true);
            if (!chdir($_to)) die("Yerelde dizin mevcut değil mi? $_to");

            if (ftp_get($ftp_connect, $tekdosya, $_from, FTP_BINARY)) {
                echo $tekdosya . " <b>[İNDİRİLDİ]</b><br />";
            } else {
                die("<span style='color: red;'>Dosya indirilemedi:</span> $_from");
            }
        } else {
            //echo "Dizin olduğu algılandı: $_from<br />";
            if (!ftp_chdir($ftp_connect, $_from)) die("FTP'de dizin bulunamadı: $_from");

            if (isset($_to)) {
                echo " <br /><b>Dizin oluşturuldu:</b> " . basename($_to) . "<br />\n";
                if (!is_dir($_to)) mkdir($_to, 0777, true);
                if (!chdir($_to)) die("Yerelde dizin mevcut değil mi? $_to");
            }

            // Çözüm: Eğer ftp_mlsd çalışmıyorsa, dizin içeriğini almak için ftp_nlist kullanabilirsiniz:
            $contents = ftp_mlsd($ftp_connect, '.');
            if ($contents === false) {
                die("<span style='color: red;'>Dizin içeriği alınamadı:</span> $_from");
            }

            foreach ($contents as $p) {
                $p = array_change_key_case($p, CASE_LOWER);

                if ($p['type'] != 'dir' && $p['type'] != 'file') continue;

                $file = $p['name'];

                if ($p['type'] == 'file') {
                    if (file_exists($file) && !is_dir($file) && filemtime($file) >= strtotime($p['modify'])) {
                        echo "$file <b>[MEVCUT VE GÜNCEL]</b><br />";
                    } elseif (@ftp_get($ftp_connect, $file, $file, FTP_BINARY)) {
                        echo "$file <b>[İNDİRİLDİ]</b><br />";
                    } else {
                        echo "<span style='color: red;'>Dosya indirilemedi:</span> $file<br />";
                    }
                } elseif ($p['type'] == 'dir') {
                    echo " <br /><b>Dizin oluşturuldu:</b> " . basename($file) . "<br />\n";
                    if (!is_dir($file)) mkdir($file, 0777, true);
                    chdir($file);
                    ftp_sync($_from . '/' . $file, $_to . '/' . $file); // Doğru parametrelerle rekürsif çağrı
                    ftp_chdir($ftp_connect, '..'); // FTP'de bir üst dizine geri dön
                    chdir('..'); // Yerel dizinde bir üst dizine geri dön
                }
            }
        }
    }
}

// Senkronizasyon başlatılır
ftp_sync(trim($ftp_secilen_kaynak_indir), trim($yerel_dizin));

// FTP bağlantısı kapatılır
ftp_close($ftp_connect);

// umask ile izinleri 777 olarak ayarlar (isteğe bağlı)
umask(0);

echo "</div>";

} //  if($_SERVER['REQUEST_METHOD'] == 'POST'){

ob_flush();
flush();
