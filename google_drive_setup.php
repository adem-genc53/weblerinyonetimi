<?php 
if (!(PHP_VERSION_ID >= 80100)) {
    exit("<div style='font-weight: bold;font-size: 16px;text-align:center;font-family: Arial, Helvetica, sans-serif;'>Google Drive Kütüphanesi En Düşük \">= 8.1.0\" PHP sürümünü gerektirir. Siz " . PHP_VERSION . " Çalıştırıyorsunuz.</div>");
}

if (!file_exists(AUTHCONFIGPATH)) {
    echo 'Hata: AuthConfig dosyası bulunamadı.';
    file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - Hata: AuthConfig dosyası bulunamadı.' . "\n", FILE_APPEND);
    exit;
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
    echo "Bağlantı başarısız, bilgileri kontrol edin 1: " . $exception->getMessage();
    file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - Bağlantı başarısız, bilgileri kontrol edin 1: ' . $exception->getMessage() . "\n", FILE_APPEND);
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
        echo "Bağlantı başarısız, bilgileri kontrol edin 2: " . $e->getMessage();
        file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - Bağlantı başarısız, bilgileri kontrol edin 2: ' .  $e->getMessage() . "\n", FILE_APPEND);
        return null;
    } catch (Exception $e) {
        echo "Beklenmeyen bir hata oluştu: " . $e->getMessage();
        file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - Beklenmeyen bir hata oluştu: ' .  $e->getMessage() . "\n", FILE_APPEND);
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
            file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - Google Drive hizmetine bağlanırken bir hata oluştu: ' .  $e->getMessage() . "\n", FILE_APPEND);
        } catch (Exception $e) {
            echo "Beklenmeyen bir hata oluştu: " . $e->getMessage();
            file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - Beklenmeyen bir hata oluştu: ' .  $e->getMessage() . "\n", FILE_APPEND);
        }
    } else {
        echo "Google Client oluşturulamadı.";
        file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - Google Client oluşturulamadı.' . "\n", FILE_APPEND);
    }
} catch (Exception $e) {
    echo "Beklenmeyen bir hata oluştu (dış try-catch): " . $e->getMessage();
    file_put_contents(KOKYOLU . 'error.log', date('Y-m-d H:i:s') . ' - ' . basename(__FILE__) . ' - Beklenmeyen bir hata oluştu (dış try-catch): ' .  $e->getMessage() . "\n", FILE_APPEND);
}


?>
