<?php 
// Bismillahirrahmanirrahim
header('Connection: Keep-Alive');
header('Keep-Alive: timeout=5, max=100');
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
include __DIR__ . '/google_drive_setup.php';

//echo '<pre>' . print_r($_POST, true) . '</pre>';
//exit;

if(file_exists("progress.json")){
    unlink("progress.json");
}
#############################################################################################################
    $starttime = microtime(true);
#############################################################################################################

ob_start();
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); // 7200 saniye 120 dakikadır, 3600 1 saat

class GoogleDriveDownloader {
    private $service;
    private $client;
    private $output = [];

    public function __construct($service, $client) {
        $this->service = $service;
        $this->client = $client;
    }

    private function listFiles($secilen_dizin, $folderId, $path = '') {
        $resultArray = [];
        $results = $this->service->files->listFiles([
            'q' => "'$folderId' in parents",
        ]);

        foreach ($results->getFiles() as $file) {
            $filePath = $path . '/' . $file->getName();

            if ($file->mimeType == 'application/vnd.google-apps.folder') {
                $resultArray = array_merge($resultArray, $this->listFiles($secilen_dizin, $file->getId(), $filePath));
                $resultArray[$file->getId()][$file->mimeType] = "/" . $secilen_dizin . $filePath;
            } else {
                $resultArray[$file->getId()][$file->mimeType] = "/" . $secilen_dizin . $filePath;
            }
        }
        return $resultArray;
    }

