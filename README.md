## Bu Script nedi?<br />
Hosting içinde 1 veya daha fazla web sitelerin `veritabanı`, `web site dizini` veya `web sitenin önemli bir klasörü` otomatik olarak yedekleyerek uzak `FTP Sunucusuna` ve veya `Google Drive Api Sunucusuna` istenen günde, istenen saatte ve istenen dakikada yedekleme yapar.<br />
. Uzak sunuculara elle dosya ve dizin yedekleme yapılabilir.<br />
. Uzak sunuculardan istenilen yerel alana yedekleri indirilebilir.<br />
. Zip formatında sıkıştırılan web dizini veya web sitenin önemli klasörünü istenilen yerel alana zip çıkarılabilir.<br />
. Yedeklenen veritabanıları geri yükleyebilir<br />
. Elle veritabanı tümü veya seçilecek tabloları yedekleyebilir.<br />
. Elle web site dizinlerini zip formatında sıkıştırabilir.<br />
. Özel görevler ekleyebilirsiniz.<br />
. Ve daha fazlası.<br />
Herhangi bir neden veya nedenlerden dolayı zarar gören web siteninizin yedekleme zamanına kısa sürede geri dönmenizi sağlar.<br />

## Nasıl kurulur<br />
Tüm dosyaları bunun için oluşturacağınız domain klasörünün içine kopyalayın<br />
Bir veri tabanı oluşturun<br />
`includes` klasörün içindeki `webyonetimi.sql` veri tabanı yedeğini oluşturduğunuz veri tabanına PhpMyAdmin kullanarak yükleyin<br />
`includes` klasörün içindeki `connect.php` dosya text editör ile açarak aşağıdaki alana eklediğiniz veri tabanı bilgilerini girim<br />

    defined('DB_USER')      or define('DB_USER', 'root');
    defined('DB_PASSWORD')  or define('DB_PASSWORD', '');
    defined('DB_HOST')      or define('DB_HOST', 'localhost');
    defined('DB_NAME')      or define('DB_NAME', 'webyonetimi');
    defined('PORT')         or define('PORT', '3306');
    defined('CHARSET')      or define('CHARSET', 'utf8mb4');

Aşağıdaki alanlar açıklamaları okuyup kendinize göre değiştirin<br />

    defined('BACKUPDIR')        or define('BACKUPDIR', '/home/user/DATABASEBACKUP');
    defined("DIZINDIR")         or define("DIZINDIR", "/home/user/");
    defined("ZIPDIR")           or define("ZIPDIR", "/home/user/WEBZIPLER/");
    defined("KOKYOLU")          or define("KOKYOLU", "/home/user/");

Ben robot değilim etkinleştirmek için domain adınıza keyleri oluşturmanız gerekiyor<br />
Buradan https://www.google.com/recaptcha/ keyleri alın<br />
`login.php` içinde `data-sitekey="xxxxxxxxxxxxxxxxxxxxx"` alana SİTE ANAHTARI girin<br />
`recaptcha.php` içinde `$secret = "xxxxxxxxxxxxxxxxxxxx"` alana GİZLİ ANAHTARI girin<br />

Kendi sunucunuzda bir klasöre ftp hesabı oluşturun ve siteye login olduktan sonra Ayarlar bölümündeki ftp alana girip kaydedin<br />

Google Drive Servis Hesabı için aşağıdaki linki tıklayın `Servis Hesabı` oluşturun JSON dosyayı indirin<br />
https://console.cloud.google.com/apis/dashboard<br />
Indirdiğiniz JSON dosyayı `client_secrets.json` olarak yeniden adlandırın ve aşağıdaki konuma kopyalayın<br />
`/home/user/web_diziniz/plugins/google_drive/client_json/client_secrets.json`<br />

`Veritabanı Ekle/Düzelt` alanından bu sitenin veri tabanı bilgilerini ekleyin ve diğer varsa sitelerinizin de veri tabanı bilgilerini ekleyin tabı aynı sunucuda olacak<br />
`Görev Zamanlayıcı` alanından yeni görevler ekleyebilirsiniz xxxxxx veri tabanı şu zamanda yedekle ve FTP ye ve veya Google la yedekle seçenekleri kullanabilirsiniz<br />

## Önemli not:<br />
Görevlerin çalışması için siteyi birileri ziyaret etmesi gerekiyor ki görevler yerine getirilsin<br />
Eğer ben tam zamanında görevin çalışmasını istiyorum diyorsanız "hosting cPaneldeki" "Cron İşleri" alanında yeni bir cron oluşturup dakikada bir kez seçiniz<br />
Komut alanına `/usr/local/bin/php /home/user/alan_adiniz.com/gorev.php >/dev/null 2>&1` girip kaydedin planladığınız zamanlarda webyönetim siteniz tetiklenecek ve zamanında görevler yerine getirilecek<br /><br />
Eğer görevleri sadece "Cron İşleri" ile çalıştırırsanız cPanelden bu siteye ait dizini şifreleyip bu web siteyi dahada güvenli halde getirmiş olursunuz. Güçlü dizin şifresi bilenin dışında kimse erişemez. (tabiki 100% güvenli garantisi hiçbir şeyde olmadığını unutmayın)<br />

## Not:<br />
Veri tabanı bilgileri eklediğiniz veri tabanına kaydederken şifreliyor bu şifre için şifre anahtarı değiştirebilirsiniz<br />
`hash.php` içindeki key alanındaki şifreyi değiştirebilirsiniz<br />
Buradan https://randomkeygen.com/ rasgle şifre oluşturabilirsiniz<br />
Bu anahtar ile şifrelenen veri tabanı bilgileri tekrar bu şifre ile çözülebilir<br />

Giriş için<br />
Kullanıcı Adı: `admin@gmail.com`<br />
Şifre: `123456`<br />

## Klasör yükleme ve İndirme<br />
İçinde alt dizinler ve alt dosyalar içeren klasörü Google Derive'a yükleyebilirsiniz ve indirebilirsiniz.<br />
Tek tek dosyalarıda yükleyebilir ve indirebilirsiniz.
