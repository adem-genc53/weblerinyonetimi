<?php
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");
/*
 Folder Tree with PHP and jQuery.

 R. Savoul Pelister
 http://techlister.com

*/
/*
class treeview {

	private $files = []; 
	private $folder; 
	
	
	function __construct( $path, $genel_ayarlar ) {
		if(empty($path)){
			$yol = DIZINDIR; 
		}else{
			$yol = $path;
		}


  $dizinler_arr = json_decode($genel_ayarlar['haric_dizinler'], true);

		if( file_exists( $path)) {
			if( $path[ strlen( $path ) - 1 ] ==  '/' ){
				$this->folder = $path;
			}else{
				$this->folder = $path . '/';
			}

			$this->dir = opendir( $this->folder );
			while(( $file = readdir( $this->dir ) ) != false ){
				if(!in_array(basename($file), $dizinler_arr) && is_dir( $this->folder . $file ) && substr($file, 0, 1) != '.' && substr($file, 0, 2) != '..' ){
					$this->files[] = $file;
				}else if(is_file( $this->folder . $file ) && substr($file, 0, 1) != '.' && substr($file, 0, 2) != '..' ){
					$this->files[] = $file;
				}
			}
			closedir( $this->dir );
		}

	}



	function create_tree( ) {

    function showSize($size_in_bytes) {
        if ($size_in_bytes >= 1073741824) {
            $size_in_bytes = number_format($size_in_bytes / 1073741824, 2) . ' GB';
        } elseif ($size_in_bytes >= 1048576) {
            $size_in_bytes = number_format($size_in_bytes / 1048576, 2) . ' MB';
        } elseif ($size_in_bytes >= 1024) {
            $size_in_bytes = number_format($size_in_bytes / 1024, 2) . ' KB';
        } elseif ($size_in_bytes > 1) {
            $size_in_bytes = $size_in_bytes . ' Bayt';
        } elseif ($size_in_bytes == 1) {
            $size_in_bytes = $size_in_bytes . ' Bayt';
        } else {
            $size_in_bytes = '0 Bayt';
        }
        return $size_in_bytes;
    }

	// Dizin boşmu dolumu kontrol fonksiyonu
	function is_dir_empty($dir) {
		foreach (new DirectoryIterator($dir) as $fileInfo) {
			if($fileInfo->isDot()) continue;
			return false;
		}
	    return true;
	}

		if( count( $this->files ) > 0 ) {
			natcasesort( $this->files );
			$list = '<ul id="yerel" class="filetree" style="display: none;">';
			// Önce klasörleri gruplandıralım
			foreach( $this->files as $file ) {
				if( file_exists( $this->folder . $file ) && $file != '..' && is_dir( $this->folder . $file )) {
					if(is_dir_empty($this->folder . $file)){ // Dizin boşmu değilmi
						$list .= '<li class="folder yerel_collapsed"><a href="#" rel="' . htmlentities( $this->folder . $file ) . '/" adi="' . htmlentities( $file ) . '">' . htmlentities( $file ) . '<span style="float: right;color: black;padding-right: 10px;">'.showSize(filesize($this->folder . $file)).'</span></a></li>';
					}else{
						$list .= '<li class="folder_plus yerel_collapsed"><a href="#" rel="' . htmlentities( $this->folder . $file ) . '/" adi="' . htmlentities( $file ) . '">' . htmlentities( $file ) . '<span style="float: right;color: black;padding-right: 10px;">'.showSize(filesize($this->folder . $file)).'</span></a></li>';
					}
					
				}
			}

			// Sonra tüm dosyaları gruplandıralım
			foreach( $this->files as $file ) {
				if( file_exists( $this->folder . $file ) && $file != '.' && $file != '..' && !is_dir( $this->folder . $file )) {
					$ext = preg_replace('/^.*\./', '', $file);
					$list .= '<li class="file ext_' . $ext . '"><a href="#" rel="' . htmlentities( $this->folder . $file ) . '" adi="' . htmlentities( $file ) . '">' . htmlentities( $file ) . '<span style="float: right;color: black;padding-right: 10px;">'.showSize(filesize($this->folder . $file)).'</span></a></li>';
				}
			}

			$list .= '</ul>';	
			return $list;
		}
	}
}

$path = urldecode( $_REQUEST['dir'] );
$tree = new treeview( $path, $genel_ayarlar );
echo $tree->create_tree();
*/
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

	// Hariç tutulacak dizin isimleri
  	$dizinler_arr = json_decode($genel_ayarlar['haric_dizinler'], true);

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
    private function showSize(string $size_in_bytes) {
        if ($size_in_bytes >= 1073741824) {
            $size_in_bytes = number_format($size_in_bytes / 1073741824, 2) . ' GB';
        } elseif ($size_in_bytes >= 1048576) {
            $size_in_bytes = number_format($size_in_bytes / 1048576, 2) . ' MB';
        } elseif ($size_in_bytes >= 1024) {
            $size_in_bytes = number_format($size_in_bytes / 1024, 2) . ' KB';
        } elseif ($size_in_bytes > 1) {
            $size_in_bytes = $size_in_bytes . ' Bayt';
        } elseif ($size_in_bytes == 1) {
            $size_in_bytes = $size_in_bytes . ' Bayt';
        } else {
            $size_in_bytes = '0 Bayt';
        }
        return $size_in_bytes;
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

$path = urldecode($_POST['dir'] ?? DIZINDIR); // Default to empty string if not set
$tree = new TreeView($path, $genel_ayarlar);
echo $tree->createTree();
?>