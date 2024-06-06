<?php 
// Bismillahirrahmanirrahim

if (!function_exists('zipDataUsingZipArchive')) {
    function zipDataUsingZipArchive($source, $destination, $comment = '') {
        $zipsonuc = [];

        // Kaynak dizin veya dosyanın var olup olmadığını kontrol et
        if (!file_exists($source)) {
            $zipsonuc[] = "HATA: Kaynak dosya veya dizin mevcut değil: " . $source;
            return $zipsonuc;
        }

        // Hedef dizinin mevcut olup olmadığını kontrol et ve gerekirse oluştur
        $destinationDirRealPath = dirname($destination);
        if (!file_exists($destinationDirRealPath)) {
            if (!mkdir($destinationDirRealPath, 0777, true)) {
                $zipsonuc[] = "HATA: Hedef dizin oluşturulamadı: " . $destinationDirRealPath;
                return $zipsonuc;
            } else {
                //$zipsonuc[] = "Hedef dizin oluşturuldu: " . $destinationDirRealPath;
            }
        } else {
            //$zipsonuc[] = "Hedef dizin zaten mevcut: " . $destinationDirRealPath;
        }

        // Zip işlemi
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE) !== TRUE) {
            $zipsonuc[] = "HATA: Zip arşivi açılamadı: " . $destination;
            return $zipsonuc;
        } else {
            //$zipsonuc[] = "Zip arşivi başarıyla açıldı: " . $destination;
        }

        $sourceRealPath = realpath($source);
        if ($sourceRealPath === false) {
            $zipsonuc[] = "HATA: Gerçek kaynak yolu bulunamadı: " . $source;
            return $zipsonuc;
        }

        if (is_dir($sourceRealPath)) {
            //$zipsonuc[] = "Kaynak bir dizin: " . $sourceRealPath;
            // Kaynak bir dizinse, tüm dosyaları ve alt dizinleri ekleyin
            $sourceIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceRealPath), RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($sourceIterator as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    if ($filePath === false) {
                        $zipsonuc[] = "HATA: Dosya yolu bulunamadı.";
                        continue;
                    }
                    $relativePath = substr($filePath, strlen($sourceRealPath) + 1);
                    if ($zip->addFile($filePath, $relativePath)) {
                        //$zipsonuc[] = "Eklendi: " . $filePath . " -> " . $relativePath;
                    } else {
                        $zipsonuc[] = "HATA: Eklenemedi: " . $filePath;
                    }
                }
            }
        } elseif (is_file($sourceRealPath)) {
            // Kaynak bir dosyaysa, sadece bu dosyayı ekleyin
            //$zipsonuc[] = "Kaynak bir dosya: " . $sourceRealPath;
            if ($zip->addFile($sourceRealPath, basename($sourceRealPath))) {
                //$zipsonuc[] = "Eklendi: " . $sourceRealPath;
            } else {
                $zipsonuc[] = "HATA: Eklenemedi: " . $sourceRealPath;
            }
        } else {
            $zipsonuc[] = "HATA: Kaynak ne dosya ne de dizin: " . $sourceRealPath;
            return $zipsonuc;
        }

        // Yorum ekleme işlemi
        if ($comment !== '') {
            if ($zip->setArchiveComment($comment)) {
                //$zipsonuc[] = "Yorum eklendi: " . $comment;
            } else {
                $zipsonuc[] = "HATA: Yorum eklenemedi.";
            }
        }

        $zip->close();

        // Orijinal dosya adındaki tek tırnakları kaldır
        $destinationClean = str_replace("'", "", $destination);

        $zipsonuc[] = "Zip Arşivi Başarıyla Oluşturuldu";
        $zipsonuc["dosya_adi"] = $destinationClean;

        return $zipsonuc;
    } // function zipDataUsingZipArchive($source, $destination, $comment = '') {
} // if (!function_exists('zipDataUsingZipArchive')) {

######################################################################################################################################################

if (!function_exists('zipDataUsingSystem')) {
    function zipDataUsingSystem($source, $destination, $comment = '') {
        $zipsonuc = [];

        // Kaynak dizin veya dosyanın var olup olmadığını kontrol et
        if (!file_exists($source)) {
            $zipsonuc[] = "HATA: Kaynak dosya veya dizin mevcut değil: " . $source;
            return $zipsonuc;
        }

        // Dosya yollarını işlemek ve güvenli hale getirmek
        $sourceRealPath = realpath($source);
        if ($sourceRealPath === false) {
            $zipsonuc[] = "HATA: Kaynak yolu bulunamadı: " . $source;
            return $zipsonuc;
        }
        $destinationSafe = escapeshellarg($destination); // Sadece komut için güvenli hale getir

        // Hedef dizinin mevcut olup olmadığını kontrol et ve gerekirse oluştur
        $destinationDirRealPath = dirname($destination);
        if (!file_exists($destinationDirRealPath)) {
            if (!mkdir($destinationDirRealPath, 0777, true)) {
                $zipsonuc[] = "HATA: Hedef dizin oluşturulamadı: " . $destinationDirRealPath;
                return $zipsonuc;
            } else {
                //$zipsonuc[] = "Hedef dizin oluşturuldu: " . $destinationDirRealPath;
            }
        } else {
            //$zipsonuc[] = "Hedef dizin zaten mevcut: " . $destinationDirRealPath;
        }

        // zip komutunu oluştur
        if (is_dir($sourceRealPath)) {
            // Kaynak bir dizinse, içine girip içeriklerini ekle
            $command = "cd " . escapeshellarg($sourceRealPath) . " && zip -r $destinationSafe .";
        } elseif (is_file($sourceRealPath)) {
            // Kaynak bir dosyaysa, dosyayı ekle
            $sourceSafe = escapeshellarg($sourceRealPath);
            $command = "zip $destinationSafe $sourceSafe";
        } else {
            $zipsonuc[] = "HATA: Kaynak ne dosya ne de dizin: " . $sourceRealPath;
            return $zipsonuc;
        }

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        // Sonuçları kontrol et
        if ($return_var === 0) {
            $zipsonuc[] = "Zip arşivi başarıyla oluşturuldu";

            // Yorum ekleme işlemi
            if ($comment !== '') {
                $comment = escapeshellarg(iconv(mb_detect_encoding($comment, mb_detect_order(), true), "UTF-8", $comment));
                $commentCommand = "echo $comment | zip -z $destinationSafe";
                exec($commentCommand, $commentOutput, $commentReturnVar);

                if ($commentReturnVar === 0) {
                    //$zipsonuc[] = "Yorum başarıyla eklendi.";
                } else {
                    $zipsonuc[] = "HATA: Yorum eklenemedi.";
                }
            }

            // Orijinal dosya adındaki tek tırnakları kaldır
            $destinationClean = str_replace("'", "", $destination);
            $zipsonuc["dosya_adi"] = $destinationClean;
        } else {
            $zipsonuc[] = "HATA: Zip arşivi oluşturulamadı. Hata kodu: $return_var";
            $zipsonuc[] = "Çıktı: " . implode("\n", $output);
        }

        return $zipsonuc;
    } // function zipDataUsingSystem($source, $destination, $comment = '') {
} // if (!function_exists('zipDataUsingSystem')) {





?>