<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");

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

?>

<thead>
    <tr class="bg-primary" style="text-align: center;line-height: 1.2;font-size: 1rem;">
        <th style="text-align:left;">ZIP - Sıkıştırılan Klasör Dosyalar - İndirmek için Tıkla</th>
        <th style="text-align:center;">ZIP Dosya Boyutu</th>
        <th style="text-align:center;">İçindeki Dosya Sayısı</th>
        <th style="text-align: center;">ZİP'ten Çıkar</th>
        <th style="text-align: center;">Yedekleme Zamanı</th>
        <th style="text-align: center;">Sil</th>
    </tr>
</thead>
<tbody>
<?php 

function zip_dosya_sayisi($dosya){
    $zip = new ZipArchive;

    // Zip dosyasının açılıp açılmadığını kontrol edin
    if ($zip->open($dosya, ZipArchive::CREATE)) 
    {
        $result_stats = array();
        //$dosyadizi = array();
        for ($i = 0; $i < $zip->numFiles; $i++)
            {
            $stat = $zip->statIndex($i);
            if(substr($stat['name'], -1) !== '/'){
                $result_stats[] = $stat;
                //$dosyadizi[$i] = $stat['name'];
            }
            }
//echo '<pre>' . print_r($dosyadizi, true) . '</pre>';
            //echo count($result_stats);
            $sayi = count($result_stats);
        return array($sayi, $zip->comment);
        // Zip dosyasını kapatın
        $zip->close();
    }
    // Zip dosyası açık/mevcut değilse
    else 
    {
        return array('Zip Açılmadı');
    }
}

  $toplam_dosya_boyutu = 0;
  $toplam_dosya_sayisi = 0;
  $zipdosyalar_dizi = glob(ZIPDIR."*.{zip}",GLOB_BRACE);
  rsort($zipdosyalar_dizi);
  //echo '<pre>' . print_r(zip_dosya_sayisi('../webzipler/yerel-2023-11-20-00-40-42.zip'), true) . '</pre>';
    foreach ($zipdosyalar_dizi as $dosya) {
        $boyutu       = filesize($dosya);
        $sayisi       = zip_dosya_sayisi($dosya)[0];
        $klasoradi    = zip_dosya_sayisi($dosya)[1];

        // Düzenli ifadeyi kullanarak tarih ve zaman bilgilerini ayıklamak
        preg_match('/(\d{4})-(\d{2})-(\d{2})-(\d{2})-(\d{2})-(\d{2})/', basename($dosya), $matches);
        // Ayıklanan bilgileri kontrol et
        if ($matches) {
            list(, $year, $month, $day, $hour, $minute, $second) = $matches;
            
            // Tarih ve zaman bilgilerini kullanarak bir DateTime nesnesi oluştur
            $datetime = new DateTime("$year-$month-$day $hour:$minute:$second");
            
            // DateTime nesnesini Unix zaman damgasına dönüştür
            $timestamp = $datetime->getTimestamp();
        }

        // Zip dosyalar listelenirken açılacak klasör adı dizin varmı kontrolu
        if(file_exists(DIZINDIR.$klasoradi)){
            $dizinvarmi = 1;
        } else {
            $dizinvarmi = 0;
        }

        echo "<tr>\n";
        echo "  <td style='text-align: left;'><img border='0' src='images/zip.png'>&nbsp;<span class='indir' style='cursor: pointer;' title='İndirmek için tıkla'>".basename($dosya)."</span></td>\n";
        echo "  <td style='text-align: right; padding-right: 70px;'>".showSize($boyutu)."</td>\n";
        echo "  <td style='text-align: right; padding-right: 110px;'>".number_format($sayisi, 0, ',', '.')."</td>\n";
        echo "  <td style='text-align: center;'><span style='cursor: pointer; text-decoration: underline dotted;' onclick=\"zipcikar('$dosya','$klasoradi','$dizinvarmi');\" title='{$dosya} dosyayı ZIP'ten çıkarmak için tıklayın'>ZIP 'ten Çıkar</span></td>\n";
        echo "  <td style='text-align: right;padding-right: 70px;'>".near_date($timestamp)."</td>";
        echo "  <td style='text-align: center;'><input type='checkbox' name='delete_ziplidizinler[]' value='".basename($dosya)."' title='Silmek için seç' onclick='javascript:renk(this);' /></td>\n";
        echo "</tr>\n";

        $toplam_dosya_boyutu += $boyutu;
        $toplam_dosya_sayisi += $sayisi;
    }
    if(count($zipdosyalar_dizi)==0){
        echo "<tr>\n";
        echo "  <th style='text-align: center;' colspan='6'>KLASÖRDE ZİP UZANTILI DOSYA MEVCUT DEĞİL</th>\n";
        echo "</tr>\n";
    }
?>
</tbody>
<tfoot>
    <tr style="border-bottom: 1px solid #ddd;">
        <td style='text-align: left;'>&nbsp</td>
        <td style='text-align: right; padding-right: 70px;'><b>Toplam: <?php echo showSize($toplam_dosya_boyutu); ?></b></td>
        <td style='text-align: right; padding-right: 110px;'><b>Toplam: <?php echo number_format($toplam_dosya_sayisi, 0, ',', '.'); ?> dosya</b></td>
        <td style='text-align: left;'>&nbsp</td>
        <td style='text-align: right;'><b>Tümünü silmek için seçiniz:</b></td>
        <td style='text-align: center;'><input type="checkbox" onclick="javascript:tumunu_sec(this);" title="Tümünü silmek için seç" /></td>
    </tr>
</tfoot>

<script type="text/javascript">
    $(".indir").click(function(){
        window.location="download.php?file=" + $(this).text();
    });
</script>
