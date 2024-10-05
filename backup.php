<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;
    //echo '<pre>' . print_r($_POST, true) . '</pre>';
    //exit;

if (!function_exists('veritabaniYedekleme')) {
    function veritabaniYedekleme($islemi_yapan, $PDOdbsecilen, $veritabani_id, $secilen_yedekleme_oneki, $combine, $elle, $grup, $dbbakim, $gz, $yedekleyen, $dblock, $db_name, $yedeklenecek_tablolar, $dosya_tarihi) {
        // ÇIKTI MESAJI BAŞLAT
        $sonuc_cikti_mesaji = [];

        // SUNUCU BİLGİLERİNİ VE AYARLARI BELİRLEME
    if (!function_exists('getInitialSettings')) {
        function getInitialSettings($PDOdbsecilen) {
            $settings = [];
            GLOBAL $genel_ayarlar;

            // DateTime ve DateTimeZone kullanarak saat dilimi farkını hesaplayın
            $dateTime = new DateTime('now', new DateTimeZone($genel_ayarlar['zaman_dilimi']));
            $offset = $dateTime->getOffset() / 3600; // Saat cinsinden farkı al

            // Farkı + veya - işareti ile formatlayın
            $formatted_offset = sprintf('%+03d:00', $offset);

            $settings['sql_mode'] = $PDOdbsecilen->query("SELECT @@sql_mode")->fetchColumn();
            $settings['time_zone'] = $formatted_offset; //$PDOdb->query("SELECT @@time_zone")->fetchColumn();

            // Eski karakter seti ayarlarını alın
            $settings['character_set_client'] = $PDOdbsecilen->query("SELECT @@character_set_client")->fetchColumn();
            $settings['character_set_results'] = $PDOdbsecilen->query("SELECT @@character_set_results")->fetchColumn();
            $settings['collation_connection'] = $PDOdbsecilen->query("SELECT @@collation_connection")->fetchColumn();

            // Karakter setini al
            $mysqlcharacter = $PDOdbsecilen->query("SHOW VARIABLES LIKE 'character_set_connection'");
            $characterSetResult = $mysqlcharacter->fetch(PDO::FETCH_ASSOC);
            $settings['karakter_seti'] = $characterSetResult['Value'] ?? null;

            // VERİTABANI ADI
            $settings['database_name'] = $PDOdbsecilen->query("SELECT DATABASE()")->fetchColumn();

            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '127.0.0.1:3306';
            $settings['http_host'] = $host;

            $settings['mysql_sunucu_surumu'] = $PDOdbsecilen->query('SELECT VERSION()')->fetchColumn();
            $settings['olusturma_zamani'] = date('Y-m-d H:i:s');

            // Tabloların listesi
            $tables = $PDOdbsecilen->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $settings['tables'] = $tables;

            // Her tablo için kayıt satır sayısı
            foreach ($tables as $table) {
                $count = $PDOdbsecilen->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                $settings['table_counts'][$table] = $count;
            }

            return $settings;
        }
    }
        // YEDEK DOSYANIN BİTİŞ AYARLARINI BELİRLEME
    if (!function_exists('finalSettings')) {
        function finalSettings($PDOdbsecilen){
            // buradan kanak şimdilik gönderilmiyor
            return null;
        }
    }

        // DİZİN OLUŞTURMA FONKSİYONU
    if (!function_exists('createDirectoryIfNotExists')) {
        function createDirectoryIfNotExists($directory) {
            if (!file_exists($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    die('Failed to create folders...');
                }
            }
        }
    }

        // .htaccess DOSYAYI OLUŞTURMA FONKSİYONU
    if (!function_exists('createHtaccessFile')) {
        function createHtaccessFile($directory) {
            $content = "deny from all";
            file_put_contents($directory . '/.htaccess', $content);
        }
    }

        // YEDEKLENECEK DOSYANIN ADINI BELİRLEME FONKSİYONU
    if (!function_exists('getBackupFilenamePrefix')) {
        function getBackupFilenamePrefix($prefix, $date) {
            return isset($prefix) && !empty($prefix) ? $prefix . "-" . $date : $date;
        }
    }

        // TABLOLARI BELİRLEME VE TABLOLARI DİZİ İÇİNE ALMA FONKSİYONU
    if (!function_exists('determineTablesToBackup')) {
        function determineTablesToBackup($PDOdbsecilen, $combine, $yedekleyen, $selectedTables) {
            if (empty($selectedTables) && ($combine == '1' || $combine == '2')) {
                $backupTables = $PDOdbsecilen->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN); // TÜM TABLOLAR
            } elseif ($combine == '3' && $yedekleyen == '2') {
                $backupTables = $selectedTables; // ELLE YEDEKLEMEDE ELLE SEÇİLEN TABLOLAR
            } elseif ($combine == '3' && $yedekleyen == '1') {
                $backupTables = $selectedTables; // GÖREVLE VERİTABANINDAN GELEN VİRGÜLLE AYRILMIŞ DİZİ OLUŞTURULMUŞ TABLOLAR
            }
            //file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);
            return $backupTables;
        }
    }

        // VERİTABANI BAKIM FONKSİYONU
    if (!function_exists('performDatabaseMaintenance')) {
        function performDatabaseMaintenance($PDOdbsecilen, $tables, $lockTables = false) {
            try {
                foreach ($tables as $table) {
                    if ($lockTables) {
                        $PDOdbsecilen->query('LOCK TABLES `' . $table . '` WRITE');
                    }

                    $operations = ['check', 'repair', 'optimize', 'analyze'];

                    foreach ($operations as $operation) {
                        $PDOdbsecilen->query(strtoupper($operation) . ' TABLE `' . $table . '`');
                    }

                    if ($lockTables) {
                        $PDOdbsecilen->query('UNLOCK TABLES');
                    }
                }
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
    }

        // TABLOLARI YEDEKLEME FONKSİYONU
    if (!function_exists('backupTables')) {
        function backupTables($islemi_yapan, $PDOdbsecilen, $tables, $combine, $elle, $onek_ve_tarih, $db_name, $gz) {

            $sonuc_cikti_mesaji = [];
            $handle = "";
            $success = false;

            // YEDEK DOSYANIN BAŞLANGIÇ AYARLARINI GETİR
            $initialSettings = getInitialSettings($PDOdbsecilen);

            // YEDEK DOSYANIN BİTİŞ AYARLARINI GETİR
            $finalSettings = finalSettings($PDOdbsecilen);

            // YEDEK DOSYANIN BİTİŞ AYARLARINI OLUŞTUR
            $finalSettingsString = generateFinalSettingsString($finalSettings);

            if ($combine == '1') { // TÜM TABLOLARI TEK DOSYAYA YAZ
                $filePath = BACKUPDIR . '/' . $onek_ve_tarih . '-Tam.sql';
                // YEDEKLEME BAŞLANGIÇ AYARLARINI OLUŞTUR
                $initialSettingsString = generateInitialSettingsString($tables, $initialSettings);
                writeToFile($filePath, $initialSettingsString); // BAŞLANGIÇ AYARLARINI YAZ
                    $sonuc_cikti_mesaji[] = [
                        'status' => 'success',
                        'message' => '<span style="color:green;">Veritabanı Başarıyla Yedeklendi</span>'
                    ];            
                    if ($gz) {
                        $sonuc_cikti_mesaji[] = [
                            'status' => 'dosya_adi',
                            'message' => $filePath.".gz"
                        ];
                    }else{
                        $sonuc_cikti_mesaji[] = [
                            'status' => 'dosya_adi',
                            'message' => $filePath
                        ];
                    }
            }

            if ($combine == '3' && $elle == '1'){ // ELLE SEÇİLEN BİRDEN FAZLA TABLOLARI TEK DOSYAYA YAZ
                if(count($tables) > 1){
                    $filePath = BACKUPDIR . '/' . $onek_ve_tarih . '-Elle.sql';
                    // YEDEKLEME BAŞLANGIÇ AYARLARINI OLUŞTUR
                    $initialSettingsString = generateInitialSettingsString($tables, $initialSettings);
                    writeToFile($filePath, $initialSettingsString); // BAŞLANGIÇ AYARLARINI YAZ
                        $sonuc_cikti_mesaji[] = [
                            'status' => 'success',
                            'message' => '<span style="color:green;">Veritabanı Başarıyla Yedeklendi</span>'
                        ];             
                        if ($gz) {
                            $sonuc_cikti_mesaji[] = [
                                'status' => 'dosya_adi',
                                'message' => $filePath.".gz"
                            ];
                        }else{
                            $sonuc_cikti_mesaji[] = [
                                'status' => 'dosya_adi',
                                'message' => $filePath
                            ];
                        }
                }
            }

            foreach ($tables as $table) {
                $tableStructure = getTableStructure($PDOdbsecilen, $table);
                $tableData = getTableData($PDOdbsecilen, $table);
                $triggers = getTriggers($PDOdbsecilen, $table);
                $backupContent = generateBackupContent($tableStructure, $tableData, $table, $triggers);

                if ($combine == '2'){ // TÜM TABLO(LARI) ALT-DİZİNE AYRI AYRI YEDEKLE

                    $subBackupDir = BACKUPDIR . '/' . $onek_ve_tarih;
                    createDirectoryIfNotExists($subBackupDir);
                    $filePath = $subBackupDir . '/' . trim($table) . '.sql';
                    // HER TABLO YEDEĞİNE KENDİ TABLO ADINI VE SATIR SAYISINI YAZAR 
                    $initialSettingsString = generateInitialSettingsString([$table], $initialSettings);
                    writeToFile($filePath, $initialSettingsString . $backupContent); //BAŞLANGIÇ AYARLARINI YAZ
                    // HER TABLONUN YEDEK DOSYANIN SONUNA BİLGİ EKLE
                    appendInfoToFile($filePath, $finalSettingsString);
                    // HER YEDEKLENEN TABLOYA GZİP AKTİF İSE SIKIŞTIR
                    if ($gz) {
                        gzipFileChunked($filePath);
                    }
                        $sonuc_cikti_mesaji[] = [
                            'status' => 'success',
                            'message' => '<span style="color:green;">Veritabanı Başarıyla Yedeklendi</span>'
                        ];             
                        $sonuc_cikti_mesaji[] = [
                            'status' => 'dosya_adi',
                            'message' => $subBackupDir
                        ];

                } elseif ($combine == '3' && $elle == '1'){ // ELLE SEÇİLEN TABLO(LAR)

                    if(count($tables) == 1){ // EĞER ELLE TEK TABLO SEÇİLİ İSE TABLO ADINI DOSYA ADINA EKLE
                        $filePath = BACKUPDIR . '/' . $onek_ve_tarih . '-' . trim($table) . '-Elle.sql';
                        // YEDEKLEME BAŞLANGIÇ AYARLARINI OLUŞTUR
                        $initialSettingsString = generateInitialSettingsString($tables, $initialSettings);
                        writeToFile($filePath, $initialSettingsString . $backupContent); //BAŞLANGIÇ AYARLARINI YAZ
                        // YEDEK DOSYANIN SONUNA BİLGİ EKLE
                        appendInfoToFile($filePath, $finalSettingsString);
                            $sonuc_cikti_mesaji[] = [
                                'status' => 'success',
                                'message' => '<span style="color:green;">Veritabanı Başarıyla Yedeklendi</span>'
                            ];              
                        if ($gz) {
                            gzipFileChunked($filePath);
                            $sonuc_cikti_mesaji[] = [
                                'status' => 'dosya_adi',
                                'message' => $filePath.".gz"
                            ];
                        }else{
                            $sonuc_cikti_mesaji[] = [
                                'status' => 'dosya_adi',
                                'message' => $filePath
                            ];
                        }
                    }else{ // $combile 3 && $elle 1 için Genel bilgi bir kez yazıldıktan sonra kalan tablo yedek içeriği yazılır
                        writeToFile($filePath, $backupContent);
                    }

                } elseif ($combine == '3' && $elle == '2'){ // ELLE SEÇİLEN TABLO(LARI) ALT-DİZİNE AYRI AYRI YEDEKLE

                    $subBackupDir = BACKUPDIR . '/' . $onek_ve_tarih;
                    createDirectoryIfNotExists($subBackupDir);
                    $filePath = $subBackupDir . '/' . trim($table) . '.sql';
                    // HER TABLO YEDEĞİNE KENDİ TABLO ADINI VE SATIR SAYISINI YAZAR 
                    $initialSettingsString = generateInitialSettingsString([$table], $initialSettings);
                    writeToFile($filePath, $initialSettingsString . $backupContent); //BAŞLANGIÇ AYARLARINI YAZ
                    // HER TABLONUN YEDEK DOSYANIN SONUNA BİLGİ EKLE
                    appendInfoToFile($filePath, $finalSettingsString);
                    // HER YEDEKLENEN TABLOYA GZİP AKTİF İSE SIKIŞTIR
                    if ($gz) {
                        gzipFileChunked($filePath);
                    }
                    $sonuc_cikti_mesaji[] = [
                        'status' => 'success',
                        'message' => '<span style="color:green;">Veritabanı Başarıyla Yedeklendi</span>'
                    ];
                    $sonuc_cikti_mesaji[] = [
                        'status' => 'dosya_adi',
                        'message' => $subBackupDir
                    ];

                } else { // $combine 1 için Genel bilgi bir kez yazıldıktan sonra kalan tablo yedek içeriği yazılır
                    writeToFile($filePath, $backupContent);
                }

            } // foreach ($tables as $table) {

            // YEDEKLENECEK KURALLARA UYGUN DOSYA İÇİN GZİP AKTİF İSE YEDEĞİ SIKIŞTIR
            if (($combine == '1' || $combine == '3' && $elle == '1')) {
                if($gz){
                    appendInfoToFile($filePath, $finalSettingsString);
                    gzipFileChunked($filePath);
                }else{
                    if(count($tables) != 1){ // Eğer tek tablo seçili değil ise
                        appendInfoToFile($filePath, $finalSettingsString);
                    }
                }
            }

        return $sonuc_cikti_mesaji;
        }
    }

        // DOSYAYA BİLGİ EKLEYEN FONKSİYON
    if (!function_exists('appendInfoToFile')) {
        function appendInfoToFile($filePath, $info) {
            writeToFile($filePath, $info);
        }
    }

        // Başlangıç ayarlarını string olarak oluşturma fonksiyonu
    if (!function_exists('generateInitialSettingsString')) {
        function generateInitialSettingsString($tables, $initialSettings) {

            // SQL Mode ayarları
            $sql_mode = 'NO_AUTO_VALUE_ON_ZERO'; // Varsayılan değer
            if (!empty($initialSettings['sql_mode'])) {
                //$sql_mode = $initialSettings['sql_mode'];
            }
            // Time Zone ayarları
            $time_zone = ''.$initialSettings["time_zone"].''; // Varsayılan değer
            if (!empty($initialSettings['time_zone']) && $initialSettings['time_zone'] != 'SYSTEM') {
                //$time_zone = $initialSettings['time_zone'];
            }
            $genel_bilgi =
                "\n-- WebSiteler Yönetimi Scripti\n" .
                "-- WebSiteler Yönetimi Script Versiyonu: " . VERSIYON . "\n\n" .
                "-- Anamakine: " . $initialSettings['http_host'] . "\n" .
                "-- Yedekleme Zamanı: " . $initialSettings['olusturma_zamani'] . "\n" .
                "-- MySQL Sunucu Sürümü: " . $initialSettings['mysql_sunucu_surumu'] . "\n" .
                "-- PHP Sürümü: " . phpversion() . "\n" .
                "-- Karakter Seti: " . $initialSettings['karakter_seti'] . "\n\n" . 
                "-- Veritabanı: `" . $initialSettings['database_name'] . "`\n";

            $genel_bilgi .= 
                "\n\nSET SQL_MODE = '" . $sql_mode . "';\n" .
                "START TRANSACTION;\n" .
                "SET time_zone = '" . $time_zone . "';\n\n" .

                "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n" .
                "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n" .
                "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n" .
                "/*!40101 SET NAMES " . $initialSettings['karakter_seti'] . " */;\n\n";

            $genel_bilgi .= "\n\n-- Tablolar:\n";
            // BAŞLANGIÇ BİLGİLERİNDE TABLO ADI VE VERİ SATIR SAYISI GÖSTERMEK İÇİNDİR
            foreach ($initialSettings['tables'] as $table) {
                if(in_array($table, $tables)){
                    $count = $initialSettings['table_counts'][$table];
                    $genel_bilgi .= "-- Tablo Adı: {$table}: {$count} kayıt\n";
                }
            }

        return $genel_bilgi;
        }
    }

        // Bitiş ayarlarını string olarak oluşturma fonksiyonu
    if (!function_exists('generateFinalSettingsString')) {
        function generateFinalSettingsString($finalSettings) {
            return "\nCOMMIT;" .
                "\n\n" .
                "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n" .
                "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n" .
                "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
        }
    }

        // TABLO YAPISINI ALMA FONKSİYONU
    if (!function_exists('getTableStructure')) {
        function getTableStructure($PDOdbsecilen, $table) {
            $quotedTable = '`' . str_replace('`', '``', $table) . '`'; // Tablo adını escape et
            $result = $PDOdbsecilen->query("SHOW CREATE TABLE {$quotedTable}")->fetch(PDO::FETCH_NUM);
            $structure = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $result[1]) . ";";
            return $structure;
        }
    }

        // TABLO VERİLERİ ALMA FONKSİYONU
    if (!function_exists('getTableData')) {
        function getTableData($PDOdbsecilen, $table) {
            $quotedTable = '`' . str_replace('`', '``', $table) . '`'; // Tablo adını escape et
            $data = [];
            $result = $PDOdbsecilen->query("SELECT * FROM {$quotedTable}")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $values = [];
                foreach ($row as $key => $value) {
                    $values[] = quoteOrNull($PDOdbsecilen, $value);
                }
                $data[] = 'INSERT INTO `' . $table . '` VALUES(' . implode(', ', $values) . ');';
            }
            return $data;
        }
    }

        // DEĞERİ QUOTE YAP VEYA NULL YAP FONKSİYONU
    if (!function_exists('quoteOrNull')) {
        function quoteOrNull($PDOdbsecilen, $value) {
            // Eğer değer null ise 'NULL' stringini döndür
            if ($value === null) {
                $data = 'NULL';
            }else
            // Eğer değer sayısal ise doğrudan döndür
            if (is_int($value)) {
                $data = $value;
            }else{
            // Eğer değer ne null ne de sayısal ise PDO::quote fonksiyonunu kullan
            $data = $PDOdbsecilen->quote($value);
            }
        return $data;
        }
    }

    // TETİKLEYİCİLER TRIGGER ALMA FONKSİYONU
    if (!function_exists('getTriggers')) {
        function getTriggers($PDOdbsecilen, $table) {
            // Tablo adını manuel olarak escape et
            $quotedTable = str_replace('`', '``', $table);

            // Sorguda tablo adını kullanma
            $triggersQuery = $PDOdbsecilen->query("SHOW TRIGGERS LIKE '$quotedTable'");
            
            $triggersResult = $triggersQuery->fetchAll(PDO::FETCH_ASSOC);
            $delimiter = '$$';
            $crlf = "\n";

            $triggerQuery = '';

            foreach ($triggersResult as $trigger) {

                $triggerQuery .= 'DROP TRIGGER IF EXISTS `' . $trigger['Trigger'] . '`;' . $crlf;
                $triggerQuery .= 'DELIMITER ' . $delimiter . $crlf;
                $triggerQuery .= 'CREATE TRIGGER `' . $trigger['Trigger'] . '` ' . $trigger['Timing'] . ' ' . $trigger['Event'] . ' ON `' . $trigger['Table'] . '` FOR EACH ROW ';
                $triggerQuery .= $trigger['Statement'] . $crlf;
                $triggerQuery .= $delimiter . $crlf;
                $triggerQuery .= 'DELIMITER ;' . $crlf.$crlf;
            }
            return $triggerQuery;
        }
    }

        // YEDEKLEME İÇERİĞİ OLUŞTURMA FONKSİYONU
    if (!function_exists('generateBackupContent')) {
        function generateBackupContent($tableStructure, $tableData, $table, $triggers) {
            $content = "\n\n\n-- ------------------------------------------------------\n";
            $content .= "-- Tablo için tablo yapısı `" . $table . "`\n";
            $content .= "-- ------------------------------------------------------\n";
            $content .= "DROP TABLE IF EXISTS `" . $table . "`;\n";
            $content .= $tableStructure;

            if (!empty($tableData)) {
                $content .= "\n\n-- ------------------------------------------------------\n";
                $content .= "-- Tablonun veri dökümü `" . $table . "`\n";
                $content .= "-- ------------------------------------------------------\n";
                $content .= implode("\n", $tableData);
            }

            if (!empty($triggers)) {
                $content .= "\n\n\n-- ------------------------------------------------------\n";
                $content .= "-- Tablo için Tetikleyiciler `" . $table . "`\n";
                $content .= "-- ------------------------------------------------------\n";
                $content .= $triggers;
            }

            return $content;
        }
    }

        // DOSYAYA YAZMA FONKSİYONU
    if (!function_exists('writeToFile')) {
        function writeToFile($filePath, $content) {
            return file_put_contents($filePath, $content, FILE_APPEND);
        }
    }

        // GZİP SIKIŞTIRMA FONKSİYONU
    if (!function_exists('gzipFileChunked')) {
        function gzipFileChunked($filePath) {
            $gzfile = $filePath . '.gz';
            $chunkSize = 4096; // 4KB
            $fp = gzopen($gzfile, 'w9');
            $input = fopen($filePath, 'rb');
            while (!feof($input)) {
                gzwrite($fp, fread($input, $chunkSize));
            }
            fclose($input);
            gzclose($fp);
            unlink($filePath); // Gzipped olduktan sonra orijinal dosyayı silebiliriz
        }
    }

        // YEDEKLEME YAPILACAK ANA DİZİN MEVCUT DEĞİL İSE OLUŞTUR
        createDirectoryIfNotExists(BACKUPDIR);

        // YEDEKLEME DİZİNE DIŞARIDAN ULAŞIMI ENGELLEMEK İÇİN .htaccess DOSYASI OLUŞTUR
        createHtaccessFile(BACKUPDIR);

        // YEDEKLEME DOSYA ADINI BELİRLE
        $onek_ve_tarih = getBackupFilenamePrefix($secilen_yedekleme_oneki, $dosya_tarihi);

        // TABLOLARI BELİRLEME
        $tables = determineTablesToBackup($PDOdbsecilen, $combine, $yedekleyen, $yedeklenecek_tablolar);

        //file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . '<pre>' . print_r($tables, true) . '</pre>' . "\n", FILE_APPEND);

        // VERİTABANI BAKIMI YAP
        if ($dbbakim == '1') {
            performDatabaseMaintenance($PDOdbsecilen, $tables, $dblock);
        }

        // YEDEKLEME İŞLEMİNİ BAŞLAT
        $sonuc_cikti_mesaji = backupTables($islemi_yapan, $PDOdbsecilen, $tables, $combine, $elle, $onek_ve_tarih, $db_name, $gz);

    return $sonuc_cikti_mesaji;
    }
}

