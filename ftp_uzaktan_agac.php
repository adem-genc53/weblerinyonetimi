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
    private string $ftp_path;
    private string $ftp_mode;
    private string $ftp_ssl;
    private array $files = [];
    private array $ftpdeki_dosyalar = [];
    private array $ftpdeki_dizinler = [];
    private $ftp_connect;
    private $genel_ayarlar;
    private $hash;
    private $dir; // Resource or false

    function __construct(string $tiklanan_path, $genel_ayarlar, $hash) {
        $this->ftpsunucu    = $genel_ayarlar['sunucu'] ?? '';
        $this->ftpusername  = !empty($genel_ayarlar['username']) ? $hash->take($genel_ayarlar['username']) : '';
        $this->ftppass      = !empty($genel_ayarlar['password']) ? $hash->take($genel_ayarlar['password']) : '';
        $this->ftppath      = $genel_ayarlar['path'];
        $this->ftp_path     = $tiklanan_path;

        // FTP Bağlantı türü ve modunu ayarlardan al
        $this->ftp_mode = $genel_ayarlar['ftp_mode']; // 'active' veya 'passive'
        $this->ftp_ssl = $genel_ayarlar['ftp_ssl']; // true veya false

        // FTP bağlantısı kur
        if ($this->ftp_ssl) {
            // SSL bağlantısı kur ve oturumu aç
            $this->ftp_connect = ftp_ssl_connect($this->ftpsunucu);
            if (!$this->ftp_connect) {
                die("FTP SSL bağlantısı kurulamadı.");
            }
        } else {
            // Standart bağlantı kur ve oturumu aç
            $this->ftp_connect = ftp_connect($this->ftpsunucu);
            if (!$this->ftp_connect) {
                die("FTP Standart bağlantısı kurulamadı.");
            }
        }

        // Zaman aşımını ayarla (örneğin, 120 saniye)
        ftp_set_option($this->ftp_connect, FTP_TIMEOUT_SEC, 120);

        if ($this->ftp_connect) {
            ftp_login($this->ftp_connect, $this->ftpusername, $this->ftppass);

            // Pasif/Aktif mod ayarı
            if ($this->ftp_mode) {
                ftp_pasv($this->ftp_connect, true);
            } else {
                ftp_pasv($this->ftp_connect, false);
            }
            $this->getFtpContent();
        }else{
            ftp_close($this->ftp_connect);
            die("FTP oturumu açılamadı.");
        }
    }

    private function ftp_mkdir_recursively($ftp_connect, $dir) {
        $parts = explode('/', $dir);
        $path = '';
        foreach ($parts as $part) {
            if ($part != '') {
                $path .= '/' . $part;
                if (!@ftp_chdir($ftp_connect, $path)) {
                    if (!ftp_mkdir($ftp_connect, $path)) {
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

        // Dizinin var olup olmadığını kontrol et
        if (@ftp_chdir($this->ftp_connect, $full_path)) {
            // Dizin mevcut, içeriğini al
            $directory_contents = ftp_mlsd($this->ftp_connect, $full_path);
            if($directory_contents !== false){
                $this->files = $directory_contents;
            }else{
                $this->files = [];
            }
        } else {
            // Dizin mevcut değil, oluştur
            $this->ftp_mkdir_recursively($this->ftp_connect, $full_path);
            // Dizin oluşturuldu, files boş dizi olarak ayarla
            $this->files = [];
        }

        if (is_array($this->files) && count($this->files) > 0) {
            foreach ($this->files as $file_list_arr) {
                // Anahtarları küçük harfe dönüştür
                $file_list_arr = array_change_key_case($file_list_arr, CASE_LOWER);
                if (!in_array($file_list_arr['type'], array("pdir", "cdir"))) {
                    if ($file_list_arr['type'] == 'file') {
                        $this->ftpdeki_dosyalar[$file_list_arr['modify']][] = $this->showSize(ftp_size($this->ftp_connect, $this->ftppath ."/". $this->ftp_path . $file_list_arr['name'])) . "|" . $file_list_arr['name'];
                    } elseif ($file_list_arr['type'] == 'dir') {
                        $bosmu = ftp_mlsd($this->ftp_connect, $this->ftppath ."/". $this->ftp_path . "/" . $file_list_arr['name']);
                        if ($bosmu === false) {
                            $bosmu = [];
                        }
                        $this->ftpdeki_dizinler[$file_list_arr['modify']][] = $this->showSize(ftp_size($this->ftp_connect, $this->ftppath ."/". $this->ftp_path . $file_list_arr['name'])) . "|" . $file_list_arr['name'] . "|" . count($bosmu);
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
}

// Bu sayfa ajax ile kullanılıyor
// $genel_ayarlar değişkeni sınıf içinde FTP bağlantı bilgilerini almak içindir.
// $_POST['dir'] ajax ilk yüklemede $ftp_$path yolun içeriğini ister. Ağaç içinde tıklanan klasöre $ftp_$path yoluda eklenir
$tiklanan_path = urldecode($_POST['dir'] ?? ''); // Default to empty string if not set
$tree = new FtpTreeView($tiklanan_path, $genel_ayarlar, $hash);
echo $tree->createTree();


 

?>