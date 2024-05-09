<?php 
// Bismillahirrahmanirrahim
require('includes/connect.php');
ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);
/*
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Connection: close");
*/
//require_once('check-login.php');
require_once("includes/turkcegunler.php");

        $haftadizi = array(1,2,3,4,5,6,7);
        $haftanin_gunleri_array = array();
        $output_array = array();
        $sonraki_calisma = false;
        $tablolar = array();
        $yedeklenecek_tablolar = array();

        $simdi = time();
/*
		function SiradakidbSec($PDOdb, $databaseadi){
        $select = $PDOdb->prepare('UPDATE veritabanlari SET selected=?, islemi_yapan=? WHERE db_name=?');
		$select->execute([1,'gorev.php',$databaseadi]);
		//return true;
		}


		function SecilidbAdi($PDOdb){
		$secilenveritabani = $PDOdb->prepare("SELECT * FROM veritabanlari WHERE selected =?");
        $secilenveritabani->execute([1]);
		$secilen_veritabani = $secilenveritabani->fetch();
		return $secilen_veritabani['db_name'];
		}
*/

    // zamanlama görevinde sonraki çalışma zamanı ile şimdi ki zamanı karşılaştırıp şimdiki zaman eşit veya geçiyor sa döngüyü çalıştır
    $gorevler = $PDOdb->prepare("SELECT * FROM zamanlanmisgorev WHERE aktif =? AND sonraki_calisma <= ? ORDER BY id DESC");
    $gorevler->execute(['Aktif', $simdi]);

    while ($row = $gorevler->fetch()) {

    set_time_limit(0);
    
    $haftanin_gunleri_array = explode(",", $row['haftanin_gunu']);

// Gönderilen gün değeri
$gun = $row['gun'] ?? "-1" ;
// Gönderilen saat değeri
$saat = $row['saat'] ?? "-1";
// Gönderilen dakika değeri
$dakika = $row['dakika'] ?? "-1";
// Gönderilen haftanın değeri
$haftanin_gunu = explode(",", $row['haftanin_gunu']);

include_once("cron_zamanlayici.php");

    if(isset($sonraki_calisma)){
        if(strlen((string) $sonraki_calisma) == 10){
            $basarili = 1;
        }else{
            $basarili = 0;
        }
    }
#########################################################################################################################
########################################### GÖREVLERİ YÜRÜTME BAŞLANGICI ################################################
#########################################################################################################################

include("gorev_yurutucu.php");

#########################################################################################################################
########################################### GÖREVLERİ YÜRÜTME SONU ######################################################
#########################################################################################################################
        //unset($calistirma_sonuc_mesaji);
        } // while ($row = $gorevler->fetch()) {
            //echo $sonraki_calisma;
            unset($sonraki_calisma);
            
?>