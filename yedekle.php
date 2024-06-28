<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
##########################################################################################################

##########################################################################################################
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
                            <h1 class="m-0">Veritabanı Elle Yedekle</h1>
                        </div><!-- / <div class="col-sm-6"> -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                                <li class="breadcrumb-item active">Veri Tabanı Yedekle</li>
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
                                Veritabanı Yedekleme Hakkında Bilmeniz Gerekenler !
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <p>Zaman zaman veritabanı yedeği alınması gerekir, bunun bir çok sebebi olabilir istemeden yapacağınız bir yanlış geri dönülmesi sağlayacağı gibi sitenize başkaları tarafından saldırıda veritabanındaki verilerin silinmesi gibi durumlarda sitenizin geri getirilmesini sağlar. Ancak bu veritabanı yedeklerin arada bir bilgisayarınıza indirmenizde fayda olacaktır.</p>
                                <p>Yedeklenmiş veritabanlarınıza URL ile doğrudan ulaşılması mümkün değil, sadece FTP ile ulaşmak mümkündür. Ancak web sitenize hack gibi durumlar için garanti edilemez.</p>
                                <p>MySQL yedeklerden <b>.gz</b> ve <b>.sql</b> uzantılı dosyaların adını tıklayarak bilgisayarınıza indirebilirsiniz.</p>
                                <p><b>ÖNEMLİ NOT:</b> sitenizde riskli değişiklikler ve ayarlamalar yapmadan önce veritabanın yedeğini almanız şiddetle önerilir.</p>
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
                        <table class="table" style="margin-bottom: 2rem; min-width: 1000px;">
                        <colgroup span="7">
                            <col style="width:25%"></col>
                            <col style="width:1%"></col>
                            <col style="width:1%"></col>
                            <col style="width:1%"></col>
                            <col style="width:1%"></col>
                            <col style="width:20%"></col>
                            <col style="width:50%"></col>
                        </colgroup>
                            <thead>
                                <tr class="bg-primary">
                                    <th colspan="7" style="text-align: center;line-height: .30;font-size: 1rem;">Seçili Veri Tabanı Yedekle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Yedeklenecek Veritabanı Seç</td>
                                    <td colspan="5" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                                    <select class="form-control" name="veritabani_id" id="ekle_veritabani_id" size="1" required>
                                        <option value="0">&nbsp;</option>
                                        <?php 
                                            foreach($veritabanlari_arr AS $id => $veritabani){
                                                echo "<option value='{$id}'>{$veritabani}</option>\n";
                                            }
                                        ?>
                                    </select>
                                    </td>
                                    <td>Yedeklemek istediğiniz veritabanı seçiniz</td>
                                </tr>
                                <tr>
                                    <td>Yedeklerin bulunduğu dizin</td>
                                    <td colspan="6"><span id="yol"><?php echo strtolower(htmlpath(BACKUPDIR)); ?></span></td>
                                </tr>
                                <tr>
                                    <td style="padding: 0.75rem 0.75rem 0.75rem 1.5rem;vertical-align: middle;">Veritabanı Yedeğin <b>Öneki</b></td>
                                    <td colspan="5" style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" id="onek" class="form-control" name="onek" style="min-width:150px;" maxlength="40" /><strong class="err" style="display: none;"></strong></td>
                                    <td style="padding: 0.75rem;vertical-align: middle;">Veritabanı yedeğini tanımlamak için yeniden adlandırabilirsiniz. En fazla 40 karakter.</td>
                                </tr>
                                <tr>
                                    <td>Veritabanını <b>GZip</b> ile sıkıştırarak yedekle</td>
                                    <td>Evet</td>
                                    <td><input type="radio" name="gz" value="1"></td>
                                    <td>Hayır</td>
                                    <td><input type="radio" name="gz" value="0"></td>
                                    <td colspan="2">Yedeği sıkıştırmak dosya boyutunu küçültür.</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı yedeklemeden önce <b>Tabloyu kilitle</b></td>
                                    <td>Evet</td>
                                    <td><input type="radio" name="lock" id="lock" value="1"></td>
                                    <td>Hayır</td>
                                    <td><input type="radio" name="lock" id="lock" value="0"></td>
                                    <td colspan="2">Yedeklenmeden önce bakım yaparken tabloları kilitler.</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı yedeklemeden önce <b>Tabloya Bakım Yap</b></td>
                                    <td>Evet</td>
                                    <td><input type="radio" name="bakim" id="bakim" value="1"></td>
                                    <td>Hayır</td>
                                    <td><input type="radio" name="bakim" id="bakim" value="0"></td>
                                    <td colspan="2">Yedeklenmeden önce tablolara CHECK, REPAIR, OPTIMIZE bakımı yapar.</td>
                                </tr>
                                <tr>
                                    <td>Veritabanını <b>Tam</b> yedekle</td>
                                    <td>Evet</td>
                                    <td><input type="radio" name="combine" id="combine" value="1" onclick="return radioEvet();"></td>
                                    <td colspan="4">Tüm veritabanını tek dosya olarak yedekler.</td>
                                </tr>
                                <tr>
                                    <td>Veritabanının tüm <b>Tabloları ayrı ayrı</b> yedekle</td>
                                    <td>Evet</td>
                                    <td><input type="radio" name="combine" id="combine" value="2" onclick="return radioEvet();"></td>
                                    <td colspan="4">Yeni bir klasörün içine tabloları ayrı ayrı yedekler.</td>
                                </tr>
                                <tr>
                                    <td>Veritabanının tabloları <b>Elle</b> seç</td>
                                    <td>Evet</td>
                                    <td><input type="radio" name="combine" id="combine3" value="3" onclick="return tablolariYukle('TABLE_NAME ASC');"></td>
                                    <td colspan="4">İsteğe bağlı olarak yedeklenecek tabloları seçme olanağı verir.</td>
                                </tr>

                            <tbody class="maxi-div-hide" id="showTablolar" style="display:none;">
                                <tr>
                                    <td>Seçilen <b>Tabloları ayrı ayrı</b> alt-klasöre yedekle</td>
                                    <td>Evet</td>
                                    <td><input type="radio" name="elle" value="2"></td>
                                    <td colspan="4">Seçilen tabloları ayrı ayrı alt-klasöre yedekler.</td>
                                </tr>
                                <tr>
                                    <td>Seçilen <b>Tabloları birleştirerek</b> yedekle</td>
                                    <td>Evet</td>
                                    <td><input type="radio" name="elle" value="1"></td>
                                    <td colspan="4">Seçilen tabloları birleştirerek tek dosya olarak yedekler.</td>
                                </tr>
                            </tbody>

                            <tbody>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td colspan="7" align="center" style="padding: 0.30rem 0.75rem 0.30rem 0.75rem;vertical-align: middle;">
                                        <button type="submit" class="btn btn-success btn-sm" value="" onclick="yedekle();return false;" title="Veritabanı Yedekler"><i class="fa fa-download" aria-hidden="true"></i> Şimdi Veritabanı Yedekle </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