    public function downloadFile($dosyaninTamBoyutu, $yerel_hedef, $google_kaynak, $fileId) {
        if (pathinfo($google_kaynak, PATHINFO_EXTENSION)) {
            $file = $this->service->files->get($fileId, ['fields' => 'id,size']);

            if (intval($file->size) <= 0) {
                $this->output[] = "Dosya boyutu geçersiz.";
                return;
            }

            $fileSize = intval($file->size);
            $http = $this->client->authorize();
            $fp = fopen(rtrim($yerel_hedef, '/') . "/" . $google_kaynak, 'w');
            //$chunkSizeBytes = 10 * 1024 * 1024; // 10 MB

// Eğer dosya 50 MB'den küçükse, bölmeden tek parça olarak indir
if ($dosyaninTamBoyutu <= 50 * 1024 * 1024) { // 50 MB'den küçük dosyalar
    $chunkSizeBytes = $fileSize; // Tüm dosya tek parça olarak indir
} elseif ($dosyaninTamBoyutu <= 100 * 1024 * 1024) { // 50 MB - 100 MB arası dosyalar
    $chunkSizeBytes = 5 * 1024 * 1024; // 5 MB parça boyutu
} elseif ($dosyaninTamBoyutu <= 500 * 1024 * 1024) { // 100 MB - 500 MB arası dosyalar
    $chunkSizeBytes = 10 * 1024 * 1024; // 10 MB parça boyutu
} else { // 500 MB'dan büyük dosyalar
    $chunkSizeBytes = 20 * 1024 * 1024; // 20 MB parça boyutu
}

            $chunkStart = 0;

            while ($chunkStart < $fileSize) {
                $chunkEnd = min($chunkStart + $chunkSizeBytes, $fileSize - 1);
                $response = $http->request(
                    'GET',
                    sprintf('/drive/v3/files/%s', $fileId),
                    [
                        'query' => ['alt' => 'media'],
                        'headers' => [
                            'Range' => sprintf('bytes=%s-%s', $chunkStart, $chunkEnd)
                        ]
                    ]
                );
                $chunkStart = $chunkEnd + 1;

                if ($response->getStatusCode() != 206 && $response->getStatusCode() != 200) {
                    $this->output[] = "Dosya parçası indirilemedi. HTTP Durum Kodu: " . $response->getStatusCode();
                    fclose($fp);
                    return;
                }

                fwrite($fp, $response->getBody()->getContents());

                if($dosyaninTamBoyutu > 0){
                    // Sunucudaki dosyanın mevcut boyutunu hesaplayın ve JSON ile gönderin
                    $currentSize = filesize(rtrim($yerel_hedef, '/') . "/" . $google_kaynak);
                    file_put_contents('progress.json', json_encode(['size' => $currentSize]));
                }

            }

            fclose($fp);
            $this->output[] = "<br /><b>Yerel </b> " . $yerel_hedef . " <b>dizine</b><br />";
            $this->output[] = $google_kaynak . " <b>[İNDİRİLDİ]</b>";
        } else {
            $secilen_dizin = $google_kaynak;
            $googleden_secilen_dizin_arr[$fileId]['application/vnd.google-apps.folder'] = "/" . $google_kaynak;
            $filePathsArray = $this->listFiles($secilen_dizin, $fileId);
            $secilen_googleden_secilen_array = array_merge($googleden_secilen_dizin_arr, $filePathsArray);

            $this->output[] = "<br /><b>Yerel </b> " . $yerel_hedef . " <b>dizine</b><br />";

            foreach ($secilen_googleden_secilen_array as $id => $dosya_tipi_dosya_adi) {
                foreach ($dosya_tipi_dosya_adi as $dosya_tipi => $dosya_adi) {
                    if ($dosya_tipi == 'application/vnd.google-apps.folder') {
                        if (!file_exists($yerel_hedef . $dosya_adi)) {
                            mkdir($yerel_hedef . $dosya_adi, 0755, true);
                        }
                    }
                }
            }

            foreach ($secilen_googleden_secilen_array as $id => $dosya_tipi_dosya_adi) {
                foreach ($dosya_tipi_dosya_adi as $dosya_tipi => $dosya_adi) {
                    if ($dosya_tipi != 'application/vnd.google-apps.folder') {
                        $file = $this->service->files->get($id, ['fields' => 'id,size']);

                        if (intval($file->size) <= 0) {
                            $this->output[] = "Dosya boyutu geçersiz.";
                            continue;
                        }

                        $fileSize = intval($file->size);
                        $http = $this->client->authorize();
                        $fp = fopen($yerel_hedef . $dosya_adi, 'w');
                        //$chunkSizeBytes = 10 * 1024 * 1024;

// Eğer dosya 50 MB'den küçükse, bölmeden tek parça olarak indir
if ($dosyaninTamBoyutu <= 50 * 1024 * 1024) { // 50 MB'den küçük dosyalar
    $chunkSizeBytes = $fileSize; // Tüm dosya tek parça olarak indir
} elseif ($dosyaninTamBoyutu <= 100 * 1024 * 1024) { // 50 MB - 100 MB arası dosyalar
    $chunkSizeBytes = 5 * 1024 * 1024; // 5 MB parça boyutu
} elseif ($dosyaninTamBoyutu <= 500 * 1024 * 1024) { // 100 MB - 500 MB arası dosyalar
    $chunkSizeBytes = 10 * 1024 * 1024; // 10 MB parça boyutu
} else { // 500 MB'dan büyük dosyalar
    $chunkSizeBytes = 20 * 1024 * 1024; // 20 MB parça boyutu
}

                        $chunkStart = 0;

                        while ($chunkStart < $fileSize) {
                            $chunkEnd = min($chunkStart + $chunkSizeBytes, $fileSize - 1);
                            $response = $http->request(
                                'GET',
                                sprintf('/drive/v3/files/%s', $id),
                                [
                                    'query' => ['alt' => 'media'],
                                    'headers' => [
                                        'Range' => sprintf('bytes=%s-%s', $chunkStart, $chunkEnd)
                                    ]
                                ]
                            );
                            $chunkStart = $chunkEnd + 1;

                            if ($response->getStatusCode() != 206 && $response->getStatusCode() != 200) {
                                $this->output[] = "Dosya parçası indirilemedi. HTTP Durum Kodu: " . $response->getStatusCode();
                                fclose($fp);
                                return;
                            }

                            fwrite($fp, $response->getBody()->getContents());

                            if($dosyaninTamBoyutu > 0){
                                // Sunucudaki dosyanın mevcut boyutunu hesaplayın ve JSON ile gönderin
                                $currentSize = filesize(rtrim($yerel_hedef, '/') . "/" . $google_kaynak);
                                file_put_contents('progress.json', json_encode(['size' => $currentSize]));
                            }

                        }

                        fclose($fp);
                        $this->output[] = $dosya_adi . " <b>[İNDİRİLDİ]</b><br />";
                    }
                }
            }
        }
    }

