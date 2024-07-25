<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

###########################################################################################################################################
function listDirectoriesAndFiles($dir) {
    $result = ['root_files' => []];
    $iterator = new DirectoryIterator($dir);
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isDot()) continue;
        if ($fileinfo->isDir()) {
            $subdir = $fileinfo->getFilename();
            $subdirPath = $dir . DIRECTORY_SEPARATOR . $subdir;
            $result[$subdirPath] = [];  // Dizinler için entry oluşturma
            $subIterator = new DirectoryIterator($subdirPath);
            foreach ($subIterator as $subfileinfo) {
                if ($subfileinfo->isFile() && preg_match('/\.sql$|\.sql\.gz$/', $subfileinfo->getFilename())) {
                    $result[$subdirPath][] = $subdirPath . DIRECTORY_SEPARATOR . $subfileinfo->getFilename();
                }
            }
        } elseif ($fileinfo->isFile() && preg_match('/\.sql$|\.sql\.gz$/', $fileinfo->getFilename())) {
            $result['root_files'][] = $dir . DIRECTORY_SEPARATOR . $fileinfo->getFilename();
        }
    }
    ksort($result);
    return $result;
}

$directoriesAndFiles = listDirectoriesAndFiles(BACKUPDIR);
###########################################################################################################################################

###########################################################################################################################################
    // Dizin içindeki dosya boyutunu hesaplama
    function dirSize($directory) {
        if(is_dir($directory)){
            $size = 0;
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
                $size+=$file->getSize();
            }
            return $size;
        }
    }
###########################################################################################################################################

###########################################################################################################################################
    // Dosya boyutunu dönüştürme
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

###########################################################################################################################################
function getIconByExtension($fileName) {
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);

    // .sql.gz dosyalarını kontrol et
    if (strtolower($extension) == 'gz' && substr($fileName, -7) == '.sql.gz') {
        return '<img src="images/gzip.png" border="0">'; // SQL arşiv dosyası ikonu
    }

    // Normal dosya uzantısını kontrol et
    switch (strtolower($extension)) {
        case 'sql':
            return '<img src="images/sql.png" border="0">'; // SQL dosyası ikonu
        case 'gz':
            return '<img src="images/gzip.png" border="0">'; // GZ dosyası ikonu
        default:
            return '📄'; // Genel dosya ikonu
    }
}
###########################################################################################################################################

