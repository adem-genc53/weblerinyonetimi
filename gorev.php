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

################################################################################
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
########### HAFTA HESAPLAMALARI ################################################          

########### HAFTA HESAPLAMALARI BİTTİ ##########################################
////////////////////////////////////////////////////////////////////////////////
/*
* * * -  Şimdi güne, saate ve dakkaya arti 1 dakika ekle

x * * -x gün geçti ise saat 00, dakika 00 arti 1 ay ertele
x * * -x güne eşit ise "şimdiki saat ve dakika arti 1 dakika ekle
x * * -x gün daha gelmedi ise x günü saat 00 dakika 00 kaydet

x x * -x gün geçti ise x günü x saati dakika 00 1 ay ertele
x x * -x güne eşit x saat geçti ise x günü x saati dakika 00 1 ay ertele
x x * -x güne eşit x saate eşit ise x günü x saati ve şimdiki dakikaya 1 dakika ekle
x x * -x güne eşit x saat daha gelmedi ise x günü x saati dakika 00 kaydet
x x * -x gün daha gelmedi ise x gün x saat dakika 00 kaydet

x x x -x gün geçti ise 1 ay ertele
x x x -x güne eşit x saat eşit dakika eşit yada geçti ise 1 ay ertele
x x x -x güne eşit x saat eşit dakika daha gelmedi ise aynen kaydet
x x x -x güne eşit x saat daha gelmedi aynen kaydet
x x x -x gün daha gelmedi aynen kaydet

* * x -x dakika eşit yada geçti ise bugünün, bu saatine 1 saat ertle
* * x -x dakika daha gelmedi ise bugün bu saate x dakika kaydet

* x x -x saat geçti ise 1 gün ertele
* x x -x saat eşit dakika eşit yada geçti ise 1 gün ertele
* x x -x saat eşit dakika dakika daha gelmedi ise bugüne aynen kaydet
* x x -x saat daha gelmedi ise bugüne aynen kaydet

* x * -x saat eşit veya geçti ise bir gün erteler
* x * -x saat daha gelmedi ise bugün, x saat ve 00 dakika kaydet

x * x -x gün geçti ise 1 ay ertele
x * x -x gün ve dakika eşit ise 1 saat ertele (saat 11'i geçmedi ise)
x * x -x gün eşit ve dakika daha gelmedi ise aynen zamanı kaydet
x * x -x gün daha gelmedi ise aynen kaydet 
*/
////////////////////////////////////////////////////////////////////////////////
########### HAFTASIZ ZAMANLARI HESAPLAMA #######################################

