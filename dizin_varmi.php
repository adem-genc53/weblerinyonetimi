<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
     //echo '<pre>' . print_r($_GET, true) . '</pre>';
     //exit;
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

if (isset($_GET['sql_varmi'])) {
    $filePath = KOKYOLU . $_GET['sql_varmi'];
    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

    if (file_exists($filePath) && is_file($filePath)) { // Dizinde aynı dosya var mı?
        echo "<span style='font-size: 11px;color:blue;'><b>DİKKAT!</b></span> Bu <b>" . htmlspecialchars($_GET['sql_varmi']) . "</b> <b style='font-size: 12px;color:blue;'> dosya mevcut</b>. Eğer kaydederseniz üzerine yazılacaktır.";
    } elseif (strlen($_GET['sql_varmi'])==0) { // Dosya adının uzunluğu kontrolü (en az 4 karakter, .sql veya .sql.gz uzantısı için)
        echo "<span style='font-size: 11px;color:blue;'><b>SQL DOSYA YOLU İLE BERABER (VEYA SADECE) DOSYA ADI VE .sql VEYA .sql.gz UZANTILARI BELİRLEMELİSİNİZ</b></span>";
    } elseif (!in_array($fileExtension, ['sql', 'gz'])) { // Dosya uzantısı kontrolü
        echo "Bu <b style='font-size: 11px;color:blue;'>" . htmlspecialchars($_GET['sql_varmi']) . "</b> bir dosya değil veya <b>.sql</b> ve ya <b>.sql.gz</b> uzantılarından birine sahip değil.";
    } else {
        echo "Dosya uzantısı geçerli, Kaydedebilirsiniz";
    }
}

?>