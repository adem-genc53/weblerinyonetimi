<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
include __DIR__ . '/google_drive_setup.php';
require_once('check-login.php');
require_once("includes/turkcegunler.php");


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

    public function showSize($size_in_bytes) {
        if ($size_in_bytes >= 1073741824) {
            $size_in_bytes = number_format($size_in_bytes / 1073741824, 2) . ' GB';
        } elseif ($size_in_bytes >= 1048576) {
            $size_in_bytes = number_format($size_in_bytes / 1048576, 2) . ' MB';
        } elseif ($size_in_bytes >= 1024) {
            $size_in_bytes = number_format($size_in_bytes / 1024, 2) . ' KB';
        } elseif ($size_in_bytes >= 1) {
            $size_in_bytes = $size_in_bytes . ' Bayt';
        } else {
            $size_in_bytes = '0 Bayt';
        }
        return $size_in_bytes;
    }

    public function getFilesAndFolders() {
        $results = $this->service->files->listFiles(array(
            'q' => "'{$this->folderId}' in parents",
            'orderBy' => 'name'
        ));

        $drive_dizinler_arr = [];
        $drive_dosyalar_arr = [];

        foreach ($results->getFiles() as $file) {
            $optpParams = array('fields' => "size");
            $response = $this->service->files->get($file->getId(), $optpParams);
            if ($file->getMimeType() == 'application/vnd.google-apps.folder') {
                $drive_dizinler_arr[$file->getId()][$this->showSize($response->size)] = $file->getName();
            } else {
                $drive_dosyalar_arr[$file->getId()][$this->showSize($response->size)] = $file->getName();
            }
        }

        return [$drive_dizinler_arr, $drive_dosyalar_arr];
    }

    public function emptyDir($dirid) {
        $results = $this->service->files->listFiles(array(
            'q' => "'$dirid' in parents"
        ));
        return count($results->getFiles());
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