if(isset($haftanin_gunleri_array)){
        if(is_array($haftanin_gunleri_array) AND in_array('-1', $haftanin_gunleri_array)){
        ## * * * ##
        // Şimdi güne, saate ve dakkaya arti 1 dakika ekle
        if($row['gun']<0 AND $row['saat']<0 AND $row['dakika']<0){

        $sonraki_calisma = mktime(date('G'), date('i')+1, 0, date('n'), date('j'), date('Y'));

        }
################################################################################          
        ## x * * ##
        // x gün geçti ise saat 00, dakika 00 arti 1 ay ertele
        if($row['gun']>-1 AND $row['gun'] < date('j') AND $row['saat']<0 AND $row['dakika']<0){

        $gun = $row['gun'];
        $sonraki_calisma = mktime(0, 0, 0, date('n')+1, $gun, date('Y'));
                 
        }
        ## x * * ##
        // x güne eşit ise "şimdiki saat ve dakika arti 1 dakika ekle
        if($row['gun']>-1 AND $row['gun']==date('j') AND $row['saat']<0 AND $row['dakika']<0){

        $gun = $row['gun'];
        $sonraki_calisma = mktime(date('G'), date('i')+1, 0, $ay, $gun, date('Y'));

        }          
        ## x * * ##
        // x gün daha gelmedi ise x günü saat 00 dakika 00 kaydet
        if($row['gun']>-1 AND $row['gun'] > date('j') AND $row['saat']<0 AND $row['dakika']<0){

        $gun = $row['gun'];
        $sonraki_calisma = mktime(0, 0, 0, date('n'), $gun, date('Y')); 

        }          
################################################################################          
        ## x x * ##
        // x gün geçti ise x günü x saati dakika 00 1 ay ertele
        if($row['gun']>-1 AND $row['gun'] < date('j') AND $row['saat']>-1 AND $row['dakika']<0){

        $gun = $row['gun'];
        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, 0, 0, date('n')+1, $gun, date('Y'));

        }          
        ## x x * ##
        // x güne eşit x saat geçti ise x günü x saati dakika 00 1 ay ertele
        if($row['gun']>-1 AND $row['gun']==date('j') AND $row['saat']>-1 AND $row['saat'] < date('G') AND $row['dakika']<0){

        $gun = $row['gun'];
        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, 0, 0, date('n')+1, $gun, date('Y'));

        }          
        ## x x * ##
        // x güne eşit x saate eşit ise x günü x saati ve şimdiki dakikaya 1 dakika ekle
        if($row['gun']>-1 AND $row['gun']==date('j') AND $row['saat']>-1 AND $row['saat']==date('G') AND $row['dakika']<0){

        $gun = $row['gun'];
        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, date('i')+1, 0, date('n'), $gun, date('Y'));

        }          
        ## x x * ##
        // x güne eşit x saat daha gelmedi ise x günü x saati dakika 00 kaydet
        if($row['gun']>-1 AND $row['gun']==date('j') AND $row['saat']>-1 AND $row['saat'] > date('G') AND $row['dakika']<0){

        $gun = $row['gun'];
        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, 0, 0, date('n'), $gun, date('Y'));

        }          
        ## x x * ##
        // x gün daha gelmedi ise x gün x saat dakika 00 kaydet
        if($row['gun']>-1 AND $row['gun'] > date('j') AND $row['saat']>-1 AND $row['dakika']<0){

        $gun = $row['gun'];
        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, 0, 0, date('n'), $gun, date('Y'));

        }
################################################################################
        ## x x x ##
        // x gün geçti ise 1 ay ertele
        if($row['gun']>-1 AND $row['gun'] < date('j') AND $row['saat']>-1 AND $row['dakika']>-1){

        $gun = $row['gun'];
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, date('n')+1, $gun, date('Y'));

        }
        ## x x x ##
        // x güne eşit x saat eşit dakika eşit yada geçti ise 1 ay ertele
        if($row['gun']>-1 AND $row['gun']==date('j') AND $row['saat']>-1 AND $row['saat']==date('G') AND $row['dakika']>-1 AND $row['dakika']<=date('i')){

        $gun = $row['gun'];
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, date('n')+1, $gun, date('Y'));

        }
        ## x x x ##
        // x güne eşit x saat eşit dakika daha gelmedi ise aynen kaydet
        if($row['gun']>-1 AND $row['gun']==date('j') AND $row['saat']>-1 AND $row['saat']==date('G') AND $row['dakika']>-1 AND $row['dakika'] > date('i')){

        $gun = $row['gun'];
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, date('n'), $gun, date('Y'));

        }
        ## x x x ##
        // x güne eşit x saat daha gelmedi aynen kaydet
        if($row['gun']>-1 AND $row['gun']==date('j') AND $row['saat']>-1 AND $row['saat'] > date('G') AND $row['dakika']>-1){

        $gun = $row['gun'];
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, date('n'), $gun, date('Y'));

        }
        ## x x x ##
        // x gün daha gelmedi aynen kaydet
        if($row['gun']>-1 AND $row['gun'] > date('j') AND $row['saat']>-1 AND $row['dakika']>-1){

        $gun = $row['gun'];
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, date('n'), $gun, date('Y'));

        }
################################################################################
        ## * * x ##
        // x dakika eşit yada geçti ise bugünün, bu saatine 1 saat ertle
        if($row['gun']<0 AND $row['saat']<0 AND $row['dakika']>-1 AND $row['dakika'] <= date('i')){

        $dakika = $row['dakika'];
        $sonraki_calisma = mktime(date('G')+1, $dakika, 0, date('n'), date('j'), date('Y'));

        }          
        ## * * x ##
        // x dakika daha gelmedi ise bugün bu saate x dakika kaydet
        if($row['gun']<0 AND $row['saat']<0 AND $row['dakika']>-1 AND $row['dakika'] > date('i')){

        $dakika = $row['dakika'];
        $sonraki_calisma = mktime(date('G'), $dakika, 0, date('n'), date('j'), date('Y'));

        }
