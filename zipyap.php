<?php 
// Bismillahirrahmanirrahim
//echo '<pre>' . print_r($_POST, true) . '</pre>';
if (!function_exists('zipDataUsingZipArchive')) {
    function zipDataUsingZipArchive($source, $destination, $comment = '') {
        $sonuc_cikti_mesaji = [];

        // Kaynak dizin veya dosyanın var olup olmadığını kontrol et
        if (!file_exists($source)) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">HATA: Kaynak dosya veya dizin mevcut değil: </span>' . $source
            ];
            return $sonuc_cikti_mesaji;
        }

        // Hedef dizinin mevcut olup olmadığını kontrol et ve gerekirse oluştur
        $destinationDirRealPath = dirname($destination);
        if (!file_exists($destinationDirRealPath)) {
            if (!mkdir($destinationDirRealPath, 0777, true)) {
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:red;">HATA: Hedef dizin oluşturulamadı: </span>' . $destinationDirRealPath
                ];
                return $sonuc_cikti_mesaji;
            } else {
/*
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:green;">Hedef dizin oluşturuldu:  </span>' . $destinationDirRealPath
                ];
*/
            }
        } else {
/*
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:green;">Hedef dizin zaten mevcut:  </span>' . $destinationDirRealPath
            ];
*/
        }

        // Zip işlemi
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE) !== TRUE) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">HATA: Zip arşivi açılamadı: </span>' . $destination
            ];
            return $sonuc_cikti_mesaji;
        } else {
/*
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:green;">Zip arşivi başarıyla açıldı:  </span>' . $destination
            ];
*/
        }

        $sourceRealPath = realpath($source);
        if ($sourceRealPath === false) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">HATA: Gerçek kaynak yolu bulunamadı: </span>' . $source
            ];
            return $sonuc_cikti_mesaji;
        }

        if (is_dir($sourceRealPath)) {
/*
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:green;">Kaynak bir dizin:  </span>' . $sourceRealPath
            ];
*/
            // Kaynak bir dizinse, tüm dosyaları ve alt dizinleri ekleyin
            $sourceIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceRealPath), RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($sourceIterator as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    if ($filePath === false) {
                        $sonuc_cikti_mesaji[] = [
                            'status' => 'error',
                            'message' => '<span style="color:red;">HATA: Dosya yolu bulunamadı.</span>'
                        ];
                        continue;
                    }
                    $relativePath = substr($filePath, strlen($sourceRealPath) + 1);
                    if ($zip->addFile($filePath, $relativePath)) {
                        /*
                        $sonuc_cikti_mesaji[] = [
                            'status' => 'error',
                            'message' => '<span style="color:green;">Eklendi: ' . $filePath . ' -> ' . $relativePath . '</span>'
                        ];
                        */
                    } else {
                        $sonuc_cikti_mesaji[] = [
                            'status' => 'error',
                            'message' => '<span style="color:red;">HATA: Eklenemedi: </span>' . $filePath
                        ];
                    }
                }
            }
        } elseif (is_file($sourceRealPath)) {
            // Kaynak bir dosyaysa, sadece bu dosyayı ekleyin
/*
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:green;">Kaynak bir dosya:  </span>' . $sourceRealPath
                ];
*/
            if ($zip->addFile($sourceRealPath, basename($sourceRealPath))) {
/*
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:green;">Eklendi:  </span>' . $sourceRealPath
                ];
*/
            } else {
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:red;">HATA: Eklenemedi: </span>' . $sourceRealPath
                ];
            }
        } else {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">HATA: Kaynak ne dosya ne de dizin: </span>' . $sourceRealPath
            ];
            return $sonuc_cikti_mesaji;
        }

        // Yorum ekleme işlemi
        if ($comment !== '') {
            if ($zip->setArchiveComment($comment)) {
/*
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:green;">Yorum eklendi:  </span>' . $comment
                ];
*/
            } else {
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:red;">HATA: Yorum eklenemedi.</span>'
                ];
            }
        }

        $zip->close();

        // Orijinal dosya adındaki tek tırnakları kaldır
        $destinationClean = str_replace("'", "", $destination);
            $sonuc_cikti_mesaji[] = [
                'status' => 'success',
                'message' => '<span style="color:green;">Zip Arşivi Başarıyla Oluşturuldu</span>'
            ];
            $sonuc_cikti_mesaji[] = [
                'status' => 'dosya_adi',
                'message' => $destinationClean
            ];
        return $sonuc_cikti_mesaji;
    } // function zipDataUsingZipArchive($source, $destination, $comment = '') {
} // if (!function_exists('zipDataUsingZipArchive')) {

######################################################################################################################################################

