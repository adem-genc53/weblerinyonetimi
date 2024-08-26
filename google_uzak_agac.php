<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
include __DIR__ . '/google_drive_setup.php';


class GoogleDriveTreeView {
    private $service;
    private $folderId;

    public function __construct($service, $folderId = 'root') {
        $this->service = $service;
        $this->folderId = $folderId;
        ob_start();
        ini_set('memory_limit', '-1');
        ignore_user_abort(true);
        set_time_limit(3600); // 1 saat
    }

    private function showSize(string $bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    private function fetchFiles($query) {
        $pageToken = null;
        $files = [];

        do {
            $response = $this->service->files->listFiles([
                'q' => $query,
                'pageToken' => $pageToken,
                'fields' => 'nextPageToken, files(id, name, mimeType, size)',
                'orderBy' => 'name',
            ]);

            $files = array_merge($files, $response->files);
            $pageToken = $response->nextPageToken;
        } while ($pageToken);

        return $files;
    }

    public function getFilesAndFolders() {
        $query = "'{$this->folderId}' in parents";
        $files = $this->fetchFiles($query);

        $drive_dizinler_arr = [];
        $drive_dosyalar_arr = [];

        foreach ($files as $file) {
            if ($file->mimeType == 'application/vnd.google-apps.folder') {
                $drive_dizinler_arr[$file->id][$this->showSize($file->size ?? 0)] = $file->name;
            } else {
                $drive_dosyalar_arr[$file->id][$this->showSize($file->size ?? 0)] = $file->name;
            }
        }

        return [$drive_dizinler_arr, $drive_dosyalar_arr];
    }

    public function emptyDir($dirid) {
        $query = "'$dirid' in parents";
        $files = $this->fetchFiles($query);
        return count($files);
    }

    public function generateList() {
        list($drive_dizinler_arr, $drive_dosyalar_arr) = $this->getFilesAndFolders();
        $list = '<ul id="uzak" class="filetree" style="display: none;">';

        foreach ($drive_dizinler_arr as $id => $arr_devam) {
            foreach ($arr_devam as $boyutu => $dizin_adi) {
                if ($this->emptyDir($id) == '0') {
                    $list .= '<li class="folder collapsed"><a href="#" rel="' . $id . '" adi="' . $dizin_adi . '">' . $dizin_adi . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
                } else {
                    $list .= '<li class="folder_plus collapsed"><a href="#" rel="' . $id . '" adi="' . $dizin_adi . '">' . $dizin_adi . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
                }
            }
        }

        foreach ($drive_dosyalar_arr as $id => $devam_arr) {
            foreach ($devam_arr as $boyutu => $dosya_adi) {
                $ext = preg_replace('/^.*\./', '', $dosya_adi);
                $list .= '<li class="file ext_' . $ext . '"><a href="#" rel="' . $id . '" adi="' . $dosya_adi . '">' . $dosya_adi . '<span style="float: right;color: black;padding-right: 10px;">' . $boyutu . '</span></a></li>';
            }
        }

        $list .= '</ul>';
        return $list;
    }
}

$folderId = isset($_POST['dir']) ? $_POST['dir'] : 'root';
$driveManager = new GoogleDriveTreeView($service, $folderId);
echo $driveManager->generateList();


?>