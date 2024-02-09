<?php 
// Bismillahirrahmanirrahim
require_once('includes/connect.php');
@ini_set('display_errors', true);
@ini_set('memory_limit', '-1');
@ignore_user_abort(true);
require_once("includes/turkcegunler.php");
/*
$dosya = fopen ("zipyap.txt" , "a"); //dosya oluşturma işlemi 
$yaz = "görev yurutucusu\n".print_r($_POST, true); // Yazmak istediginiz yazı 
fwrite($dosya,$yaz); fclose($dosya);
*/
if(isset($_POST['zipyap'])){
 //echo '<pre>' . print_r($_POST, true) . '</pre>';
 //exit;
/*
    $simdizaman         = date("Y-m-d-H-i-s",time());
    $dizinyolu          = ZIPDIR;
    $dizinadi           = 'yeni-webyonetimi';//$_POST['dizinadi'];
    $ziparsivadi        = 'deneme_dizin.zip';//pathinfo($_POST['ziparsivadi'], PATHINFO_EXTENSION) == "zip" ? $_POST['ziparsivadi'] : $_POST['ziparsivadi'].".zip";
    $zipyorumadizinadi  = pathinfo($ziparsivadi, PATHINFO_FILENAME);
*/
    $simdizaman         = date("Y-m-d-H-i-s",time());
    $zipdizinyolu       = ZIPDIR; // zip dosyalarının bulunacağın konum "../WEBZIPLER/" bu site dizinin bir gerisinde WEBZIPLER klasör olmalıdır
    $dizinyolu          = $_POST['dizindir']; // sıkıştırılacak web site dizinin bulunduğu alan "../" bu site dizinin bir gerisinde
    $dizinadi           = $_POST['dizinadi']; // sıkıştırılacak web site dizin adı
    // zip dosya adı varsayılan dizin adıdır ancak sıkıştıran isterse adının değiştirebilir. zip dosya adının sonunda .zip uzantı yoksa ekliyoruz
    $ziparsivadi        = pathinfo($_POST['ziparsivadi'], PATHINFO_EXTENSION) == "zip" ? $_POST['ziparsivadi'] : $_POST['ziparsivadi'].".zip";
    // zip dosya adına tarih eklendiği için zip açarken dosya adını alabilmek için zipin yorum alanına dosya adını ekliyoruz
    $zipyorumadizinadi  = pathinfo($ziparsivadi, PATHINFO_FILENAME);

function zipData( $source, $destination ) 
{
if (file_exists($source) && count(glob($source . '*')) !== 0) {
    $zip = new ZipArchive();
    if($zip->open($destination, ZIPARCHIVE::CREATE) === true) {
        $source = realpath($source);
        global $zipyorumadizinadi;
        $zip->setArchiveComment($zipyorumadizinadi);
        if(is_dir($source)) {
        $iterator = new RecursiveDirectoryIterator($source);
        $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach($files as $file) {
            $file = realpath($file);
            if(is_dir($file)) {
            $zip->addEmptyDir(str_replace($source . DIRECTORY_SEPARATOR, '', $file . DIRECTORY_SEPARATOR));
            }elseif(is_file($file)) {
            $zip->addFile($file,str_replace($source . DIRECTORY_SEPARATOR, '', $file));
            }
        }
        }elseif(is_file($source)) {
        $zip->addFile($source,basename($source));
        }
    }
        if($zip->close()){
            return 1; //"Zip Arşivi Başarıyla Oluşturuldu";
        }else{
            return 0; //"Zip Arşivi Bir Hatadan Dolayı Oluşturulamadı";
        }
}
    return "none"; //"Klasör yolu geçersiz: <b>".$source."</b>";
}
##################################################################################################################################################################
##################################################################################################################################################################

##################################################################################################################################################################
##################################################################################################################################################################
    $zipyap_sonucu = zipData($dizinyolu.'/'.$dizinadi,    $zipdizinyolu.$zipyorumadizinadi."-".$simdizaman.'.zip');

    // Otomatik veya elle yürütme başarılı sonuç çıktısı
    if(trim($zipyap_sonucu) == 1 && isset($_POST['oto_yedek']) && $_POST['oto_yedek'] == 1){
        $jsonData = array("basarili"=>"Zip Arşivi Başarıyla Oluşturuldu", "dosya_adi"=>$zipdizinyolu.$zipyorumadizinadi."-".$simdizaman.'.zip');
        echo "<span>".json_encode($jsonData)."</span>";
    // Otomatik veya elle yürütme başarısız sonuç çıktısı
    }else if(trim($zipyap_sonucu) == 0 && isset($_POST['oto_yedek']) && $_POST['oto_yedek'] == 1){
        $jsonData = array("basarili"=>"Zip Arşivi Bir Hatadan Dolayı Oluşturulamadı", "dosya_adi"=>$zipdizinyolu.$zipyorumadizinadi."-".$simdizaman.'.zip');
        echo "<span>".json_encode($jsonData)."</span>";
    // Otomatik veya elle yürütme klasör yolu geçersiz sonuç çıktısı
    }else if(trim($zipyap_sonucu) == 'none' && isset($_POST['oto_yedek']) && $_POST['oto_yedek'] == 1){
        $jsonData = array("basarili"=>"Klasör yolu geçersiz: ".$dizinyolu.'/'.$dizinadi."", "dosya_adi"=>$zipdizinyolu.$zipyorumadizinadi."-".$simdizaman.'.zip');
        echo "<span>".json_encode($jsonData)."</span>";
    // Dizin listelemede zip yapma sonucu
    }else{
        if(trim($zipyap_sonucu) == 1){
            echo "Zip Arşivi Başarıyla Oluşturuldu<br />".$zipyorumadizinadi."-".$simdizaman.'.zip';
        }elseif(trim($zipyap_sonucu) == 0){
            echo "Zip Arşivi Bir Hatadan Dolayı Oluşturulamadı";
        }elseif(trim($zipyap_sonucu) == 'none'){
            echo "Klasör yolu geçersiz: <b>".$dizinyolu.'/'.$dizinadi."</b>";
        }
    }

}

?>