################################################################################
        ## * x x ##
        // x saat geçti ise 1 gün ertele
        if($row['gun']<0 AND $row['saat']>-1 AND $row['saat'] < date('G') AND $row['dakika']>-1){

        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, date('n'), date('j')+1, date('Y'));

        }          
        ## * x x ##
        // x saat eşit dakika eşit yada geçti ise 1 gün ertele
        if($row['gun']<0 AND $row['saat']>-1 AND $row['saat']==date('G') AND $row['dakika']>-1 AND $row['dakika']<=date('i')){

        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, date('n'), date('j')+1, date('Y'));

        }
        ## * x x ##
        // x saat eşit dakika dakika daha gelmedi ise bugüne aynen kaydet
        if($row['gun']<0 AND $row['saat']>-1 AND $row['saat']==date('G') AND $row['dakika']>-1 AND $row['dakika'] > date('i')){

        $saat = $row['saat'];
        $dakika = $row['dakika']; 
        $sonraki_calisma = mktime($saat, $dakika, 0, date('n'), date('j'), date('Y'));

        }
        ## * x x ##
        // x saat daha gelmedi ise bugüne aynen kaydet
        if($row['gun']<0 AND $row['saat']>-1 AND $row['saat'] > date('G') AND $row['dakika']>-1){

        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, date('n'), date('j'), date('Y'));

        }
################################################################################
        ## * x * ##
        // x saat eşit yada geçti ise bir gün ertele
        if($row['gun']<0 AND $row['saat']>-1 AND $row['saat'] <= date('G') AND $row['dakika']<0){

        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, 0, 0, date('n'), date('j')+1, date('Y'));

        }
        // x saat eşit yada geçti ise bir gün ertele
        if($row['gun']<0 AND $row['saat']>-1 AND $row['saat'] > date('G') AND $row['dakika']<0){

        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, 0, 0, date('n'), date('j'), date('Y'));

        }
