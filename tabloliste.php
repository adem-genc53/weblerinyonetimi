<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once(__DIR__ . '/hash.php');
$hash = new Hash;

    //echo '<pre>' . print_r($_POST, true) . '</pre>';
    //exit;

//@error_reporting(E_ALL & ~E_NOTICE);
//header('Content-Type: text/html; charset=utf-8');

    $secilen_tablolar = array();
    if(isset($_POST['tablolar']) && !empty($_POST['tablolar']) ){
        $secilen_tablolar = array_map('trim', explode(',',$_POST['tablolar']));
    }

    //echo '<pre>' . print_r($secilen_tablolar, true) . '</pre>';

    $post_tablolar = $_POST['tablolar'] ?? '';
#########################################################################################################################################
    // Ajax ile veritabanı ID geliyormu, geliyorsa hem değişkene hemde sessiona ata
    // Gelmiyorsa else den sesiiondan kullan
    // POST ile veritabanı id
    if(isset($_POST['secilen_yedekleme']) && !empty($_POST['secilen_yedekleme'])){
        $veritabani_id = $_POST['secilen_yedekleme'];
    }else{
        $veritabani_id = "";
    }
#########################################################################################################################################
    // Seçilen veritabanı 
    $default = $PDOdb->prepare("SELECT * FROM veritabanlari WHERE id=? LIMIT 1");
    $default->execute([$veritabani_id]);
    $varsayilan = $default->fetch(PDO::FETCH_ASSOC);

    $db_yok = false;
    if($default->rowCount() > 0){

        $db_name = "";
        // Seçilen veritabanı varsa bağlantı oluşturuyoruz
            $secilen = "mysql:host=".$varsayilan['database_host'].";dbname=".$varsayilan['db_name'].";charset=".$varsayilan['charset'].";port=".$varsayilan['port']."";
            try {
                $PDOdbsecilen = new PDO($secilen, $hash->take($varsayilan['database_user']), $hash->take($varsayilan['database_password']), $options);
                $PDOdbsecilen->exec("set names ".CHARSET);
                $PDOdbsecilen->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db_yok = true;
                $db_name = $varsayilan['db_name'];
            } catch (\PDOException $e) {
                die($e->getMessage());
                $PDOdbsecilen = false;
                $db_yok = false;
                //echo $e->getMessage(); // Unknown database 'antenfiyati_uce-stok' 
                //throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
            //$db_name = $varsayilan['db_name'];
    }else{ // Veritabanı hiç seçili değil ise bu scriptin veritabanını bağla
        $PDOdbsecilen = $PDOdb;
        $db_name = DB_NAME;
        $db_yok = true;
    }
#########################################################################################################################################
    $post_sort = isset($_POST['sort']) ? $_POST['sort'] : 'TABLE_NAME ASC';

    $sort = isset($_POST['sort']) || !empty($_POST['sort']) ? $_POST['sort'] : 'TABLE_NAME ASC';

    if(empty($sort)) exit;

    $result = $PDOdbsecilen->query("
    SELECT TABLE_SCHEMA, 
    TABLE_NAME, 
    (INDEX_LENGTH+DATA_LENGTH) AS SIZE_KB, 
    TABLE_ROWS, DATA_FREE, INDEX_LENGTH, DATA_LENGTH
    FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = '$db_name'
    ORDER BY $sort ");

    $tablolararr = $result->fetchAll();

    //echo '<pre>' . print_r($tablolararr, true) . '</pre>';

    $toplam_tablo = "";
    $veri = "";
    $indexveri = "";
    $ekyuk = "";
    $satirlar = "";
    $toplam = "";

    if($post_sort == 'TABLE_NAME DESC'){
    $asagiokname = '<img id="satiragore" border="0" src="images/yukari.png">';
    $sortname = "TABLE_NAME ASC";
    }elseif($post_sort == 'TABLE_NAME ASC'){
    $sortname = "TABLE_NAME DESC";
    $asagiokname = '<img id="satiragore" border="0" src="images/asagi.png">';
    }else{
    $sortname = "TABLE_NAME ASC";
    $asagiokname = "";
    }

    if($post_sort == 'TABLE_ROWS DESC'){
    $asagioksatir = '<img id="satiragore" border="0" src="images/asagi.png">';
    $sortsatir = "TABLE_ROWS ASC";
    }elseif($post_sort == 'TABLE_ROWS ASC'){
    $asagioksatir = '<img id="satiragore" border="0" src="images/yukari.png">';
    $sortsatir = "TABLE_ROWS DESC";
    }else{
    $sortsatir = "TABLE_ROWS DESC";
    $asagioksatir = "";
    }

    if($post_sort == 'SIZE_KB DESC'){
    $asagiokboyutu = '<img id="satiragore" border="0" src="images/asagi.png">';
    $sortboyutu = "SIZE_KB ASC";
    }elseif($post_sort == 'SIZE_KB ASC'){
    $asagiokboyutu = '<img id="satiragore" border="0" src="images/yukari.png">';
    $sortboyutu = "SIZE_KB DESC";
    }else{
    $sortboyutu = "SIZE_KB DESC";
    $asagiokboyutu = "";
    }

    if($post_sort == 'DATA_LENGTH DESC'){
    $asagiokdata = '<img id="satiragore" border="0" src="images/asagi.png">';
    $sortdata = "DATA_LENGTH ASC";
    }elseif($post_sort == 'DATA_LENGTH ASC'){
    $asagiokdata = '<img id="satiragore" border="0" src="images/yukari.png">';
    $sortdata = "DATA_LENGTH DESC";
    }else{
    $sortdata = "DATA_LENGTH DESC";
    $asagiokdata = "";
    }

    if($post_sort == 'INDEX_LENGTH DESC'){
    $asagiokindex = '<img id="satiragore" border="0" src="images/asagi.png">';
    $sortindex = "INDEX_LENGTH ASC";
    }elseif($post_sort == 'INDEX_LENGTH ASC'){
    $asagiokindex = '<img id="satiragore" border="0" src="images/yukari.png">';
    $sortindex = "INDEX_LENGTH DESC";
    }else{
    $sortindex = "INDEX_LENGTH DESC";
    $asagiokindex = "";
    }

    if($post_sort == 'DATA_FREE DESC'){
    $asagiokfree = '<img id="satiragore" border="0" src="images/asagi.png">';
    $sortfree = "DATA_FREE ASC";
    }elseif($post_sort == 'DATA_FREE ASC'){
    $asagiokfree = '<img id="satiragore" border="0" src="images/yukari.png">';
    $sortfree = "DATA_FREE DESC";
    }else{
    $sortfree = "DATA_FREE DESC";
    $asagiokfree = "";
    }

?>
    <colgroup span="7">
        <col style="width:28%"></col>
        <col style="width:12%"></col>
        <col style="width:12%"></col>
        <col style="width:12%"></col>
        <col style="width:12%"></col>
        <col style="width:12%"></col>
        <col style="width:12%"></col>
    </colgroup>

    <thead>
        <tr class="bg-primary">
            <th colspan="7" style="text-align: center;line-height: .20;font-size: 1rem;">Seçili Veri Tabanı <span style="color: yellow;"><?php echo $db_name; ?></span> Tablolarıdır</th>
        </tr>
        <tr>
            <td style="padding:5px 0px 0px 10px;cursor:pointer;"><div style="text-align:left;"><a class="table-sort" onclick="javascript:tablolariYukle('<?php echo $veritabani_id; ?>','<?php echo $post_tablolar; ?>','<?php echo $sortname ?>');" title="TABLO ADINA GÖRE SIRALA"><b>Tablo Adı</b><?php echo $asagiokname ?></a></div></td>
            <td style="nowrap:nowrap;padding:5px 20px 0px 0px;text-align:right;cursor:pointer;"><a class="table-sort" onclick="javascript:tablolariYukle('<?php echo $veritabani_id; ?>','<?php echo $post_tablolar; ?>','<?php echo $sortdata ?>');" title="VERİ BOYUTUNA GÖRE SIRALA"><b>Veri Boyutu</b><?php echo $asagiokdata ?></a></td>
            <td style="nowrap:nowrap;padding:5px 10px 0px 0px;text-align:right;cursor:pointer;"><a class="table-sort" onclick="javascript:tablolariYukle('<?php echo $veritabani_id; ?>','<?php echo $post_tablolar; ?>','<?php echo $sortindex ?>');" title="INDEX BOYUTUNA GÖRE SIRALA"><b>İndex Boyutu</b><?php echo $asagiokindex ?></a></td>
            <td style="nowrap:nowrap;padding:5px 10px 0px 0px;text-align:right;cursor:pointer;"><a class="table-sort" onclick="javascript:tablolariYukle('<?php echo $veritabani_id; ?>','<?php echo $post_tablolar; ?>','<?php echo $sortfree ?>');" title="EK YÜK BOYUTUNA GÖRE SIRALA"><b>Ek Yük</b><?php echo $asagiokfree ?></a></td>
            <td style="nowrap:nowrap;padding:5px 20px 0px 0px;text-align:right;cursor:pointer;"><a class="table-sort" onclick="javascript:tablolariYukle('<?php echo $veritabani_id; ?>','<?php echo $post_tablolar; ?>','<?php echo $sortsatir ?>');" title="TABLO SATIRA GÖRE SIRALA"><b>Satır</b><?php echo $asagioksatir ?></a></td>
            <td style="nowrap:nowrap;padding:5px 20px 0px 0px;text-align:right;cursor:pointer;"><a class="table-sort" onclick="javascript:tablolariYukle('<?php echo $veritabani_id; ?>','<?php echo $post_tablolar; ?>','<?php echo $sortboyutu ?>');" title="TABLO BOYUTUNA GÖRE SIRALA"><b>Boyutu</b><?php echo $asagiokboyutu ?></a></td>
            <td style="padding:5px 10px 0px 0px;"><div style="text-align:right;">Tümünü Seç: <input type="checkbox" onclick="javascript:tumunusec(this);" id="hepsi" title="Tümünü Yedeklemek için Seç" /></div></td>        
        </tr>
    </thead>
<?php
    $ek_yuk = 0;
    $indexveri = 0;
    $veri = 0;
    $satir = 0;
    $boyut = 0;

    $i = 1;
    foreach( $tablolararr AS $table ) {

    $toplam_tablo = $i;
    $ek_yuk += $table['DATA_FREE'];
    $ekyuk = showSize($ek_yuk);
    $ekyuktek = showSize($table['DATA_FREE']);
    $indexboyutu = showSize($table['INDEX_LENGTH']);
    $indexveri += $table['INDEX_LENGTH'];
    $veriboyutu = showSize($table['DATA_LENGTH']);
    $veri += $table['DATA_LENGTH'];          
    $boyutu = showSize($table['SIZE_KB']);
    $satir += $table['TABLE_ROWS'];
    $satirlar = number_format($satir, 0, ',', '.');
    $boyut += $table['SIZE_KB'];
    $toplam = showSize($boyut);

    if (is_array($secilen_tablolar) && in_array($table['TABLE_NAME'], $secilen_tablolar)) {
        $secilirenk = 'style="background-color: rgb(255, 235, 144); border-bottom: thin solid;"';
        $tdaltsiyahcizgi = 'style="nowrap:nowrap;padding:5px 0px 0px 10px; background: rgb(255, 235, 144); border-bottom: thin solid;"';
        $birdensonrakitdler = 'style="nowrap:nowrap;padding:5px 25px 0px 0px;text-align:right; background: rgb(255, 235, 144); border-bottom: thin solid;"';
    }else{
        $secilirenk = '';
        $tdaltsiyahcizgi = 'style="nowrap:nowrap;padding:5px 0px 0px 10px;"';
        $birdensonrakitdler = 'style="nowrap:nowrap;padding:5px 25px 0px 0px;text-align:right;"';
    }
    
    ?>
    <tr <?php echo $secilirenk; ?>>          
    <td <?php echo $tdaltsiyahcizgi; ?>>
    <?php echo $table['TABLE_NAME']; ?>
    </td>
    <td <?php echo $birdensonrakitdler; ?>>
    <?php echo $veriboyutu ?>
    </td>
    <td <?php echo $birdensonrakitdler; ?>>
    <?php echo $indexboyutu ?>
    </td>
    <td <?php echo $birdensonrakitdler; ?>>
    <?php echo $ekyuktek ?>
    </td>     
    <td <?php echo $birdensonrakitdler; ?>>
    <?php echo $table['TABLE_ROWS'] ?>
    </td>
    <td <?php echo $birdensonrakitdler; ?>>
    <?php echo $boyutu ?>
    </td>
    <td <?php echo $birdensonrakitdler; ?>>
    <?php

    if (is_array($secilen_tablolar) && in_array(trim($table['TABLE_NAME']), $secilen_tablolar)) {
        echo "<input type=\"checkbox\" class=\"tablolar\" checked=\"checked\" name=\"tablolar[]\" value=\"".$table['TABLE_NAME']."\" onclick=\"javascript:renk(this);\">";
    }else{
        echo "<input type=\"checkbox\" class=\"tablolar\" name=\"tablolar[]\" value=\"".$table['TABLE_NAME']."\" onclick=\"javascript:renk(this);\">";
    }

    ?>
    </td>
    </tr>
    <?php
    $i++;
    }
    function showSize($bytes) {
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
?>
<tfoot>
     <tr>
     <td style="nowrap:nowrap;padding:5px 0px 0px 10px;"><b>Toplam Tablo Sayısı:&nbsp;<?php echo $toplam_tablo ?></b></td>
     <td style="nowrap:nowrap;padding:5px 25px 0px 0px;text-align:right;"><b>Top Veri: <?php echo showSize($veri) ?></b></td>
     <td style="nowrap:nowrap;padding:5px 25px 0px 0px;text-align:right;"><b>Top İndex: <?php echo showSize($indexveri) ?></b></td>
     <td style="nowrap:nowrap;padding:5px 10px 0px 0px;text-align:right;"><b>Top Ek Yük: <?php echo $ekyuk ?></b></td>
     <td style="nowrap:nowrap;padding:5px 25px 0px 0px;text-align:right;"><b>Top Satır: <?php echo $satirlar ?></b></td>
     <td style="nowrap:nowrap;padding:5px 25px 0px 0px;text-align:right;"><b>Top Boyutu: <?php echo $toplam ?></b></td>
     <td style="nowrap:nowrap;padding:5px 10px 0px 0px;text-align:right;"><b>Tümünü Seç:</b> <input type="checkbox" onclick="javascript:tumunusec(this);" id="hepsiiki" title="Tümünü Yedeklemek için Seç" /></td>
     </tr>
</tfoot>

<script type="text/javascript">
    $(".tablolar").change(function(){
        var ischecked= $(this).is(':checked');
        if(ischecked){
            $(this).closest('tr').find("td").css('background', '#FFEB90');
            $(this).closest('tr').find("td").css('borderBottom', 'thin solid');
            $(this).closest('tr').find("td").css('color', '');
        }else{
            $(this).closest('tr').find("td").css('background', '');
            $(this).closest('tr').find("td").css('borderBottom', '');
            $(this).closest('tr').find("td").css('color', '');
        }
    });

    function tumunusec(spanChk){
        var IsChecked = spanChk.checked;
        var Chk = spanChk;
            Parent = document.getElementById('sortliste');
            var items = Parent.getElementsByTagName('input');
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