<div id="tbliste"></div>
                        <div id="showTablolarYedekler" style="display:none;margin-top: 0px;">
                            <div id="loading" style='text-align: center;'>
                                <img src="images/ajax-loader.gif" alt="Yükleniyor..." />
                                <br />Veritabanı Tabloları Yükleniyor...
                            </div>
                            <table id="sortliste" class="table table-striped table-hover">
                                <colgroup span="7">
                                    <col style="width:40%"></col>
                                    <col style="width:10%"></col>
                                    <col style="width:10%"></col>
                                    <col style="width:10%"></col>
                                    <col style="width:10%"></col>
                                    <col style="width:10%"></col>
                                    <col style="width:10%"></col>
                                </colgroup>
                            </table>
                                <div align="center">
                                    <div style="width:260px;padding-bottom:15px;">
                                        <button type="submit" class="btn btn-success btn-sm" value="" onclick="yedekle();return false;" title="Veritabanı Yedekler"><i class="fa fa-download" aria-hidden="true"></i> Şimdi Veritabanı Yedekle </button>
                                    </div>
                                </div>
                        </div><!-- / <div id="showTablolarYedekler" style="display:none;margin-top: 0px;"> -->
                    </form>

<form name="teklifsil" id="gvUsers" method="POST" onsubmit="return false;" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <div id="yedekler-listesi">
                <table class="table table-sm table-striped table-hover" style="min-width: 1000px;">
                <colgroup span="5">
                    <col style="width:45%"></col>
                    <col style="width:15%"></col>
                    <col style="width:10%"></col>
                    <col style="width:20%"></col>
                    <col style="width:10%"></col>
                </colgroup>
            <thead>
                <tr class="bg-primary">
                    <th colspan="5" style="text-align: center;line-height: 1;font-size: 1rem;">Yedeklenen Veri Tabanı Listesi</th>
                </tr>
                <tr>
                    <th>Veritabanı Adı ve Yedekleme Tarihi</th>
                    <th style='text-align: right;padding-right: 20px;'>Veritabanı Boyutu</th>
                    <th style='text-align: right;padding-right: 20px;'>Tablo Sayısı</th>
                    <th style='text-align: right;padding-right: 20px;'>Yedekleme/Düzenleme Zamanı</th>
                    <th style='text-align: right;padding-right: 20px;'>Sil</th>
                </tr>
            </thead>