################################################################################
        ## x * ##
        //x * x -x gün geçti ise 1 ay ertele
        if($row['gun']>-1 AND $row['gun'] < date('j') AND $row['saat']<0 AND $row['dakika']>-1){

        $gun = $row['gun'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime(0, $dakika, 0, date('n')+1, $gun, date('Y'));

        }
        //x * x -x gün eşit ve dakika eşit yada geçti ise 1 saat ertele (saat 11'i geçmedi ise)
        if($row['gun']>-1 AND $row['gun']==date('j') AND $row['saat']<0 AND $row['dakika']>-1 AND $row['dakika']<=date('i')){

        $gun = $row['gun'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime(date('G'), $dakika, 0, date('n'), $gun, date('Y'));

        if(date('G')<=22 AND $row['dakika']<=59){
          $artibirsaat=1;
          $sonraki_calisma = mktime($saat, $dakika, 0, date('n'), $gun, date('Y'));
        }
        if(date('G')==23 AND $row['dakika']>=0){
          $artibiray=1;
          $sonraki_calisma = mktime($saat, $dakika, 0, date('n'), $gun, date('Y'));
        }
        }
        //x * x -x gün eşit ve dakika daha gelmedi ise aynen zamanı kaydet
        if($row['gun']>-1 AND $row['gun']==date('j') AND $row['saat']<0 AND $row['dakika']>-1 AND $row['dakika']>date('i')){

        $gun = $row['gun'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime(date('G'), $dakika, 0, date('n'), $gun, date('Y'));

        }          
        //x * x -x gün daha gelmedi ise aynen kaydet
        if($row['gun']>-1 AND $row['gun'] > date('j') AND $row['saat']<0 AND $row['dakika']>-1){

        $gun = $row['gun'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime(0, $dakika, 0, date('n')+1, $gun, date('Y'));

        }          
                          
        } // if(is_array($haftanin_gunleri_array) AND in_array('-1', $haftanin_gunleri_array)){
        } // if(isset($haftanin_gunleri_array)){
        
########### HAFTASIZ ZAMANLARI HESAPLAMA #######################################
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
########### ÇALIŞMA ZAMANI UNIX DEĞERİ ALMA İŞLEMİ #############################
################################################################################
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/*                    ÇOKLU HAFTA GÜNLERİ HESAPLAMA                           */
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/*
* * -Seçilen gün bugün ise şimdi saat ve şimdiki dakikaya artı 1 dakika ekle
* * -SEÇILEN GÜN BUGÜN DEĞIL ISE SONRAKI GÜNE VE SAAT 00 DAKIKA 00 ERTELE

x * -Seçilen gün bugün ise saat geçti ise SONRAKI GÜNE ve saat xx dakika 00 ertele
x * -Seçilen gün bugün ise saat eşit ise bugüne saat xx şimdiki dakikaya artı 1 dakika ekle
x * -Seçilen gün bugün ise saat henüz gelmedi ise bugüne saat xx dakika 00 ayarla
x * -SEÇILEN GÜN BUGÜN DEĞIL ISE SONRAKI GÜNE VE SAAT XX DAKIKA 00 KAYDET

x x -Seçilen gün bugün ise saat geçti ise SONRAKI GÜNE ve saat xx dakika xx ertele
x x -Seçilen gün bugün ise saat eşit ise dakika eşit yada geçti ise SONRAKI GÜNE ve saat xx dakika xx ertele
x x -Seçilen gün bugün ise saat eşit ise dakika henüz gelmedi ise bugüne ve saat xx dakika xx kaydet
x x -Seçilen gün bugün ise saat henüz gelmedi ise bugüne ve saat xx dakika xx kaydet
x x -SEÇILEN GÜN BUGÜN DEĞIL ISE SONRAKI GÜNE VE SAAT XX DAKIKA XX KAYDET

* x -Seçilen gün bugün ise dakika eşit yada geçti ise şimdiki saat ve dakika xx artı 1 saat ertele
* x -Seçilen gün bugün ise dakika henüz gelmedi ise şimdiki saat ve dakika xx kaydet
* x -SEÇILEN GÜN BUGÜN DEĞIL ISE SONRAKI GÜNE VE SAAT 00 DAKIKA XX KAYDET
*/
########### HAFTA HESAPLAMALARI ################################################
        if(isset($haftanin_gunleri_array)){
        if(is_array($haftanin_gunleri_array) AND array_intersect($haftadizi, $haftanin_gunleri_array)){
        $haftanin_gunleri = $haftanin_gunleri_array;
        $gunsayisi = count($haftanin_gunleri);
        $haftaninbugunu = date('N');
        if(isset($haftanin_gunleri)){
        // Dizide bugün varsa bugünü ver                    
        if(in_array($haftaninbugunu,$haftanin_gunleri)){
        $haftaningunu=date('N');
        // Dizide bugün yoksa dizideki bugünden sonra gelen günü ver 
        }elseif(!in_array($haftaninbugunu,$haftanin_gunleri)){
        $number = $haftanin_gunleri_array;
        sort($number);
        $sourc = $haftaninbugunu;
        $haftaningunu = $number[0];

        foreach($number as $numbe) {
        if($numbe > $sourc) {
        $haftaningunu = $numbe;
        break;
        }
        }          
        
        }
////////////////////////////////////////////////////////////////////////////////
        $numbers = $haftanin_gunleri_array;
        sort($numbers);
        $source = $haftaninbugunu;
        $sonraki_gun = $numbers[0];

        foreach($numbers as $number) {
        if($number > $source) {
        $sonraki_gun = $number;
        break;
        }
        }

             if($sonraki_gun=='1'){
        $h_tarihi=date('d.m.Y', strtotime('noon monday')); // Pazartesi
        }elseif($sonraki_gun=='2'){
        $h_tarihi=date('d.m.Y', strtotime('noon tuesday')); // Salı
        }elseif($sonraki_gun=='3'){
        $h_tarihi=date('d.m.Y', strtotime('noon wednesday')); // Çarşamba
        }elseif($sonraki_gun=='4'){
        $h_tarihi=date('d.m.Y', strtotime('noon thursday')); // Perşembe
        }elseif($sonraki_gun=='5'){
        $h_tarihi=date('d.m.Y', strtotime('noon friday')); // Cuma
        }elseif($sonraki_gun=='6'){
        $h_tarihi=date('d.m.Y', strtotime('noon saturday')); // Cumartesi
        }elseif($sonraki_gun=='7'){
        $h_tarihi=date('d.m.Y', strtotime('noon sunday')); // Pazar
        }          
////////////////////////////////////////////////////////////////////////////////
        }
     
################################################################################
        if($haftaningunu=='1'){
            $haftanintarihi=date('d.m.Y', strtotime('noon monday')); // Pazartesi
        }elseif($haftaningunu=='2'){
            $haftanintarihi=date('d.m.Y', strtotime('noon tuesday')); // Salı
        }elseif($haftaningunu=='3'){
            $haftanintarihi=date('d.m.Y', strtotime('noon wednesday')); // Çarşamba
        }elseif($haftaningunu=='4'){
            $haftanintarihi=date('d.m.Y', strtotime('noon thursday')); // Perşembe
        }elseif($haftaningunu=='5'){
            $haftanintarihi=date('d.m.Y', strtotime('noon friday')); // Cuma
        }elseif($haftaningunu=='6'){
            $haftanintarihi=date('d.m.Y', strtotime('noon saturday')); // Cumartesi
        }elseif($haftaningunu=='7'){
            $haftanintarihi=date('d.m.Y', strtotime('noon sunday')); // Pazar
        }
      
        ## * * ##
        // Seçilen gün bugün ise şimdi saat ve şimdiki dakikaya artı 1 dakika ekle          
        if(date('N') == $haftaningunu AND $row['saat'] <0 AND $row['dakika'] <0){
        $degisken = explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $sonraki_calisma = mktime(date('G'), date('i')+1, 0, $ay, $gun, $yil);

        //echo mktime($saat, $dakika+$artibirdakika, 0, $ay, $gun, $yil)."<br>";
        //echo date_tr('d M Y, l, H:i', mktime($saat, $dakika+$artibirdakika, 0, $ay, $gun, $yil));       
        }
        ## x * ##
        // x * -Seçilen gün bugün ise saat eşit ise bugüne saat xx şimdiki dakikaya artı 1 dakika ekle          
        if(date('N')==$haftaningunu AND $row['saat']>-1 AND $row['dakika']<0 AND $row['saat']==date('G')){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, date('i')+1, 0, $ay, $gun, $yil);          
        }
        ## x * ##
        // x * -Seçilen gün bugün ise saat henüz gelmedi ise bugüne saat xx dakika 00 ayarla          
        if(date('N')==$haftaningunu AND $row['saat']>-1 AND $row['dakika']<0 AND $row['saat']>date('G')){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, 0, 0, $ay, $gun, $yil);          
        }
        
        ## x x ##
        // x x -Seçilen gün bugün ise saat eşit ise dakika henüz gelmedi ise bugüne ve saat xx dakika xx kaydet
        if(date('N')==$haftaningunu AND $row['saat']>-1 AND $row['dakika']>-1 AND $row['saat']==date('G') AND $row['dakika']>date('i')){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, $ay, $gun, $yil);         
        }
        ## x x ##
        // x x -Seçilen gün bugün ise saat henüz gelmedi ise bugüne ve saat xx dakika xx kaydet
        if(date('N')==$haftaningunu AND $row['saat']>-1 AND $row['dakika']>-1 AND $row['saat']>date('G')){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, $ay, $gun, $yil);         
        }
        
        ## * x ##
        // * x -Seçilen gün bugün ise dakika eşit yada geçti ise şimdiki saat ve dakika xx artı 1 saat ertele
        if(date('N')==$haftaningunu AND $row['saat']<0 AND $row['dakika']>-1 AND $row['dakika']<=date('i')){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime(date('G')+1, $dakika, 0, $ay, $gun, $yil);          
        }
        ## * x ##
        // * x -Seçilen gün bugün ise dakika henüz gelmedi ise şimdiki saat ve dakika xx kaydet
        if(date('N')==$haftaningunu AND $row['saat']<0 AND $row['dakika']>-1 AND $row['dakika']>date('i')){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime(date('G'), $dakika, 0, $ay, $gun, $yil);          
        }                                                            
