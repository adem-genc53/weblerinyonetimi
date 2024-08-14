<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;

ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);

    //echo '<pre>' . print_r($_POST, true) . '</pre>';
    //exit;
#########################################################################################################################################
    // POST ile veritabanı id
    if(isset($_POST['veritabani_id']) && $_POST['veritabani_id'] > 0){
        $veritabani_id = $_POST['veritabani_id'];
    }else{
        //$veritabani_id = 0;
    }
#########################################################################################################################################
    // Seçilen veritabanı 
    $default = $PDOdb->prepare("SELECT * FROM veritabanlari WHERE id=? LIMIT 1");
    $default->execute([$veritabani_id]);
    $varsayilan = $default->fetch(PDO::FETCH_ASSOC);

    // Seçilen veritabanı varsa bağlantı oluşturuyoruz
    $secilen = "mysql:host=".$varsayilan['database_host'].";dbname=".$varsayilan['db_name'].";charset=".CHARSET.";port=".$varsayilan['port']."";
    try {
    $PDOdbsecilen = new PDO($secilen, $hash->take($varsayilan['database_user']), $hash->take($varsayilan['database_password']), $options);
    $PDOdbsecilen->exec("set names ".CHARSET);
    $PDOdbsecilen->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
        die($e->getMessage());
    }

    $db_name = $varsayilan['db_name'];

#########################################################################################################################################
#########################################################################################################################################

function parseBackupFile($backupFile, $serverDbName, $serverTableNames, $yinede_karsılastir) {
    if (empty($backupFile)) {
        return null;
    }

    $data = [];
    $data['dbname'] = null;
    $data['tables'] = array_fill_keys($serverTableNames, null);

    if (strpos($backupFile, '.gz') !== false) {
        $handle = gzopen($backupFile, 'r');
    } else {
        $handle = fopen($backupFile, 'r');
    }

    if ($handle) {
        $currentTable = null;
        $backupDbName = null;

        while (($line = (strpos($backupFile, '.gz') !== false) ? gzgets($handle) : fgets($handle)) !== false) {
            if (strpos($line, '-- Veritabanı:') !== false) {
                $backupDbName = trim(str_replace(array('-- Veritabanı:', '`'), '', $line));
                if ($backupDbName !== $serverDbName && $yinede_karsılastir == 0) {                    
                    // Veritabanı adı eşleşmiyorsa işlemi bitir
                    fclose($handle);
                    return ['dbname' => $backupDbName];
                }
                $data['yedek_dbname'] = $backupDbName;
                $data['dbname'] = $backupDbName;
            }

            if ( preg_match('/CREATE TABLE IF NOT EXISTS `([\w-]+)`/', $line, $matches) ) {
                $currentTable = $matches[1];
                if (in_array($currentTable, $serverTableNames)) {
                    $data['tables'][$currentTable] = 0; // Tablo yapısı mevcut, ancak veri satırı yok
                } else {
                    $currentTable = null; // Geçerli tablo sunucu tabloları arasında yoksa sıfırla
                }
            }

            if ( preg_match('/INSERT INTO `([\w-]+)`/', $line, $matches) ) {
                $tableName = $matches[1];
                if ($currentTable === $tableName && in_array($tableName, $serverTableNames)) {
                    $data['tables'][$tableName]++;
                }
            }
        }

        if (strpos($backupFile, '.gz') !== false) {
            gzclose($handle);
        } else {
            fclose($handle);
        }
    }

    return $data;
}

function parseBackupFolder($folderPath, $expectedDbName, $serverTableNames, $yinede_karsılastir) {
    $data = ['dbname' => $expectedDbName, 'tables' => []];

    $files = array_merge(glob($folderPath . '*.sql'), glob($folderPath . '*.gz'));

    foreach ($files as $file) {
        $fileData = parseBackupFile($file, $expectedDbName, $serverTableNames, $yinede_karsılastir);
        $data['yedek_dbname'] = $fileData['dbname'];
        
        if (isset($fileData['dbname']) && $fileData['dbname'] == $expectedDbName || $yinede_karsılastir == 1) {
            foreach ($fileData['tables'] as $table => $count) {
                if (!isset($data['tables'][$table])) {
                    $data['tables'][$table] = $count;
                } else {
                    if ($data['tables'][$table] === null) {
                        $data['tables'][$table] = $count;
                    } else {
                        $data['tables'][$table] += $count;
                    }
                }
            }
        }
        
    }

    if(isset($data['tables']) && count($data['tables'])==0){
        unset($data['dbname']);
        $data['dbname'] = $fileData['dbname'];
    }
    return $data;
}

