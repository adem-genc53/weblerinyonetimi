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

function parseBackupFile($backupFile) {
    if(empty($backupFile)){ return null; }

    $data = [];
    
    if (strpos($backupFile, '.gz') !== false) {
        $handle = gzopen($backupFile, 'r');
    } else {
        $handle = fopen($backupFile, 'r');
    }

    if ($handle) {
        while (($line = (strpos($backupFile, '.gz') !== false) ? gzgets($handle) : fgets($handle)) !== false) {
            if (strpos($line, '-- Veritabanı Adı:') !== false) {
                $data['dbname'] = trim(str_replace('-- Veritabanı Adı:', '', $line));
            }
            if (preg_match('/-- - ([\w-]+): (\d+) kayıt/', $line, $matches) && count($matches) == 3) {
                $data['tables'][$matches[1]] = (int)$matches[2];
            }
            if (strpos($line, '-- - SON') !== false) {
                break;
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

function parseBackupFolder($folderPath, $expectedDbName) {
    $data = ['dbname' => $expectedDbName, 'tables' => []];

    $files = glob($folderPath . '/*.sql') + glob($folderPath . '/*.gz');
    foreach ($files as $file) {
        $fileData = parseBackupFile($file);
        if (isset($fileData['dbname']) && $fileData['dbname'] == $expectedDbName) {
            $data['tables'] = array_merge($data['tables'], $fileData['tables']);
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

    if((htmlspecialchars($serverData['dbname']) == htmlspecialchars($backupData['dbname'])) || isset($_POST['yinede'])){

    $allTables = array_unique(array_merge(array_keys($serverData['tables']), array_keys($backupData['tables'])));

    $html = '<table class="table table-bordered table-sm" style="min-width: 1000px;">
    <colgroup span="7">
        <col style="width:43%"></col>
        <col style="width:2%"></col>
        <col style="width:2%"></col>
        <col style="width:6%"></col>
        <col style="width:43%"></col>
        <col style="width:2%"></col>
        <col style="width:2%"></col>
    </colgroup>
    <thead>
  <tr class="bg-primary">    
    <th style="padding-left: 0.5rem;">Kaynak Tablolar</th>
	<th style="padding-left: 0.5rem;">Satır</th>
    <th>Sonuç</th>

    <th style="border-bottom: 1px solid white;border-top: 1px solid white;padding:5px;background:#FFFFFF;">&nbsp;</th>
    
		<th>Yedek Tablolar</th>
		<th style="padding-right: 0.5rem;">Satır</th>
    <th style="padding-right: 0.5rem;">Sonuç</th>
  </tr>

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

        if ($serverCount === null) {
            $html .= '<tr class="table-danger">
                <td colspan="3">Bu tablo sunucuda mevcut değil</td>
                <td>' . htmlspecialchars($table) . '</td>
                <td>' . htmlspecialchars($backupCount) . '</td>
                <td><i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i></td>
            </tr>';
        } elseif ($backupCount === null) {
            $html .= '<tr class="table-warning">
                <td>' . htmlspecialchars($table) . '</td>
                <td>' . htmlspecialchars($serverCount) . '</td>
                <td><i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i></td>
                <td colspan="3">Bu tablo yedekte mevcut değil</td>
            </tr>';
        } else {
            // Sunucuda ve yedekte satır sayıları eşit mi değil mi ona göre ikon ata
            $trstatus = ($serverCount == $backupCount) ? '' : ' class="table-info"';
            $status = ($serverCount == $backupCount) ? '<i class="fa fa-check" aria-hidden="true" style="color:green;"></i>' : '<i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i>';
            $html .= '<tr'.$trstatus.'>
                <td style="padding-left: 0.5rem;">' . htmlspecialchars($table) . '</td>
                <td style="text-align: right;">' . htmlspecialchars($serverCount) . '</td>
                <td style="text-align: center;">' . $status . '</td>

                <td>' . htmlspecialchars($table) . '</td>
                <td style="text-align: right;">' . htmlspecialchars($backupCount) . '</td>
                <td style="text-align: center;padding-right: 0rem;">' . $status . '</td>
            </tr>';
        }
    }

    $html .= '</tbody></table>';
    return $html;
}else{
    return '<p align="center">Karşılaştırmak için sunucudaki veritabanı ile yedek veritabanı aynı olması gerekir.</p><p align="center">Yinede karşılaştırmak istiyorsanız kutuyu işaretleyin.</p>';
}
}

// Yedek dosyasını oku
    $backupData = null;
    if (isset($_POST['sqlsec']) && !empty($_POST['sqlsec'])) {
        $backupFile = $_POST['sqlsec']; // .gz veya .sql uzantılı dosyalar
        $backupData = parseBackupFile($backupFile);
    } elseif (isset($_POST['klasorsec']) && !empty($_POST['klasorsec'])) {
        $folderPath = $_POST['klasorsec']; // klasör, içinde her tablo ayrı ayrı .gz veya .sql uzantılı dosyalar mevcut
        $backupData = parseBackupFolder($folderPath, $db_name);
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