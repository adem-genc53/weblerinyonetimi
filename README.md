## Bu Script nedir?<br />
Hosting içinde 1 veya daha fazla web sitelerin `veritabanı`, `web site dizini` veya `web sitenin önemli bir klasörünü` otomatik olarak yedekleyerek uzak `FTP Sunucusuna` ve veya `Google Drive Api Sunucusuna` istenen günde, istenen saatte ve istenen dakikada yedekleme yapar.<br />
* Uzak sunuculara elle dosya ve dizin yedekleme yapar.<br />
* Uzak sunuculardan istenilen yerel alana yedekleri indirir.<br />
* Zip formatında sıkıştırılan web dizini veya web sitenin önemli klasörünü istenilen yerel alana zip çıkarır.<br />
* Yedeklenen veritabanıları geri yükler<br />
* Elle veritabanının tümü veya seçilecek tabloları yedekleme yapar.<br />
* Elle web site dizinlerini zip formatında sıkıştırır.<br />
* Eğer az php bilginiz varsa özel görevler için script ekleyebilirsiniz ve görevle çalıştırabilirsiniz.<br />
* Ve daha fazlası.<br />

Herhangi bir neden veya nedenlerden dolayı zarar gören web siteninizin yedekleme zamanına kısa bir sürede geri dönmenizi sağlar.<br />

## Nasıl kurulur<br />
Tüm dosyaları bunun için oluşturacağınız domain klasörünün içine kopyalayın<br />
cPanelden veri tabanı oluşturun<br />
`includes` klasörün içindeki `webyonetimi.sql` veri tabanı yedeğini oluşturduğunuz veri tabanına PhpMyAdmin kullanarak yükleyin<br />
`includes` klasörün içindeki `connect.php` dosyayı text editör ile açarak aşağıdaki alana eklediğiniz veri tabanı bilgilerini girin<br />
```php
    defined('DB_USER')      or define('DB_USER', 'root');
    defined('DB_PASSWORD')  or define('DB_PASSWORD', '');
    defined('DB_HOST')      or define('DB_HOST', 'localhost');
    defined('DB_NAME')      or define('DB_NAME', 'webyonetimi');
    defined('PORT')         or define('PORT', '3306');
    defined('CHARSET')      or define('CHARSET', 'utf8mb4');
```
Aşağıdaki alanların açıklamalarını okuyup kendinize göre değiştirin<br />
```php
    defined('BACKUPDIR')        or define('BACKUPDIR', '/home/user/DATABASEBACKUP');
    defined("DIZINDIR")         or define("DIZINDIR", "/home/user/");
    defined("ZIPDIR")           or define("ZIPDIR", "/home/user/WEBZIPLER/");
    defined("KOKYOLU")          or define("KOKYOLU", "/home/user/");
```
Ben robot değilim etkinleştirmek için domain adınıza ait keyleri oluşturmanız gerekiyor<br />
Buradan https://www.google.com/recaptcha/ domain adını ekleyip keyleri alın<br />
`login.php` içinde `data-sitekey="xxxxxxxxxxxxxxxxxxxxx"` alanına SİTE ANAHTARI girin<br />
`recaptcha.php` içinde `$secret = "xxxxxxxxxxxxxxxxxxxx"` alanına GİZLİ ANAHTARI girin<br />

Kendi sunucunuzda bir klasöre `/home/user/test_ftp_hesabi` gibi FTP hesabı oluşturun ve web yönetimi siteye giriş yaptığınızda Genel Ayarlar bölümündeki FTP bilgileri kaydetme alanına girip kaydedin<br />

Google Drive Servis Hesabı için aşağıdaki linki tıklayın `Servis Hesabı` oluşturun JSON dosyayı indirin<br />
`Servis Hesabı` servis hesabı olması zorunludur.<br />
https://console.cloud.google.com/apis/dashboard<br />
Indirdiğiniz JSON dosyayı `client_secrets.json` olarak yeniden adlandırın ve aşağıdaki konuma kopyalayın<br />
`/home/user/web_diziniz/plugins/google_drive/client_json/client_secrets.json`<br />

## Not:<br />
Veri tabanı bilgileri eklediğinizde veri tabanına kaydederken şifreliyor bu şifre için `hash.php` alanındaki şifreyi değiştirin<br />
`hash.php` içindeki `$this->key` alanındaki şifreyi değiştiriniz<br />
Buradan https://randomkeygen.com/ rasgle şifre oluşturabilirsiniz<br />
Bu anahtar ile şifrelenen veri tabanı bilgileri tekrar bu şifre ile çözülebilir<br />

`Veritabanı Ekle/Düzelt` alanına bu sitenin veri tabanı bilgilerini ekleyin ve eğer aynı sunucuda başka site veri tabanıları varsa onlarıda ekleyin<br />
`Görev Zamanlayıcı` alanından yeni görevler ekleyebilirsiniz `Xxx Web Site Veritabanı Yedekleme`, `Xxx Web Site Dizin Yedekleme` şu zamanda yedekle ve FTP ye ve veya Google la yedekle seçenekleri kullanabilirsiniz<br />
Eklediğiniz görevleri çalışma zamanı geldiğinde otomatikman çalışacağı gibi ellede istediğiniz zaman bu görevleri çalıştırabilirsiniz.<br />

## Önemli not:<br />
Görevlerin çalışması için web yönetim sitesine birileri ziyaret etmesi gerekiyor ki görevler yerine getirilsin<br />
Ancak, eğer ben tam zamanında görevin çalışmasını istiyorum diyorsanız "Hosting cPaneldeki" -> "Cron İşleri" alanında yeni bir cron oluşturup dakikada bir kez seçiniz<br />
Komut alanına `/usr/local/bin/php /home/user/alan_adiniz.com/gorev.php >/dev/null 2>&1` girip kaydedin. (düzenlemeyi unutmayın)<br />
Bu sayede planladığınız zamanlarda webyönetim siteniz tetiklenecek ve tam zamanında görevler yerine getirilecek<br /><br />

Görevleri "Cron İşleri" için web yönetim web sitenin dizini şifreleyerek web yönetim sitesininin güvenliğini artırmış olursunuz. (tabiki 100% güvenli garantisi hiçbir şeyde olmadığını unutmayın)<br />

Giriş için<br />
Kullanıcı Adı: `admin@gmail.com`<br />
Şifre: `123456`<br />

## Klasör yükleme ve İndirme<br />
FTP ve Google Drive Api sunuculara Tek dosyaları yedekleyebileceğiniz gibi İç içe geçmiş (alt-klasör dahil) dizini sorunsuz yedekleyebilirsiniz.<br />