    public function getOutput() {
        // Diziyi birleştirip tek bir string olarak döndür
        return implode(' ', $this->output);
    }

}

    if (isset($_POST['yerel_den_secilen_dosya']) && isset($_POST['google_drive_dan_secilen_dosya_id'])) {

        $yerel_hedef = rtrim($_POST['yerel_den_secilen_dosya'], '/');
        $google_kaynak = trim($_POST['google_drive_dan_secilen_dosya_adini_goster'], '/');
        $fileId = $_POST['google_drive_dan_secilen_dosya_id'];
        $dosyaninTamBoyutu = $_POST['google_drive_dan_secilen_dosya_boyutu'] ? $_POST['google_drive_dan_secilen_dosya_boyutu'] : 0;

        $downloader = new GoogleDriveDownloader($service, $client);
        $downloader->downloadFile($dosyaninTamBoyutu, $yerel_hedef, $google_kaynak, $fileId);

    } else {
        echo "Kaynak ve indirilecek dizin seçilmelidir";
    }

#############################################################################################################
    $endtime = microtime(true);
    $duration = $endtime - $starttime;
    $hours = floor($duration / 60 / 60);
    $minutes = floor(($duration / 60) - ($hours * 60));
    $seconds = floor($duration - ($hours * 60 * 60) - ($minutes * 60));
    $milliseconds = ($duration - floor($duration)) * 1000;
    $calisma_suresi = sprintf('%02d:%02d:%02d:%05.0f', $hours,$minutes,$seconds,$milliseconds);
#############################################################################################################

    //echo "<b>İndirme Süresi:</b> " . $calisma_suresi . "<br />";
    $output = $downloader -> getOutput();

    if (empty($output)) {
        echo "Dosya indirilemedi veya bir hata oluştu.";
    } else {
        echo $output;
    }

ob_flush();
flush();

/*
use Google\Client as GoogleClient;
use Google\Service\Drive;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\Utils;

class GoogleDriveDownloader {
    private $service;
    private $client;

    public function __construct($service, $client) {
        $this->service = $service;
        $this->client = $client;
    }

    private function listFiles($secilen_dizin, $folderId, $path = '') {
        $resultArray = [];
        $results = $this->service->files->listFiles([
            'q' => "'$folderId' in parents",
        ]);

        foreach ($results->getFiles() as $file) {
            $filePath = $path . '/' . $file->getName();

            if ($file->mimeType == 'application/vnd.google-apps.folder') {
                $resultArray = array_merge($resultArray, $this->listFiles($secilen_dizin, $file->getId(), $filePath));
                $resultArray[$file->getId()][$file->mimeType] = "/" . $secilen_dizin . $filePath;
            } else {
                $resultArray[$file->getId()][$file->mimeType] = "/" . $secilen_dizin . $filePath;
            }
        }
        return $resultArray;
    }

    public function downloadFile($yerel_hedef, $google_kaynak, $fileId) {
        if (pathinfo($google_kaynak, PATHINFO_EXTENSION)) {
            $file = $this->service->files->get($fileId, ['fields' => 'id,size']);

            if (intval($file->size) <= 0) {
                echo "Dosya boyutu geçersiz.";
                return;
            }

            $fileSize = intval($file->size);
            $http = new GuzzleClient(['base_uri' => 'https://www.googleapis.com']);
            $fp = fopen(rtrim($yerel_hedef, '/') . "/" . $google_kaynak, 'w');
            $chunkSizeBytes = 10 * 1024 * 1024; // 10 MB
            $promises = [];
            $chunkStart = 0;

            while ($chunkStart < $fileSize) {
                $chunkEnd = min($chunkStart + $chunkSizeBytes, $fileSize - 1);
                $range = sprintf('bytes=%s-%s', $chunkStart, $chunkEnd);
                $promises[] = $http->requestAsync('GET', sprintf('/drive/v3/files/%s', $fileId), [
                    'query' => ['alt' => 'media'],
                    /*'headers' => ['Range' => $range],*/