function getDatabaseInfo($PDOdbsecilen, $dbname) {
    $data = ['dbname' => $dbname, 'tables' => []];

    $query = $PDOdbsecilen->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = :dbname");
    $query->execute(['dbname' => $dbname]);
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $query = $PDOdbsecilen->prepare("SELECT COUNT(*) FROM `$table`");
        $query->execute();
        $count = $query->fetchColumn();
        $data['tables'][$table] = (int)$count;
    }
    return $data;
}

function compareDatabases($backupData, $serverData) {

        $allTables = array_unique(array_merge(array_keys($serverData['tables']), array_keys($backupData['tables'])));

        $html = '<table class="table table-bordered table-sm" style="min-width: 1000px;">
        <colgroup span="7">
            <col style="width:40%"></col>
            <col style="width:6%"></col>
            <col style="width:2%"></col>
            <col style="width:6%"></col>
            <col style="width:40%"></col>
            <col style="width:6%"></col>
            <col style="width:2%"></col>
        </colgroup>
        <thead>
        <tr class="bg-primary">    
            <th style="padding-left: 0.5rem;">Kaynak Tablolar</th>
            <th style="padding-left: 0.5rem;">Veri Satırı</th>
            <th>Sonuç</th>
            <th style="border-bottom: 1px solid white;border-top: 1px solid white;padding:5px;background:#FFFFFF;">&nbsp;</th>
            <th>Yedek Tablolar</th>
            <th style="padding-right: 0.5rem;">Veri Satırı</th>
            <th style="padding-right: 0.5rem;">Sonuç</th>
        </tr>
        </thead>
        <tbody>';

        $html .= "
        <tr>
            <td colspan='3' style='text-align:center;background:#F0F1F9;'><b>Kaynak Veritabanı: </b>".htmlspecialchars($serverData['dbname'])."</td>
            <td rowspan='".(count($allTables)+1)."' style='border-bottom: 1px solid white;display: table-cell; vertical-align: middle;text-align:center;'><img border='0' src='images/diff.png' width='48' height='45'></td>
            <td colspan='3' style='text-align:center;background:#F0F1F9;'><b>Yedek Veritabanı: </b>".htmlspecialchars($backupData['yedek_dbname']) ."</td>
        </tr>";

        sort($allTables);
        foreach ($allTables as $table) {
            $serverCount = $serverData['tables'][$table] ?? null;
            $backupCount = $backupData['tables'][$table] ?? null;

            if ($serverCount === null) { // Sunucuda olmayan tablo için
                $html .= '<tr class="table-danger">';
                    $html .= '<td colspan="3" style="padding-left: 0.5rem;">Bu tablo sunucuda mevcut değil</td>';
                    $html .= '<td style="padding-left: 0.5rem;">' . htmlspecialchars($table) . '</td>';
                    $html .= '<td style="text-align: right;">' . htmlspecialchars($backupCount) . '</td>';
                    $html .= '<td style="text-align: center;padding-right: 0rem;"><i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i></td>';
                $html .= '</tr>';
            } elseif ($backupCount === null) { // Yedekte olmayan tablo için
                $html .= '<tr class="table-warning">';
                    $html .= '<td style="padding-left: 0.5rem;">' . htmlspecialchars($table) . '</td>';
                    $html .= '<td style="text-align: right;">' . htmlspecialchars($serverCount) . '</td>';
                    $html .= '<td style="text-align: center;padding-right: 0rem;"><i class="fa fa-plus" aria-hidden="true" style="color:red;"></i></td>';
                    $html .= '<td colspan="2">Bu tablo yedekte mevcut değil</td>';
                    $html .= '<td style="text-align: center;padding-right: 0rem;"><i class="fa fa-minus" aria-hidden="true" style="color:red;"></i></td>';
                $html .= '</tr>';
            } elseif ($backupCount === 0 && $serverCount >0) { // Yedekte sadece tablo yapısı olan, veri satırı yok, ancak sunucuda veri satırı var
                $html .= '<tr class="table-warning">';
                    $html .= '<td style="padding-left: 0.5rem;">' . htmlspecialchars($table) . '</td>';
                    $html .= '<td style="text-align: right;">' . htmlspecialchars($serverCount) . '</td>';
                    $html .= '<td style="text-align: center;padding-right: 0rem;"><i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i></td>';
                    $html .= '<td style="padding-left: 0.5rem;">' . htmlspecialchars($table) . '</td>';
                    $html .= '<td style="text-align: right;">0</td>';
                    $html .= '<td style="text-align: center;padding-right: 0rem;"><i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i></td>';
                $html .= '</tr>';
            } elseif ($backupCount === 0) { // Yedekte sadece tablo yapısı olan, veri satırı olmayan tablo için
                $html .= '<tr>';
                    $html .= '<td style="padding-left: 0.5rem;">' . htmlspecialchars($table) . '</td>';
                    $html .= '<td style="text-align: right;">' . htmlspecialchars($serverCount) . '</td>';
                    $html .= '<td style="text-align: center;padding-right: 0rem;"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></td>';
                    $html .= '<td style="padding-left: 0.5rem;">' . htmlspecialchars($table) . '</td>';
                    $html .= '<td style="text-align: right;">0</td>';
                    $html .= '<td style="text-align: center;padding-right: 0rem;"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></td>';
                $html .= '</tr>';
            } else { // Hem sunucuda hem de yedekte mevcut olan tablolar için
                $trstatus = ($serverCount == $backupCount) ? '' : ' class="table-danger"';
                $status = ($serverCount == $backupCount) ? '<i class="fa fa-check" aria-hidden="true" style="color:green;"></i>' : '<i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i>';
                $html .= '<tr' . $trstatus . '>';
                    $html .= '<td style="padding-left: 0.5rem;">' . htmlspecialchars($table) . '</td>';
                    $html .= '<td style="text-align: right;">' . htmlspecialchars($serverCount) . '</td>';
                    $html .= '<td style="text-align: center;">' . $status . '</td>';
                    $html .= '<td style="padding-left: 0.5rem;">' . htmlspecialchars($table) . '</td>';
                    $html .= '<td style="text-align: right;">' . htmlspecialchars($backupCount) . '</td>';
                    $html .= '<td style="text-align: center;padding-right: 0rem;">' . $status . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';
        return $html;

}

// Yedek dosyasını oku
$backupData = null;
$yinede_karsılastir = isset($_POST['yinede']) ? $_POST['yinede'] : 0;
$serverTableNames = array_keys(getDatabaseInfo($PDOdbsecilen, $db_name)['tables']); // Sunucudaki tablo isimlerini al
if (isset($_POST['sqlsec']) && !empty($_POST['sqlsec'])) {
    $backupFile = $_POST['sqlsec'];
    $backupData = parseBackupFile($backupFile, $db_name, $serverTableNames, $yinede_karsılastir);
} elseif (isset($_POST['klasorsec']) && !empty($_POST['klasorsec'])) {
    $folderPath = $_POST['klasorsec'];
    $backupData = parseBackupFolder($folderPath, $db_name, $serverTableNames, $yinede_karsılastir);
} elseif (isset($_POST['alt_dosya']) && !empty($_POST['alt_dosya'])) {
    $altbackupFile = $_POST['alt_dosya'];
    $backupData = parseBackupFile($altbackupFile, $db_name, $serverTableNames, $yinede_karsılastir);
}else{

}

if ($backupData) {
    // Sunucudaki veritabanı bilgilerini al
    $serverData = getDatabaseInfo($PDOdbsecilen, $db_name);

    // Karşılaştırma ve sonuçları göster
    //echo "<pre>Sunucu\n" . print_r($serverData, true) . '</pre>';
    //echo "<pre>Yedek\n" . print_r($backupData, true) . '</pre>';

    if ( ( isset($backupData['dbname']) && htmlspecialchars($serverData['dbname']) == htmlspecialchars($backupData['dbname']) ) || isset($backupData['dbname']) && isset($_POST['yinede'])) {
        echo compareDatabases($backupData, $serverData);
    }else{
        if(!isset($backupData['dbname'])){
            echo '<p align="center">Yedek veri tabanı dosyasında veri tabanı adına ulaşılamadı.<br /><br />Dosyanın veri tabanı yedek dosyası olduğundan ve bu script ile yedeklendiğinden emin olunuz.</p>';
        }else{
            echo '<p align="center">Karşılaştırmak için sunucudaki veritabanı ile yedek veritabanı aynı olması gerekir.</p><p align="center">Yinede karşılaştırmak istiyorsanız kutuyu işaretleyin.</p>';
        }
    }
} else {
    echo 'Yedek dosyası veya klasörü seçilmedi.';
}
unset($backupData,$altbackupFile,$folderPath,$backupFile)
?>