include('includes/header.php');
include('includes/navigation.php');
include('includes/sub_navbar.php');
?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Yedeklenen Veri Tabanı Kaynakğa Göre Karşılaştır</h1>
                        </div><!-- / <div class="col-sm-6"> -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                                <li class="breadcrumb-item active">Veri Tabanı Karşılaştır</li>
                            </ol>
                        </div><!-- / <div class="col-sm-6"> -->
                    </div><!-- / <div class="row mb-2"> -->
                </div><!-- / <div class="container-fluid"> -->
            </div><!-- / <div class="content-header"> -->


    <!-- Bilgilendirme Satırı Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
                    <!-- Bilgilendirme bölümü -->
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                            <h5 class="m-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                Veritabanı Yedek ile Kaynak Karşılaştırma Hakkında Bilmeniz Gerekenler !
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <p>Buradan veritabanı yedeğinizin sorunsuz yedeklenip yedeklenmediğini kontrol edebilirsiniz.</p>
                                <p>Aşağıdaki seçeneklerden <b>.gz</b> veya <b>.sql</b> uzantılı veritabanı yedeklerinizi seçerek kontrol edebilirsiniz.</p>
                                <p>Sunucudan seçilen veritabanı tablo adlarını baz alarak yedek veritabanında önce tablo yapısına bakar sonra <b>(INSERT INTO ...)</b> veri satırlarını sayarak kaynak ile karşılaştırır</p>
                                <p>PhpMyAdmin ile yedekleme yaparken<br />
                                <b style="padding-left: 15px;">Özel - tüm olası seçenekleri göster</b> alanından<br />
                                <b style="padding-left: 15px;">DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT / TRIGGER ifadesi ekle</b><br />
                                <b style="padding-left: 15px;">IF NOT EXISTS (tablo oluşumu sırasında üretilecek olan indeksler gibi daha az etkili)</b><br />
                                <b style="padding-left: 15px;">AUTO_INCREMENT değeri</b><br />
                                <b style="padding-left: 15px;">yukarıdakilerin hiçbiri: Örnek: INSERT INTO tbl_adı VALUES (1,2,3)</b><br />
                                seçenekleri seçerek yapacağınız yedekleme kaynak ile karşılaştırabilir ve bu script ile geri yükleyebilirsiniz.</p>
                                <p>Bu vesile ile hangi tabloların yedeklenip yedeklenmediğini ve hangi tabloların veri satırları eksik yedeklenip yedeklenmediğini görebilirsiniz.</p>
                                <p><i class="fa fa-plus" aria-hidden="true" style="color:red;"></i> İkonu diğer veritabanında bu tablonun olmadığını gösterir.</p>
                                <p><i class="fa fa-minus" aria-hidden="true" style="color:red;"></i> ikonu diğer veritabanında olan tablonun burada olmadığını gösterir.</p>
                                <p><i class="fa fa-check" aria-hidden="true" style="color:green;"></i> İkonu her iki veritabanında bu tablo ve veri satırları tam yedeklendiğini gösterir.</p>
                                <p><i class="fa fa-exclamation-circle" aria-hidden="true" style="color:red;"></i> İkonu bu tablonun veri satırlarında sorun olduğunu, muhtemelen eksik veri satırı yedeklendiğini gösterir.</p>
                                <b>Veritabanı yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(BACKUPDIR)); ?></span><br />
                                <p><b>Web site zip yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(ZIPDIR)); ?></span></p>
                            </div>
                            </div>
                        </div><!-- / <div class="card"> -->
                    </div><!-- / <div id="accordion"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Bilgilendirme Satırı Sonu -->

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

                    <?php 
                    //echo '<pre>' . print_r($directoriesAndFiles, true) . '</pre>';
                    //echo '<pre>' . print_r($alt_dizin_dosyalari, true) . '</pre>';
                    ?>

        <form id="f">
            <table class="table" style="min-width: 1000px;">
                <colgroup span="2">
                    <col style="width:13%"></col>
                    <col style="width:35%"></col>
                    <col style="width:50%"></col>
                </colgroup>
            <thead>
                <tr class="bg-primary">
                    <th colspan="3" style="text-align: center;line-height: .30;font-size: 1rem;"><u>SQL</u> veya <u>GZ</u> Uzantılı Yedek Veri Tabanı Kaynakğa Göre Karşılaştır</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Sadece</b></td>
                    <td colspan="2"><span style="padding-right:20px;">Karşılaştır: <input class="sadece" type="radio" name="sadece" value="1" checked></span> <span style="padding:0 20px;">İçeriği Listele: <input class="sadece" type="radio" name="sadece" value="2"></span>Büyük boyutlu yedeğin içeriği listelerken tarayıcının kilitlenebileceğini unutmayın</td>
                </tr>
                <tr>
                    <td>Veritabanı Seç</td>
                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">

<button class="btn btn-secondary dropdown-toggle d-flex justify-content-between align-items-center" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" style="width:550px;">
<span id="selectedFileName1">İşlem Yapacağınız Veritabanı Seçin</span>
<span class="dropdown-toggle-icon"></span>
</button>

<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" style="width:550px;">
<div class="modal-scrollbar">
<?php 
    foreach($veritabanlari_arr AS $id => $veritabani){
        echo '
        <li><a class="dropdown-item" href="#" data-file-path="'.$id.'" data-file-name="'.$veritabani.'" data-size="Klasör">
        <span class="icon"><img style="width:20px;height:20px;" border="0" src="images/pngegg.png"></span>
        <span class="file-name">'.$veritabani.'</span>
        </a></li>
        ';
    }
?>
</div>
</ul>
<input type="hidden" name="veritabani_id" id="selectedFilePath1">

                    </td>
                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><button type="button" id="bakim" class="btn btn-success btn-sm" title="Seçili Veritabanına Bakım Yap"><i class="fa fa-wrench" aria-hidden="true"></i> Seçili Veritabanına Bakım Yap </button> <b>check</b>, <b>repair</b>, <b>optimize</b>, <b>analyze</b> seçeneklerini kullanarak bakım yapar.</td>
                </tr>
                <tr>
                    <td style="text-align:right;"><img style="width:40px;height:20px;" border="0" src="images/mysqlwinrar.png"></td>
                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">

<button class="btn btn-secondary dropdown-toggle d-flex justify-content-between align-items-center" data-default-text="Veritabanı Yedek Dosyayı Seçin" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false" style="width:550px;">
<span id="selectedFileName2">Veritabanı Yedek Dosyayı Seçin</span>
<span class="dropdown-toggle-icon"></span>
</button>