/*
'headers' => [
    'Range' => $range,
    'Authorization' => 'Bearer ' . $this->service->getClient()->getAccessToken()['access_token']
],
                ])->then(function($response) use ($fp) {
                    fwrite($fp, $response->getBody()->getContents());
                });
                $chunkStart = $chunkEnd + 1;
            }

            Utils::all($promises)->wait();
            fclose($fp);
            echo "<br /><b>Yerel </b> " . $yerel_hedef . " <b>dizine</b><br />";
            echo $google_kaynak . " <b>[İNDİRİLDİ]</b>";
        } else {
            $secilen_dizin = $google_kaynak;
            $googleden_secilen_dizin_arr[$fileId]['application/vnd.google-apps.folder'] = "/" . $google_kaynak;
            $filePathsArray = $this->listFiles($secilen_dizin, $fileId);
            $secilen_googleden_secilen_array = array_merge($googleden_secilen_dizin_arr, $filePathsArray);

            echo "<br /><b>Yerel </b> " . $yerel_hedef . " <b>dizine</b><br />";

            foreach ($secilen_googleden_secilen_array as $id => $dosya_tipi_dosya_adi) {
                foreach ($dosya_tipi_dosya_adi as $dosya_tipi => $dosya_adi) {
                    if ($dosya_tipi == 'application/vnd.google-apps.folder') {
                        if (!file_exists($yerel_hedef . $dosya_adi)) {
                            mkdir($yerel_hedef . $dosya_adi, 0755, true);
                        }
                    }
                }
            }

            foreach ($secilen_googleden_secilen_array as $id => $dosya_tipi_dosya_adi) {
                foreach ($dosya_tipi_dosya_adi as $dosya_tipi => $dosya_adi) {
                    if ($dosya_tipi != 'application/vnd.google-apps.folder') {
                        $file = $this->service->files->get($id, ['fields' => 'id,size']);

                        if (intval($file->size) <= 0) {
                            echo "Dosya boyutu geçersiz.";
                            continue;
                        }

                        $fileSize = intval($file->size);
                        $http = new GuzzleClient(['base_uri' => 'https://www.googleapis.com']);
                        $fp = fopen($yerel_hedef . $dosya_adi, 'w');
                        $chunkSizeBytes = 10 * 1024 * 1024;
                        $promises = [];
                        $chunkStart = 0;

                        while ($chunkStart < $fileSize) {
                            $chunkEnd = min($chunkStart + $chunkSizeBytes, $fileSize - 1);
                            $range = sprintf('bytes=%s-%s', $chunkStart, $chunkEnd);
                            $promises[] = $http->requestAsync('GET', sprintf('/drive/v3/files/%s', $id), [
                                'query' => ['alt' => 'media'],
                                'headers' => ['Range' => $range],
                            ])->then(function($response) use ($fp) {
                                fwrite($fp, $response->getBody()->getContents());
                            });
                            $chunkStart = $chunkEnd + 1;
                        }

                        Utils::all($promises)->wait();
                        fclose($fp);
                        echo $dosya_adi . " <b>[İNDİRİLDİ]</b><br />";
                    }
                }
            }
        }
    }
}

ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600);


if (isset($_POST['yerel_den_secilen_dosya']) && isset($_POST['google_drive_dan_secilen_dosya_id'])) {
    $yerel_hedef = rtrim($_POST['yerel_den_secilen_dosya'], '/');
    $google_kaynak = trim($_POST['google_drive_dan_secilen_dosya_adini_goster'], '/');
    $fileId = $_POST['google_drive_dan_secilen_dosya_id'];

    $downloader = new GoogleDriveDownloader($service, $client);
    $downloader->downloadFile($yerel_hedef, $google_kaynak, $fileId);
} else {
    echo "Kaynak ve indirilecek dizin seçilmelidir";
}
*/

?>
