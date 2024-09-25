<?php 
// Bismillahirrahmanirrahim
//header('Connection: Keep-Alive');
//header('Keep-Alive: timeout=5, max=100');
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;

//ob_start();
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);

#########################################################################################################################
// Cron job tarafından çalıştırılmasını engellemek için PHP_SAPI kontrolü
if (php_sapi_name() === 'cli' && $genel_ayarlar['gorevi_calistir'] === 2) {
    // Eğer CLI (komut satırı) ortamında çalıştırılıyorsa, sayfayı çalıştırmayı durdur
    exit();
}
#########################################################################################################################
// Geçici dizini dinamik olarak al
$temp_dir = sys_get_temp_dir();

// Kilit dosyasının yolu
$lock_file = $temp_dir . DIRECTORY_SEPARATOR . 'gorev.lock';

// Eğer kilit dosyası varsa ve dosya halen var ise işlemi sonlandır
if (file_exists($lock_file)) {
    // Hata günlüğüne yazmak isterseniz aşağıdaki satırı yorumdan çıkarabilirsiniz
     //file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);
    
    // Kilit dosyası mevcut, başka bir işlem çalışıyor.
    exit();
}

// Kilit dosyasını oluştur ve içine bir şeyler yaz (örn. locked)
file_put_contents($lock_file, "locked");

