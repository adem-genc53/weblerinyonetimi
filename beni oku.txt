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

    defined('BACKUPDIR')        or define('BACKUPDIR', '../DATABASEBACKUP');
    defined("DIZINDIR")         or define("DIZINDIR", "../");
    defined("ZIPDIR")           or define("ZIPDIR", "../WEBZIPLER/");
    defined("KOKYOLU")          or define("KOKYOLU", "/home/user/");

Ben robot değilim etkinleştirmek için domain adınıza keyleri oluşturmanız gerekiyor<br />
Buradan https://www.google.com/recaptcha/ keyleri alın<br />
`login.php` içinde `data-sitekey="xxxxxxxxxxxxxxxxxxxxx"` alana SİTE ANAHTARI girin<br />
`recaptcha.php` içinde `$secret = "xxxxxxxxxxxxxxxxxxxx"` alana GİZLİ ANAHTARI girin<br />

Kendi sunucunuzda bir klasöre ftp hesabı oluşturun ve siteye login olduktan sonra Ayarlar bölümündeki ftp alana girip kaydedin<br />

Google Drive Servis Hesabı için aşağıdaki linki tıklayın `Servis Hesabı` oluşturun JSON dosyayı indirin<br />
https://console.cloud.google.com/apis/dashboard<br />
Indirdiğiniz JSON dosyayı `client_secrets.json` olarak yeniden adlandırın ve aşağıdaki konuma kopyalayın<br />
`plugins/google_drive/client_json/client_secrets.json`<br />

`Veritabanı Ekle/Düzelt` alanından bu sitenin veri tabanı bilgilerini ekleyin ve diğer varsa sitelerinizin de veri tabanı bilgilerini ekleyin tabı aynı sunucuda olacak<br />
`Görev Zamanlayıcı` alanından yeni görevler ekleyebilirsiniz xxxxxx veri tabanı şu zamanda yedekle ve FTP ye ve veya Google la yedekle seçenekleri kullanabilirsiniz<br />

## Önemli not:<br />
Görevlerin çalışması için siteyi birileri ziyaret etmesi gerekiyor ki görev çalışsın<br />
Örnek saat 10:00 da bir görev planladınız ama hiç kimse saat 10:00 da ziyaret etmedi ama 10:30 da ziyaret etti diyelim bu görev 10:00 yerine 10:30 da yerine getirecek<br />
Eğer ben tam zamanında görevin çalışmasını istiyorum diyorsanız "hosting cPaneldeki" "Cron İşleri" alanında yeni bir oluşturup ister dakikada bir ister saatte iki kez ister saatte bir kez nasıl tercih ederseniz<br />
Komut alanına `curl --silent https://alanadiniz.com/gorev.php` girip kaydedin planladığınız zamanlarda webyönetim siteniz tetiklenecek ve zamanında görevler yerine getirilecek<br />

## Not:<br />
Veri tabanı bilgileri eklediğiniz veri tabanına kaydederken şifreliyor bu şifre için şifre anahtarı değiştirebilirsiniz<br />
`hash.php` içindeki key alanındaki şifreyi değiştirebilirsiniz<br />
Buradan https://randomkeygen.com/ rasgle şifre oluşturabilirsiniz<br />
Bu anahtar ile şifrelenen veri tabanı bilgileri tekrar bu şifre ile çözülebilir<br />

Giriş için<br />
Kullanıcı Adı: `admin@gmail.com`<br />
Şifre: `123456`<br />

## Klasör yükleme ve İndirme<br />
İçinde alt dizinler ve alt dosyalar içeren klasörü Google Derive' a yükleyebilirsiniz ve indirebilirsiniz.<br />
Tek tek dosyalarıda yükleyebilir ve indirebilirsiniz.
