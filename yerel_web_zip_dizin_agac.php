<?php
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
class TreeView {

    private array $files = [];
    private string $folder;
	private $size_in_bytes;
	private $genel_ayarlar;
    private $dir; // Resource or false

    function __construct(string $path, $genel_ayarlar) {

    $haric_dizinler_json = $genel_ayarlar['haric_dizinler'];

    // JSON verisi null veya boş mu kontrol edin
    if (is_null($haric_dizinler_json) || $haric_dizinler_json === '') {
        $dizinler_arr = []; // Boş dizi olarak ayarlayın
    } else {
        // JSON verisini decode edin
        $dizinler_arr = json_decode($haric_dizinler_json, true);
        
        // Decode işlemi başarısız olduysa boş dizi olarak ayarlayın
        if (json_last_error() !== JSON_ERROR_NONE) {
            $dizinler_arr = [];
        }
    }

        if (file_exists($path)) {
            $this->folder = rtrim($path, '/') . '/';
            $this->dir = opendir($path);
            if ($this->dir !== false) {
                while (($file = readdir($this->dir)) !== false) {
					if(!in_array(basename($file), $dizinler_arr))
                    	$this->files[] = $file;
                }
                closedir($this->dir);
            }
        }
    }

    function createTree(): string {
        if (count($this->files) > 2) {
            natcasesort($this->files);
            $list = '<ul id="yerel" class="filetree" style="display: none;">';

            // Group folders first
            foreach ($this->files as $file) {
                if ($this->isValidFile($file) && is_dir($this->folder . $file)) {
					if($this->is_dir_empty($this->folder . $file)){ // Dizin boşmu değilmi
						$list .= '<li class="folder yerel_collapsed"><a href="#" rel="' . htmlentities( $this->folder . $file ) . '/" adi="' . htmlentities( $file ) . '">' . htmlentities( $file ) . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
					}else{
						$list .= '<li class="folder_plus yerel_collapsed"><a href="#" rel="' . htmlentities( $this->folder . $file ) . '/" adi="' . htmlentities( $file ) . '">' . htmlentities( $file ) . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
					}
                }
            }

            // Group all files
            foreach ($this->files as $file) {
                if ($this->isValidFile($file) && !is_dir($this->folder . $file)) {
                    $ext = preg_replace('/^.*\./', '', $file);
                    $list .= '<li class="file ext_' . $ext . '"><a href="#" rel="' . htmlentities( $this->folder . $file ) . '" adi="' . htmlentities( $file ) . '">' . htmlentities( $file ) . '<span style="float: right;color: black;padding-right: 10px;">'.$this->showSize(filesize($this->folder . $file)).'</span></a></li>';
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
/*
	private function GetDirectorySize(string $path){
		$bytestotal = 0;
		$path = realpath($path);
		if($path!==false && $path!='' && file_exists($path)){
			foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
				$bytestotal += $object->getSize();
			}
		}
		return $this->showSize($bytestotal);
	}
*/
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
	
	// Dizin boşmu dolumu kontrol fonksiyonu
	private function is_dir_empty(string $dir): bool {
		foreach (new DirectoryIterator($dir) as $fileInfo) {
			if($fileInfo->isDot()) continue;
			return false;
		}
	    return true;
	}
}

$path = urldecode($_POST['dir'] ?? ZIPDIR); // Default to empty string if not set
$tree = new TreeView($path, $genel_ayarlar);
echo $tree->createTree();
?>