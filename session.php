<?php
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';


echo "\n<strong>SESSION LİSTESİ</strong><br /><br />\n";
echo '<pre>' . print_r($_SESSION, true) . '</pre>';


echo "\n<br /><br /><strong>COOKIE LİSTESİ</strong><br /><br />\n";
echo '<pre>' . print_r($_COOKIE, true) . '</pre>';


?>
<h4>Tüm localStrodeki verileri</h4>
<div id="tumlocalstroverileri"></div>
</body>
<script>
fruits = [];
Object.entries(localStorage).forEach(([ key, value ]) => {
    fruits.push(`[${key}] => ${value}<br />`);
});
document.getElementById('tumlocalstroverileri').innerHTML = fruits.join(" ");
</script>
</body>