<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;

    if (!extension_loaded('ftp')) {
        exit("<div style='font-weight: bold;font-size: 16px;text-align:center;'>PHP.ini de FTP uzantısı etkinleştirilmedi.</div>");
    }

class FtpTreeView {

    private string $ftpsunucu;
    private string $ftpusername;
    private string $ftppass;
    private string $ftppath;
    private array $files = [];
    private array $ftpdeki_dosyalar = [];
    private array $ftpdeki_dizinler = [];
    private $ftp;
    private string $login_result;
    private $genel_ayarlar;
    private $hash;
    private string $folder;
    private $dir; // Resource or false

    function __construct(string $tiklanan_path, $genel_ayarlar, $hash) {
        $this->ftpsunucu    = $genel_ayarlar['sunucu'];
        $this->ftpusername  = $hash->take($genel_ayarlar['username']);
        $this->ftppass      = $hash->take($genel_ayarlar['password']);
        $this->ftppath      = $genel_ayarlar['path'];
        $this->ftp_path     = $tiklanan_path;

        // Güvenli olmayan kısmı try-catch içine alalım
        try {
            $this->ftp = @ftp_ssl_connect($this->ftpsunucu);
            if (!$this->ftp) {
                throw new Exception($this->ftpsunucu . " sunucuya bağlanamadı");
            }

            $this->login_result = ftp_login($this->ftp, $this->ftpusername, $this->ftppass);
            ftp_pasv($this->ftp, true);

            if (!$this->login_result) {
                throw new Exception("FTP giriş hatası oluştu!");
            }

            $this->getFtpContent();

            ftp_close($this->ftp);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    private function ftp_mkdir_recursively($ftp, $dir) {
        $parts = explode('/', $dir);
        $path = '';
        foreach ($parts as $part) {
            if ($part != '') {
                $path .= '/' . $part;
                if (!@ftp_chdir($ftp, $path)) {
                    if (!ftp_mkdir($ftp, $path)) {
                        throw new Exception("Dizin oluşturulamadı: " . $path);
                    }
                }
            }
        }
    }

    private function getFtpContent() {

        $full_path = "/".trim($this->ftppath, '/');

        if ($this->ftp_path !== '/') {
            $full_path .= $this->ftp_path;
        }

            //file_put_contents(KOKYOLU.'error.log', date('Y-m-d H:i:s') . " - FTP Ağaç: " .$full_path. "\n", FILE_APPEND);

        // Dizinin var olup olmadığını kontrol et
        if (@ftp_chdir($this->ftp, $full_path)) {
            // Dizin mevcut, içeriğini al
            $directory_contents = ftp_mlsd($this->ftp, $full_path);
            if($directory_contents !== false){
                $this->files = $directory_contents;
            }else{
                $this->files = [];
            }
        } else {
            // Dizin mevcut değil, oluştur
            $this->ftp_mkdir_recursively($this->ftp, $full_path);
            // Dizin oluşturuldu, files boş dizi olarak ayarla
            $this->files = [];
        }

        if (is_array($this->files) && count($this->files) > 0) {
            foreach ($this->files as $file_list_arr) {
                if (!in_array($file_list_arr['type'], array("pdir", "cdir"))) {
                    if ($file_list_arr['type'] == 'file') {
                        $this->ftpdeki_dosyalar[$file_list_arr['modify']][] = $this->showSize(ftp_size($this->ftp, $this->ftppath ."/". $this->ftp_path . $file_list_arr['name'])) . "|" . $file_list_arr['name'];
                    } elseif ($file_list_arr['type'] == 'dir') {
                        $bosmu = ftp_mlsd($this->ftp, $this->ftppath ."/". $this->ftp_path . "/" . $file_list_arr['name']);
                        if ($bosmu === false) {
                            $bosmu = [];
                        }
                        $this->ftpdeki_dizinler[$file_list_arr['modify']][] = $this->showSize(ftp_size($this->ftp, $this->ftppath ."/". $this->ftp_path . $file_list_arr['name'])) . "|" . $file_list_arr['name'] . "|" . count($bosmu);
                    }
                }
            }

            if (isset($this->ftpdeki_dosyalar) && count($this->ftpdeki_dosyalar) > 0) {
                krsort($this->ftpdeki_dosyalar);
                $this->ftpdeki_dosyalar = call_user_func_array('array_merge', $this->ftpdeki_dosyalar);
            }
            if (isset($this->ftpdeki_dizinler) && count($this->ftpdeki_dizinler) > 0) {
                krsort($this->ftpdeki_dizinler);
                $this->ftpdeki_dizinler = call_user_func_array('array_merge', $this->ftpdeki_dizinler);
            }
        }
    }

    function createTree(): string {
        $list = '<ul id="ftp_uzak" class="filetree" style="display: none;">';
        // Önce klasörleri gruplandıralım
        foreach ($this->ftpdeki_dizinler as $file) {
            list($size, $filename, $bosmu) = explode("|", $file);
            if ($filename != '.' && $filename != '..' && $bosmu > 2) {
                $list .= '<li class="folder_plus collapsed"><a href="#" rel="' . htmlentities($this->ftp_path . $filename) . '/" adi="' . htmlentities($filename) . '">' . htmlentities($filename) . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
            } else {
                $list .= '<li class="folder collapsed"><a href="#" rel="' . htmlentities($this->ftp_path . $filename) . '/" adi="' . htmlentities($filename) . '">' . htmlentities($filename) . '<span style="float: right;color: black;padding-right: 10px;">4 KB</span></a></li>';
            }
        }

        // Sonra tüm dosyaları gruplandıralım
        foreach ($this->ftpdeki_dosyalar as $file) {
            list($size, $filename) = explode("|", $file);
            if ($filename != '.ftpquota') {
                $ext = preg_replace('/^.*\./', '', $filename);
                $list .= '<li class="file ext_' . $ext . '"><a href="#" rel="' . htmlentities($this->ftp_path . $filename) . '" adi="' . htmlentities($filename) . '">' . htmlentities($filename) . '<span style="float: right;color: black;padding-right: 10px;">' . $size . '</span></a></li>';
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
        } elseif ($size_in_bytes >= 1) {
            $size_in_bytes = $size_in_bytes . ' Bayt';
        } else {
            $size_in_bytes = '0 Bayt';
        }
        return $size_in_bytes;
    }
}

// Bu sayfa ajax ile kullanılıyor
// $genel_ayarlar değişkeni sınıf içinde FTP bağlantı bilgilerini almak içindir.
// $_POST['dir'] ajax ilk yüklemede $ftp_$path yolun içeriğini ister. Ağaç içinde tıklanan klasöre $ftp_$path yoluda eklenir
$tiklanan_path = urldecode($_POST['dir'] ?? ''); // Default to empty string if not set
$tree = new FtpTreeView($tiklanan_path, $genel_ayarlar, $hash);
echo $tree->createTree();

 

?>