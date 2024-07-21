<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;

ob_start();
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); //7200 saniye 120 dakikadır, 3600 1 saat

if($_SERVER['REQUEST_METHOD'] == 'POST'){
echo "<div style='margin: 25px;'>";
$ftphost = $genel_ayarlar['sunucu'] ?? '';
$ftpuser = !empty($genel_ayarlar['username']) ? $hash->take($genel_ayarlar['username']) : '';
$ftppass = !empty($genel_ayarlar['password']) ? $hash->take($genel_ayarlar['password']) : '';
$ftp_path = $genel_ayarlar['path'];


$ftp_connect = @ftp_ssl_connect($ftphost) 
    or die($ftphost  . " sunucuya bağlanamadı"); 

$login_result = ftp_login($ftp_connect, $ftpuser, $ftppass); 
if ((!$ftp_connect) || (!$login_result)) 
    die("FTP Bağlantısı Başarısız");

    //var_dump(ftp_chdir($ftp_connect, $_POST['ftp_den_secilen_dosya']));
    //echo '<pre>' . print_r($yerel_dizin, true) . '</pre>';
    //exit;

if(isset($_POST['ftp_den_secilen_dosya']) && !empty($_POST['ftp_den_secilen_dosya'])){
    $ftp_kaynak = trim($_POST['ftp_den_secilen_dosya']); //"/dizinsizwebyonetimitablotablo-2023-10-12-00-00/"; // başında ve sonunda eğik çizgi var

    // Başlangıçta değişkeni boş olarak tanımla
    $ftp_secilen_kaynak_indir = '';
    $ftp_hesabindaki_dizini_bosalt = false;

    $ftp_kaynak = trim($_POST['ftp_den_secilen_dosya']);
    
    // FTP Hesap bilgileri ile DİZİN belirlenmedi ise ve / eğik çizgi varsa
    if ($ftp_path === '/') {

        $ftp_secilen_kaynak_indir .= "/"; // eğik / çizgiyi tekrar ekle

    // FTP hesabında DİZİN var ve Ağaçtan Ev seçildi ise / eğik çizgi geliyor
    }elseif($ftp_path != '/' && $ftp_kaynak == '/'){

        // FTP hesabında DİZİN var ve AĞAÇ tan Ev seçilerek / eğik çizgi geldi
        // Bu durumda FTP hesabındaki dizini boşaltacağız
        $ftp_secilen_kaynak_indir .= "/".trim($ftp_path, '/');
        // Bu değişken true gönderek FTP hesab dizini silmesini engelleyecek
        $ftp_hesabindaki_dizini_bosalt = true;

    // FTP hesabında DİZİN var ve Ağaçtan DOSYA veya DİZİN seçildiz ise
    }elseif($ftp_path !== '/' && $ftp_kaynak !== '/'){

        // Ağaçta klasör veya dosya seçildi ise seçileni sil
        $ftp_secilen_kaynak_indir .= "/".trim($ftp_path, '/');

    }

    // Ağaçtan Ev seçili DEĞİL İSE
    if ($ftp_kaynak !== '/') {

        // Ağaçtan seçilen dosya veya klasörü sil
        $ftp_secilen_kaynak_indir .= $ftp_kaynak;

    }
}

if(isset($_POST['yerel_den_secilen_dosya']) && !empty($_POST['yerel_den_secilen_dosya'])){
    if(!@ftp_chdir($ftp_connect, trim($_POST['ftp_den_secilen_dosya']))){
        $yerel_dizin = trim($_POST['yerel_den_secilen_dosya']);
    }else{
        $yerel_dizin = trim($_POST['yerel_den_secilen_dosya']).basename(trim($_POST['ftp_den_secilen_dosya']));
    }
}
    //$ftp_kaynak = preg_replace('/^\//', '', $ftp_kaynak);
    //$ftp_kaynak = preg_replace('/\/*$/', '', $ftp_kaynak);

    echo "<b>Yerel:</b> ".$_POST['yerel_den_secilen_dosya']." <b>Dizine:</b><br />";
function ftp_sync($_from = null, $_to = null) {
    
    global $ftp_connect;
    
    // Kaynak boş değil ise
    if (!is_null($_from) && ftp_nlist($ftp_connect, $_from) == false) {
        // FTP de olmayan dizin olursa burada kontrol ediyoruz
        // Bu FTP de olmayan dizini veya dosyayı kotrol ediyor. Aslında bu son değiştirlme zamanı dönduruyor ama olmayan dizin veya dosya için -1 döndürüyor buda dizin ve dosya varmı yokmu kontrol ediyoruz
        if ( ftp_mdtm($ftp_connect, $_from) != '-1' ){
        if (isset($_to)) {
            $tekdosya = basename($_from);
            if (!is_dir($_to)) mkdir($_to, 0777, true);
            if (!chdir($_to)) die("Yerelde dizin mevcut değil mi? $_to");
            if (ftp_get($ftp_connect, $tekdosya, $_from, FTP_BINARY)) {
                echo $tekdosya." [KOPYALANDI]";
            }
        }
    }else{
        die("<span style='color: red;'>FTP'de bu mevcut değil:</span> $_from");
    }

    }else{

        if (isset($_from)) {
            if (!ftp_chdir($ftp_connect, $_from)) die("FTP'de dizin bulunamadı: $_from");
            if (isset($_to)) {
                if (!is_dir($_to)) mkdir($_to, 0777, true);
                if (!chdir($_to)) die("Yerelde dizin mevcut değil mi? $_to"); 
            }
        }

    
        $contents = ftp_mlsd($ftp_connect, '.');
        
        foreach ($contents as $p) {
            
            if ($p['type'] != 'dir' && $p['type'] != 'file') continue;
            
            $file = $p['name'];
            
            //echo ftp_pwd($ftp_connect).'/'.$file;
            echo $p['type'] == 'file' ? $file : "";
            
            if (file_exists($file) && !is_dir($file) && filemtime($file) >= strtotime($p['modify'])) {
                echo " [MEVCUT VE GÜNCEL]";
            }
            elseif ($p['type'] == 'file' && @ftp_get($ftp_connect, $file, $file, FTP_BINARY)) {
                echo " [KOPYALANDI]";
            }
            elseif ($p['type'] == 'dir' && @ftp_chdir($ftp_connect, $file)) {
                echo " <br /><b>Dizin oluşturuldu:</b> $file<br />\n";
                if (!is_dir($file)) mkdir($file, 0777, true);
                chdir($file);
                ftp_sync();
                ftp_chdir($ftp_connect, '..');
                chdir('..');
            }
            
            echo "<br />\n";
        }
    } // else if (ftp_nlist($ftp_connect, $_from) == false) {

}

ftp_pasv($ftp_connect, true); // pasif FTP bağlantısı (comment-out if needed)

ftp_sync(trim($ftp_secilen_kaynak_indir), trim($yerel_dizin));

ftp_close($ftp_connect);

umask(0); // her dizin chmod 777 olacak
echo "</div>";
} //  if($_SERVER['REQUEST_METHOD'] == 'POST'){
