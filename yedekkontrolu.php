<?php 
// Bismillahirrahmanirrahim
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");
##########################################################################################################
$dizin = BACKUPDIR;
    // Yedeklenecek dizin yoksa oluştur
    if(!file_exists(BACKUPDIR)){
        if (!mkdir(BACKUPDIR, 0777, true)) {
            die('Failed to create folder' .BACKUPDIR);
        }
    }
###########################################################################################################################################
    // Select option için Dizinleri listeliyoruz
    $folder_arr = array();
    $i = 0;
    foreach (new DirectoryIterator(BACKUPDIR) AS $file) {
        if ($file->isDir() && $file->getFilename() != '.' && $file->getFilename() != '..') {
          $folder_arr['3-'.$file->getCTime().'-'.$i] = $file->getFilename();
        }
    $i++;
    }
    krsort($folder_arr);
###########################################################################################################################################
    // Dizin içindeki dosya boyutunu hesaplama
    function dirSize($directory) {
        $size = 0;
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
            $size+=$file->getSize();
        }
        return $size;
    }
###########################################################################################################################################
    // Dizin içindeki dizin ve dosyaları listeliyoruz
    $files_arr = array();
    $i = 0;
    foreach (new DirectoryIterator(BACKUPDIR) AS $file) {

        if ($file->isDir() && $file->getFilename() != '.' && $file->getFilename() != '..') {

            //$files['3-'.$file->getCTime().'-'.$i] = $file->getFilename(); // dizinleri listeliyor
        }elseif ($file->isFile() && $file->getFilename() != '.htaccess') {

            if(pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'gz'){

                $files_arr['1-'.$file->getCTime().'-'.$i] = $file->getFilename(); // 1 yerine 2 olursa gzip dosyalar üste olur
            }elseif(pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'sql'){

                $files_arr['1-'.$file->getCTime().'-'.$i] = $file->getFilename();
            }
            
        }
    $i++;
    }
    krsort($files_arr);
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
                            <h1 class="m-0">Yedeklenen Veri Tabanı Kaynak İle Karşılaştır</h1>
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

        <form id="f">
            <table class="table" style="min-width: 1000px;">
                <colgroup span="2">
                    <col style="width:13%"></col>
                    <col style="width:85%"></col>
                </colgroup>
            <thead>
                <tr class="bg-primary">
                    <th colspan="2" style="text-align: center;line-height: .30;font-size: 1rem;"><u>SQL</u> veya <u>GZ</u> Uzantılı Yedek Veri Tabanı Kaynak Veri Tabanı ile Karşılaştır</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Sadece</b></td>
                    <td><span style="padding-right:20px;">Karşılaştır: <input class="sadece" type="radio" name="sadece" value="1" checked></span> <span style="padding:0 20px;">İçeriği Listele: <input class="sadece" type="radio" name="sadece" value="2"></span>Büyük boyutlu yedeğin içeriği listelerken tarayıcının kilitlenebileceğini unutmayın</td>
                </tr>
                <tr>
                    <td>Veritabanı Seç</td>
                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                    <select class="form-control" name="veritabani_id" id="ekle_veritabani_id" size="1" style="width:550px;">
                        <option value="0">&nbsp;</option>
                        <?php 
                            foreach($veritabanlari_arr AS $id => $veritabani){
                                if(isset($_POST['veritabani_id']) && $_POST['veritabani_id'] == $id){
                                    echo "<option value='{$id}' selected>{$veritabani}</option>\n";
                                }else{
                                    echo "<option value='{$id}'>{$veritabani}</option>\n";
                                }
                            }
                        ?>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:right;"><img style="width:40px;height:20px;" border="0" src="images/mysqlwinrar.png"></td>
                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                    <select size="1" name="sqlsec" id="sqlsec" class="form-control" style="width:550px;">
                        <?php
                            echo "<option value=''>&nbsp;</option>";
                            foreach($files_arr AS $key => $value){
                                echo "<option value='{$dizin}/{$value}'>{$value}";
                                echo "&nbsp&nbsp&nbsp-&nbsp&nbsp&nbsp".showSize(filesize($dizin."/".$value));
                                echo "</option>";
                            }
                        ?>      
                    </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><b>YADA</b> <img style="width:20px;height:20px;" border="0" src="images/folder.png">klasör içindeki veritabanı tabloları karşılaştır</td>
                </tr> 
                <tr>
                    <td style="text-align:right;"><img style="width:20px;height:20px;" border="0" src="images/folder.png"></td>
                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                    <select size="1" name="klasorsec" id="klasorsec" class="form-control" style="width:550px;">
                        <?php
                            echo "<option value=''>&nbsp;</option>";
                            foreach($folder_arr AS $key => $value){
                                echo "<option value='{$dizin}/{$value}'>{$value}";
                                echo "&nbsp&nbsp&nbsp-&nbsp&nbsp&nbsp".showSize(dirSize($dizin."/".$value));
                                echo "</option>";
                            }
                        ?>      
                    </select>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:right;"><input type="checkbox" name="yinede" id="yinede"></td>
                    <td>Veritabanı adı aynı olmasada yinede karşılaştır (<u>aynı veritabanı ancak isimleri farklı ise bu seçeneği kullanın</u>)</td>
                </tr>
                <tr id="karsilastir" style='display:none;'>
                    <td colspan="2">
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
</style>

    <div id="sql-loading" style='text-align: center;'>
        <img src="images/ajax-loader.gif" alt="Yükleniyor..." />
        <br />Yedek Veritabanı İçeriği Listeleniyor...
    </div>

    <span style="padding-left: 10px;">Sadece önizleme, düzenleme yok</span>
    <code-input required id="sql-listele" class="line-numbers" style="width:101%;height:1000px;display:none-;" lang="sql" placeholder="Biraz SQL yazın!" template="code-input"></code-input>

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
    var satir = '';
    var query = '';
    var tarih = '';
    var firma = '';
