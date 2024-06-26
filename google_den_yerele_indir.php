<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
include __DIR__ . '/google_drive_setup.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

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
            $http = $this->client->authorize();
            $fp = fopen(rtrim($yerel_hedef, '/') . "/" . $google_kaynak, 'w');
            $chunkSizeBytes = 10 * 1024 * 1024; // 10 MB
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
                fwrite($fp, $response->getBody()->getContents());
            }

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
                        $http = $this->client->authorize();
                        $fp = fopen($yerel_hedef . $dosya_adi, 'w');
                        $chunkSizeBytes = 10 * 1024 * 1024;
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
                            fwrite($fp, $response->getBody()->getContents());
                        }

                        fclose($fp);
                        echo $dosya_adi . " <b>[İNDİRİLDİ]</b><br />";
                    }
                }
            }
        }
    }
}

ob_start();
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(3600); // 7200 saniye 120 dakikadır, 3600 1 saat

if (isset($_POST['yerel_den_secilen_dosya']) && isset($_POST['google_drive_dan_secilen_dosya_id'])) {
    $yerel_hedef = rtrim($_POST['yerel_den_secilen_dosya'], '/');
    $google_kaynak = trim($_POST['google_drive_dan_secilen_dosya_adini_goster'], '/');
    $fileId = $_POST['google_drive_dan_secilen_dosya_id'];

    $downloader = new GoogleDriveDownloader($service, $client);
    $downloader->downloadFile($yerel_hedef, $google_kaynak, $fileId);
} else {
    echo "Kaynak ve indirilecek dizin seçilmelidir";
}
?>
