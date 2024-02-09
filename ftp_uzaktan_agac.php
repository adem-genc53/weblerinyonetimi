<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");
    if (!extension_loaded('ftp')) {
        exit("<div style='font-weight: bold;font-size: 16px;text-align:center;'>PHP.ini de FTP uzantısı etkinleştirilmedi.</div>");
    }
/*
 Folder Tree with PHP and jQuery.

 R. Savoul Pelister
 http://techlister.com

*/
/*
class treeview {

	private array $files = [];
    private array $ftpdeki_dizinler = [];
    private array $ftpdeki_dosyalar = [];
	private $folder;
	private $ftpsunucu;
	private $ftpusername;
	private $ftppass;
	private $ftp_dizinler;
	private $ftp_directory;
	private $ftp;
	private $login_result;
	public $genel_ayarlar;

	function __construct( $path, $genel_ayarlar ) {

    $this->ftpsunucu 	= $genel_ayarlar['sunucu'];
    $this->ftpusername 	= $genel_ayarlar['username'];
    $this->ftppass 		= $genel_ayarlar['password'];

        $this->ftp_dizinler = $path;

    $this->ftp = @ftp_ssl_connect($this->ftpsunucu)
					or die($this->ftpsunucu  . " sunucuya bağlanamadı");

    $this->login_result = ftp_login($this->ftp, $this->ftpusername, $this->ftppass);
    ftp_pasv($this->ftp, true);

if($this->ftp) {

    if($this->login_result){

    $this->files = ftp_mlsd($this->ftp, $this->ftp_dizinler ); 
        
############################################################################################################
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
###########################################################################################################

        if(is_array($this->files) && count($this->files)> 0) { 
            foreach($this->files as $file_list_arr) {
                if( !in_array($file_list_arr['type'], array("pdir","cdir") ) !== false){
                    if($file_list_arr['type'] == 'file'){
                        $this->ftpdeki_dosyalar[$file_list_arr['modify']][] = showSize(ftp_size($this->ftp, $this->ftp_dizinler.$file_list_arr['name']))."|".$file_list_arr['name'];
                    }elseif($file_list_arr['type'] == 'dir'){
                        $this->ftpdeki_dizinler[$file_list_arr['modify']][] = showSize(ftp_size($this->ftp, $this->ftp_dizinler.$file_list_arr['name']))."|".$file_list_arr['name'];
                    }

                }
            } // foreach($file_list as $file_list_arr) 
        }
                if(isset($this->ftpdeki_dosyalar) && count($this->ftpdeki_dosyalar)>0){
                krsort($this->ftpdeki_dosyalar);
                //echo '<pre>' . print_r($this->ftpdeki_dosyalar, true) . '</pre>';
                $this->ftpdeki_dosyalar = call_user_func_array('array_merge', $this->ftpdeki_dosyalar);
                }
                if(isset($this->ftpdeki_dizinler) && count($this->ftpdeki_dizinler)>0){
                    
                krsort($this->ftpdeki_dizinler);
                //echo '<pre>' . print_r($this->ftpdeki_dizinler, true) . '</pre>';
                $this->ftpdeki_dizinler = call_user_func_array('array_merge', $this->ftpdeki_dizinler);
                }
############################################################################################################
    } // if($login){
    else {
        $errors[] = "FTP giriş hatası oluştu!";
    }
     ftp_close($this->ftp);
}

	}


	function create_tree( ) {

			//echo '<pre>' . print_r($this->files, true) . '</pre>';
			$list = '<ul id="ftp_uzak" class="filetree" style="display: none;">';
			// Önce klasörleri gruplandıralım
			foreach( $this->ftpdeki_dizinler as $file ) {
				$dosya = explode("|", $file);
				if( $dosya[1] != '.' && $dosya[1] != '..') {
					$list .= '<li class="folder_plus collapsed"><a href="#" rel="' . htmlentities( $this->ftp_dizinler . $dosya[1] ) . '/">' . htmlentities( $dosya[1] ) . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
				}
			}

			// Sonra tüm dosyaları gruplandıralım
			foreach( $this->ftpdeki_dosyalar as $file ) {
				$dosya = explode("|", $file);
				if( $dosya[1] != '.ftpquota' ) {
					$ext = preg_replace('/^.*\./', '', $dosya[1]);
					$list .= '<li class="file ext_' . $ext . '"><a href="#" rel="' . htmlentities( $this->ftp_dizinler . $dosya[1] ) . '">' . htmlentities( $dosya[1] ) . '<span style="float: right;color: black;padding-right: 10px;">'.$dosya[0].'</span></a></li>';
				}
			}
			$list .= '</ul>';	
			return $list;
	}
}

//if(isset($_POST['dir']) ){
	$path = urldecode($_POST['dir'] ?? '');
	$tree = new treeview( $path, $genel_ayarlar );
	echo $tree->create_tree();
//}
*/
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class TreeView {

	private string $ftpsunucu;
	private string $ftpusername;
	private string $ftppass;
	private string $ftp_path;
	private string $ftp_directory;
	private $ftp;
	private string $login_result;
    private array $files = [];
    private array $ftpdeki_dosyalar = [];
    private array $ftpdeki_dizinler = [];
	private $genel_ayarlar;
    private string $folder;
    private $dir; // Resource or false

    function __construct(string $path, $genel_ayarlar) {

    $this->ftpsunucu 	= $genel_ayarlar['sunucu'];
    $this->ftpusername 	= $genel_ayarlar['username'];
    $this->ftppass 		= $genel_ayarlar['password'];

    $this->ftp_path = $path;

    $this->ftp = @ftp_ssl_connect($this->ftpsunucu)
					or die($this->ftpsunucu . " sunucuya bağlanamadı");

    $this->login_result = ftp_login($this->ftp, $this->ftpusername, $this->ftppass);
    ftp_pasv($this->ftp, true);

if($this->ftp) {

    if($this->login_result){

    $this->files = ftp_mlsd($this->ftp, $this->ftp_path ); 

        if(is_array($this->files) && count($this->files)> 0) { 
            foreach($this->files as $file_list_arr) {
                if( !in_array($file_list_arr['type'], array("pdir","cdir") ) !== false){
                    if($file_list_arr['type'] == 'file'){
                        $this->ftpdeki_dosyalar[$file_list_arr['modify']][] = $this->showSize(ftp_size($this->ftp, $this->ftp_path.$file_list_arr['name']))."|".$file_list_arr['name'];
                    }elseif($file_list_arr['type'] == 'dir'){
                        $bosmu = ftp_mlsd($this->ftp, $this->ftp_path."/".$file_list_arr['name'] );
                        $this->ftpdeki_dizinler[$file_list_arr['modify']][] = $this->showSize(ftp_size($this->ftp, $this->ftp_path.$file_list_arr['name']))."|".$file_list_arr['name']."|".count($bosmu);
                    }

                }
            } // foreach($file_list as $file_list_arr) 
        }
                if(isset($this->ftpdeki_dosyalar) && count($this->ftpdeki_dosyalar)>0){
                krsort($this->ftpdeki_dosyalar);
                //echo '<pre>' . print_r($this->ftpdeki_dosyalar, true) . '</pre>';
                $this->ftpdeki_dosyalar = call_user_func_array('array_merge', $this->ftpdeki_dosyalar);
                }
                if(isset($this->ftpdeki_dizinler) && count($this->ftpdeki_dizinler)>0){
                    
                krsort($this->ftpdeki_dizinler);
                //echo '<pre>' . print_r($this->ftpdeki_dizinler, true) . '</pre>';
                $this->ftpdeki_dizinler = call_user_func_array('array_merge', $this->ftpdeki_dizinler);
                }

ftp_close($this->ftp);
    } // if($login){
        
    else {
        $errors[] = "FTP giriş hatası oluştu!";
    }
    //ftp_close($this->ftp); 
}

    }

    function createTree(): string {
			//echo '<pre>' . print_r($this->files, true) . '</pre>';
			$list = '<ul id="ftp_uzak" class="filetree" style="display: none;">';
			// Önce klasörleri gruplandıralım
			foreach( $this->ftpdeki_dizinler as $file ) {
				list($size, $filename, $bosmu) = explode("|", $file);
				if( $filename != '.' && $filename != '..' && $bosmu > 2) {
					$list .= '<li class="folder_plus collapsed"><a href="#" rel="' . htmlentities( $this->ftp_path . $filename ) . '/" adi="' . htmlentities( $filename ) . '">' . htmlentities( $filename ) . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
				}else{
                    $list .= '<li class="folder collapsed"><a href="#" rel="' . htmlentities( $this->ftp_path . $filename ) . '/" adi="' . htmlentities( $filename ) . '">' . htmlentities( $filename ) . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
                }
			}

			// Sonra tüm dosyaları gruplandıralım
			foreach( $this->ftpdeki_dosyalar as $file ) {
				list($size, $filename) = explode("|", $file);
				if( $filename != '.ftpquota' ) {
					$ext = preg_replace('/^.*\./', '', $filename);
					$list .= '<li class="file ext_' . $ext . '"><a href="#" rel="' . htmlentities( $this->ftp_path . $filename ) . '" adi="' . htmlentities( $filename ) . '">' . htmlentities( $filename ) . '<span style="float: right;color: black;padding-right: 10px;">'.$size.'</span></a></li>';
				}
			}
			$list .= '</ul>';	
			return $list;
    }

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

}

$path = urldecode($_POST['dir'] ?? ''); // Default to empty string if not set
$tree = new TreeView($path, $genel_ayarlar);
echo $tree->createTree(); 

?>