<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2" style="width:550px;">
<div class="modal-scrollbar">
<?php 
foreach($directoriesAndFiles AS $key => $klasor_dosya_arr){
    if($key == 'root_files'){
    foreach($klasor_dosya_arr AS $value){
        echo '
        <li><a class="dropdown-item" href="#" data-file-path="'.$value.'" data-file-name="'.basename($value).'" data-size="Klasör">
        <span class="icon">'.getIconByExtension($value).'</span>
        <span class="file-name">'.basename($value).'</span>
        <span class="badge bg-primary rounded-pill">'.showSize(filesize($value)).'</span>
        </a></li>
        ';
    }
    }
}
?>
</div>
</ul>
<input type="hidden" name="sqlsec" id="selectedFilePath2">
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><b>YADA</b> <img style="width:20px;height:20px;" border="0" src="images/folder.png">klasör içindeki veritabanı tabloları karşılaştır veya içeriğini görüntüle</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td style="text-align:right;"><img style="width:20px;height:20px;" border="0" src="images/folder.png"></td>
                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">

<button class="btn btn-secondary dropdown-toggle d-flex justify-content-between align-items-center" data-default-text="Veritabanı Yedek Klasör Seçin" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false" style="width:550px;">
<span id="selectedFileName3">Veritabanı Yedek Klasör Seçin</span>
<span class="dropdown-toggle-icon"></span>
</button>

<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton3" style="width:550px;">
<div class="modal-scrollbar">
<?php 
    foreach($directoriesAndFiles AS $key => $value){
        if($key != 'root_files'){
        echo '
            <li><a class="dropdown-item" href="#" data-file-path="'.$key.'/" data-file-name="'.basename($key).'" data-size="Klasör">
            <span class="icon"><img style="width:20px;height:20px;" border="0" src="images/folder.png"></span>
            <span class="file-name">'.basename($key).'</span>
            <span class="badge bg-primary rounded-pill">'.showSize(dirSize($key)).'</span>
            </a></li>
        ';
        }
    }
?>
</div>
</ul>
<input type="hidden" name="klasorsec" id="selectedFilePath3">

                    </td>
                    <td>Klasörlerin içindeki tüm tabloları görüntülemek veya karşılaştırmak için burayı kullanın</td>
                </tr>

                <tr>
                    <td style="text-align:right;"><img style="width:20px;height:20px;" border="0" src="images/folder_files.png"></td>
                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">

<button class="btn btn-secondary dropdown-toggle d-flex justify-content-between align-items-center" data-default-text="İçeriğini Görüntülemek İçin Bir Tablo Seçin" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-expanded="false" style="width:550px;">
<span id="selectedFileName4">İçeriğini Görüntülemek İçin Bir Tablo Seçin</span>
<span class="dropdown-toggle-icon"></span>
</button>

<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton4" style="width:550px;">
<div class="modal-scrollbar">
<?php 
    foreach($directoriesAndFiles AS $key => $klasor_dosya_arr){
        if($key != 'root_files'){
        echo '<li><h6 class="dropdown-header" style="text-align: left;"><span class="icon"><img style="width:20px;height:20px;" border="0" src="images/folder.png"> </span>'.basename($key).'</h6></li>';
            foreach($klasor_dosya_arr AS $value){
                echo '
                <li><a class="dropdown-item" href="#" data-file-path="'.$value.'" data-file-name="'.basename($value).'" data-size="Klasör">
                <span class="icon  dosya_adi">'.getIconByExtension($value).'</span>
                <span class="file-name">'.basename($value).'</span>
                <span class="badge bg-primary rounded-pill">'.showSize(filesize($value)).'</span>
                </a></li>
                ';
            }
        }
    }
?>
</div>
</ul>
<input type="hidden" name="alt_dosya" id="selectedFilePath4">

                    </td>
                    <td>Klasörlerin içindeki tablolardan birinin içeriğini görüntülemek için burayı kullanın</td>
                </tr>
                <tr>
                    <td style="text-align:right;"><input type="checkbox" name="yinede" id="yinede" value="1"></td>
                    <td colspan="2">Veritabanı adı aynı olmasada yinede karşılaştır (<u>aynı veritabanı ancak isimleri farklı ise bu seçeneği kullanın</u>)</td>
                </tr>
                <tr id="karsilastir" style='display:none;'>
                    <td colspan="3">
                            <div id="loading" style='text-align: center;'>
                                <img src="images/ajax-loader.gif" alt="Yükleniyor..." />
                                <br />Veritabanı Karşılaştırmaya Hazırlanıyor...
                            </div>
                            <div id="veritabanikarsilastir"></div>
                    </td>
                </tr>
            </body>                     
            </table>
        </form>