<?php 
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

    // Dizin içindeki dosya boyutunu hesaplama
    function dirSize($directory) {
        $size = 0;
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
            $size+=$file->getSize();
        }
        return $size;
    }
    
    // Dizin içindeki dizin ve dosyaları listeliyoruz
    $files = array();
    $i = 0;
    foreach (new DirectoryIterator(BACKUPDIR) AS $file) {

        if ($file->isDir() && $file->getFilename() != '.' && $file->getFilename() != '..') {

            $files['3-'.$file->getCTime().'-'.$i] = $file->getFilename();
        }elseif ($file->isFile() && $file->getFilename() != '.htaccess') {

            if(pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'gz'){

                $files['2-'.$file->getCTime().'-'.$i] = $file->getFilename();
            }elseif(pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'sql'){

                $files['1-'.$file->getCTime().'-'.$i] = $file->getFilename();
            }
            
        }
    $i++;
    }

    // Tarihe göre sırala, en yeni en üstte olacak şeklinde 
    krsort($files);
    $dizintoplamboyutu = 0;
    $dosyatoplamboyutu = 0;
    foreach($files AS $key => $value){
        $icon = explode("-",$key);
        echo "<tr>";
            echo "<td>";
            if($icon[0] == 3){
                echo "<img src='images/klasor.png' border='0'> ";
            }elseif($icon[0] == 2){
                echo "<img src='images/rar.png' border='0'> ";
            }elseif($icon[0] == 1){
                echo "<img src='images/sql.png' border='0'> ";
            }else{
                echo "<img src='images/bos.png' border='0' alt='Bilinmeyen dosya tipi'> ";
            }
            if($icon[0] == 3){
            echo $value;
            }else{
            echo "<span class='indir' style='cursor: pointer;' title='İndirmek için tıkla'>".$value."</span>";
            }
            echo "</td>";
            echo "<td style='text-align: right;padding-right: 20px;'>";
            if($icon[0]==3){
                $dizinboyutu = dirSize(BACKUPDIR."/".$value);
                echo showSize($dizinboyutu); // dizin boyutu
                $dizintoplamboyutu += $dizinboyutu;
            }else{
                $dosyaboyutu = filesize(BACKUPDIR."/".$value);
                echo showSize($dosyaboyutu); // dosya boyutu
                $dosyatoplamboyutu += $dosyaboyutu;
            }
            echo "</td>";
            echo "<td style='text-align: right;padding-right: 20px;'>";
            if($icon[0]==3){
                echo count(glob(BACKUPDIR."/".$value . "/*.{sql,gz}",GLOB_BRACE)); // dizin içindeki dosya sayısını döndürür
            }else{
                echo "";
            }
            echo "</td>";
            echo "<td style='text-align: right;padding-right: 20px;'>";
                echo near_date($icon[1]);
            echo "</td>";
            echo "<td style='text-align: right;padding-right: 20px;'>";
                echo "<input type='checkbox' class='delete_veritabaniyedek' name='delete_veritabaniyedek[]' value='{$value}' title='Silmek için seç' onclick='javascript:renk(this);' />";
            echo "</td>";
        echo "</tr>";
    }
        echo "<tfoot>";
        echo "<tr>";
        echo "  <th>&nbsp</td>";
        echo "  <th style='text-align: right;padding-right: 20px;'>Toplam Boyutu: ".showSize(($dizintoplamboyutu+$dosyatoplamboyutu))."</td>";
        echo "  <th style='text-align: right;padding-right: 20px;'>&nbsp</td>";
        echo "  <th style='text-align: right;padding-right: 20px;'>&nbsp</td>";
        echo "  <th style='text-align: right;padding-right: 20px;'>Tümünü Seç: <input type='checkbox' onclick='javascript:tumunu_sec(this);' title='Tümünü silmek için seç' /></td>";
        echo "</tr>";

