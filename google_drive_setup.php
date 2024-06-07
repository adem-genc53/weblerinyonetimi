<?php 
if (!(PHP_VERSION_ID >= 80100)) {
    exit("<div style='font-weight: bold;font-size: 16px;text-align:center;font-family: Arial, Helvetica, sans-serif;'>Google Drive Kütüphanesi En Düşük \">= 8.1.0\" PHP sürümünü gerektirir. Siz " . PHP_VERSION . " Çalıştırıyorsunuz.</div>");
}

    if (!file_exists(AUTHCONFIGPATH)) {
        echo 'Hata: AuthConfig dosyası bulunamadı.';
        die('Hata: AuthConfig dosyası bulunamadı.');
    }
require_once __DIR__ . '/plugins/google_drive/vendor/autoload.php';

function getClient() {
    $client = new \Google\Client();
    $client->setApplicationName('Google Drive API PHP Quickstart');
    $client->setScopes(\Google\Service\Drive::DRIVE);
    $client->setAuthConfig(AUTHCONFIGPATH);
    $client->setAccessType('offline');
    return $client;
}

$client = getClient();
$service = new \Google\Service\Drive($client);

?>
