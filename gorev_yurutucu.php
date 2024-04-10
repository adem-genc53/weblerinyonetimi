<?php 
// Bismillahirrahmanirrahim
    if(isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1 && isset($_POST['gorevid']) && is_numeric($_POST['gorevid'])){

    ini_set('memory_limit', '-1');
    ignore_user_abort(true);
    set_time_limit(0);
    require('includes/connect.php');

    unset($calistirma_sonuc_mesaji);
    $cikis_sonuc = "";
    $calistirma_sonuc_mesaji = "";
    $otomatikyedeksil = false;
    $gorevler = $PDOdb->prepare("SELECT * FROM zamanlanmisgorev WHERE id=? LIMIT 1 ");
    $gorevler->execute([ $_POST['gorevid'] ]);
    $basarili = true;
    $row = $gorevler->fetch();
    //$otomatikyedeksil = $row['yerel_korunacak_yedek'] == '-1' ? false : $row['yerel_korunacak_yedek'];
    $gorevadi = $row['gorev_adi']."<br />";
    $sonraki_calisma = $row['sonraki_calisma'];
    }
        //echo "Başarılı: ".$basarili."<br />";
        //echo "Sonraki Zaman: ".$sonraki_calisma."<br />";
        //exit;
        //echo $row['dosya_adi'];
        $yedeklenecek_tablolar = [];
        $post_dizi = [];
