<?php 
// Bismillahirrahmanirrahim
require_once('includes/connect.php');
require_once('check-login.php');
    // echo '<pre>' . print_r($_GET, true) . '</pre>';
    // Açılacak klasör dizinde varmı yok mu kontrolu
    if(isset($_GET['dizin'])){
    if(file_exists(DIZINDIR.$_GET['dizin']) && strlen(DIZINDIR.$_GET['dizin']) > strlen(DIZINDIR) ){
        echo "<span style='font-size: 16px;color:blue;'><b>DİKKAT!</b></span> Bu <b style='font-size: 12px;color:blue;'>".basename(DIZINDIR.$_GET['dizin'])."</b> klasör dizinde mevcut. Eğer buraya açarsanız dosyaların üzerine yazılacaktır.";
    }elseif(strlen($_GET['dizin'])==0){
        echo "<span style='font-size: 14px;color:blue;'><b>ZİP'IN ÇIKARILACAĞI DİZİN GEREKLİDİR. LÜTFEN KLASÖR ADI GİRİNİZ</b></span>";
    }else{
        echo "Bu <b style='font-size: 12px;color:blue;'>".basename($_GET['dizin'])."</b> klasör dizinde mevcut değil, üstüne yazma riski yok. Açabilirsiniz.";
    }
    }

?>