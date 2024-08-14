<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;

//echo '<pre>' . print_r($_POST, true) . '</pre>';
//exit;
#########################################################################################################################################
    // POST ile veritabanı id
    $veritabani_id = isset($_POST['veritabani_id']) ? $_POST['veritabani_id'] : "";
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

function performDatabaseMaintenance($PDOdbsecilen, $tables, $lockTables = false) {
    $results = [];

    foreach ($tables as $table) {
        try {
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

            $results[$table] = 'OK';
        } catch (PDOException $e) {
            $results[$table] = 'Başarısız: ' . $e->getMessage();
        }
    }

    return $results;
}



$tables = $PDOdbsecilen->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN); // TÜM TABLOLAR

$maintenanceResults = performDatabaseMaintenance($PDOdbsecilen, $tables);
echo "<table class='table-striped' width='100%'>";
echo "
<thead>
    <tr>
    <th colspan='2'><h6>Veritabanı Adı: $db_name</h6></th>
    </tr>
</thead>
";
echo "<tbody>";
foreach ($maintenanceResults as $table => $result) {
    echo "
        <tr>
            <td>$table:</td>
            <td>$result</td>
        </tr>
    ";
}
echo "</tbody>";
echo "</table>";

?>