<style>
    .dropdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dropdown-item .file-name {
        flex: 1;
    }
    .dropdown-item .badge {
        margin-left: 1rem;
        white-space: nowrap;
        font-size: 95%;
    }
    .dropdown-item .icon {
        margin-right: 0.5rem;
    }
    .dropdown-toggle {
        text-align: left;
        width: 100%;
    }
    .dropdown-toggle::after {
        margin-left: auto; /* Select ikonu sağ tarafa hizalar */
    }
    .dosya_adi {
        margin-left: 20px; /* Dosya adlarına girinti ekler */
    }
    .dropdown-item.selected {
        background-color: #E0E0E6; /* Vurgu rengi */
        color: black;
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  $(document).ready(function() {
    $('.dropdown-item').on('click', function(event) {
        event.preventDefault();

        const filePath = $(this).data('file-path');
        const fileName = $(this).data('file-name');
        const $dropdownMenu = $(this).closest('.dropdown-menu');
        const $dropdownButton = $dropdownMenu.prev('.dropdown-toggle');
        const $selectedFileName = $dropdownButton.find('#selectedFileName' + $dropdownButton.attr('id').slice(-1));
        const $selectedFilePath = $('#selectedFilePath' + $dropdownButton.attr('id').slice(-1));

        // Seçili dosya adını ve dosya yolunu güncelle
        $selectedFileName.text(fileName);
        $selectedFilePath.val(filePath);

      // Buton rengini değiştir
      $dropdownButton.removeClass('btn-secondary').addClass('btn-primary');

      // Diğer dropdownları sıfırla, ancak dropdownMenuButton4 hariç
      $('.dropdown-toggle').not($dropdownButton).not('#dropdownMenuButton1').each(function() {
        const defaultText = $(this).attr('data-default-text');
        $(this).find('span:first').text(defaultText);
        const inputId = 'selectedFilePath' + $(this).attr('id').slice(-1);
        $('#' + inputId).val('');

        // Buton rengini sıfırla
        $(this).removeClass('btn-primary').addClass('btn-secondary');
      });

      // Seçili öğeyi vurgula
      $dropdownMenu.find('.dropdown-item').removeClass('selected');
      $(this).addClass('selected');


if($("#selectedFilePath2").val()!=='' || $("#selectedFilePath3").val()!=='' || $("#selectedFilePath4").val()!==''){

      var veritabani_id = $('#selectedFilePath1').val();

    if(veritabani_id==="" && $("input[name='sadece']:checked").val()==1) {
        $(function(){
            jw("b olumsuz").baslik("Veritabanı Belirlemediniz!").icerik("Karşılaştıracağınız veritabanı seçmelisiniz").kilitle().en(400).boy(100).ac();
        })
        return false;
    }

    var str = 'grup=1';
    var t = $('#f').serialize();
    (t !='')? str += '&'+t :'';

    if( $("input[name='sadece']:checked").val()==1){
        $('#veritabanikarsilastir').empty();
        $("#karsilastir").show();
        $("#loading").show();    
        xhr = $.ajax({
        type: "POST",
        url: "veritabanikarsilastir.php",
        data: str,
        success: function(veriler){
        $("#loading").hide();
        $("#veritabanikarsilastir").html(veriler);
        }
        });
    } else if( $("input[name='sadece']:checked").val()==2){
        $('#sql-listele').val("");
        $("#sql-listeleme-aktif").show();
        $("#sql-loading").show(); 
        xhr = $.ajax({
        type: "POST",
        url: "sql_listele.php",
        data: str,
        success: function(sql){
        $("#sql-loading").hide();
        $("#sql-listele").show();
        $("#sql-listele").val(sql);
        }
        });
    }
}
    });
    // Sabit butonun rengini ilk durumda ayarla
    if ($('#selectedFilePath1').val()) {
      $('#dropdownMenuButton1').removeClass('btn-secondary').addClass('btn-primary');
    }
  });

$("input[name='sadece']").click(function(){
    $('#sql-listele').val("");
    $("#sql-listele").hide();
    $("#sql-listeleme-aktif").hide();
    $('#veritabanikarsilastir').empty();
});
</script>

                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->

    

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content" style="display:none;" id="sql-listeleme-aktif">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

<!--Prism-->
<link id="import-theme" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.css"/>

<!--Code-input is on GitHub ==> https://github.com/WebCoder49/code-input-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/WebCoder49/code-input@2.1/code-input.css">
<script src="https://cdn.jsdelivr.net/gh/WebCoder49/code-input@2.1/code-input.js"></script>
<script src="https://cdn.jsdelivr.net/gh/WebCoder49/code-input@2.1/plugins/indent.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/WebCoder49/code-input@2.1/plugins/prism-line-numbers.min.css">

<script>
  codeInput.registerTemplate("code-input", codeInput.templates.prism(Prism, [new codeInput.plugins.Indent()]));
  codeInput.registerTemplate("demo", codeInput.templates.prism(Prism, [new codeInput.plugins.Indent()]));
</script>

<style>
    code-input textarea, code-input pre {
        position: absolute;
        top: unset;
        left: unset;
    }

        #tamekran.fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 110%;
            z-index: 1000000000;
            background-color: white;
        }

        #fullscreen-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            z-index: 1000000001;
            
        }
        .pencere-tam {
            height: 90%;
            width: 100%;
        }
        .pencere-normal {
            height: 600px;
            width: 100%;
        }
