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
        $file = gzopen($filePath, 'r');
        $fileContent = '';
        while (!gzeof($file)) {
            $fileContent .= gzgets($file, 4096);
        }
        gzclose($file);
    } else {
        // .sql dosyasını aç ve içeriğini oku
        $file = fopen($filePath, 'r');
        $fileContent = '';
        while (!feof($file)) {
            $fileContent .= fgets($file, 4096);
        }
        fclose($file);
    }
    return $fileContent;
}
/*
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
*/

// Sonunda eklemek istediğimiz SQL ifadeleri
$footerSQL = "COMMIT;\n
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";

// Belirli satırlara boşluk ve çizgi ekleme fonksiyonu
function addLinesAndSpaces($line, $markers) {
    foreach ($markers as $marker) {
        if (strpos($line, $marker) !== false) {
            if ($line == 'SET SQL_MODE = \'NO_AUTO_VALUE_ON_ZERO\';') {
                return "\n" . $line;
            } elseif ($line == 'SET time_zone = \'+03:00\';') {
                return $line . "\n";
            } elseif ($line == '-- Tablolar:'){
                return false;
            } elseif ($line == '-- Tablonun veri dökümü'){
                return "-- ------------------------------------------------------\n" . $line . "\n-- ------------------------------------------------------";
            } else {
                return "\n-- ------------------------------------------------------\n" . $line . "\n-- ------------------------------------------------------";
            }
        }
    }
    return $line;
}

if (is_dir($path)) {
    // Dizin içindeki tüm dosyaları al
    $files = array_diff(scandir($path), array('..', '.'));

    // Sadece .sql ve .sql.gz dosyalarını filtrele
    $sqlFiles = array_filter($files, function ($file) use ($path) {
        return preg_match('/\.sql(\.gz)?$/i', $file) && is_file($path . DIRECTORY_SEPARATOR . $file);
    });

    $uniqueLines = [];
    $markers = ['-- Tablolar:', '-- Tablo Adı:', '-- Tablonun veri dökümü', '-- Tablo için tablo yapısı', '-- Tablo için Tetikleyiciler', 'SET SQL_MODE = \'NO_AUTO_VALUE_ON_ZERO\';', 'SET time_zone = \'+03:00\';'];

    // Hash set olarak kullanmak için array
    $uniqueLinesHashSet = [];

    foreach ($sqlFiles as $file) {
        $filePath = $path . DIRECTORY_SEPARATOR . $file;
        $fileContent = getFileContent($filePath);

        $lines = explode("\n", $fileContent);
        $isTableDefinition = false;

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // Tablo yapısı tanımlamalarını kontrol et
            if (strpos($trimmedLine, 'DROP TABLE IF EXISTS') === 0 || strpos($trimmedLine, 'DROP TRIGGER IF EXISTS') === 0) {
                $isTableDefinition = true;
                $tableDefinition = $line . "\n";
                continue;
            }

            // Tablo yapısına benzersiz uygulamayı hariç tut
            if ($isTableDefinition) {
                $tableDefinition .= $line . "\n";
                if (strpos($trimmedLine, ') ENGINE=') !== false || strpos($trimmedLine, 'DELIMITER ;') !== false) {
                    $isTableDefinition = false;
                    if (!isset($uniqueLinesHashSet[$tableDefinition])) {
                        $uniqueLinesHashSet[$tableDefinition] = true;
                        $uniqueLines[] = $tableDefinition;
                    }
                }
                continue;
            } else {
                // Array ile belirlenen alanlara boşluk ve çizgi ekleme fonksiyon çağırma kodu
                $line = addLinesAndSpaces($line, $markers);

                // INSERT INTO satırlara benzersiz uygulamayı hariç tut
                if (strpos($trimmedLine, 'INSERT INTO') === 0) {
                    //if (!isset($uniqueLinesHashSet[$line])) // ID si olmayan benzer satırleri eksiltir
                        $uniqueLinesHashSet[$line] = true;
                        $uniqueLines[] = $line;
                } else {

                    // Diğer satırları benzersizlik kontrolü ile ekle
                    if (!isset($uniqueLinesHashSet[$line]) && !empty($trimmedLine) && strpos($footerSQL, $trimmedLine) === false) {
                        $uniqueLinesHashSet[$line] = true;
                        $uniqueLines[] = $line;
                    }
                }
            }
        }
    }

    $bul = "-- ------------------------------------------------------\n\n-- ------------------------------------------------------";
    $degistir = "-- ------------------------------------------------------\n-- ------------------------------------------------------";

    // Birleştirilmiş içeriğin sonuna footer SQL ifadelerini ekle
    $mergedContent = implode("\n", $uniqueLines) . "\n" . $footerSQL;
    $mergedContent = str_replace($bul, $degistir, $mergedContent);

    if ($save) {
        // Birleştirilen içeriği UTF-8 olarak encode et ve dosyaya kaydet
        $mergedContent = mb_convert_encoding($mergedContent, 'UTF-8', 'auto');
        file_put_contents($outputFilePath, $mergedContent);
        echo "Dosya başarıyla birleştirilerek kaydedildi: $outputFileName";
    } else {
        // İçeriği ekrana yazdır (isteğe bağlı, sadece debug için)
        //echo $mergedContent;
    }
} else {
    echo "Belirtilen yol geçerli bir dizin değil.";
}

?>
