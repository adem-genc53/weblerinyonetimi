<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");
?>
 <thead>
    <tr class="bg-primary" style="text-align: center;line-height: 1.2;font-size: 1rem;">
        <th style="text-align:left;">Klasör adı</th>
        <th style="text-align:center;">Klasör Boyutu</th>
        <th style="text-align:center;">Klasördeki Dosya Sayısı</th>
        <th style="text-align: center;">Klasör Adını Değiştir</th>
        <th style="text-align: center;">Sıkıştır</th>
    </tr>
</thead>

<?php 

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

function scan_dir($path){
    $ite=new RecursiveDirectoryIterator($path);

    $bytestotal=0;
    $nbfiles=0;
    foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
        if(basename($filename) != '.' && basename($filename) != '..'){
            $filesize = $cur->getSize();
            $bytestotal += $filesize;
            $nbfiles++;
            $files[] = $filename;
        }
    }
    //echo '<pre>' . print_r($files, true) . '</pre>';

    return array('total_files'=>$nbfiles,'total_size'=>$bytestotal);
}

  $dizinler_arr = json_decode($genel_ayarlar['haric_dizinler'], true);
  $total_size = 0;
  $total_files = 0;
  $dizinler_dizi = array_filter(glob(DIZINDIR.'*'), 'is_dir');
  natcasesort($dizinler_dizi);
  //echo '<pre>' . print_r($dizinler_dizi, true) . '</pre>';
    foreach($dizinler_dizi AS $dizinler){
        if(!in_array(basename($dizinler), $dizinler_arr)){
            $files = scan_dir($dizinler);
                echo "<tr>\n";
                echo "  <td style='text-align: left;'><img border='0' src='images/klasor.png'>&nbsp;".basename($dizinler)."</td>\n";
                echo "  <td style='text-align: right; padding-right: 70px;'>".showSize($files['total_size'])."</td>\n";
                echo "  <td style='text-align: right; padding-right: 110px;'>".number_format($files['total_files'], 0, ',', '.')."</td>\n";
                echo "  <td style='text-align: center;'><span style='cursor: pointer; text-decoration: underline dotted;' onclick=\"dizinadidegistir('".basename($dizinler)."');\" title='Klasörün adını değiştirmek için tıklayın'>Klasör Adını Değiştir</span></td>\n";
                echo "  <td style='text-align: center;'><span style='cursor: pointer; text-decoration: underline dotted;' onclick=\"sikistir('".basename($dizinler)."');\" title='".basename($dizinler)." Klasörü sıkıştırmak için tıklayın'>ZIP Yap</span></td>\n";
                echo "</tr>\n";
            $total_size += $files['total_size'];
            $total_files += $files['total_files'];
        }
    }

    if(count($dizinler_dizi)==0){
        echo "<tr>\n";
        echo "  <th style='text-align: center;' colspan='5'>KLASÖRDE KLASÖR MEVCUT DEĞİL</th>\n";
        echo "</tr>\n";
    }

?>
    <tfoot>
        <tr>
            <th style='text-align: left;'>&nbsp</td>
            <th style='text-align: right; padding-right: 70px;'>Toplam: <?php echo showSize($total_size); ?></td>
            <th style='text-align: right; padding-right: 110px;'>Toplam: <?php echo number_format($total_files, 0, ',', '.'); ?></td>
            <th style='text-align: center;'>&nbsp</td>
            <th style='text-align: center;'>&nbsp</td>
        </tr>
    </tfoot>