## BUGÜN ANCAK SAAT VEYA DAKİKA EŞİT YADA GEÇTİ ##########          
        ## x * ##
        // x * -Seçilen gün bugün ve saat geçti ise SONRAKI GÜNE ve saat xx dakika 00 ertele          
        if(date('N')==$haftaningunu AND $row['saat']>-1 AND $row['dakika']<0  AND $row['saat'] < date('G') AND $gunsayisi=='1'){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, 0, 0, $ay, $gun+7, $yil);

        }elseif(date('N')==$haftaningunu AND $row['saat']>-1 AND $row['dakika']<0  AND $row['saat'] < date('G') AND $gunsayisi!='1'){

        $degisken=explode(".", $h_tarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];          
        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, 0, 0, $ay, $gun, $yil);                         
        }           
        ## x x ##
        // x x -Seçilen gün bugün ise saat geçti ise SONRAKI GÜNE ve saat xx dakika xx ertele          
        if(date('N')==$haftaningunu AND $row['saat']>-1 AND $row['dakika']>-1  AND $row['saat'] < date('G') AND $gunsayisi=='1'){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, $ay, $gun+7, $yil);

        }elseif(date('N')==$haftaningunu AND $row['saat']>-1 AND $row['dakika']>-1  AND $row['saat'] < date('G') AND $gunsayisi!='1'){

        $degisken=explode(".", $h_tarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];          
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, $ay, $gun, $yil);
        }          
        ## x x ##
        // x x -Seçilen gün bugün ise saat eşit ise dakika eşit yada geçti ise SONRAKI GÜNE ve saat xx dakika xx ertele         
        if(date('N')==$haftaningunu AND $row['saat']>-1 AND $row['dakika']>-1 AND $row['saat']==date('G') AND $row['dakika']<=date('i') AND $gunsayisi=='1'){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, $ay, $gun+7, $yil);

        }elseif(date('N')==$haftaningunu AND $row['saat']>-1 AND $row['dakika']>-1 AND $row['saat']==date('G') AND $row['dakika']<=date('i') AND $gunsayisi!='1'){

        $degisken=explode(".", $h_tarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];          
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, $ay, $gun, $yil);
        }
                            
