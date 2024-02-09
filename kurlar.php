<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');

require "plugins/doviz/vendor/autoload.php";

use Teknomavi\Tcmb\Doviz;

$doviz = new Doviz();

/*
echo " USD Alış:" . $doviz->kurAlis("USD", Doviz::TYPE_EFEKTIFALIS);
echo "<br />";
echo " USD Satış:" . $doviz->kurSatis("USD", Doviz::TYPE_EFEKTIFSATIS);
echo "<br />";
echo " EURO Efektif Alış:" . $doviz->kurAlis("EUR", Doviz::TYPE_EFEKTIFALIS);
echo "<br />";
echo " EURO Efektif Satış:" . $doviz->kurSatis("EUR", Doviz::TYPE_EFEKTIFSATIS);
echo "<br />";
echo " EURO/USD Çapraz Kur:" . $doviz->kurSatis("EUR", Doviz::TYPE_CAPRAZ);
echo "<br />";
*/

    // dolar start //  
    $dolar_alis = $doviz->kurAlis("USD", Doviz::TYPE_EFEKTIFALIS);
    $dolar_satis = $doviz->kurSatis("USD", Doviz::TYPE_EFEKTIFSATIS);
    //dolar end //

    
    //euro start // 
    $euro_alis = $doviz->kurAlis("EUR", Doviz::TYPE_EFEKTIFALIS);
    $euro_satis = $doviz->kurSatis("EUR", Doviz::TYPE_EFEKTIFSATIS);
    //euro end //

    $bireuro_dolar = $doviz->kurSatis("EUR", Doviz::TYPE_CAPRAZ);

/*
    echo "Dolar Alış: ".$dolar_alis."<br />";
    echo "Dolar Satış: ".$dolar_satis."<br />";
    
    echo "Euro Alış: ".$euro_alis."<br />";
    echo "Euro Satış: ".$euro_satis."<br />";

    echo "Çapraz Kur: ".$bireuro_dolar."<br />";
*/

    $sonuc = [];  
    $kurlar = [];
    if(isset($dolar_satis)){
    $kurlar[] = ['id'=>1, 'doviz_cinsi'=>1, 'birime'=>3, 'tcmb_kur'=>$dolar_satis, 'birim'=>'USD den TL'];
    }
    if(isset($euro_satis)){
    $kurlar[] = ['id'=>2, 'doviz_cinsi'=>2, 'birime'=>3, 'tcmb_kur'=>$euro_satis, 'birim'=>'EURO dan TL'];
    }
    if(isset($bireuro_dolar)){
    $kurlar[] = ['id'=>3, 'doviz_cinsi'=>2, 'birime'=>1, 'tcmb_kur'=>$bireuro_dolar, 'birim'=>'EURO dan USD'];
    }
    
    if(isset($dolar_satis) OR isset($euro_satis) OR isset($bireuro_dolar)){

       if($dolar_satis > 0 AND $euro_satis > 0 AND $bireuro_dolar > 0){

    $check = $PDOdb->prepare("SELECT COUNT(*) AS num FROM dovizkuru WHERE doviz_cinsi=? AND birime=? AND tcmb_kur=? AND id=?");

    if (is_array($kurlar) || is_object($kurlar)) {

    foreach ($kurlar AS $key => $value){

     $id            = $value['id'];
     $doviz_cinsi   = $value['doviz_cinsi'];
     $birime        = $value['birime'];
     $tcmb_kur      = $value['tcmb_kur'];
     $birimne       = $value['birim'];

     $check->bindParam(1, $doviz_cinsi, PDO::PARAM_STR);
     $check->bindParam(2, $birime, PDO::PARAM_STR);
     $check->bindParam(3, $tcmb_kur, PDO::PARAM_STR);
     $check->bindParam(4, $id, PDO::PARAM_INT);
     $check->execute();
     $row = $check->fetch(PDO::FETCH_ASSOC);

     if($row['num'] == 0){

     try {
       $sorgu = $PDOdb->prepare("UPDATE dovizkuru SET 
       doviz_cinsi=?, 
       birime=?, 
       tcmb_kur=? 
       WHERE id=? ");
       
       $sorgu->bindParam(1, $doviz_cinsi, PDO::PARAM_STR);
       $sorgu->bindParam(2, $birime, PDO::PARAM_STR);
       $sorgu->bindParam(3, $tcmb_kur, PDO::PARAM_STR);
       $sorgu->bindParam(4, $id, PDO::PARAM_INT);
       $sorgu->execute();

       if ($sorgu->rowCount() > 0) {
            $sonuc[$birimne] = "Güncellendi";
        } else {
            $sonuc[$birimne] = "Güncellenemedi";
        }

    } catch (PDOException $e) {
        $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
        if (strpos($e->getMessage(), $existingkey) !== FALSE) {
            $sonuc[$birimne] = "Duplicate";
        } else {
            throw $e;
        }
      }
    }else{
        $sonuc[$birimne] = "Zaten Güncel";
    }
  } // foreach
} // if (is_array($kurlar) || is_object($kurlar)) {

       }else{
       $sonuc['TCMB'] = "TCMB'DAN KURLAR ALINAMADI";
       }
    }

        //$sonuc['TCMB'] = "TCMB'DAN KURLAR ALINAMADI";

    //echo '{"USD den TL":"Güncellendi","EURO dan TL":"Zaten Güncel","EURO dan USD":"Zaten Güncel"}';
    $json_sonuc = json_encode($sonuc, JSON_UNESCAPED_UNICODE);

    //echo '<pre>' . print_r($sonuc, true) . '</pre>';
    //echo "<br>".$dolar_satis."<br>".$euro_satis."<br>".$bireuro_dolar;
    //echo '<pre>' . print_r(json_decode($json_sonuc, true), true) . '</pre>';

?>
<div style="display:none"><?php echo $json_sonuc; ?></div>