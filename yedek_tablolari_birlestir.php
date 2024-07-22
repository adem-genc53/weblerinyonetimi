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

// Sonunda eklemek istediğimiz SQL ifadeleri
$footerSQL = "COMMIT;\n
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";

if (is_dir($path)) {
    // Dizin içindeki tüm dosyaları al
    $files = array_diff(scandir($path), array('..', '.'));

    // Sadece .sql ve .sql.gz dosyalarını filtrele
    $sqlFiles = array_filter($files, function ($file) use ($path) {
        return preg_match('/\.sql(\.gz)?$/i', $file) && is_file($path . DIRECTORY_SEPARATOR . $file);
    });

    // Birleştirirken dizideki alanlara böşluk ve çizgiler ekleme fonksiyonu
    $bosluk_eklenecekler = ['-- Tablonun veri dökümü', '-- Tablo için tablo yapısı', 'SET SQL_MODE = \'NO_AUTO_VALUE_ON_ZERO\';', 'SET time_zone = \'+03:00\';'];
    function strposa($haystack, $needles)
    {
        foreach($needles as $needle) {
            if(strpos($haystack, $needle) !== false) {
                if($haystack == 'SET SQL_MODE = \'NO_AUTO_VALUE_ON_ZERO\';'){
                    return "\n".$haystack;
                }elseif($haystack == 'SET time_zone = \'+03:00\';'){
                    return $haystack."\n";
                }else{
                    return "\n-- ------------------------------------------------------\n".$haystack."\n-- ------------------------------------------------------";
                }
                
            }
        }

        return $haystack;
    }

    $uniqueLines = [];
    foreach ($sqlFiles as $file) {
        $filePath = $path . DIRECTORY_SEPARATOR . $file;
        $fileContent = getFileContent($filePath);
        
        $lines = explode("\n", $fileContent);
        foreach ($lines as $line) {
            $line = strposa($line, $bosluk_eklenecekler);
            // Footer SQL ifadelerini hariç tut
            if (!in_array($line, $uniqueLines) && !empty(trim($line)) && strpos($footerSQL, trim($line)) === false) {
                $uniqueLines[] = $line;
            }
        }
    }

    // Birleştirilmiş içeriğin sonuna footer SQL ifadelerini ekle
    $mergedContent = implode("\n", $uniqueLines) . "\n" . $footerSQL;

    if ($save) {
        // Birleştirilen içeriği UTF-8 olarak encode et ve dosyaya kaydet
        $mergedContent = mb_convert_encoding($mergedContent, 'UTF-8', 'auto');
        file_put_contents($outputFilePath, $mergedContent);
        echo "Dosya başarıyla birleştirilerek kaydedildi: $outputFileName";
    } else {
        // İçeriği ekrana yazdır (isteğe bağlı, sadece debug için)
        echo $mergedContent;
    }
} else {
    echo "Belirtilen yol geçerli bir dizin değil.";
}

?>