############# ZAMANI GELEN DOSYALARI ÇALIŞTIR ##################################
        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';

        if($basarili == 1){

        $starttime = microtime(true);

        $calistirilacak_dosya = $row['dosya_adi'];

        // Çalıştırılacak dosyanın başında http veya https varmı, yani tam url mi girilmiş kontrolu
        if(substr($calistirilacak_dosya, 0, 7)  ==  'http://' OR substr($calistirilacak_dosya, 0, 8)  ==  'https://'){
                //Tam url ise olduğu gibi url yi ver
        $url = $calistirilacak_dosya;
        // Çalıştırılacak dosyanın başında www varmı, yani tam url mi girilmiş kontrolu
        }elseif(substr($calistirilacak_dosya, 0, 4)  ==  'www.' AND isset($_SERVER["HTTPS"]) ){
                // Tam url ise başında protokol yoksa ekleyerek url yi ver
        $url = $protocol."://".$calistirilacak_dosya;
        // Hiçbiri içermiyorsa
        }else{
                // Çalıştırılacak dosya lokal dosya ise tam url tamamlarak url yi ver
        $url = $protocol."://".$_SERVER['SERVER_NAME']."/".$calistirilacak_dosya;
        }

##########################################################################################################################################
/*
ob_end_clean();
ob_start();
echo "Zamanlanmış Görevdeki Kayıttan: ".$row['secilen_yedekleme']."<br>";
SiradakidbSec($PDOdb, $row['secilen_yedekleme']);
echo "Seçili Veritabanı: ".SecilidbAdi($PDOdb)."<br>";
veritabaniSecimiKaldir($PDOdb);
ob_flush();
flush();
*/
##########################################################################################################################################
    // Çalıştırılacak dosya veritabanı yedekeme ise aşağıdaki kodu çalıştır
    $post_dizi = [];
    if($row['yedekleme_gorevi'] == '1' && is_numeric($row['secilen_yedekleme'])){

#########################################################################################################################################
    // Seçilen veritabanı 
    $default = $PDOdb->prepare("SELECT * FROM veritabanlari WHERE id=? LIMIT 1");
    $default->execute([$row['secilen_yedekleme']]);
    $varsayilan = $default->fetch(PDO::FETCH_ASSOC);

    // Seçilen veritabanı varsa bağlantı oluşturuyoruz
    $secilen = "mysql:host=".$varsayilan['database_host'].";dbname=".$varsayilan['db_name'].";charset=".CHARSET.";port=".PORT."";

    $PDOdbsecilen = new PDO($secilen, $hash->take($varsayilan['database_user']), $hash->take($varsayilan['database_password']), $options);
##########################################################################################################################################
##########################################################################################################################################
/*
$dosya = fopen ("metin.txt" , "a"); //dosya oluşturma işlemi 
$yaz = "görev dosyasından\n".print_r($calistirilacak_dosya, true); // Yazmak istediginiz yazı 
fwrite($dosya,$yaz); fclose($dosya);
*/
##########################################################################################################################################        
        $post_dizi = array(
            'veritabani_id' => $row['secilen_yedekleme'],
            'onek' => $row['secilen_yedekleme_oneki'],
            'gz' => $row['gz'],
            'bakim' => $row['dbbakim'],
            'lock' => $row['dblock'],
            'combine' => $row['combine'],
            'elle' => $row['elle'],
            'grup' =>1,
            'sonraki_calisma' => $row['sonraki_calisma'],
            'oto_yedek' =>1,
            'ftp_yedekle' =>$row['ftp_yedekle']
            );
            
            // Otomatik yedeklemede elle seçilen tablolar olduğunda yedekleme yapılacak
            if( isset($row['tablolar']) && !empty($row['tablolar']) && $row['combine'] == '3' ) {
                //dbTazele($PDOdbsecilen);

                // Görev yerine getirilirken görevde hangi veritabanı ise onu seçiyoruz
                //$sql1 = "UPDATE veritabanlari SET selected = ?";
                //$PDOdb->prepare($sql1)->execute([0]);
                //echo "Tümü sıfırla: ".print_r($sql1->rowCount());

				// Tüm seçimi kaldırıyoruz
                
                //$unselect->execute([0,'gorev.php']);
                //echo "<br />";
                //$sql = "UPDATE veritabanlari SET selected = ? WHERE db_name = ?";
                //$PDOdb->prepare($sql)->execute([1, $row['secilen_yedekleme']]);
                //echo $row['secilen_yedekleme']." Seç: ".$sql->rowCount();
                //$select->execute([1,'gorev.php',$row['secilen_yedekleme']]);


				// Sıradaki veritabanı seçiyoruz
				

                // Elle seçilen tabloları diziye alıyoruz
                $tablolar['tablolar'] = explode(",", $row['tablolar']);

                //$guncellemezamani = $PDOdbsecilen->query("SELECT UPDATE_TIME, CREATE_TIME FROM INFORMATION_SCHEMA.TABLES WHERE table_name=? ");

                foreach($tablolar['tablolar'] AS $table){

                    // Bu sorgu ile ilgili tablonun son güncelleme tarıhını alıyoruz
                    $guncellemezamani = $PDOdbsecilen->query("SELECT UPDATE_TIME, CREATE_TIME FROM INFORMATION_SCHEMA.TABLES WHERE table_name = '".$table."' ");
                    //$guncellemezamani->execute([$table]);
                    $guncelleme_zamani = $guncellemezamani->fetch(PDO::FETCH_ASSOC);

                    // Veritabanı güncellenme tarihi alıyoruz
                    // Tarih yoksa now ile şimdiki zamanı alıyoruz
                    $update_time = $guncelleme_zamani['UPDATE_TIME'] ?? 'now';

                    $dateTimeObj = new DateTime($update_time, new DateTimeZone($genel_ayarlar['secili_zaman_dilimi']));
                    
                    if($update_time == 'now'){
                        // Güncelleme tarihi olmadığı için şimdiki tarihi alıp 1 yıl geri alıyoruz ki 
                        // tabloda güncelleme olmadığını belirtiyoruz
                        $update_time_unix = $dateTimeObj->modify('-1 year')->getTimestamp();
                    }else{
                        // Tablodan güncelleme tarihi geldi ve unix formatını alıyoruz
                        $update_time_unix = $dateTimeObj->getTimestamp();
                    }

                    //echo '<pre>' . print_r($guncelleme_zamani, true) . '</pre>';

                    //echo $varsayilan['db_name']." - ".$table." - ".$guncelleme_zamani['UPDATE_TIME']."<br />";

                    // Elle seçilen tabloların $row['sonraki_calisma'] zamanından sonra güncellendi ise tablolar dizisine ekliyoruz
                    // $row['sonraki_calisma'] zamanından önce güncellenen tabloları boşuna yedeklememek için ayıklıyoruz

                    if( ( $row['elle']>0 && $update_time_unix >= $row['sonraki_calisma'] ) ){
                        $yedeklenecek_tablolar['tablolar'][] = $table;
                    }
                }

            }else{ // Otomatik yedeklemede elle tablo seçilmedi tam yedekleme yapıldığında

                $seri = $post_dizi;

            }
                // Otomatik yedeklemede hiç tablo yoksa bir sonraki çalışma zamanı güncelle
                // Günlük kaydı yok
                if( isset($yedeklenecek_tablolar) && count($yedeklenecek_tablolar) == 0 && $row['combine'] == '3' ){

                    $output = 'Veritabanı Başarıyla Yedeklendi';
                    $yedeklendi_mi = false;

                }else{
                    // Elle seçilen tablo yoksa tam yedekleme var demektir
                    $seri = array_merge($post_dizi,$yedeklenecek_tablolar);
                    $yedeklendi_mi = true;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_POST, count($seri));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($seri));
                    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                    $out = curl_exec($ch);
                    $output = trim($out);
                    curl_close($ch);
                    unset($yedeklenecek_tablolar);
/*
$dosya = fopen ("gorev.txt" , "a"); //dosya oluşturma işlemi 
$yaz = "görev dosyasından\n".print_r($row['secilen_yedekleme']."\n", true); // Yazmak istediginiz yazı 
fwrite($dosya,$yaz); fclose($dosya);
*/
                } // else if(count($yedeklenecek_tablolar)==0){

            $gorev_adi = $row['gorev_adi'];
            $calistirilan_dosya = $row['dosya_adi'];                                          
            $calisma_zamani = time();

            $endtime = microtime(true);

            //$calismasuresi =  ($endtime - $starttime);
            //$hours = (int)($minutes = (int)($seconds = (int)($milliseconds = (int)($calismasuresi * 1000)) / 1000) / 60) / 60;
            //$calisma_suresi = $hours.':'.($minutes%60).':'.($seconds%60).(($milliseconds===0)?'':'.'.rtrim($milliseconds%1000, '0'));

            $duration = $endtime - $starttime;
            $hours = floor($duration / 60 / 60);
            $minutes = floor(($duration / 60) - ($hours * 60));
            $seconds = floor($duration - ($hours * 60 * 60) - ($minutes * 60));
            $milliseconds = ($duration - floor($duration)) * 1000;
            $calisma_suresi = sprintf('%02d:%02d:%02d:%05.0f', $hours,$minutes,$seconds,$milliseconds);

        //Başarılı görev bir sonraki zamana güncelle

        // Backup.php sayfasından <span> etiketin içinden gelen json verileri etiketten temizliyoruz
        $temiz_json = substr($output, strpos($output, '<span>')+6);
        $temiz_json = substr($temiz_json, 0, strpos($temiz_json, '</span>'));

        // json verileri php dizi olarak dönüştürüyoruz
        $cikis_sonucu = json_decode($temiz_json, true);
        $cikis_sonuc = isset($cikis_sonucu['basarili']) && !empty($cikis_sonucu['basarili']) ? $cikis_sonucu['basarili'] : "Başarısız"; 


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    $dosya = fopen ("metin.txt" , "a"); //dosya oluşturma işlemi 
    $yaz = "görev dosyasından\n".print_r($output, true); // Yazmak istediginiz yazı 
    fwrite($dosya,$yaz); fclose($dosya);
