<?php 
// Bismillahirrahmanirrahim
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");

ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);

//echo '<pre>' . print_r($_POST, true) . '</pre>';

#########################################################################################################################################
    // POST ile veritabanı id
    if(isset($_POST['veritabani_id']) && $_POST['veritabani_id'] > 0){
        $veritabani_id = $_POST['veritabani_id'];
    }else{
        $veritabani_id = 0;
    }
#########################################################################################################################################
    // Seçilen veritabanı 
    $default = $PDOdb->prepare("SELECT * FROM veritabanlari WHERE id=? LIMIT 1");
    $default->execute([$veritabani_id]);
    $varsayilan = $default->fetch(PDO::FETCH_ASSOC);

    // Seçilen veritabanı varsa bağlantı oluşturuyoruz
    $secilen = "mysql:host=".$varsayilan['database_host'].";dbname=".$varsayilan['db_name'].";charset=".CHARSET.";port=".PORT."";
    try {
    $PDOdbsecilen = new PDO($secilen, $hash->take($varsayilan['database_user']), $hash->take($varsayilan['database_password']), $options);
    $PDOdbsecilen->exec("set names ".CHARSET);
    $PDOdbsecilen->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
        die($e->getMessage());
    }

    $db_name = $varsayilan['db_name'];
#########################################################################################################################################

function parseBackupFile($backupFile, $serverTableNames) {
    if (empty($backupFile)) { return null; }

    $data = [];

    if (strpos($backupFile, '.gz') !== false) {
        $handle = gzopen($backupFile, 'r');
    } else {
        $handle = fopen($backupFile, 'r');
    }

    if ($handle) {
        $currentTable = null;
        $tableHasInsert = [];

        // Sunucudaki tablo isimlerine göre başlangıçta hepsini NULL olarak ayarla
        foreach ($serverTableNames as $table) {
            $data['tables'][$table] = null;
        }

        while (($line = (strpos($backupFile, '.gz') !== false) ? gzgets($handle) : fgets($handle)) !== false) {
            if (strpos($line, '-- Veritabanı Adı:') !== false) {
                $data['dbname'] = trim(str_replace('-- Veritabanı Adı:', '', $line));
            }
            if (preg_match('/CREATE TABLE IF NOT EXISTS `([\w-]+)`/', $line, $matches)) {
                $currentTable = $matches[1];
                if (in_array($currentTable, $serverTableNames)) {
                    $data['tables'][$currentTable] = 0; // Tablo yapısı mevcut, ancak veri satırı yok
                }
                $tableHasInsert[$currentTable] = false;
            }
            if (preg_match('/INSERT INTO `([\w-]+)`/', $line, $matches)) {
                $tableName = $matches[1];
                if (in_array($tableName, $serverTableNames)) {
                    $data['tables'][$tableName]++;
                    $tableHasInsert[$tableName] = true;
                }
            }
        }

        if (strpos($backupFile, '.gz') !== false) {
            gzclose($handle);
        } else {
            fclose($handle);
        }
/*
        // Eğer tablo yapısı varsa ve veri satırı yoksa 0 olarak kalacak
        // Eğer tablo yapısı ve veri satırı yoksa NULL olarak kalacak
        foreach ($tableHasInsert as $table => $hasInsert) {
            
            if (!$hasInsert && $data['tables'][$table] === 0) {
                $data['tables'][$table] = 0; // Tablo yapısı var ama satır yok
            }
        }
        */
    }
    return $data;
}

function parseBackupFolder($folderPath, $expectedDbName, $serverTableNames) {
    $data = ['dbname' => $expectedDbName, 'tables' => []];

    $files = array_merge(glob($folderPath . '/*.sql'), glob($folderPath . '/*.gz'));
    foreach ($files as $file) {
        $fileData = parseBackupFile($file, $serverTableNames);
        if (isset($fileData['dbname']) && $fileData['dbname'] == $expectedDbName) {
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
    if ((htmlspecialchars($serverData['dbname']) == htmlspecialchars($backupData['dbname'])) || isset($_POST['yinede'])) {
        $allTables = array_unique(array_merge(array_keys($serverData['tables']), array_keys($backupData['tables'])));

        $html = '<table class="table table-bordered table-sm" style="min-width: 1000px;">
        <colgroup span="7">
            <col style="width:40%"></col>
            <col style="width:5%"></col>
            <col style="width:2%"></col>
            <col style="width:6%"></col>
            <col style="width:40%"></col>
            <col style="width:5%"></col>
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
            <td colspan='3' style='text-align:center;background:#F0F1F9;'><b>Yedek Veritabanı: </b>".htmlspecialchars($backupData['dbname']) ."</td>
        </tr>";

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
                    $html .= '<td style="text-align: center;padding-right: 0rem;"><i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i></td>';
                    $html .= '<td colspan="3">Bu tablo yedekte mevcut değil</td>';
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
    } else {
        return '<p align="center">Karşılaştırmak için sunucudaki veritabanı ile yedek veritabanı aynı olması gerekir.</p><p align="center">Yinede karşılaştırmak istiyorsanız kutuyu işaretleyin.</p>';
    }
}

// Yedek dosyasını oku
$backupData = null;
$serverTableNames = array_keys(getDatabaseInfo($PDOdbsecilen, $db_name)['tables']); // Sunucudaki tablo isimlerini al
if (isset($_POST['sqlsec']) && !empty($_POST['sqlsec'])) {
    $backupFile = $_POST['sqlsec'];
    $backupData = parseBackupFile($backupFile, $serverTableNames);
} elseif (isset($_POST['klasorsec']) && !empty($_POST['klasorsec'])) {
    $folderPath = $_POST['klasorsec'];
    $backupData = parseBackupFolder($folderPath, $db_name, $serverTableNames);
}

if ($backupData) {
    // Sunucudaki veritabanı bilgilerini al
    $serverData = getDatabaseInfo($PDOdbsecilen, $db_name);

    // Karşılaştırma ve sonuçları göster
    echo compareDatabases($backupData, $serverData);
} else {
    echo 'Yedek dosyası veya klasörü seçilmedi.';
}

?>