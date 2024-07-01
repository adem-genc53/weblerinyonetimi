<?php 
// Bismillahirrahmanirrahim
if(session_status() == PHP_SESSION_NONE && !headers_sent()){
// Oturum adını belirleyin
if (isset($_SERVER['SERVER_NAME']) && !is_int($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
    $serverName = str_replace('.','_', $_SERVER['SERVER_NAME']);
} elseif (isset($_SERVER['HTTP_HOST']) && !is_int($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
    $serverName = str_replace('.','_', $_SERVER['HTTP_HOST']);
} elseif (getenv('SERVER_NAME') !== null && !is_int(getenv('SERVER_NAME')) && !empty(getenv('SERVER_NAME'))) {
    $serverName = str_replace('.','_', getenv('SERVER_NAME'));
}else{
    $serverName = str_replace('.','_', 'antenfiyati');
}
session_name($serverName);
// Oturumu başlatın
session_start();
}

// Oturum kimliğini yenileme süresini belirleyin (örneğin 15 dakika)
$regenerate_time = 15 * 60; // 15 dakika
// Şu anki zaman ve oturum başlangıç zamanını kontrol edin
if (isset($_SESSION['start_time']) && (time() - $_SESSION['start_time'] > $regenerate_time) && isset($_SESSION)) {
    // Oturum kimliğini yenileyin
    session_regenerate_id(true);
    // Yeni başlangıç zamanını ayarlayın
    $_SESSION['start_time'] = time();
}


// Hata raporlamayı aç
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hata günlüğünü belirli bir dosyaya yönlendir
ini_set('log_errors', 1);
ini_set('error_log', '/home/user/error.log'); // Bu yolu kendi sunucunuzdaki uygun bir yolla değiştirin


    defined('DB_USER')      or define('DB_USER', 'root');
    defined('DB_PASSWORD')  or define('DB_PASSWORD', '');
    defined('DB_HOST')      or define('DB_HOST', 'localhost');
    defined('DB_NAME')      or define('DB_NAME', 'github_webyonetimi');
    defined('PORT')         or define('PORT', '3306');
    defined('CHARSET')      or define('CHARSET', 'utf8mb4');

    $options = [
        PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES      => false,
    ];

    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".CHARSET.";port=".PORT."";
    try {
        $PDOdb = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        $PDOdb->exec("set names ".CHARSET);
        $PDOdb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
        echo '<h2 style="text-align: center;">' . $e->getMessage() . ' Bağlantı başarısız oldu</h2>';
        exit;
    }

    // Veritabanı işlemlerinde hataları ayıklamak için gereklidir
    $PDOdb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
########################################################################################################################
    // Tüm veritabanılarını dizi oluşturuyoruz
    $sql = " SELECT id, db_name
    FROM veritabanlari ORDER BY db_name ASC";
    $stmt = $PDOdb->prepare($sql);
    $stmt->execute();
    $veritabanlari_arr = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

########################################################################################################################
    // Her sayfada kullanılacak ayarlar
    $gene_ayarlar = $PDOdb->prepare('SELECT * FROM genel_ayarlar WHERE id=?');
    $gene_ayarlar->execute(['1']);
    $genel_ayarlar = $gene_ayarlar->fetch(PDO::FETCH_ASSOC);
########################################################################################################################
    // Google Drive Servis Hesabının hesap bilgileri içeren json dosyanın yolu
    // Bu yolun tam yol olması gerekiyor yani "../../" gibi değil "/home/user/website/plugins/google_drive/client_json/client_secrets.json" gibi tam yol olmalıdır
    $authConfigPath = '/home/user/web_site_dizin/plugins/google_drive/client_json/client_secrets.json';
########################################################################################################################

    // veritabanı yedeklenecek 'DATABASEBACKUP' klasör adını değiştirebilirsiniz
    // Bu web sitenin dışında yedekleme klasörü oluşturabilirsiniz
    // Bu sayede bu web sitenin boyutu artmaz
    // Bu yolun tam yol olması gerekiyor yani "../../" gibi değil "/home/user/DATABASEBACKUP" gibi tam yol olmalıdır
    defined('BACKUPDIR')        or define('BACKUPDIR', '/home/user/DATABASEBACKUP');    // Veritabanıları yedeklenecek Klasörün adı, SONUNDA EĞİK ÇİZGİ YOK

    // "Web Site Dizinleri" sayfasında listelenecek web dizinlerinin bulunduğu alanın yolu girilmedir.
    // Bu yolun tam yol olması gerekiyor yani "../../" gibi değil "/home/user/" gibi tam yol olmalıdır
    defined("DIZINDIR")         or define("DIZINDIR", "/home/user/");    // Yolun sonunda / eğik çizgi olmalıdır
    
    // "Zipli Web Site Dizinleri" sayfasında listelecek yedeklemek için sıkıştırılan zipli dosyaların bulunduğu alanın yolu girilmelidir
    // Bu yolun tam yol olması gerekiyor yani "../../" gibi değil "/home/user/WEBZIPLER/" gibi tam yol olmalıdır
    defined("ZIPDIR")           or define("ZIPDIR", "/home/user/WEBZIPLER/");     // Yolun sonunda / eğik çizgi olmalıdır

    // KOKYOLU sunucunuzda en geriye ulaşamabildiğiniz yoldur.
    // Örnek "/home/user/" en geri user alanına kadar gidilebilir
    // Bunun kullanım özelliği zipli web site dizinin zipi açarken otomatik olarak yolun başlangıcı olarak "/home/user/" bunu sağlayacak bundan sonra istediğiniz gibi yol girebilirsiniz
    // Örnek /home/user/111/222/333 diye belirlediğiniz user alana 111 dizini ve alt-dizinleride oluşturup 333 dizine zip dosyayı açacaktır
    defined("KOKYOLU")          or define("KOKYOLU", "/home/user/"); // web sitelerin dizinleri bulunduğu tam yolu girin. Yolun sonunda / eğik çizgi olmalıdır

    //Script versiyonu
    defined('VERSIYON')         or define('VERSIYON', '2.0.1');

/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////
    // DEFINE de belirlenen dizin yoksa oluştur
if(preg_match("/[a-zA-Z0-9_]/i", BACKUPDIR)){
    if(!file_exists(BACKUPDIR)){
        if (!mkdir(BACKUPDIR, 0777, true)) {
            die('Klasörler oluşturulamadı.');
        }
    }
    // Dizinin içine kimse ulaşamasın diye .htaccess oluşturuyoruz ve içine 'deny from all' yazıyoruz
    $file = new SplFileObject(BACKUPDIR . '/.htaccess', "w") ;
    $file->fwrite('deny from all');
}
/////////////////////////////////////////////////////////////////////////////////////////////////////
if(preg_match("/[a-zA-Z0-9_]/i", ZIPDIR)){
    if(!file_exists(ZIPDIR)){
        if (!mkdir(ZIPDIR, 0777, true)) {
            die('Klasörler oluşturulamadı.');
        }
    }
    // Dizinin içine kimse ulaşamasın diye .htaccess oluşturuyoruz ve içine 'deny from all' yazıyoruz
    $file = new SplFileObject(ZIPDIR . '/.htaccess', "w") ;
    $file->fwrite('deny from all');
}
/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////
    /* BURAYAI DEĞİŞTİRMEYİN */
    function htmlpath($relative_path) {
        $realpath = realpath($relative_path);
        $htmlpath = str_replace($_SERVER['DOCUMENT_ROOT'],'',$realpath);
        $htmlpath = str_replace('\\', '/', $htmlpath);
        return $htmlpath;
    }

?>