*/

    // Görev aktif olmazsa bile elle yürütme ise çalıştır
    if( ($row['aktif'] == 'Aktif' || isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1) && $cikis_sonuc == 'Veritabanı Başarıyla Yedeklendi'){
        $calistirma_sonuc_mesaji = "";
        if(isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1)
        {
            $calistirma_sonuc_mesaji .= "Elle Yürütme<br />";
        }
            $calistirma_sonuc_mesaji .= $cikis_sonucu['basarili'];


#############################################################################################################################
#############################################################################################################################
#############################################################################################################################
    if(isset($row['ftp_yedekle']) && $row['ftp_yedekle'] == 1){

        $ftpurl = $protocol."://".$_SERVER['SERVER_NAME']."/gorevle_uzak_sunucuya_yedekle.php";

        $ftp_arr = array(
            "id"                            => $row['id'],
            "ftp_yedekle"                   =>  1,
            "ftpsonrakidizin"               =>  $row['uzak_sunucu_ici_dizin_adi'],
            "dosya_adi_yolu"                =>  $cikis_sonucu['dosya_adi'],
            "ftp_sunucu_korunacak_yedek"    =>  $row['ftp_sunucu_korunacak_yedek']
        );

        $ftp_ch = curl_init();
        curl_setopt($ftp_ch, CURLOPT_URL, $ftpurl);
        curl_setopt($ftp_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ftp_ch, CURLOPT_HEADER, false);
        curl_setopt($ftp_ch, CURLOPT_POST, count($ftp_arr));
        curl_setopt($ftp_ch, CURLOPT_POSTFIELDS, http_build_query($ftp_arr));
        curl_setopt($ftp_ch, CURLOPT_FRESH_CONNECT, true);
        $ftp_out = curl_exec($ftp_ch);
        $ftp_output = trim($ftp_out);
        curl_close($ftp_ch);

        $temiz_ftp = substr($ftp_output, strpos($ftp_output, '<span>')+6);
        $temiz_ftp = substr($temiz_ftp, 0, strpos($temiz_ftp, '</span>'));
    if(!empty($temiz_ftp)){
        $calistirma_sonuc_mesaji .= "<br />".$temiz_ftp;
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // FTP Sunucusuna başarıyla yedeklendi ise eski dosyaları silme işlemine başla
    if($temiz_ftp == 'FTP Sunucusuna Başarıyla Yedeklendi'){
        $ftpsilurl = $protocol."://".$_SERVER['SERVER_NAME']."/ftp_yedek_sil.php";

        $ftpsil_arr = array(
            "id"                            => $row['id'],
            "ftp_yedekle"                   =>  1,
            "ftpsonrakidizin"               =>  $row['uzak_sunucu_ici_dizin_adi'],
            "dosya_adi_yolu"                =>  $cikis_sonucu['dosya_adi'],
            "ftp_sunucu_korunacak_yedek"    =>  $row['ftp_sunucu_korunacak_yedek']
        );

        $ftpsil_ch = curl_init();
        curl_setopt($ftpsil_ch, CURLOPT_URL, $ftpsilurl);
        curl_setopt($ftpsil_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ftpsil_ch, CURLOPT_HEADER, false);
        curl_setopt($ftpsil_ch, CURLOPT_POST, count($ftpsil_arr));
        curl_setopt($ftpsil_ch, CURLOPT_POSTFIELDS, http_build_query($ftpsil_arr));
        curl_setopt($ftpsil_ch, CURLOPT_FRESH_CONNECT, true);
        $ftpsil_out = curl_exec($ftpsil_ch);
        $ftpsil_output = trim($ftpsil_out);
        curl_close($ftpsil_ch);

        $temiz_ftpsil = substr($ftpsil_output, strpos($ftpsil_output, '<span>')+6);
        $temiz_ftpsil = substr($temiz_ftpsil, 0, strpos($temiz_ftpsil, '</span>'));
    if(!empty($temiz_ftpsil)){
        $calistirma_sonuc_mesaji .= "<br />".$temiz_ftpsil;
    }
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
    } // if(isset($row['ftp_yedekle']) && $row['ftp_yedekle'] == 1){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if(isset($row['google_yedekle']) && $row['google_yedekle'] == 1){
/*
    $dosya = fopen ("metin.txt" , "a"); //dosya oluşturma işlemi 
    $yaz = "görev dosyasından\n".print_r($cikis_sonucu, true); // Yazmak istediginiz yazı 
    fwrite($dosya,$yaz); fclose($dosya);
*/
        $googleurl = $protocol."://".$_SERVER['SERVER_NAME']."/gorevle_uzak_sunucuya_yedekle.php";

        $google_arr = array(
            "id"                            => $row['id'],
            "google_yedekle"                =>  1,
            "uzak_sunucu_ici_dizin_adi"     =>  $row['uzak_sunucu_ici_dizin_adi'],
            "dosya_adi_yolu"                =>  $cikis_sonucu['dosya_adi'],
            "google_sunucu_korunacak_yedek" =>  $row['google_sunucu_korunacak_yedek'],
            "elle_yurutme"                  =>  isset($_POST['elle_yurutme']) ? $_POST['elle_yurutme'] : ""
        );

        $google_ch = curl_init();
        curl_setopt($google_ch, CURLOPT_URL, $googleurl);
        curl_setopt($google_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($google_ch, CURLOPT_HEADER, false);
        curl_setopt($google_ch, CURLOPT_POST, count($google_arr));
        curl_setopt($google_ch, CURLOPT_POSTFIELDS, http_build_query($google_arr));
        curl_setopt($google_ch, CURLOPT_FRESH_CONNECT, true);
        $ftp_out = curl_exec($google_ch);
        $ftp_output = trim($ftp_out);
        curl_close($google_ch);

        $temiz_google = substr($ftp_output, strpos($ftp_output, '<span>')+6);
        $temiz_google = substr($temiz_google, 0, strpos($temiz_google, '</span>'));
    if(!empty($temiz_google)){
        $calistirma_sonuc_mesaji .= "<br />".$temiz_google;
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    $dosya = fopen ("metin.txt" , "a"); //dosya oluşturma işlemi 
    $yaz = "görev dosyasından\n".print_r($ftp_output, true); // Yazmak istediginiz yazı 
    fwrite($dosya,$yaz); fclose($dosya);
*/
    if($temiz_google == "Google Drive Sunucusuna Başarıyla Yedeklendi"){

        $googlesilurl = $protocol."://".$_SERVER['SERVER_NAME']."/gorevle_uzak_sunucuda_dosyalari_sil.php";

        $googlesil_arr = array(
            "id"                            => $row['id'],
            "gorevle_google_yedek_sil"      =>  1,
            "uzak_sunucu_ici_dizin_adi"     =>  $row['uzak_sunucu_ici_dizin_adi'],
            "dosya_adi_yolu"                =>  $cikis_sonucu['dosya_adi'],
            "google_sunucu_korunacak_yedek" =>  $row['google_sunucu_korunacak_yedek']
        );

        $googlesil_ch = curl_init();
        curl_setopt($googlesil_ch, CURLOPT_URL, $googlesilurl);
        curl_setopt($googlesil_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($googlesil_ch, CURLOPT_HEADER, false);
        curl_setopt($googlesil_ch, CURLOPT_POST, count($googlesil_arr));
        curl_setopt($googlesil_ch, CURLOPT_POSTFIELDS, http_build_query($googlesil_arr));
        curl_setopt($googlesil_ch, CURLOPT_FRESH_CONNECT, true);
        $googlesil_out = curl_exec($googlesil_ch);
        $googlesil_output = trim($googlesil_out);
        curl_close($googlesil_ch);

        $temiz_googlesil = substr($googlesil_output, strpos($googlesil_output, '<span>')+6);
        $temiz_googlesil = substr($temiz_googlesil, 0, strpos($temiz_googlesil, '</span>'));
    if(!empty($temiz_googlesil)){
        $calistirma_sonuc_mesaji .= "<br />".$temiz_googlesil;
    }
    } // if($ftp_output == "Google Drive Sunucusuna Başarıyla Yedeklendi"){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
    } // if(isset($row['google_yedekle']) && $row['google_yedekle'] == 1){
#############################################################################################################################
#############################################################################################################################
#############################################################################################################################

/*
        $dosya = fopen ("gorev_yurutme.txt" , "a"); //dosya oluşturma işlemi 
        $yaz = print_r($temiz_ftpsil, true); // Yazmak istediginiz yazı 
        fwrite($dosya,$yaz); fclose($dosya);
*/

        //Başarılı görev bir sonraki zamana güncelle
        if( $row['aktif'] == 'Aktif' ){
            $sonuc = $PDOdb->prepare("UPDATE zamanlanmisgorev SET sonraki_calisma =? WHERE id =? ");

            $sonuc->bindParam(1, $sonraki_calisma, PDO::PARAM_INT);
            $sonuc->bindParam(2, $row['id'], PDO::PARAM_INT);
            $sonuc->execute();
            if($sonuc->rowCount() > 0){
                    //echo "Yedek Sonraki çalışma zamanı başarıyla güncellendi<br />";
                $yedekleme_basarili = true;
            }else{
                    //echo "Bir hatadan dolayı sonraki çalışma zamanı güncellenemedi<br />";
                $yedekleme_basarili = false;
            }
        }
                
    } // if( $row['aktif'] == 'Aktif' && $output == 'Veritabanı Başarıyla Yedeklendi.'){
///////////////////////////////////////////////////////////////////////////////////////////////////////
        if( $row['gunluk_kayit'] == 'Aktif' && $yedeklendi_mi ){

            $sonuc_yaz = $PDOdb->prepare("INSERT INTO zamanlanmisgorev_gunluk (calistirma_ciktisi, gorev_adi, calistirilan_dosya, calisma_zamani, calisma_suresi) values (:calistirma_ciktisi, :gorev_adi, :calistirilan_dosya, :calisma_zamani, :calisma_suresi)");
            $sonuc_yaz->bindValue(':calistirma_ciktisi', $calistirma_sonuc_mesaji);
            $sonuc_yaz->bindParam(':gorev_adi', $gorev_adi);
            $sonuc_yaz->bindParam(':calistirilan_dosya', $calistirilan_dosya);
            $sonuc_yaz->bindParam(':calisma_zamani', $calisma_zamani);
            $sonuc_yaz->bindParam(':calisma_suresi', $calisma_suresi);
            $sonuc_yaz->execute();
            
            if(!empty($PDOdb->lastInsertId())){
                    //echo "Yedek Başarılı sonuç günlüğe yazıldı<br />";
            }else{
                    //echo "Bir hatadan dolayı başarılı sonuç günlüğe yazılamadı<br />";
            }
        } // if($row['gunluk_kayit'] == 'Aktif'){
##########################################################################################################################################
        // Veritabanı yedekleme bitti sonraki görev için tüm belleği temizle
    //unset($row,$varsayilan,$secilen,$PDOdbsecilen);
        }elseif($row['yedekleme_gorevi'] == '2' && is_string($row['secilen_yedekleme']) && !is_null($row['secilen_yedekleme']) ){ // if($row['yedekleme_gorevi'] == '1'){ Çalıştırılacak zipyap.php ise aşağıdakileri çalıştır
##########################################################################################################################################
##########################################################################################################################################
##########################################################################################################################################

$zip_post_dizi = array();

// zipyap
    $zip_post_dizi = array(
        'zipyap'            =>  1,
        'grup'              =>  1,
        'oto_yedek'         =>  1,
        'dizinadi'          =>  $row['secilen_yedekleme'],
        'ziparsivadi'       =>  $row['secilen_yedekleme_oneki'],
        'dizindir'          =>  DIZINDIR,
        'sonraki_calisma'   =>  $row['sonraki_calisma'],
        'ftp_yedekle'       =>  $row['ftp_yedekle']
        );

        //$zip_output = 'Dizin Başarıyla Arşivlendi';
        //$yedeklendi_mi = false;

        $yedeklendi_mi = true;

        $zip_ch = curl_init();
        curl_setopt($zip_ch, CURLOPT_URL, $url);
        curl_setopt($zip_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($zip_ch, CURLOPT_HEADER, false);
        curl_setopt($zip_ch, CURLOPT_POST, count($zip_post_dizi));
        curl_setopt($zip_ch, CURLOPT_POSTFIELDS, http_build_query($zip_post_dizi));
        curl_setopt($zip_ch, CURLOPT_FRESH_CONNECT, true);
        $zip_out = curl_exec($zip_ch);
        $zip_output = trim($zip_out);
        curl_close($zip_ch);


        $gorev_adi = $row['gorev_adi'];
        $calistirilan_dosya = $row['dosya_adi'];                                          
        $calisma_zamani = time();

        $endtime = microtime(true);

        //$calismasuresi =  ($endtime - $starttime);
        //$hours = (int)($minutes = (int)($seconds = (int)($milliseconds = (int)($calismasuresi * 1000)) / 1000) / 60) / 60;
        //$calisma_suresi = $hours.':'.($minutes%60).':'.($seconds%60).(($milliseconds===0)?'':'.'.rtrim($milliseconds%1000, '0'));

        $duration = $endtime - $starttime;
        $hours = floor($duration / 60 / 60);
        $minutes = floor(($duration / 60) - ($hours * 60));
        $seconds = floor($duration - ($hours * 60 * 60) - ($minutes * 60));
        $milliseconds = ($duration - floor($duration)) * 1000;
        $calisma_suresi = sprintf('%02d:%02d:%02d:%05.0f', $hours,$minutes,$seconds,$milliseconds);

        //Başarılı görev bir sonraki zamana güncelle

        // Backup.php sayfasından <span> etiketin içinden gelen json verileri etiketten temizliyoruz
        $temiz_json = substr($zip_output, strpos($zip_output, '<span>')+6);
        $temiz_json = substr($temiz_json, 0, strpos($temiz_json, '</span>'));

        // json verileri php dizi olarak dönüştürüyoruz
        $cikis_sonucu = json_decode($temiz_json, true);
        $cikis_sonuc = isset($cikis_sonucu['basarili']) && !empty($cikis_sonucu['basarili']) ? $cikis_sonucu['basarili'] : "Zip Arşivi Başarısız"; 
/*
$dosya = fopen ("dizin.txt" , "a"); //dosya oluşturma işlemi 
$yaz = "görev yurutucusu\n".print_r($cikis_sonucu, true); // Yazmak istediginiz yazı 
fwrite($dosya,$yaz); fclose($dosya);
*/
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
/*
        $dosya = fopen ("zipyap_ftp.txt" , "a"); //dosya oluşturma işlemi 
        $yaz = "görev yurutucu\n".print_r($cikis_sonucu, true); // Yazmak istediginiz yazı 
        fwrite($dosya,$yaz); fclose($dosya);
*/
    // Görev aktif olmazsa bile elle yürütme ise çalıştır
    if( ($row['aktif'] == 'Aktif' || isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1) && $cikis_sonucu['basarili'] == 'Zip Arşivi Başarıyla Oluşturuldu'){
        $calistirma_sonuc_mesaji = "";
        if(isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1)
        {
            $calistirma_sonuc_mesaji .= "Elle Yürütme<br />";
        }
            $calistirma_sonuc_mesaji .= $cikis_sonucu['basarili'];

        if(isset($row['ftp_yedekle']) && $row['ftp_yedekle'] == 1){

            $ftpurl = $protocol."://".$_SERVER['SERVER_NAME']."/gorevle_uzak_sunucuya_yedekle.php";
            $ftp_arr = array(
                "id"                            => $row['id'],
                "ftp_yedekle"                   =>  1,
                "ftpsonrakidizin"               =>  $row['uzak_sunucu_ici_dizin_adi'],
                "dosya_adi_yolu"                =>  $cikis_sonucu['dosya_adi'],
                "ftp_sunucu_korunacak_yedek"    =>  $row['ftp_sunucu_korunacak_yedek']
            );

            $ftp_ch = curl_init();
            curl_setopt($ftp_ch, CURLOPT_URL, $ftpurl);
            curl_setopt($ftp_ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ftp_ch, CURLOPT_HEADER, false);
            curl_setopt($ftp_ch, CURLOPT_POST, count($ftp_arr));
            curl_setopt($ftp_ch, CURLOPT_POSTFIELDS, http_build_query($ftp_arr));
            curl_setopt($ftp_ch, CURLOPT_FRESH_CONNECT, true);
            $ftp_out = curl_exec($ftp_ch);
            $ftp_output = trim($ftp_out);
            curl_close($ftp_ch);

            $temiz_ftp = substr($ftp_output, strpos($ftp_output, '<span>')+6);
            $temiz_ftp = substr($temiz_ftp, 0, strpos($temiz_ftp, '</span>'));
        if(!empty($temiz_ftp)){
            $calistirma_sonuc_mesaji .= "<br />".$temiz_ftp;
        }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // FTP Sunucusuna başarıyla yedeklendi ise eski dosyaları silme işlemine başla
    if($temiz_ftp == 'FTP Sunucusuna Başarıyla Yedeklendi'){
        $ftpsilurl = $protocol."://".$_SERVER['SERVER_NAME']."/ftp_yedek_sil.php";

        $ftpsil_arr = array(
            "id"                            => $row['id'],
            "ftp_yedekle"                   =>  1,
            "ftpsonrakidizin"               =>  $row['uzak_sunucu_ici_dizin_adi'],
            "dosya_adi_yolu"                =>  $cikis_sonucu['dosya_adi'],
            "ftp_sunucu_korunacak_yedek"    =>  $row['ftp_sunucu_korunacak_yedek']
        );

        $ftpsil_ch = curl_init();
        curl_setopt($ftpsil_ch, CURLOPT_URL, $ftpsilurl);
        curl_setopt($ftpsil_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ftpsil_ch, CURLOPT_HEADER, false);
        curl_setopt($ftpsil_ch, CURLOPT_POST, count($ftpsil_arr));
        curl_setopt($ftpsil_ch, CURLOPT_POSTFIELDS, http_build_query($ftpsil_arr));
        curl_setopt($ftpsil_ch, CURLOPT_FRESH_CONNECT, true);
        $ftpsil_out = curl_exec($ftpsil_ch);
        $ftpsil_output = trim($ftpsil_out);
        curl_close($ftpsil_ch);

        $temiz_ftpsil = substr($ftpsil_output, strpos($ftpsil_output, '<span>')+6);
        $temiz_ftpsil = substr($temiz_ftpsil, 0, strpos($temiz_ftpsil, '</span>'));
    if(!empty($temiz_ftpsil)){
        $calistirma_sonuc_mesaji .= "<br />".$temiz_ftpsil;
    }
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
    } // if(isset($row['ftp_yedekle']) && $row['ftp_yedekle'] == 1){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if(isset($row['google_yedekle']) && $row['google_yedekle'] == 1){
/*
    $dosya = fopen ("metin.txt" , "a"); //dosya oluşturma işlemi 
    $yaz = "görev dosyasından\n".print_r($cikis_sonucu, true); // Yazmak istediginiz yazı 
    fwrite($dosya,$yaz); fclose($dosya);
*/
        $googleurl = $protocol."://".$_SERVER['SERVER_NAME']."/gorevle_uzak_sunucuya_yedekle.php";

        $ftp_arr = array(
            "id"                            => $row['id'],
            "google_yedekle"                =>  1,
            "uzak_sunucu_ici_dizin_adi"     =>  $row['uzak_sunucu_ici_dizin_adi'],
            "dosya_adi_yolu"                =>  $cikis_sonucu['dosya_adi'],
            "google_sunucu_korunacak_yedek" =>  $row['google_sunucu_korunacak_yedek']
        );

        $google_ch = curl_init();
        curl_setopt($google_ch, CURLOPT_URL, $googleurl);
        curl_setopt($google_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($google_ch, CURLOPT_HEADER, false);
        curl_setopt($google_ch, CURLOPT_POST, count($ftp_arr));
        curl_setopt($google_ch, CURLOPT_POSTFIELDS, http_build_query($ftp_arr));
        curl_setopt($google_ch, CURLOPT_FRESH_CONNECT, true);
        $google_out = curl_exec($google_ch);
        $google_output = trim($google_out);
        curl_close($google_ch);

        $temiz_google = substr($google_output, strpos($google_output, '<span>')+6);
        $temiz_google = substr($temiz_google, 0, strpos($temiz_google, '</span>'));
    if(!empty($temiz_google)){
        $calistirma_sonuc_mesaji .= "<br />".$temiz_google;
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    $dosya = fopen ("metin.txt" , "a"); //dosya oluşturma işlemi 
    $yaz = "görev dosyasından\n".print_r($google_output, true); // Yazmak istediginiz yazı 
    fwrite($dosya,$yaz); fclose($dosya);
*/
    if($temiz_google == "Google Drive Sunucusuna Başarıyla Yedeklendi"){
        $googlesilurl = $protocol."://".$_SERVER['SERVER_NAME']."/gorevle_uzak_sunucuda_dosyalari_sil.php";

        $googlesil_arr = array(
            "id"                            => $row['id'],
            "gorevle_google_yedek_sil"      =>  1,
            "uzak_sunucu_ici_dizin_adi"     =>  $row['uzak_sunucu_ici_dizin_adi'],
            "dosya_adi_yolu"                =>  $cikis_sonucu['dosya_adi'],
            "google_sunucu_korunacak_yedek" =>  $row['google_sunucu_korunacak_yedek']
        );

        $googlesil_ch = curl_init();
        curl_setopt($googlesil_ch, CURLOPT_URL, $googlesilurl);
        curl_setopt($googlesil_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($googlesil_ch, CURLOPT_HEADER, false);
        curl_setopt($googlesil_ch, CURLOPT_POST, count($googlesil_arr));
        curl_setopt($googlesil_ch, CURLOPT_POSTFIELDS, http_build_query($googlesil_arr));
        curl_setopt($googlesil_ch, CURLOPT_FRESH_CONNECT, true);
        $googlesil_out = curl_exec($googlesil_ch);
        $googlesil_output = trim($googlesil_out);
        curl_close($googlesil_ch);

        $temiz_googlesil = substr($googlesil_output, strpos($googlesil_output, '<span>')+6);
        $temiz_googlesil = substr($temiz_googlesil, 0, strpos($temiz_googlesil, '</span>'));
    if(!empty($temiz_googlesil)){
        $calistirma_sonuc_mesaji .= "<br />".$temiz_googlesil;
    }
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
    } // if(isset($row['google_yedekle']) && $row['google_yedekle'] == 1){
#############################################################################################################################
#############################################################################################################################
#############################################################################################################################
        //Başarılı görev bir sonraki zamana güncelle
        if( $row['aktif'] == 'Aktif' ){
            $sonuc = $PDOdb->prepare("UPDATE zamanlanmisgorev SET sonraki_calisma =? WHERE id =? ");

            $sonuc->bindParam(1, $sonraki_calisma, PDO::PARAM_INT);
            $sonuc->bindParam(2, $row['id'], PDO::PARAM_INT);
            $sonuc->execute();
            if($sonuc->rowCount() > 0){
                    //echo "Yedek Sonraki çalışma zamanı başarıyla güncellendi<br />";
                $yedekleme_basarili = true;
            }else{
                    //echo "Bir hatadan dolayı sonraki çalışma zamanı güncellenemedi<br />";
                $yedekleme_basarili = false;
            }
        }
                
    } // if( $row['aktif'] == 'Aktif' && $zip_output == 'Veritabanı Başarıyla Yedeklendi.'){
///////////////////////////////////////////////////////////////////////////////////////////////////////
        if( $row['gunluk_kayit'] == 'Aktif' && $yedeklendi_mi ){

            $sonuc_yaz = $PDOdb->prepare("INSERT INTO zamanlanmisgorev_gunluk (calistirma_ciktisi, gorev_adi, calistirilan_dosya, calisma_zamani, calisma_suresi) values (:calistirma_ciktisi, :gorev_adi, :calistirilan_dosya, :calisma_zamani, :calisma_suresi)");
            $sonuc_yaz->bindValue(':calistirma_ciktisi', $calistirma_sonuc_mesaji);
            $sonuc_yaz->bindParam(':gorev_adi', $gorev_adi);
            $sonuc_yaz->bindParam(':calistirilan_dosya', $calistirilan_dosya);
            $sonuc_yaz->bindParam(':calisma_zamani', $calisma_zamani);
            $sonuc_yaz->bindParam(':calisma_suresi', $calisma_suresi);
            $sonuc_yaz->execute();
            
            if(!empty($PDOdb->lastInsertId())){
                    //echo "Yedek Başarılı sonuç günlüğe yazıldı<br />";
            }else{
                    //echo "Bir hatadan dolayı başarılı sonuç günlüğe yazılamadı<br />";
            }
        } // if($row['gunluk_kayit'] == 'Aktif'){

##########################################################################################################################################
##########################################################################################################################################
// KURLARI GÜNCELLEME GÖREV YÜRÜTME BÖLÜMÜ BAŞLANGICI
##########################################################################################################################################
        }elseif($row['yedekleme_gorevi'] == '3' && (empty($row['secilen_yedekleme']) || is_null($row['secilen_yedekleme'])) ){ // if($row['yedekleme_gorevi'] == '1'){ Çalıştırılacak kurlar.php ise aşağıdakileri çalıştır
##########################################################################################################################################
##########################################################################################################################################
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,"runtask = 1");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,300);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        $out = curl_exec($ch);
        $output = trim($out);
        curl_close($ch);

        //Çalıştılan sayfada {} süslü parantez içindeki veriyi almak
        //preg_match('#{(.*?)}#',$output,$cikti);
        preg_match('/<div style="display:none">(.*?)<\/div>/s', $output, $cikti);

        //echo '<pre>' . print_r($url, true) . '</pre>';

// json nu denetleme
function isJson($str) {
    $json = json_decode($str);
    return $json && $str != $json;
}
        $output_array = [];
        if(isJson($cikti[1])){
            $output_array = json_decode( $cikti[1] ?? [], JSON_UNESCAPED_SLASHES );
        }

        $gorev_adi = $row['gorev_adi'];
        $calistirilan_dosya = $row['dosya_adi'];                                          
        $calisma_zamani = time();

        $endtime = microtime(true);

        //$calismasuresi =  ($endtime - $starttime);
        //$hours = (int)($minutes = (int)($seconds = (int)($milliseconds = (int)($calismasuresi * 1000)) / 1000) / 60) / 60;
        //$calisma_suresi = $hours.':'.($minutes%60).':'.($seconds%60).(($milliseconds===0)?'':'.'.rtrim($milliseconds%1000, '0'));

        $duration = $endtime - $starttime;
        $hours = floor($duration / 60 / 60);
        $minutes = floor(($duration / 60) - ($hours * 60));
        $seconds = floor($duration - ($hours * 60 * 60) - ($minutes * 60));
        $milliseconds = ($duration - floor($duration)) * 1000;
        $calisma_suresi = sprintf('%02d:%02d:%02d:%05.0f', $hours,$minutes,$seconds,$milliseconds);

        //echo '<pre>' . print_r($output_array, true) . '</pre>';

/*
        echo "Ham Sonuç: ".$output."<br />";
        echo "Sonuç: ".$calistirma_sonuc_mesaji."<br />";
        echo "Çalışma Zamanı: ".$calisma_zamani."<br />";
        echo "Çalışma Süresi: ".$calisma_suresi."<br />";
        exit;
*/
############# ZAMANI GELEN DOSYALARI ÇALIŞTIR ##################################
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
############# SONUÇLARI VE SONRAKİ ZAMANI VERİTABANI YAZ #######################

        if (is_array($output_array)) {
        //if( in_array( "Güncellendi" ,$output_array ) ){
        $calistirma_sonuc_mesaji = "";
        if(isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1)
        {
            $calistirma_sonuc_mesaji .= "Elle Yürütme<br>";
        }
        foreach($output_array AS $key => $value){
           $calistirma_sonuc_mesaji .= $key." ".$value."<br>";     
        }

        //Başarılı görev bir sonraki zamana güncelle
        if( $row['aktif'] == 'Aktif' ){

                $sonuc = $PDOdb->prepare("UPDATE zamanlanmisgorev SET sonraki_calisma =? WHERE id =? ");

                $sonuc->bindParam(1, $sonraki_calisma, PDO::PARAM_INT);
                $sonuc->bindParam(2, $row['id'], PDO::PARAM_INT);
                $sonuc->execute();
                if($sonuc->rowCount() > 0){
                        //echo "Sonraki çalışma zamanı başarıyla güncellendi<br />";
                }else{
                        //echo "Bir hatadan dolayı sonraki çalışma zamanı güncellenemedi<br />";
                }
                
        } // if( $row['aktif'] == 'Aktif'){
##########################################################################################################################################
// GÖREV YÜRÜTÜLERKEN GÜNLÜK KAYIT AKTİF İSE YÜRÜTME SONUCUNU VERİTABANINA KAYDET
    if($row['gunluk_kayit'] == 'Aktif' && in_array( "Güncellendi" ,$output_array )){
/*
$dosya = fopen ("kurlar.txt" , "a"); //dosya oluşturma işlemi 
$yaz = "görev dosyasından\n".print_r($calistirma_sonuc_mesaji, true); // Yazmak istediginiz yazı 
fwrite($dosya,$yaz); fclose($dosya);
*/
        $sonuc_yaz = $PDOdb->prepare("INSERT INTO zamanlanmisgorev_gunluk (calistirma_ciktisi, gorev_adi, calistirilan_dosya, calisma_zamani, calisma_suresi) values (:calistirma_ciktisi, :gorev_adi, :calistirilan_dosya, :calisma_zamani, :calisma_suresi)");
        $sonuc_yaz->bindParam(':calistirma_ciktisi', $calistirma_sonuc_mesaji);
        $sonuc_yaz->bindParam(':gorev_adi', $gorev_adi);
        $sonuc_yaz->bindParam(':calistirilan_dosya', $calistirilan_dosya);
        $sonuc_yaz->bindParam(':calisma_zamani', $calisma_zamani);
        $sonuc_yaz->bindParam(':calisma_suresi', $calisma_suresi);
        $sonuc_yaz->execute();
        
        if(!empty($PDOdb->lastInsertId())){
                //echo "Başarılı sonuç günlüğe yazıldı<br />";
        }else{
                //echo "Bir hatadan dolayı başarılı sonuç günlüğe yazılamadı<br />";
        }
    } // if($row['gunluk_kayit'] == 'Aktif'){
##########################################################################################################################################
        //} // if( in_array( "Güncellendi" ,$output_array ) ){
        } // if (is_array($output_array)) {
##########################################################################################################################################
##########################################################################################################################################
        } // if($row['yedekleme_gorevi'] == '1'){
##########################################################################################################################################
// KURLARI GÜNCELLEME GÖREV YÜRÜTME BÖLÜM SONU
##########################################################################################################################################

############# SONUÇLARI VE SONRAKİ ZAMANI VERİTABANI YAZ #######################
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
################################################################################ 

###################################################################################################
// YERELDEN SİLİNECEK KLASÖR İSE KLASÖR SİLME FONKSİYONU
###################################################################################################
if($row['yerel_korunacak_yedek'] != '-1'){
    if (!function_exists('delete_directory')){
        // Klasörü silecek fonksiyon
        function delete_directory($dir){
            if(is_dir($dir)){
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach($files as $file){
                    if ($file->isDir()){
                        rmdir($file->getRealPath());
                    }else{
                        unlink($file->getRealPath());
                    }
                }
                // Seçilen dizinide silmek içindir. Yukarısı içeriği siler
                rmdir($dir);
            } // if(is_dir($dir)){
        }
    }
###################################################################################################
// YERELDEN DOSYALARI SİLME BÖLÜMÜ
###################################################################################################
    // Veritabanı yedekleme görevi
    if($row['yedekleme_gorevi'] == 1){
        $yol = BACKUPDIR."/";
    // Web dizinlerin zipleme görevi
    }elseif($row['yedekleme_gorevi'] == 2){
        $yol = ZIPDIR;
    // Kur güncelleme
    }elseif($row['yedekleme_gorevi'] == 3){
        $yol = "";
    }

        // Dizinde "secilen_yedekleme_oneki" ön ekine sahip dosyaları arayalım

        // Burada sadece dizin arıyoruz
        $dizinler = array_filter(scandir($yol), function($dizin) {
            GLOBAL $row,$yol;
            //return strpos($dizin, $row['secilen_yedekleme_oneki']) === 0 && is_dir($yol.$dizin); // Dizinin "secilen_yedekleme_oneki" ile başlayıp başlamadığını kontrol edelim
            $secilen_yedekleme_oneki = $row['secilen_yedekleme_oneki'];
            if (is_string($secilen_yedekleme_oneki)) {
                return strpos($dizin, $secilen_yedekleme_oneki) === 0 && is_dir($yol.$dizin);
            } else {
                // İkinci parametre bir dize değilse, hata işleme yöntemi burada uygulanabilir.
                return [];
            }
        });
        // Burada dizin olmayan dosyaları arıyoruz
        $dosyalar = array_filter(scandir($yol), function($dosya) {
            GLOBAL $row,$yol;
            //return strpos($dosya, $row['secilen_yedekleme_oneki']) === 0 && is_file($yol.$dosya); // Dosyanın "secilen_yedekleme_oneki" ile başlayıp başlamadığını kontrol edelim
            $secilen_yedekleme_oneki = $row['secilen_yedekleme_oneki'];
            if (is_string($secilen_yedekleme_oneki)) {
                return strpos($dosya, $secilen_yedekleme_oneki) === 0 && is_file($yol.$dosya);
            } else {
                // İkinci parametre bir dize değilse, hata işleme yöntemi burada uygulanabilir.
                return [];
            }
        });

        // Dizinlerin son değiştirilme veya oluşturma zamanlarına göre sıralayalım
        usort($dizinler, function($a, $b) use ($yol) {
            return filemtime($yol . $b) - filemtime($yol . $a);
        });
        // Dosyaların son değiştirilme veya oluşturma zamanlarına göre sıralayalım
        usort($dosyalar, function($c, $d) use ($yol) {
            return filemtime($yol . $d) - filemtime($yol . $c);
        });

        //echo "<pre>" . print_r($dizinler, true) . "</pre>";
        //echo "<pre>" . print_r($dosyalar, true) . "</pre>";
    if (!function_exists('validateDate')){
        function validateDate($date, $format = 'Y-m-d-H-i-s')
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }
    }

        // En yeni iki dosyayı saklayalım ve gerisini silelim
    if(count($dizinler)>0){
        while (count($dizinler) > $row['yerel_korunacak_yedek']) {
            $silinendizin = array_pop($dizinler);
                //echo "<b>Bu dizin:</b> ".$yol.$silinendizin."<br />";
                $dizin_tarihi = substr($silinendizin, -19);
                if(validateDate($dizin_tarihi)){
                    delete_directory($yol.$silinendizin);
                }
        }
    }
    if(count($dosyalar)>0){
        while (count($dosyalar) > $row['yerel_korunacak_yedek']) {
                $silinendosya = array_pop($dosyalar);
                //echo "<b>Bu dosya:</b> ".$yol.$silinendosya."<br />";
                $dosya_tarihi = substr($silinendosya, strpos($silinendosya, $row['secilen_yedekleme_oneki']."-") + strlen($row['secilen_yedekleme_oneki']."-"), 19);
                if(validateDate($dosya_tarihi)){
                    unlink($yol.$silinendosya);
                }
        }
    }
    } // if($row['yerel_korunacak_yedek'] != '-1'){
###################################################################################################
###################################################################################################
    unset($row,$varsayilan,$secilen,$PDOdbsecilen);
    }//if($basarili == 1){

    if(isset($_POST['elle_yurutme']) && $_POST['elle_yurutme'] == 1 && isset($_POST['gorevid']) && is_numeric($_POST['gorevid'])){
        echo $gorevadi.$calistirma_sonuc_mesaji;
    }

?>