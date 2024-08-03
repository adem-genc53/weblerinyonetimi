<?php 
if (!(PHP_VERSION_ID >= 80100)) {
    exit("<div style='font-weight: bold;font-size: 16px;text-align:center;font-family: Arial, Helvetica, sans-serif;'>Google Drive Kütüphanesi En Düşük \">= 8.1.0\" PHP sürümünü gerektirir. Siz " . PHP_VERSION . " Çalıştırıyorsunuz.</div>");
}

if (!file_exists(AUTHCONFIGPATH)) {
    echo 'Hata: AuthConfig dosyası bulunamadı.';
    die('Hata: AuthConfig dosyası bulunamadı.');
}

require_once __DIR__ . '/plugins/google_drive/vendor/autoload.php';


// Özel hata ve özel durum işleyicileri
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // Bu hata raporlanmamalı
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($exception) {
    echo "Bağlantı başarısız, bilgileri kontrol edin: " . $exception->getMessage();
});


function getClient() {
    try {
        $client = new \Google\Client();
        $client->setApplicationName('Google Drive API PHP Quickstart');
        $client->setScopes(\Google\Service\Drive::DRIVE);
        
        // Doğru dosya yolunu ve içeriğini kontrol et
        if (!file_exists(AUTHCONFIGPATH)) {
            throw new Exception("Config file not found: " . AUTHCONFIGPATH);
        }

        // Hataları bastırmak için @ operatörünü kullanarak setAuthConfig çağrısı yapıyoruz
        @$client->setAuthConfig(AUTHCONFIGPATH);
        $client->setAccessType('offline');
        return $client;
    } catch (\Google\Exception $e) {
        echo "Bağlantı başarısız, bilgileri kontrol edin: " . $e->getMessage();
        return null;
    } catch (Exception $e) {
        echo "Beklenmeyen bir hata oluştu: " . $e->getMessage();
        return null;
    }
}

// Normal çalışma
try {
    $client = getClient();
    if ($client) {
        try {
            $service = new \Google\Service\Drive($client);
        } catch (\Google\Exception $e) {
            echo "Google Drive hizmetine bağlanırken bir hata oluştu: " . $e->getMessage();
        } catch (Exception $e) {
            echo "Beklenmeyen bir hata oluştu: " . $e->getMessage();
        }
    } else {
        echo "Google Client oluşturulamadı.";
    }
} catch (Exception $e) {
    echo "Beklenmeyen bir hata oluştu (dış try-catch): " . $e->getMessage();
}


?>