## BUGÜN DEĞİL İSE ###################          
        ## * * ##
        // * * -SEÇILEN GÜN BUGÜN DEĞIL ISE SONRAKI GÜNE VE SAAT 00 DAKIKA 00 ERTELE          
        if(date('N')!=$haftaningunu AND $row['saat']<0 AND $row['dakika']<0){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $sonraki_calisma = mktime(0, 0, 0, $ay, $gun, $yil);          
        }
        ## x * ##
        // x * -SEÇILEN GÜN BUGÜN DEĞIL ISE SONRAKI GÜNE VE SAAT XX DAKIKA 00 KAYDET          
        if(date('N')!=$haftaningunu AND $row['saat']>-1 AND $row['dakika']<0){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $saat = $row['saat'];
        $sonraki_calisma = mktime($saat, 0, 0, $ay, $gun, $yil);          
        }                   
        ## x x ##
        // x x -SEÇILEN GÜN BUGÜN DEĞIL ISE SONRAKI GÜNE VE SAAT XX DAKIKA XX KAYDET         
        if(date('N')!=$haftaningunu AND $row['saat']>-1 AND $row['dakika']>-1){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $saat = $row['saat'];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime($saat, $dakika, 0, $ay, $gun, $yil);
        }
        ## * x ##
        // * x -SEÇILEN GÜN BUGÜN DEĞIL ISE SONRAKI GÜNE VE SAAT 00 DAKIKA XX KAYDET
        if(date('N')!=$haftaningunu AND $row['saat']<0 AND $row['dakika']>-1){
        $degisken=explode(".", $haftanintarihi);
        $ay = $degisken[1];
        $gun = $degisken[0];
        $yil = $degisken[2];
        $dakika = $row['dakika'];
        $sonraki_calisma = mktime(0, $dakika, 0, $ay, $gun, $yil);
        }                   
        } // if(is_array($haftanin_gunleri_array) AND array_intersect($haftadizi, $haftanin_gunleri_array)){
        } // if(isset($haftanin_gunleri_array)){

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

        } // while ($row = $gorevler->fetch()) {
?>