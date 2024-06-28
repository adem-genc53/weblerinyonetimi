<?php
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

class LocaleTreeView {
    private array $files = [];
    private string $folder;
    private mixed $size_in_bytes;
    private mixed $genel_ayarlar;
    private mixed $dir;

    public function __construct(string $path, mixed $genel_ayarlar) {
        // Hariç tutulacak dizin isimleri
        $dizinler_arr = json_decode($genel_ayarlar['haric_dizinler'], true);

        if (file_exists($path)) {
            $this->folder = rtrim($path, '/') . '/';
            $this->dir = opendir($path);
            if ($this->dir !== false) {
                while (($file = readdir($this->dir)) !== false) {
                    if (!in_array(basename($file), $dizinler_arr)) {
                        $this->files[] = $file;
                    }
                }
                closedir($this->dir);
            }
        }
    }

    public function createTree(): string {
        if (count($this->files) > 2) {
            natcasesort($this->files);
            $list = '<ul id="yerel" class="filetree" style="display: none;">';

            // Önce klasörleri gruplandıralım
            foreach ($this->files as $file) {
                if ($this->isValidFile($file) && is_dir($this->folder . $file)) {
                    if ($this->is_dir_empty($this->folder . $file)) { // Dizin boşmu değilmi
                        $list .= '<li class="folder yerel_collapsed"><a href="#" rel="' . htmlentities($this->folder . $file) . '/" adi="' . htmlentities($file) . '">' . htmlentities($file) . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
                    } else {
                        $list .= '<li class="folder_plus yerel_collapsed"><a href="#" rel="' . htmlentities($this->folder . $file) . '/" adi="' . htmlentities($file) . '">' . htmlentities($file) . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
                    }
                }
            }

            // Sonra tüm dosyaları gruplandıralım
            foreach ($this->files as $file) {
                if ($this->isValidFile($file) && !is_dir($this->folder . $file)) {
                    $ext = preg_replace('/^.*\./', '', $file);
                    $list .= '<li class="file ext_' . $ext . '"><a href="#" rel="' . htmlentities($this->folder . $file) . '" adi="' . htmlentities($file) . '">' . htmlentities($file) . '<span style="float: right;color: black;padding-right: 10px;">' . $this->showSize(filesize($this->folder . $file)) . '</span></a></li>';
                }
            }

            $list .= '</ul>';
            return $list;
        }
        return '';
    }

    private function isValidFile(string $file): bool {
        return file_exists($this->folder . $file) && $file !== '.' && $file !== '..';
    }

    private function showSize(string $size_in_bytes): string {
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

    // Dizin boşmu dolumu kontrol fonksiyonu
    private function is_dir_empty(string $dir): bool {
        foreach (new DirectoryIterator($dir) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            return false;
        }
        return true;
    }
}

$path = urldecode($_POST['dir'] ?? DIZINDIR);
$tree = new LocaleTreeView($path, $genel_ayarlar);
echo $tree->createTree();

?>