?>
                <tr>
                    <td colspan="5" align="center">
                        <button type="submit" class="btn btn-warning btn-sm" onclick="return confirmDel();"><span class="glyphicon glyphicon-trash"></span> Seçilen Veri Tabanı Yedek(leri) Sil </button>
                    </td>
                </tr>
            </tfoot>
                </table>
            </div>
</form>

                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->
    

        </div><!-- / <div class="content-wrapper"> -->
        
<script type="text/javascript">

    function tumunu_sec(spanChk){
        var IsChecked = spanChk.checked;
        var Chk = spanChk;
            var items = document.getElementsByClassName("delete_veritabaniyedek");
            //console.log(items);
            for(i=0;i<items.length;i++)
            {
                if(items[i].id != Chk && items[i].type=="checkbox")
                {
                    if(items[i].checked!= IsChecked)
                    {
                        items[i].click();
                    }
                }
            }
    }
</script>

<script type="text/javascript">
    $(".indir").click(function(){
        window.location="download.php?file=" + $(this).text();
    });
</script>

<script type="text/javascript">  
    function renk(chkB){
    var IsChecked = chkB.checked;           
        if(IsChecked){
            chkB.parentElement.parentElement.style.backgroundColor='#FFEB90';
            chkB.parentElement.parentElement.style.borderBottom='thin solid';
            chkB.parentElement.parentElement.style.color='';
        }else{
            chkB.parentElement.parentElement.style.backgroundColor='';
            chkB.parentElement.parentElement.style.borderBottom='';
            chkB.parentElement.parentElement.style.color='';
        }
    }
</script>

<script language="javascript">
    function confirmDel() {
        var inputElems = document.getElementsByTagName('input'), count = 0;
        for (var i=0; i<inputElems.length; i++) {
            if (inputElems[i].type === 'checkbox' && inputElems[i].checked === true) {
                count++;
            }
        }

        if (count<1){
    $(function(){
        jw("b olumsuz").baslik("Seçim Yapılmamış").icerik("Silinecek veritabanı yedeği seçmediniz!").kilitle().en(400).boy(100).ac();
    })  
    return false;
    }
    
    $(function()
    {
        jw('b secim',OK).baslik("Veritabanı Silmeyi Onayla").icerik("Yedek Veritabanını silmek istediğinizden emin misiniz?").en(350).kilitle().ac();
    })

    function OK(x){
            if(x==1){
            var bekleme = jw("b bekle").baslik("Veritabanları siliniyor...").en(300).boy(10).kilitle().akilliKapatPasif().ac();
            var str = 'grup=sqlyedeksil';
            var t = $('#gvUsers').serialize();
            (t !='')? str += '&'+t :'';    
                xhr = $.ajax({
                type: "POST",
                url: "dosyasil.php",
                data: str,
                    success: function(veriler){
                        bekleme.kapat();
                        jw("b olumlu").baslik("Veritabanı Silme Sonucu").icerik(veriler).en(450).boy(10).kilitle().akilliKapatPasif().kapaninca(function(){ window.location.href='<?php echo $_SERVER['REQUEST_URI']; ?>' }).ac();       
                    }
                });             
            } //if(x==1){
        } //function DUR(x){
    }
