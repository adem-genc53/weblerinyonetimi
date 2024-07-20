<?php
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

################################################################################
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);

    //sleep(5);
     //echo '<pre>' . print_r($_POST, true) . '</pre>';
     //exit;
################################################################################

// İşlenecek dosya veya dizin yolu ve kaydet parametresi

$path = BACKUPDIR.'/'.$_POST['folder'] ?? '';
$save = true;

// Hedef dosya adı ve yolu (birleştirilecek SQL dosyası için)
$outputFileName = 'BIRLESTIRILDI-'.$_POST['folder'].'.sql';

$outputFilePath = BACKUPDIR.'/'.$outputFileName;

// Fonksiyon: Dosya içeriğini oku ve birleştir
function getFileContent($filePath) {
    if (preg_match('/\.sql\.gz$/i', $filePath)) {
        // .sql.gz dosyasını aç ve içeriğini oku
        $fileContent = gzfile($filePath);
        $fileContent = implode("", $fileContent);
    } else {
        // .sql dosyasını aç ve içeriğini oku
        $fileContent = file_get_contents($filePath);
    }
    return $fileContent;
}

if (is_dir($path)) {
    // Dizin içindeki tüm dosyaları al
    $files = array_diff(scandir($path), array('..', '.'));

    // Sadece .sql ve .sql.gz dosyalarını filtrele
    $sqlFiles = array_filter($files, function ($file) use ($path) {
        return preg_match('/\.sql(\.gz)?$/i', $file) && is_file($path . DIRECTORY_SEPARATOR . $file);
    });

    $mergedContent = '';
    foreach ($sqlFiles as $file) {
        $filePath = $path . DIRECTORY_SEPARATOR . $file;
        $mergedContent .= getFileContent($filePath) . "\n";
    }

    if ($save) {
        // Birleştirilmiş içeriği UTF-8 olarak encode et ve dosyaya kaydet
        $mergedContent = mb_convert_encoding($mergedContent, 'UTF-8', 'auto');
        file_put_contents($outputFilePath, $mergedContent);
        echo "Dosya başarıyla birleştirilerek kaydedildi: $outputFileName";
    } else {
        // İçeriği ekrana yazdır
        //echo $mergedContent;
    }

} else {
    echo "Belirtilen yol geçerli bir dosya veya dizin değil.";
}

?>