</style>


    <div id="sql-loading" style='text-align: center;'>
        <img src="images/ajax-loader.gif" alt="Yükleniyor..." />
        <br />Yedek Veritabanı İçeriği Listeleniyor...
    </div>

<div id="tamekran">
    <button id="fullscreen-btn" type="button" title="Tam Ekran" onclick="toggleFullScreen()"><i class="fas fa-expand-arrows-alt"></i></button>
    <span style="padding-left: 10px;">Sadece önizleme, düzenleme yok</span>

    <code-input required id="sql-listele" class="line-numbers pencere-normal" lang="sql" placeholder="Yükleniyor Lütfen bekleyin...!" template="code-input"></code-input>
</div>

    <script>
        function toggleFullScreen() {
            const codeInputElement = document.getElementById('tamekran');
            const fullscreenBtnIcon = document.querySelector('#fullscreen-btn i');
            const codeInputStyle = document.querySelector('#sql-listele');
            if (codeInputElement.classList.contains('fullscreen')) {
                console.log("aaa");
                codeInputStyle.classList.remove('pencere-tam');
                codeInputStyle.classList.add('pencere-normal');

                codeInputElement.classList.remove('fullscreen');
                fullscreenBtnIcon.classList.remove('fa-compress-arrows-alt');
                fullscreenBtnIcon.classList.add('fa-expand-arrows-alt');
            } else {
                codeInputStyle.classList.remove('pencere-normal');
                codeInputStyle.classList.add('pencere-tam');
                codeInputElement.classList.add('fullscreen');
                fullscreenBtnIcon.classList.remove('fa-expand-arrows-alt');
                fullscreenBtnIcon.classList.add('fa-compress-arrows-alt');
            }
        }
    </script>

                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->

<br />
        </div><!-- / <div class="content-wrapper"> -->

<script type='text/javascript'>
$('#bakim').click(function( e ){
  var veritabani_id = $('#selectedFilePath1').val();
      if(!veritabani_id) {
        $(function(){
            jw("b olumsuz").baslik("Veritabanı Belirlemediniz!").icerik("Bakım yapacağınız veritabanı seçmelisiniz").kilitle().en(400).boy(100).ac();
        })
        return false;
    }
    var bekleme = jw("b bekle").baslik("Veri Tabanı Bakım Yapılıyor...").en(350).boy(10).kilitle().akilliKapatPasif().ac();

    $.ajax({
        type:'POST',
        url: "db_bakim.php",
        data: { veritabani_id : veritabani_id},
        success: function(msg){
            $(function () {
                bekleme.kapat();
                var pen = jw('d').baslik('Veritabanı Bakım Sonucu').icerik(msg).en(750).boy(550).kucultPasif().acEfekt(2, 1000).kapatEfekt(2, 1000).ac();
            });
        }
    });

});
</script>

<script type='text/javascript'>
    var satir = '';
    var query = '';
    var tarih = '';
    var firma = '';
</script>

<?php 
include('includes/footer.php');
?>