</script>

<script type="text/javascript">

    function radioEvet(){
        $("#sortliste").empty();
        $('#showTablolarYedekler').hide();
        $('#showTablolar').hide();
        $("#yedekler-listesi").show();
    }

</script>

<script type="text/javascript">   
                
    function yedekle() {

        var veritabani_id = $('select[name="veritabani_id"] option:selected').val();
        var onek = $('#onek').val();
        var gz = $("input[name='gz']:checked").attr('value');
        var lock = $("input[name='lock']:checked").attr('value');
        var bakim = $("input[name='bakim']:checked").attr('value');
        var combine = $("input[name='combine']:checked").attr('value');
        var tablolar = $('input[id=tablolar]:checked').length;
        var elle = $("input[name='elle']:checked").attr('value');

        if(veritabani_id=="0") {
            $(function(){
                jw("b olumsuz").baslik("Veritabanı Belirlemediniz!").icerik("Yedeklenecek veritabanı seçmelisiniz").kilitle().en(400).boy(100).ac();
            })
            return false;
        }
        if(/^[a-z-.A-Z-0-9_]*$/.test(onek) == false) {
            $(function(){
                jw("b olumsuz").baslik("Yedeğin Öneki Geçersiz").icerik("Girdiğiniz Önek metinde geçersiz karakter(ler) içeriyor<br /><br />Önek için sadece boşluksuz latin karakterlere izin veriliyor").kilitle().en(400).boy(100).ac();
            })
            return false;
        }   
        if(gz==undefined) {
            $(function(){
                jw("b olumsuz").baslik("Yedeği GZipleme Belirlemediniz!").icerik("Veritabanı yedeğin GZip ile sıkıştırılıp veya sıkıştırılmayacağını belirlemediniz").kilitle().en(400).boy(100).ac();
            })
            return false;
        }
        if(lock==undefined) {
            $(function(){
                jw("b olumsuz").baslik("Tabloyu Kilitle Belirlemediniz!").icerik("Yedeklenmeden önce tabloyu kilitle belirlemediniz").kilitle().en(400).boy(100).ac();
            })
            return false;
        }
        if(bakim==undefined) {
            $(function(){
                jw("b olumsuz").baslik("Tablolara Bakım Belirlemediniz!").icerik("Yedeklenmeden önce tablolara bakım belirlemediniz").kilitle().en(400).boy(100).ac();
            })
            return false;
        }
        if(combine==undefined) {
            $(function(){
                jw("b olumsuz").baslik("Yedekleme Biçimi Belirlemediniz!").icerik("Veritabanı yedekleme biçimi belirlemediniz").kilitle().en(400).boy(100).ac();
            })
            return false;
        }
        if (elle==undefined && combine==3) {
            $(function(){
                jw("b olumsuz").baslik("Tabloarı Elle Seçerek Yedekleme Biçimi Belirlemediniz!").icerik("Tabloları elle seçerek yedekleme biçimini seçmediniz").kilitle().en(400).boy(100).ac();
            })
            return false;
        }  
        if (tablolar < 1 && combine==3) {
            $(function(){
                jw("b olumsuz").baslik("Tabloları Belirlemediniz!").icerik("Yedeklemek istediğiniz tabloyu veya tabloları seçmediniz").kilitle().en(400).boy(100).ac();
            })
            return false;
        }
            $(function()
            {
                jw('b secim',OK).baslik("Yedekleyeceğiniz Veritabanı!").icerik("Yedekleyeceğiniz veritabanının adı: <b>" + $('select[name="veritabani_id"] option:selected').text() + "</b><br /><br />Yedeklemeye devam etsin mi?").en(450).kilitle().ac();
            })

        function OK(x){
            if(x==1){
            var bekleme = jw("b bekle").baslik("Veri Tabanı Yedekleniyor...").en(350).boy(10).kilitle().akilliKapatPasif().ac();
                var str = 'yedekleyen=2';
                var t = $('#f').serialize();
                (t !='')? str += '&'+t :'';
                $.ajax({
                type: "POST",
                url: "backup.php",
                data: str,
                success: function(veriler){       
                bekleme.kapat();
                jw("b olumlu").baslik("Veri Tabanı Yedekleme Sonucu").icerik(veriler).en(450).boy(10).kilitle().akilliKapatPasif().kapaninca(function(){ window.location.href=window.location.href }).ac();       
                }
                });             
            } //if(x==1){
        } //function DUR(x){     
    } // function yedekle() {