// Hata durumunda kilidi temizlemek için bir kapanış fonksiyonu tanımla
register_shutdown_function(function() use ($lock_file) {
    if (file_exists($lock_file)) {
        unlink($lock_file);
    }
});
#########################################################################################################################
//echo '<pre>' . print_r($zip_google_dosya_adi_yolu, true) . '</pre>';
#########################################################################################################################
#########################################################################################################################
try {
#########################################################################################################################
    // BENİ HATIRLA TOKENİN SÜRESİ DOLANI TEMİZLE
    $stmt = $PDOdb->prepare(" UPDATE uyeler SET remember_me_token = NULL, token_expiry = NULL WHERE remember_me_token IS NOT NULL AND token_expiry < ? ");
    $stmt->execute([time()]);

#########################################################################################################################
    // FTP BAĞLANTI BİLGİLERİ
    $ftp_server     = $genel_ayarlar['sunucu'] ?? ''; //ftp domain name
    $ftp_username   = !empty($genel_ayarlar['username']) ? $hash->take($genel_ayarlar['username']) : ''; //ftp user name 
    $ftp_password   = !empty($genel_ayarlar['password']) ? $hash->take($genel_ayarlar['password']) : ''; //ftp passowrd
    $ftp_path       = $genel_ayarlar['path']; //ftp passowrd
#########################################################################################################################
// ÇALIŞTIRILACAK DOSYANIN URL OLUP OLMADIĞINI KONTROLUNU YAP
function isFullUrl($kaynak_url) {
    return filter_var($kaynak_url, FILTER_VALIDATE_URL) !== false;
}
#########################################################################################################################
  // file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);
$gorevden = true; // CRON ZAMANLAYICI DOSYA İÇİNDE ETKİNLEŞTİRMEK İÇİNDİR

require __DIR__ . '/cron_zamanlayici.php'; // sonraki çalışacak zamanı unix zaman damgası olarak verir
require_once __DIR__ . '/backup.php'; // seçilen veritabanını yedekler
require_once __DIR__ . '/zipyap.php'; // seçilen web dizini zip arşivi olarak oluşturur
require_once __DIR__ . '/gorevle_uzak_ftp_yedekle.php'; // veritabanı veya zipli web dizin FTP hesabına yükler ve korunacak sayının dışındaki eski tarihilileri siler
require_once __DIR__ . '/gorevle_uzak_google_yedekle.php'; // veritabanı veya zipli web dizin google dirive service hesabına yükler ve korunacak sayının dışındaki eski tarihilileri siler
#########################################################################################################################
// GÖREV ZAMANLAYICI SAYFASINDA OLUŞTURULAN GÖREVLERİ ELLE YÜRÜTÜLÜRKEN
if(isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1 && isset($_POST['gorevid']) && is_numeric($_POST['gorevid'])){
    $gorevler = $PDOdb->prepare("SELECT * FROM zamanlanmisgorev WHERE id=? LIMIT 1");
    $gorevler->execute([$_POST['gorevid']]);
}else {
    // zamanlama görevinde sonraki çalışma zamanı ile şimdi ki zamanı karşılaştırıp şimdiki zaman eşit veya geçiyor sa döngüyü çalıştır
    $gorevler = $PDOdb->prepare("SELECT * FROM zamanlanmisgorev WHERE aktif =? AND sonraki_calisma <= ? ORDER BY id ASC");
    $gorevler->execute(['Aktif', time()]);
}

$yedeklenecek_tablolar = [];
$yedeklendi_mi = false;

     // file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);

while ($row = $gorevler->fetch()) {

     // file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);

$yedeklenecek_tablolar = [];
$calistirma_sonuc_mesaji[] = array();
$yedeklendi_mi = false;

    $dosya_tarihi                   = date('Y-m-d-H-i-s'); // date('Y-m-d-H-i-s', $row['sonraki_calisma']);
    $starttime                      = microtime(true);
    $id                             = $row['id'];
    $gorev_adi                      = $row['gorev_adi'];
    $kaynak_url                     = $row['dosya_adi'];
    $sonraki_calisma                = $row['sonraki_calisma'];
    $haftanin_gunu                  = explode(",", $row['haftanin_gunu']);
    $gun                            = $row['gun'];
    $saat                           = $row['saat'];
    $dakika                         = $row['dakika'];
    $aktif                          = $row['aktif'];
    $gunluk_kayit                   = $row['gunluk_kayit'];
    $yedekleme_gorevi               = $row['yedekleme_gorevi'];
    $ftp_yedekle                    = $row['ftp_yedekle'];
    $google_yedekle                 = $row['google_yedekle'];
    $uzak_sunucu_ici_dizin_adi      = $row['uzak_sunucu_ici_dizin_adi'];
    $google_sunucu_korunacak_yedek  = $row['google_sunucu_korunacak_yedek'];
    $ftp_sunucu_korunacak_yedek     = $row['ftp_sunucu_korunacak_yedek'];
    $secilen_yedekleme_oneki        = $row['secilen_yedekleme_oneki'];
    $yerel_korunacak_yedek          = $row['yerel_korunacak_yedek'];
    $gz                             = $row['gz'];
    $dbbakim                        = $row['dbbakim'];
    $dblock                         = $row['dblock'];
    $combine                        = $row['combine'];
    $elle                           = $row['elle'];
    $veritabani_id                  = $row['secilen_yedekleme'];
    $secilen_yedekleme              = $row['secilen_yedekleme'];
    $secilen_alt_dizin              = $row['secilen_web_sitenin_alt_dizini'];
    $ozel_onek                      = $row['ozel_onek'];
    $grup                           = 1;
    $yedekleyen                     = 1;
#########################################################################################################################
if(!empty($yedekleme_gorevi) && $yedekleme_gorevi == '1' && $gz == '0' && $combine == '1'){ // veritabanı yedekleme
    $silinecek_dosya_tipi = "2"; // .sql uzantılı dosya
} elseif(!empty($yedekleme_gorevi) && $yedekleme_gorevi == '1' && $gz == '1' && $combine == '1'){ // veritabanı yedekleme
    $silinecek_dosya_tipi = "1"; // .gz uzantılı dosya
} elseif(!empty($yedekleme_gorevi) && $yedekleme_gorevi == '1' && $combine == '2'){ // veritabanı yedekleme
    $silinecek_dosya_tipi = "4"; // klasör dosyası
} elseif(!empty($yedekleme_gorevi) && $yedekleme_gorevi == '1' && $gz == '1' && $combine == '3' && $elle == '2'){ // veritabanı yedekleme
    $silinecek_dosya_tipi = "4"; // klasör dosyası
} elseif(!empty($yedekleme_gorevi) && $yedekleme_gorevi == '1' && $gz == '0' && $combine == '3' && $elle == '1'){ // veritabanı yedekleme
    $silinecek_dosya_tipi = "2"; // .sql uzantılı dosya
} elseif(!empty($yedekleme_gorevi) && $yedekleme_gorevi == '1' && $gz == '1' && $combine == '3' && $elle == '1'){ // veritabanı yedekleme
    $silinecek_dosya_tipi = "1"; // .gz uzantılı dosya
} elseif (!empty($yedekleme_gorevi) && $yedekleme_gorevi == '2'){ // dizin yedekleme
    $silinecek_dosya_tipi = "3";
} elseif (!empty($yedekleme_gorevi) && $yedekleme_gorevi == '3'){ // diğer özel dosyaları çalıştırma
    $silinecek_dosya_tipi = "";
}
/*
    $extension = [
        '1' => '.gz',
        '2' => '.sql',
        '3' => '.zip',
        '4' => ''
    ];
*/
#########################################################################################################################
#########################################################################################################################
#########################################################################################################################
########################################### GÖREVLERİ YÜRÜTME BAŞLANGICI ################################################
#########################################################################################################################
#########################################################################################################################
#########################################################################################################################
//
//
//
//
#########################################################################################################################
################################### GÖREV VERİTABANI YEDEKLEME GÖREVİ BAŞLAMA ###########################################
#########################################################################################################################
// YEDEKLEME GÖREVİ 1 İSE VERİTABANI YEDEKLEMEDİR. SEÇİLEN YEDEKLEME YEDEKLENECEK VERİTABANI ID İÇERDİĞİ İÇİN NUMARA OLMALIDIR
if(isset($row['yedekleme_gorevi']) && $row['yedekleme_gorevi'] == '1' && is_numeric($row['secilen_yedekleme'])){
    // ÖNCE YEDEKLENECEK VERİTABANI BİLGİLERİNİ ALALIM
    $default = $PDOdb->prepare("SELECT * FROM veritabanlari WHERE id=? LIMIT 1");
    $default->execute([$row['secilen_yedekleme']]);
    $varsayilan = $default->fetch(PDO::FETCH_ASSOC);
    $db_name = $varsayilan['db_name'];

    // YEDEKLENECEK VERİTABANINA BAĞLANTI KURALIM
    $secilen = "mysql:host=".$varsayilan['database_host'].";dbname=".$varsayilan['db_name'].";charset=".$varsayilan['charset'].";port=".$varsayilan['port']."";
    try {
    $PDOdbsecilen = new PDO($secilen, $hash->take($varsayilan['database_user']), $hash->take($varsayilan['database_password']), $options);
    $PDOdbsecilen->exec("set names ".CHARSET);
    $PDOdbsecilen->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
        die($e->getMessage());
    }
#########################################################################################################################
// OTOMATİK YEDEKLEME İLE ELLE SEÇİLEN VERİTABANINDAN GELEN VİRGÜLLE AYRILMIŞ TABLOLAR OLDUĞUNDA YEDEKLEME İŞLEMİ
if( isset($row['tablolar']) && !empty($row['tablolar']) && $row['combine'] == '3' ) {

    function tablonunSonDegisikligi($PDOdbsecilen, $db_name, $table, $sonraki_calisma) {
        try {
            // Tablonun motor tipini ve son güncelleme zamanını sorgulama
            $tablo_bilgisi = $PDOdbsecilen->query("SELECT ENGINE, UPDATE_TIME, CREATE_TIME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$db_name' AND TABLE_NAME = '$table'");
            $tablo_bilgileri = $tablo_bilgisi->fetch(PDO::FETCH_ASSOC);

            $engine = $tablo_bilgileri['ENGINE'];

            if ($engine == 'InnoDB') {
                // InnoDB ise doğrudan yedekleme yap
                return $table;
            } else {
                // Diğer motor tipleri için son güncelleme zamanını kontrol et
                if ($tablo_bilgileri['UPDATE_TIME'] !== null) {
                    $update_time = strtotime($tablo_bilgileri['UPDATE_TIME']);
                } else {
                    // Eğer UPDATE_TIME null ise, CREATE_TIME kullan (tablo hiç güncellenmemişse)
                    $update_time = strtotime($tablo_bilgileri['CREATE_TIME']);
                }

                if ($update_time > $sonraki_calisma) {
                    // Yedekleme işlemi yapılacak tabloyu döndür
                    return $table;
                } else {
                    // Güncelleme olmadığı için yedekleme yapılacak tablo yok
                    return null;
                }
            }
        } catch (PDOException $e) {
            // Hata oluştuğunda işlenebilir, şu an için yapılmamış
            //echo "Bağlantı hatası: " . $e->getMessage();
            return null;
        }
    }

    // EĞER TABLO GÜNCELLENDİ Mİ DENETLEME DEVRE DIŞI İSE (DEĞER 1 DIR) SEÇİLEN TABLOLARI YEDEKLE
    if($row['tablo_guncelmi_denetle'] == 1){

        $yedeklenecek_tablolar = explode(",", $row['tablolar']);

    }else{ // EĞER SON YEDEKLEME SONRASI GÜNCELLENEN TABLOLARI YEDEKLE İSE (DEĞER 0 DIR) FONKSİYON İLE GÜNCEL TABLOLARI VER

        // GÖREVLE VERİTABANINDAN GELEN VİRGÜLLE AYRILMIŞ TABLOLAR
        $tables = explode(",", $row['tablolar']);
        foreach ($tables as $table) {
            if (tablonunSonDegisikligi($PDOdbsecilen, $db_name, trim($table), $sonraki_calisma)) {
                $yedeklenecek_tablolar[] = trim($table);
            }
        }

    }


} // if( isset($row['tablolar']) && !empty($row['tablolar']) && $row['combine'] == '3' ) {

#########################################################################################################################

    // EĞER YUKARIDAN SONRAKİ ÇALIŞMA ZAMANINDAN SONRA GÜNCELLENEN TABLO HİÇ YOKSA GÜNLÜK KAYDI YAPMAYA GEREK YOK VE SONRAKİ ÇALIŞACAK ZAMANI GÜNCELLİYORUZ
    if( isset($yedeklenecek_tablolar) && count($yedeklenecek_tablolar) == 0 && $row['combine'] == '3' ){

        $calistirma_sonuc_mesaji[] = 
            'Tabloların Elle Seçili Olduğundan
            <br>Son <b>Çalışacağı Zaman</b>dan sonra hiçbir tabloda güncelleme olmadığı için yedeklemeye gerek yoktur.
            <br>Eğer güncelleme olmadan da yedekleme yapmak istiyorsanız tablo listesinin üstündeki kutuyu işaretletin<br /> Veya <b>Veritabanı Yedekle</b> sayfasından veya tabloları elle seçiminden çıkarın';
        $yedeklendi_mi = false;
    }else{
        // Elle seçilen tablo yoksa tam yedekleme var demektir
        $yedeklendi_mi = true;

          // file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);

        $veritabani_backup_yedekleme_sonucu = veritabaniYedekleme($PDOdbsecilen, $veritabani_id, $secilen_yedekleme_oneki, $combine, $elle, $grup, $dbbakim, $gz, $yedekleyen, $dblock, $db_name, $yedeklenecek_tablolar, $dosya_tarihi);

          // file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);

    } // else if(count($yedeklenecek_tablolar)==0){
#############################################################################################################################
// VERİTABANI YEDEKLEME YAPILMADI İSE $yedeklendi_mi = false; OLACAĞI İÇİN UZAK SUNUCULARA YÜKLEMEYİ YAPMAYACAKTIR
if(!empty($veritabani_backup_yedekleme_sonucu) && in_array('Veritabanı Başarıyla Yedeklendi',$veritabani_backup_yedekleme_sonucu) && $yedeklendi_mi){

    if(isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1){
        $calistirma_sonuc_mesaji[] = array("Görev Elle Yürütüldü");
    }
        $calistirma_sonuc_mesaji[] = $veritabani_backup_yedekleme_sonucu;

#############################################################################################################################
// EĞER FTP YEDEKLEME ETKİN İSE FTP SUNUCUSUNA YEDEKLE
    if(isset($row['ftp_yedekle']) && $row['ftp_yedekle'] == 1){

    if(!empty($veritabani_backup_yedekleme_sonucu['dosya_adi']) && strlen($veritabani_backup_yedekleme_sonucu['dosya_adi'])>10){

        $veritabani_ftp_dosya_adi_yolu = $veritabani_backup_yedekleme_sonucu['dosya_adi'];
        $veritabani_ftp_yedekleme_sonucu = uzakFTPsunucuyaYedekle($ftp_server, $ftp_username, $ftp_password, $ftp_path, $veritabani_ftp_dosya_adi_yolu, $yedekleme_gorevi, $uzak_sunucu_ici_dizin_adi, $ftp_sunucu_korunacak_yedek, $secilen_yedekleme_oneki);
        // UZAK FTP YEDEKLEME BAŞARILI OLURSA KENDİ DOSYA İÇİNDE ESKİ FTP YEDEKLERİ SİLECEK
        if(!empty($veritabani_ftp_yedekleme_sonucu) && in_array('FTP Sunucusuna Başarıyla Yüklendi',$veritabani_ftp_yedekleme_sonucu)){

            $calistirma_sonuc_mesaji[] = $veritabani_ftp_yedekleme_sonucu;
            unset($veritabani_ftp_dosya_adi_yolu);
        }else{
            $calistirma_sonuc_mesaji[] = $veritabani_ftp_yedekleme_sonucu;
        }
    } // if(!empty($veritabani_backup_yedekleme_sonucu['dosya_adi']) && strlen($veritabani_backup_yedekleme_sonucu['dosya_adi'])>10){
    } // if(isset($row['ftp_yedekle']) && $row['ftp_yedekle'] == 1){
#############################################################################################################################
// EĞER GOOGLE YEDEKLEME ETKİN İSE GOOGLE DRIVE SUNUCUSUNA YEDEKLE
    if(isset($row['google_yedekle']) && $row['google_yedekle'] == 1){

    if(!empty($veritabani_backup_yedekleme_sonucu['dosya_adi']) && strlen($veritabani_backup_yedekleme_sonucu['dosya_adi'])>10){

        $veritabani_google_dosya_adi_yolu = $veritabani_backup_yedekleme_sonucu['dosya_adi'];

        $veritabani_google_yedekleme_sonucu = uzakGoogleSunucuyaYedekle( $veritabani_google_dosya_adi_yolu, $yedekleme_gorevi, $silinecek_dosya_tipi, $uzak_sunucu_ici_dizin_adi, $google_sunucu_korunacak_yedek, $secilen_yedekleme_oneki );
        // UZAK GOOGLE YEDEKLEME BAŞARILI OLURSA KENDİ DOSYA İÇİNDE ESKİ GOOGLE YEDEKLERİ SİLECEK
        if(!empty($veritabani_google_yedekleme_sonucu) && in_array('Google Drive Sunucusuna Başarıyla Yüklendi',$veritabani_google_yedekleme_sonucu)){
            $calistirma_sonuc_mesaji[] = $veritabani_google_yedekleme_sonucu;
            unset($veritabani_google_dosya_adi_yolu);
        }else{
            $calistirma_sonuc_mesaji[] = $veritabani_google_yedekleme_sonucu;
        }
    } // if(!empty($veritabani_backup_yedekleme_sonucu['dosya_adi']) && strlen($veritabani_backup_yedekleme_sonucu['dosya_adi'])>10){
    } // if(isset($row['google_yedekle']) && $row['google_yedekle'] == 1){
#############################################################################################################################

} // if(isset($veritabani_backup_yedekleme_sonucu[]) && $veritabani_backup_yedekleme_sonucu[] == 'Veritabanı Başarıyla Yedeklendi' && $yedeklendi_mi){

}else // if(isset($row['yedekleme_gorevi']) && $row['yedekleme_gorevi'] == '1' && is_numeric($row['secilen_yedekleme'])){
#########################################################################################################################
################################### GÖREV VERİTABANI YEDEKLEME GÖREVİ SONU ##############################################
#########################################################################################################################
//
//
//
//
//
//
#########################################################################################################################
########################## GÖREV WEB DİZİNLERİ ZİP İLE SIKIŞTIRIP YEDEKLEME GÖREVİ BAŞLAMA ##############################
#########################################################################################################################
// YEDEKLEME GÖREVİ 2 İSE WEB DİZİNLERİ YEDEKLEMEDİR. SEÇİLEN YEDEKLEME YEDEKLENECEK WEB DİZİN ADI İÇERDİĞİ İÇİN METİN OLMALIDIR
if($row['yedekleme_gorevi'] == '2' && is_string($row['secilen_yedekleme']) && !is_null($row['secilen_yedekleme']) ){
    if($secilen_alt_dizin){
        $secilen_yedekleme = $secilen_alt_dizin;
    }else{
        $secilen_yedekleme = $secilen_yedekleme;
    }
    $source = DIZINDIR . $secilen_yedekleme;
    $destination = ZIPDIR . $secilen_yedekleme_oneki . "-" . $dosya_tarihi . '.zip';
    $comment = $secilen_yedekleme;

    //file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);

        if(isset($genel_ayarlar['zip_tercihi']) && $genel_ayarlar['zip_tercihi'] == 1){
            $zipyap_sonucu = zipDataUsingZipArchive($source, $destination, $comment);
        } else if(isset($genel_ayarlar['zip_tercihi']) && $genel_ayarlar['zip_tercihi'] == 2){
            $zipyap_sonucu = zipDataUsingSystem($source, $destination, $comment);
        }else{
            $zipyap_sonucu = "";
        }

    //file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);

    if(!empty($zipyap_sonucu) && in_array('Zip Arşivi Başarıyla Oluşturuldu',$zipyap_sonucu)){
    $yedeklendi_mi = true;

    if(isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1){
        $calistirma_sonuc_mesaji[] = array("Görev Elle Yürütüldü");
    }
        $calistirma_sonuc_mesaji[] = $zipyap_sonucu;
#############################################################################################################################
// EĞER FTP YEDEKLEME ETKİN İSE FTP SUNUCUSUNA YEDEKLE
    if(isset($row['ftp_yedekle']) && $row['ftp_yedekle'] == 1){

    if(!empty($zipyap_sonucu['dosya_adi']) && strlen($zipyap_sonucu['dosya_adi'])>10){

        $zip_ftp_dosya_adi_yolu = $zipyap_sonucu['dosya_adi'];
        $dizin_zip_ftp_yedekleme_sonucu = uzakFTPsunucuyaYedekle($genel_ayarlar, $ftp_server, $ftp_username, $ftp_password, $ftp_path, $zip_ftp_dosya_adi_yolu, $yedekleme_gorevi, $uzak_sunucu_ici_dizin_adi, $ftp_sunucu_korunacak_yedek, $secilen_yedekleme_oneki);
        // UZAK FTP YEDEKLEME BAŞARILI OLURSA KENDİ DOSYA İÇİNDE ESKİ FTP YEDEKLERİ SİLECEK
        if(!empty($dizin_zip_ftp_yedekleme_sonucu) && in_array('FTP Sunucusuna Başarıyla Yüklendi',$dizin_zip_ftp_yedekleme_sonucu)){

            $calistirma_sonuc_mesaji[] = $dizin_zip_ftp_yedekleme_sonucu;
            unset($zip_ftp_dosya_adi_yolu);
        }else{
            $calistirma_sonuc_mesaji[] = $dizin_zip_ftp_yedekleme_sonucu;
        }
    } // if(!empty($zipyap_sonucu['dosya_adi']) && strlen($zipyap_sonucu['dosya_adi'])>10){
    } // if(isset($row['ftp_yedekle']) && $row['ftp_yedekle'] == 1){
#############################################################################################################################
// EĞER GOOGLE YEDEKLEME ETKİN İSE GOOGLE DRIVE SUNUCUSUNA YEDEKLE
    
    if(isset($row['google_yedekle']) && $row['google_yedekle'] == 1){

    if(!empty($zipyap_sonucu['dosya_adi']) && strlen($zipyap_sonucu['dosya_adi'])>10){

        $zip_google_dosya_adi_yolu = $zipyap_sonucu['dosya_adi'];
        $dizin_zip_google_yedekleme_sonucu = uzakGoogleSunucuyaYedekle( $zip_google_dosya_adi_yolu, $yedekleme_gorevi, $silinecek_dosya_tipi, $uzak_sunucu_ici_dizin_adi, $google_sunucu_korunacak_yedek, $secilen_yedekleme_oneki );

        // UZAK GOOGLE YEDEKLEME BAŞARILI OLURSA KENDİ DOSYA İÇİNDE ESKİ GOOGLE YEDEKLERİ SİLECEK
        if(!empty($dizin_zip_google_yedekleme_sonucu) && in_array('Google Drive Sunucusuna Başarıyla Yüklendi',$dizin_zip_google_yedekleme_sonucu)){

            $calistirma_sonuc_mesaji[] = $dizin_zip_google_yedekleme_sonucu;
            unset($zip_google_dosya_adi_yolu);

        }else{
            $calistirma_sonuc_mesaji[] = $dizin_zip_google_yedekleme_sonucu;
        }
    } // if(!empty($zipyap_sonucu['dosya_adi']) && strlen($zipyap_sonucu['dosya_adi'])>10){
    } // if(isset($row['google_yedekle']) && $row['google_yedekle'] == 1){
#############################################################################################################################

    } // if(isset($zipyap_sonucu[]) && $zipyap_sonucu[] == 'Zip Arşivi Başarıyla Oluşturuldu'){

}else
#########################################################################################################################
############################ GÖREV WEB DİZİNLERİ SIKIŞTIRIP YEDEKLEME GÖREVİ SONU #######################################
#########################################################################################################################
//
//
//
//
//
//
#########################################################################################################################
################################# GÖREV DİĞER SCRIPTLER İÇİN GÖREVİ BAŞLAMA #############################################
#########################################################################################################################
// YEDEKLEME GÖREVİ 3 İSE ÖZEL HAZIRLANAN SCRIPTLERİN ÇALIŞTIRILMASI İÇİNDİR
if($row['yedekleme_gorevi'] == '3' && (empty($row['secilen_yedekleme']) || is_null($row['secilen_yedekleme'])) ){

    if(isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1){
        $calistirma_sonuc_mesaji[] = array("Görev Elle Yürütüldü");
    }

// ÇALIŞTIRILACAK DOSYANIN URL OLUP OLMADIĞINI KONTROL EDİYORUZ
if(isFullUrl($kaynak_url)){
/**
 * Görev zamanlayıcı alanından uzak URL girerken GET parametler eklenebilir.
 * Eğer GET parametre yerine POST parametre kullanmak istiyorsanız 
 * aşağıdaki POST verileri diziyi aktif edin ve örnekteki gibi key ve value değerleri girin.
 * "'method' => 'POST'," bu alanı POST olarak değiştirin
 * "'content' => http_build_query($data)" alanındaki başındaki // slachları kaldırın
 * Artık uzak tam URL ye POST verileri gönderebilirsiniz
 */

/*
    // POST verileri
    $data = [
        'key1' => 'value1',
        'key2' => 'value2'
    ];
*/

    // POST isteği için stream ayarları
    $options = [
        'http' => [
            'method' => 'GET', // GET veya POST
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
            //'content' => http_build_query($data) // POST verileri
        ]
    ];

    $context = stream_context_create($options);

    // URL'ye POST isteği gönder ve yanıtı al
    $response = @file_get_contents($kaynak_url, false, $context);

    if ($response === false) {
        $calistirma_sonuc_mesaji[] = array("<b>Bağlantı başarısız:</b> ". $kaynak_url);
        $error = error_get_last();
        $calistirma_sonuc_mesaji[] = array("<b>Hata mesajı:</b> " . $error['message']);
    } else {
        // POST isteği başarılı, sunucudan gelen yanıtı al ve kontrol et
        //echo "POST isteği başarılı. Sunucudan gelen yanıt:<br>";
        //echo htmlspecialchars($response);

        // Sunucudan gelen yanıtı bir DOM belgesine yükle
        $dom = new DOMDocument();
        @$dom->loadHTML($response);

        // Belirli bir ID'ye sahip elementi bul
        $element = $dom->getElementById('tamurl');

        if ($element) {
            // Element bulundu, içeriğini al ve görüntüle
            $content = $element->textContent;
            //echo "ID 'tamurl' içeriği: " . $content;
            $yedeklendi_mi = true;
            $calistirma_sonuc_mesaji[] = array($content);
        } else {
            // Element bulunamadı, başarılı bir mesaj görüntüle
            //echo "ID 'tamurl' bulunamadı. Başarılı bir şekilde işlem yapıldı.";
            $yedeklendi_mi = true;
            $calistirma_sonuc_mesaji[] = array("Başarıyla Tam URL Çalıştırıldı.");
        }
    }

}else{

require __DIR__ . '/' . $kaynak_url;

    $ozel_dosya_calisma_sonucu = array();

    // Dosya adından namespace kısmını belirle
    $basename = basename($kaynak_url, '.php');
    $parts = explode('_', $basename);
    $namespace = strtoupper($parts[0]);

    // Fonksiyon adını belirle
    $function_name = "\\$namespace\\ozelCalistirilacakDosya";
    
    // Fonksiyonun varlığını kontrol et
    if (function_exists($function_name)) {
        // Fonksiyonu çağır ve çıktıyı al
        $ozel_dosya_calisma_sonucu = call_user_func($function_name, $PDOdb);

    } else {
        if(isset($_POST['elle_yurutme'])){
            echo "<b>$function_name</b> fonksiyonu bulunamadı.<br /><br />Özel dosyanızın<br />namespace adı <b>$namespace</b><br />fonksiyon adı <b>function ozelCalistirilacakDosya(\$PDOdb){}</b><br /> olduğundan emin olunuz ve örnek <b>test_gorev.php</b> dosyaya bakınız.";
            exit;
        }
    }

    if(!empty($ozel_dosya_calisma_sonucu) && in_array('Özel Dosya Başarıyla Çalıştırıldı',$ozel_dosya_calisma_sonucu)){
        $yedeklendi_mi = true;
        $ozel_dosya_calisma_sonucu = array_unique($ozel_dosya_calisma_sonucu);
        // "Özel Dosya Başarıyla Çalıştırıldı" metni çıktıların en üstünde göstermek için önce diziden çıkarıyoruz sonra dizinin ilk sırasına ekliyoruz
        // Hedef değer
        $hedef = "Özel Dosya Başarıyla Çalıştırıldı";
        // Hedef değeri bul ve diziden çıkar
        if(($anahtar = array_search($hedef, $ozel_dosya_calisma_sonucu)) !== false) {
            unset($ozel_dosya_calisma_sonucu[$anahtar]);
        }
        // Hedef değeri dizinin başına ekle
        array_unshift($ozel_dosya_calisma_sonucu, $hedef);
        $calistirma_sonuc_mesaji[] = $ozel_dosya_calisma_sonucu;
    }else{
        $calistirma_sonuc_mesaji[] = $ozel_dosya_calisma_sonucu;
    }

}
}
#########################################################################################################################
################################### GÖREV DİĞER SCRIPTLER İÇİN GÖREVİ SONU ##############################################
#########################################################################################################################
//
//
//
//
//
//
#########################################################################################################################
#########################################################################################################################
#########################################################################################################################
########################################### GÖREVLERİ YÜRÜTME SONU ######################################################
#########################################################################################################################
#########################################################################################################################
#########################################################################################################################
//
//
//
//
//
//
#########################################################################################################################
############################## GÖREV SONRASI VERİTABANI GÜNCELLEME BAŞLANGICI ###########################################
#########################################################################################################################
//Başarılı görev bir sonraki zamana güncelle
if( !isset($_POST['elle_yurutme']) ){

    // Şu anki tarihi ve saat bilgisini al
    $bugun = new DateTime('now', new DateTimeZone($genel_ayarlar['zaman_dilimi']));

    // Unix zaman damgasını depolamak için varsayılan tarih nesnesi oluştur
    $tarih = new DateTime('now', new DateTimeZone($genel_ayarlar['zaman_dilimi']));

    // HAFTANIN GÜN(LERİ) SEÇİLİ İSE HAFTANIN GÜN(LERİ) İŞLEMLERİNE BAŞLA
    if (!in_array("-1", $haftanin_gunu)){

        $tarih = haftaKontrolu($bugun, $tarih, $haftanin_gunu, $saat, $dakika);

    }else{ // HAFTANIN GÜNÜ -1 * YILDIZ SEÇİLİ İSE GÜN İŞLEMLERİNE BAŞLA

        $tarih = gunKontrolu($bugun, $tarih, $gun, $saat, $dakika);

    }

    // Unix zaman damgasını (timestamp) UTC olarak alma
    $sonraki_calisma = $tarih->setTimezone(new DateTimeZone('UTC'))->format('U');

    $sonuc = $PDOdb->prepare("UPDATE zamanlanmisgorev SET sonraki_calisma =? WHERE id =? ");
    $sonuc->bindParam(1, $sonraki_calisma, PDO::PARAM_INT);
    $sonuc->bindParam(2, $row['id'], PDO::PARAM_INT);
    $sonuc->execute();
    if($sonuc->rowCount() > 0){
            //echo "Yedek Sonraki çalışma zamanı başarıyla güncellendi<br />";
        $yedekleme_basarili = true;
    }else{
            //echo "Bir hatadan dolayı sonraki çalışma zamanı güncellenemedi<br />";
        $yedekleme_basarili = false;
    }
}
#########################################################################################################################
// GÖREV ÇALIŞTIRMA SONRASI İŞLEM MESAJLARI YENİDEN DÜZENLEME
// Dizi düzleştirme ve 'dosya_adi' anahtarını çıkarma fonksiyonu
if (!function_exists('flatten_and_filter_array')) {
function flatten_and_filter_array($array) {
    $result = array();
    foreach ($array as $item) {
        if (is_array($item)) {
            foreach ($item as $key => $value) {
                if ($key !== 'dosya_adi') {
                    if (is_array($value)) {
                        $result = array_merge($result, flatten_and_filter_array($value));
                    } else {
                        $result[] = $value;
                    }
                }
            }
        } else {
            $result[] = $item;
        }
    }
    return $result;
}
}

// Diziyi düzleştir ve 'dosya_adi' anahtarını çıkar
$calistirma_sonuc_mesaji = flatten_and_filter_array($calistirma_sonuc_mesaji);

// Boş değerleri çıkarın
$calistirma_sonuc_mesaji = array_filter($calistirma_sonuc_mesaji, function($value) {
    return !empty($value);
});

// Tekrar eden değerleri kaldırın
$calistirma_sonuc_mesaji = array_unique($calistirma_sonuc_mesaji);

//echo '<pre>' . print_r($calistirma_sonuc_mesaji, true) . '</pre>';

$calistirmasonucmesaji = implode("<br>", $calistirma_sonuc_mesaji);
#########################################################################################################################
// GÜNLÜK KAYIT AKTİF İSE VE $yedeklendi_mi TRUE İSE GÜNLÜĞE YAZ
if( $row['gunluk_kayit'] == 'Aktif' && $yedeklendi_mi ){

    $calisma_zamani = time();
    $endtime = microtime(true);
    $duration = $endtime - $starttime;
    $hours = floor($duration / 60 / 60);
    $minutes = floor(($duration / 60) - ($hours * 60));
    $seconds = floor($duration - ($hours * 60 * 60) - ($minutes * 60));
    $milliseconds = ($duration - floor($duration)) * 1000;
    $calisma_suresi = sprintf('%02d:%02d:%02d:%05.0f', $hours,$minutes,$seconds,$milliseconds);

    $sonuc_yaz = $PDOdb->prepare("INSERT INTO zamanlanmisgorev_gunluk (calistirma_ciktisi, gorev_adi, calistirilan_dosya, calisma_zamani, calisma_suresi) values (:calistirma_ciktisi, :gorev_adi, :calistirilan_dosya, :calisma_zamani, :calisma_suresi)");
    $sonuc_yaz->bindValue(':calistirma_ciktisi', $calistirmasonucmesaji);
    $sonuc_yaz->bindParam(':gorev_adi', $gorev_adi);
    $sonuc_yaz->bindParam(':calistirilan_dosya', $kaynak_url);
    $sonuc_yaz->bindParam(':calisma_zamani', $calisma_zamani);
    $sonuc_yaz->bindParam(':calisma_suresi', $calisma_suresi);
    $sonuc_yaz->execute();

    if(!empty($PDOdb->lastInsertId())){
        if( !isset($_POST['elle_yurutme']) ){
            unset($calistirma_sonuc_mesaji, $calistirma_sonuc_mesaji_filter, $calistirmasonucmesaji, $calisma_suresi, $calisma_zamani);
        }
        //echo "Yedek Başarılı sonuç günlüğe yazıldı<br />";
    }else{
        //echo "Bir hatadan dolayı başarılı sonuç günlüğe yazılamadı<br />";
    }

} // if($row['gunluk_kayit'] == 'Aktif'){
#########################################################################################################################
################################# GÖREV SONRASI VERİTABANI GÜNCELLEME SONU ##############################################
#########################################################################################################################

###################################################################################################
// YERELDEN SİLİNECEK KLASÖR İSE KLASÖR SİLME FONKSİYONU
###################################################################################################
if($row['yerel_korunacak_yedek'] != '-1'){
    if (!function_exists('delete_directory')){
        // Klasörü silecek fonksiyon
        function delete_directory($dir){
            if(is_dir($dir)){
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach($files as $file){
                    if ($file->isDir()){
                        rmdir($file->getRealPath());
                    }else{
                        unlink($file->getRealPath());
                    }
                }
                // Seçilen dizinide silmek içindir. Yukarısı içeriği siler
                rmdir($dir);
            } // if(is_dir($dir)){
        }
    }
###################################################################################################
// YERELDEN DOSYALARI SİLME BÖLÜMÜ
###################################################################################################
    // Veritabanı yedekleme görevi
    if($row['yedekleme_gorevi'] == 1){
        $yol = BACKUPDIR."/";
    // Web dizinlerin zipleme görevi
    }elseif($row['yedekleme_gorevi'] == 2){
        $yol = ZIPDIR;
    // Kur güncelleme
    }elseif($row['yedekleme_gorevi'] == 3){
        $yol = "";
    }

        // Dizinde "secilen_yedekleme_oneki" ön ekine sahip dosyaları arayalım
if(!empty($yol)){
        // Burada sadece dizin arıyoruz
        $dizinler = array_filter(scandir($yol), function($dizin) {
            GLOBAL $row,$yol;
            //return strpos($dizin, $row['secilen_yedekleme_oneki']) === 0 && is_dir($yol.$dizin); // Dizinin "secilen_yedekleme_oneki" ile başlayıp başlamadığını kontrol edelim
            $secilen_yedekleme_oneki = $row['secilen_yedekleme_oneki'];
            if (is_string($secilen_yedekleme_oneki)) {
                return strpos($dizin, $secilen_yedekleme_oneki) === 0 && is_dir($yol.$dizin);
            } else {
                // İkinci parametre bir dize değilse, hata işleme yöntemi burada uygulanabilir.
                return [];
            }
        });
        // Burada dizin olmayan dosyaları arıyoruz
        $dosyalar = array_filter(scandir($yol), function($dosya) {
            GLOBAL $row,$yol;
            //return strpos($dosya, $row['secilen_yedekleme_oneki']) === 0 && is_file($yol.$dosya); // Dosyanın "secilen_yedekleme_oneki" ile başlayıp başlamadığını kontrol edelim
            $secilen_yedekleme_oneki = $row['secilen_yedekleme_oneki'];
            if (is_string($secilen_yedekleme_oneki)) {
                return strpos($dosya, $secilen_yedekleme_oneki) === 0 && is_file($yol.$dosya);
            } else {
                // İkinci parametre bir dize değilse, hata işleme yöntemi burada uygulanabilir.
                return [];
            }
        });

        // Dizinlerin son değiştirilme veya oluşturma zamanlarına göre sıralayalım
        usort($dizinler, function($a, $b) use ($yol) {
            return filemtime($yol . $b) - filemtime($yol . $a);
        });
        // Dosyaların son değiştirilme veya oluşturma zamanlarına göre sıralayalım
        usort($dosyalar, function($c, $d) use ($yol) {
            return filemtime($yol . $d) - filemtime($yol . $c);
        });

        //echo "<pre>" . print_r($dizinler, true) . "</pre>";
        //echo "<pre>" . print_r($dosyalar, true) . "</pre>";
    if (!function_exists('validateDate')){
        function validateDate($date, $format = 'Y-m-d-H-i-s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }
    }

        // En yeni iki dosyayı saklayalım ve gerisini silelim
    if(count($dizinler)>0){
        while (count($dizinler) > $row['yerel_korunacak_yedek']) {
            $silinendizin = array_pop($dizinler);
                //echo "<b>Bu dizin:</b> ".$yol.$silinendizin."<br />";
                $dizin_tarihi = substr($silinendizin, -19);
                if(validateDate($dizin_tarihi)){
                    delete_directory($yol.$silinendizin);
                }
        }
    }
    if(count($dosyalar)>0){
        while (count($dosyalar) > $row['yerel_korunacak_yedek']) {
                $silinendosya = array_pop($dosyalar);
                //echo "<b>Bu dosya:</b> ".$yol.$silinendosya."<br />";
                $dosya_tarihi = substr($silinendosya, strpos($silinendosya, $row['secilen_yedekleme_oneki']."-") + strlen($row['secilen_yedekleme_oneki']."-"), 19);
                if(validateDate($dosya_tarihi)){
                    unlink($yol.$silinendosya);
                }
        }
    }
}
    } // if($row['yerel_korunacak_yedek'] != '-1'){
###################################################################################################
###################################################################################################

if( isset($_POST['elle_yurutme']) ){
    echo $calistirmasonucmesaji;
}
unset($id,
$yerelden_secilen,
$gorev_adi,
$kaynak_url,
$sonraki_calisma,
$haftanin_gunu,
$gun,
$saat,
$dakika,
$aktif,
$gunluk_kayit,
$yedekleme_gorevi,
$ftp_yedekle,
$google_yedekle,
$uzak_sunucu_ici_dizin_adi,
$google_sunucu_korunacak_yedek,
$ftp_sunucu_korunacak_yedek,
$secilen_yedekleme_oneki,
$yerel_korunacak_yedek,
$gz,
$dbbakim,
$dblock,
$combine,
$elle,
$veritabani_id,
$secilen_yedekleme,
$ozel_onek,
$grup,
$yedekleyen,
$sonraki_calisma,
$db_name,
$tablolar,
$update_time,
$update_time_unix,
$veritabani_backup_yedekleme_sonucu,
$veritabani_google_sil_sonucu,
$calisma_zamani,
$calisma_suresi,
$dosya_tarihi,
$zipyap_sonucu,
$dizin_zip_ftp_yedekleme_sonucu,
$dizin_zip_google_yedekleme_sonucu,
$calistirmasonucmesaji,
$PDOdbsecilen,
$veritabani_ftp_yedekleme_sonucu,
$veritabani_google_yedekleme_sonucu,
$ozel_dosya_calisma_sonucu);
$yedeklenecek_tablolar = array();
$yedeklendi_mi = false;
} // while ($row = $gorevler->fetch()) {
unset($id,
$gorev_adi,
$kaynak_url,
$sonraki_calisma,
$haftanin_gunu,
$bugun,
$tarih,
$gun,
$saat,
$dakika,
$aktif,
$gunluk_kayit,
$yedekleme_gorevi,
$ftp_yedekle,
$google_yedekle,
$uzak_sunucu_ici_dizin_adi,
$google_sunucu_korunacak_yedek,
$ftp_sunucu_korunacak_yedek,
$secilen_yedekleme_oneki,
$yerel_korunacak_yedek,
$gz,
$dbbakim,
$dblock,
$combine,
$elle,
$veritabani_id,
$secilen_yedekleme,
$ozel_onek,
$grup,
$yedekleyen,
$sonraki_calisma,
$db_name,
$tablolar,
$update_time,
$update_time_unix,
$veritabani_backup_yedekleme_sonucu,
$veritabani_google_sil_sonucu,
$calisma_zamani,
$calisma_suresi,
$dosya_tarihi,
$zipyap_sonucu,
$dizin_zip_ftp_yedekleme_sonucu,
$dizin_zip_google_yedekleme_sonucu,
$calistirmasonucmesaji,
$calistirma_sonuc_mesaji,
$PDOdbsecilen,
$yedeklenecek_tablolar,
$yedeklendi_mi,
$veritabani_ftp_yedekleme_sonucu,
$veritabani_google_yedekleme_sonucu,
$ozel_dosya_calisma_sonucu);

// file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);

    // İşlem tamamlandığında kilit dosyasını sil
    if (file_exists($lock_file)) {
        unlink($lock_file);
    }
} catch (Exception $e) {
    // Hata yönetimi burada yapılabilir
    file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($e->getMessage(), true) . '</pre>' . "\n", FILE_APPEND);

    // Hata oluştuğunda da kilit dosyasını sil
    if (file_exists($lock_file)) {
        unlink($lock_file);
    }

    // İsterseniz, kullanıcıya hata mesajı gösterin veya yönlendirin
    // echo "Bir hata oluştu: " . $e->getMessage();
}
//ob_flush();
//flush();
?>