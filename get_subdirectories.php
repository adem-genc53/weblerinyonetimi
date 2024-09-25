<?php
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';

// POST isteğiyle gelen ana dizini al
if (isset($_POST['mainDir'])) {
    $mainDir = $_POST['mainDir'];
    $secilen_alt_dizin = !empty($_POST['secilen_alt_dizin']) ? $_POST['secilen_alt_dizin'] : "";
    $fullPath = DIZINDIR . $mainDir;

    // Geçerli bir dizin olup olmadığını kontrol et
    if (is_dir($fullPath)) {
        // Tüm alt dizinleri alacak özyinelemeli fonksiyon (tam yollarla birlikte)
        function getAllDirectories($dir, $relativePath = '', $secilen_alt_dizin = '') {
            $subDirs = array_filter(glob($dir . '/*'), 'is_dir');
            $output = '';
            foreach ($subDirs as $secilen_web_sitenin_alt_dizini) {
                $dirName = basename($secilen_web_sitenin_alt_dizini);
                // Tam dizin yolunu oluştur (root dizin hariç)
                $currentPath = trim($relativePath . '/' . $dirName, '/');
                
                // Seçilen alt dizin ile tam yolu karşılaştır ve 'selected' ekle
                $isSelected = ($currentPath === $secilen_alt_dizin) ? 'selected' : '';
                
                // Option elementine ekle
                $output .= '<option value="' . $currentPath . '" ' . $isSelected . '>' . $currentPath . '</option>';
                
                // Alt dizinlere devam et (recursive)
                $output .= getAllDirectories($secilen_web_sitenin_alt_dizini, $currentPath, $secilen_alt_dizin);
            }
            return $output;
        }

        // Ana dizindeki tüm dizinler ve alt dizinleri al
        $options = getAllDirectories($fullPath, $mainDir, $secilen_alt_dizin);
        
        if (!empty($options)) {
            echo '<option value="">İsteğe bağlı alt-dizin seçiniz</option>';
            echo $options;
        } else {
            echo '<option value="">Alt dizin yok</option>';
        }
    } else {
        echo '<option value="">Geçersiz dizin</option>';
    }
}
?>
