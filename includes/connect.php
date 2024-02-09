<?php 
// Bismillahirrahmanirrahim
if (session_id() == '' && !headers_sent()) {
session_start();
}

//Tüm hataları gizle
//error_reporting(0);
//ini_set('display_errors', 0);

// Tüm hataları göster
error_reporting(E_ALL);
ini_set('display_errors', 1);

    require(dirname(dirname(__FILE__))."/hash.php");
    $hash = new Hash;

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
        $PDOdb->exec("set names utf8");
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
    $authConfigPath = 'plugins/google_drive/client_json/client_secrets.json';
########################################################################################################################

    // veritabanı yedeklenecek 'DATABASEBACKUP' klasör adını değiştirebilirsiniz
    // Bu web sitenin dışında yedekleme klasörü oluşturabilirsiniz
    // Bu sayede bu web sitenin boyutu artmaz
    defined('BACKUPDIR')        or define('BACKUPDIR', '../DATABASEBACKUP');    // Veritabanıları yedeklenecek Klasörün adı, SONUNDA EĞİK ÇİZGİ YOK

    // "Web Site Dizinleri" sayfasında listelenecek web dizinlerinin bulunduğu alanın yolu girilmedir.
    defined("DIZINDIR")         or define("DIZINDIR", "../");    // Yolun sonunda / eğik çizgi olmalıdır
    
    // "Zipli Web Site Dizinleri" sayfasında listelecek yedeklemek için sıkıştırılan zipli dosyaların bulunduğu alanın yolu girilmelidir
    defined("ZIPDIR")           or define("ZIPDIR", "../WEBZIPLER/");     // Yolun sonunda / eğik çizgi olmalıdır

    // KOKYOLU sunucunuzda en geriye ulaşamabildiğiniz yoldur.
    // Örnek "/home/user/" en geri user alanına kadar gidilebilir
    // Bunun kullanım özelliği zipli web site dizinin zipi açarken otomatik olarak yolun başlangıcı olarak "/home/user/" bunu sağlayacak bundan sonra istediğiniz gibi yol girebilirsiniz
    // Örnek /home/user/111/222/333 diye belirlediğiniz user alana 111 dizini ve alt-dizinleride oluşturup 333 dizine zip dosyayı açacaktır
    defined("KOKYOLU")          or define("KOKYOLU", "D:/SUNUCU/www/projelerim/"); // web sitelerin dizinleri bulunduğu tam yolu girin. Yolun sonunda / eğik çizgi olmalıdır

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
