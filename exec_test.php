<?php
header('Content-Type: application/json');

if (isset($_POST['exec_test'])) {
    $output = [];
    $return_var = 0;
    exec('echo test', $output, $return_var);

    if ($return_var === 0) {
        echo json_encode(['status' => 'success', 'message' => '<span class="glyphicon glyphicon-ok"></span> Sunucunuz EXEC Fonksiyonu destekliyor. Kullanabilirsiniz.']);
    } else {
        echo json_encode(['status' => 'danger', 'message' => '<span class="glyphicon glyphicon-remove"></span> Sunucunuz EXEC Fonksiyonu desteklemiyor. Üzgünüm']);
    }
} else {
    echo json_encode(['status' => 'danger', 'message' => 'Geçersiz istek.']);
}
?>
