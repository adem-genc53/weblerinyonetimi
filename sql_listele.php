<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

################################################################################
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);

    //sleep(10);
     //echo '<pre>' . print_r($_POST, true) . '</pre>';
     //exit;
################################################################################

    if(isset($_POST['alt_dosya']) && is_file($_POST['alt_dosya'])){
        $path = $_POST['alt_dosya'];
    }elseif(isset($_POST['klasorsec']) && is_dir($_POST['klasorsec'])){
        $path = $_POST['klasorsec'];
    }else{
        $path = isset($_POST['sqlsec']) ? $_POST['sqlsec'] : '';
    }

// Fonksiyon: Dosya içeriğini oku ve ekrana yazdır
function readAndPrintFile($filePath, $isLastFile) {
    if (preg_match('/\.sql\.gz$/i', $filePath)) {
        // .sql.gz dosyasını aç ve içeriğini oku
        $fileContent = gzfile($filePath);
        $fileContent = implode("", $fileContent);
    } else {
        // .sql dosyasını aç ve içeriğini oku
        $fileContent = file_get_contents($filePath);
    }

    // İçeriği ekrana yazdır
    echo $fileContent;

    // Son tablo değilse ayrıcı satırları ekle
    if (!$isLastFile) {
        echo "\n/************************************************************************************/\n";
        echo "/*************************** SONRAKİ TABLONUN BAŞLANGICI ****************************/\n";
        echo "/************************************************************************************/\n";
    }
}

// Belirtilen yol bir dizin mi?
if (is_dir($path)) {
    // Dizin içindeki tüm dosyaları al
    $files = array_diff(scandir($path), array('..', '.'));

    // Sadece .sql ve .sql.gz dosyalarını filtrele
    $sqlFiles = array_filter($files, function ($file) use ($path) {
        return preg_match('/\.sql(\.gz)?$/i', $file) && is_file($path . DIRECTORY_SEPARATOR . $file);
    });

    $totalFiles = count($sqlFiles);
    $currentFileIndex = 0;

    foreach ($sqlFiles as $file) {
        $currentFileIndex++;
        $filePath = $path . DIRECTORY_SEPARATOR . $file;
        $isLastFile = ($currentFileIndex === $totalFiles);
        readAndPrintFile($filePath, $isLastFile);
    }
} elseif (is_file($path)) {
    // Belirtilen yol tek bir dosya ise
    readAndPrintFile($path, true);
} else {
    echo "Belirtilen yol geçerli bir dosya veya dizin değil.";
}

?>