if (!function_exists('zipDataUsingSystem')) {
    function zipDataUsingSystem($source, $destination, $comment = '') {
        $sonuc_cikti_mesaji = [];

        // Kaynak dizin veya dosyanın var olup olmadığını kontrol et
        if (!file_exists($source)) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">HATA: Kaynak dosya veya dizin mevcut değil: </span>' . $source
            ];
            return $sonuc_cikti_mesaji;
        }

        // Dosya yollarını işlemek ve güvenli hale getirmek
        $sourceRealPath = realpath($source);
        if ($sourceRealPath === false) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">HATA: Kaynak yolu bulunamadı: </span>' . $source
            ];
            return $sonuc_cikti_mesaji;
        }
        $destinationSafe = escapeshellarg($destination); // Sadece komut için güvenli hale getir

        // Hedef dizinin mevcut olup olmadığını kontrol et ve gerekirse oluştur
        $destinationDirRealPath = dirname($destination);
        if (!file_exists($destinationDirRealPath)) {
            if (!mkdir($destinationDirRealPath, 0777, true)) {
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:red;">HATA: Hedef dizin oluşturulamadı: </span>' . $destinationDirRealPath
                ];
                return $sonuc_cikti_mesaji;
            } else {
/*
                $sonuc_cikti_mesaji[] = [
                    'status' => 'error',
                    'message' => '<span style="color:green;">Hedef dizin oluşturuldu: </span>' . $destinationDirRealPath
                ];
*/
            }
        } else {
/*
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:green;">Hedef dizin zaten mevcut: </span>' . $destinationDirRealPath
            ];
*/
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
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">HATA: Kaynak ne dosya ne de dizin: </span>' . $sourceRealPath
            ];
            return $sonuc_cikti_mesaji;
        }

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        // Sonuçları kontrol et
        if ($return_var === 0) {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:green;">Zip arşivi başarıyla oluşturuldu</span>'
            ];

            // Yorum ekleme işlemi
            if ($comment !== '') {
                $comment = escapeshellarg(iconv(mb_detect_encoding($comment, mb_detect_order(), true), "UTF-8", $comment));
                $commentCommand = "echo $comment | zip -z $destinationSafe";
                exec($commentCommand, $commentOutput, $commentReturnVar);

                if ($commentReturnVar === 0) {
                /*
                    $sonuc_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => '<span style="color:green;">Yorum başarıyla eklendi.</span>'
                    ];
                    */
                } else {
                    $sonuc_cikti_mesaji[] = [
                        'status' => 'error',
                        'message' => '<span style="color:red;">HATA: Yorum eklenemedi.</span>'
                    ];
                }
            }

            // Orijinal dosya adındaki tek tırnakları kaldır
            $destinationClean = str_replace("'", "", $destination);
                $sonuc_cikti_mesaji[] = [
                    'status' => 'dosya_adi',
                    'message' => $destinationClean
                ];
        } else {
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">HATA: Zip arşivi oluşturulamadı. Hata kodu: </span>' . $return_var
            ];
            $sonuc_cikti_mesaji[] = [
                'status' => 'error',
                'message' => '<span style="color:red;">Çıktı: </span>' . implode("\n", $output)
            ];
        }

        return $sonuc_cikti_mesaji;
    } // function zipDataUsingSystem($source, $destination, $comment = '') {
} // if (!function_exists('zipDataUsingSystem')) {


/*
Array
(
    [zipyap] => 1
    [dizinadi] => bulut
    [ziparsivadi] => bulut.zip
    [dizindir] => D:/SUNUCU/www/projelerim/
)
*/
if(isset($_POST['zipyap']) && $_POST['zipyap'] == 1){

function dosyaAdiniAl($dosyaAdi) {
    // Dosya adında .zip uzantısı var mı kontrol et
    if (substr($dosyaAdi, -4) === '.zip') {
        // .zip uzantısını kaldır
        return substr($dosyaAdi, 0, -4);
    }
    // .zip uzantısı yoksa, dosya adını olduğu gibi döndür
    return $dosyaAdi;
}

require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/turkcegunler.php';

    $dosya_tarihi               = date('Y-m-d-H-i-s'); // date('Y-m-d-H-i-s', $row['sonraki_calisma']);
    $secilen_yedekleme          = $_POST['dizinadi'];
    $secilen_yedekleme_oneki    = dosyaAdiniAl($_POST['ziparsivadi']);

    $source = DIZINDIR . $secilen_yedekleme;
    $destination = ZIPDIR . $secilen_yedekleme_oneki . "-" . $dosya_tarihi . '.zip';
    $comment = $secilen_yedekleme;

    if(isset($genel_ayarlar['zip_tercihi']) && $genel_ayarlar['zip_tercihi'] == 1){
        $zipyap_sonucu = zipDataUsingZipArchive($source, $destination, $comment);
    } else if(isset($genel_ayarlar['zip_tercihi']) && $genel_ayarlar['zip_tercihi'] == 2){
        $zipyap_sonucu = zipDataUsingSystem($source, $destination, $comment);
    }else{
        $zipyap_sonucu = "";
    }

foreach ($zipyap_sonucu AS $item) {
    if (isset($item['status']) && ( $item['status'] === 'success' || $item['status'] === 'error') ) {
        echo $item['message'] . "<br>";
    } else if (isset($item['status']) && $item['status'] === 'dosya_adi') {
        echo "<b>Dosya Adı:</b> " . basename($item['message']) . "<br>";
    }else{

    }
}
/*
    foreach($zipyap_sonucu AS $key => $value){
        if($key == 'dosya_adi'){
            echo "<b>Dosya Adı:</b> " . basename($value) . "<br />";
        }else{
            echo $value . "<br />";
        }
    }
*/
    //echo '<pre>' . print_r($zipyap_sonucu, true) . '</pre>';
}

?>