</script>

<script type="text/javascript">
    function yenile(){
        window.self.location.reload();
    }
</script>

<script type="text/javascript">
    $('select[name="veritabani_id"]').change(function(){    
        $('#onek').val( $('select[name="veritabani_id"] option:selected').text() );
        if($('input[name=combine]:checked').val() == 3){
            tablolariYukle();
        }
    });

    $('#onek').val( $('select[name="veritabani_id"] option:selected').text() );

    function tablolariYukle(tablolar, sort) {
    const element = document.getElementById("tbliste");
    var veritabani_id = $('select[name="veritabani_id"]').val();

    $("#loading").show();
    $('#showTablolarYedekler').show();
    $('#showTablolar').show();

    $('#bekle-sort').fadeIn('');
    $("#sortliste").empty();
    $.ajax({
        type:'POST',
        url: "tabloliste.php",
        data: { secilen_yedekleme : veritabani_id, sort : sort},
        success: function(msg){
            $('#sortliste').html(msg);
            $('#bekle-sort').fadeOut('');
            $('#gizle').fadeOut('');
            $("#yedekler-listesi").hide();
            $("#loading").hide();
            element.scrollIntoView();
        }
    });   
    } 
</script>

<script type="text/javascript">
    function sil(gorev,adi,metin){
        $(function()
        {
            jw('b secim',OK).baslik("Silmeyi Onaylayın!").icerik("Sileceğiniz " + metin + " adı: " + adi + " <br /><br />Silmek istediğinizden emin misimiz?").en(450).kilitle().ac();
        })
        function OK(x){
            if(x==1){
                window.location.href = 'yedekle.php?'+gorev+'='+adi;
            }
        }
    }

</script>

<script type="text/javascript">
      $(document).ready(function(){
        $("#onek").on("keypress", function(event) {
              var englishAlphabetAndWhiteSpace = /[A-Z.a-z0-9-_]/g;
              var key = String.fromCharCode(event.which);
              if (event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 39 || englishAlphabetAndWhiteSpace.test(key)) {
                  return true;
              }
              return false;
          });
          $('#onek').on("paste", function(e) {
              e.preventDefault();
          });
      });
</script>

<style>
    strong {
        display: none;
        background-color: #E1E4F2;
        color: #F00;
        border: 1px solid #000;
        border-radius: 5px;
        position: absolute;
        right: 25%;
        padding: 5px;
        box-shadow: 0px 0px 10px #6E6E80;
        z-index: 0;
    }
</style>
<script type='text/javascript'>
    var satir = '';
    var query = '';
    var tarih = '';
    var firma = '';
</script>
<?php 
include('includes/footer.php');
?>