###################################################################################################################################################
###################################################################################################################################################
###################################################################################################################################################
###################################################################################################################################################
###################################################################################################################################################
###################################################################################################################################################

// Fonksiyonu çağırarak işlemleri başlatın
if(isset($_POST['yedekleyen']) && $_POST['yedekleyen'] == 2){

ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);

    $veritabani_id = isset($_POST['veritabani_id']) ? $_POST['veritabani_id'] : "";
    // Seçilen veritabanı 
    $default = $PDOdb->prepare("SELECT * FROM veritabanlari WHERE id=? LIMIT 1");
    $default->execute([$veritabani_id]);
    $varsayilan = $default->fetch(PDO::FETCH_ASSOC);

    // Seçilen veritabanı varsa bağlantı oluşturuyoruz
    $secilen = "mysql:host=".$varsayilan['database_host'].";dbname=".$varsayilan['db_name'].";charset=".$varsayilan['charset'].";port=".$varsayilan['port']."";
    try {
        $PDOdbsecilen = new PDO($secilen, $hash->take($varsayilan['database_user']), $hash->take($varsayilan['database_password']), $options);
        $PDOdbsecilen->exec("set names ".CHARSET);
        $PDOdbsecilen->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
        die($e->getMessage());
    }
    $db_name = $varsayilan['db_name'];

    $secilen_yedekleme_oneki    = isset($_POST['onek'])         ? $_POST['onek']        : "";
    $combine                    = isset($_POST['combine'])      ? $_POST['combine']     : "";
    $elle                       = isset($_POST['elle'])         ? $_POST['elle']        : "";
    $grup                       = isset($_POST['grup'])         ? $_POST['grup']        : "";
    $dbbakim                    = isset($_POST['bakim'])        ? $_POST['bakim']       : "";
    $gz                         = isset($_POST['gz'])           ? $_POST['gz']          : "";
    $yedekleyen                 = isset($_POST['yedekleyen'])   ? $_POST['yedekleyen']  : "0";
    $dblock                     = isset($_POST['lock'])         ? $_POST['lock']        : "";
    $yedeklenecek_tablolar      = isset($_POST['tablolar'])     ? $_POST['tablolar']    : [];
    $dosya_tarihi               = date_tr('Y-m-d-H-i-s', time());
    $islemi_yapan               = false;

    $backup_yedekleme_sonucu = veritabaniYedekleme($islemi_yapan, $PDOdbsecilen, $veritabani_id, $secilen_yedekleme_oneki, $combine, $elle, $grup, $dbbakim, $gz, $yedekleyen, $dblock, $db_name, $yedeklenecek_tablolar, $dosya_tarihi);
    //echo array_column($backup_yedekleme_sonucu, ['status']['dosya_adi']);
    //echo '<pre>' . print_r($backup_yedekleme_sonucu, true) . '</pre>';
//exit;
if(!empty($backup_yedekleme_sonucu)){

//echo '<pre>' . print_r($backup_yedekleme_sonucu, true) . '</pre>';

foreach ($backup_yedekleme_sonucu AS $item) {
    if ( isset($item['status']) && $item['status'] === 'success' ) {
        echo $item['message'] . "<br>";
    } else if ( isset($item['status']) && $item['status'] === 'dosya_adi' ) {
        echo "<b>Dosya Adı:</b> " . basename($item['message']) . "<br>";
    }else{

    }
}

}

}

###################################################################################################################################################
###################################################################################################################################################
###################################################################################################################################################
###################################################################################################################################################
###################################################################################################################################################
###################################################################################################################################################

?>