</script>

<?php 
include('includes/footer.php');
?>
<style>
textarea {
    /*
    pointer-events: none;
    height: 100%;
    */
/*display: none;*/
/*visibility: hidden;*/
}
</style>
<script type="text/javascript">
$( document ).ready(function() {

$('#klasorsec').change(function(){
    $('#sqlsec').prop('selectedIndex',0);
    $('#veritabanikarsilastir').html();
});

$('#sqlsec').change(function(){
    $('#klasorsec').prop('selectedIndex',0);
    $('#veritabanikarsilastir').html();
});

$('#ekle_veritabani_id').change(function(){
    $('#sqlsec').prop('selectedIndex',0);
    $('#klasorsec').prop('selectedIndex',0);
    $('#veritabanikarsilastir').html();
});


$('#klasorsec, #sqlsec').change(function(){
      var veritabani_id = $('select[name="veritabani_id"] option:selected').val();

    if(veritabani_id=="0") {
        $(function(){
            jw("b olumsuz").baslik("Veritabanı Belirlemediniz!").icerik("Karşılaştıracağınız veritabanı seçmelisiniz").kilitle().en(400).boy(100).ac();
        })
        return false;
    }

if($( "#sqlsec option:selected" ).val()!=='' || $( "#klasorsec option:selected" ).val()!==''){

    var str = 'grup=1';
    var t = $('#f').serialize();
    (t !='')? str += '&'+t :'';
if( $("input:radio:checked").val()==1 ){
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
    } else
    if( $("input:radio:checked").val()==2 ){
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
    } // if($("input[name='sadece']:checked").val()==1)

    }
   });
});

$("input[name='sadece']").click(function(){
    $('#sql-listele').val("");
    $("#sql-listele").hide();
    $("#sql-listeleme-aktif").hide();
    $('#sqlsec,#klasorsec').prop('selectedIndex',0);
    $('#veritabanikarsilastir').empty();
});

$('#yinede').on('change', function() {
    $('#sqlsec,#klasorsec').prop('selectedIndex',0);
    $('#veritabanikarsilastir').empty();
    $('#sql-listele').val("");
    $("#sql-listele").hide();
    $("#sql-listeleme-aktif").hide();
});

$('#sqlsec,#klasorsec .sadece').on('change', function() {
    if($( "#sqlsec option:selected" ).val()!=='' && $( "#klasorsec option:selected" ).val()!==''){
        $('#veritabanikarsilastir').empty();
    }
    $('#sql-listele').val("");
    $("#sql-listele").hide();
});

</script>
