<?php 
header('Content-Type: application/json');

if (isset($_POST['exec_test'])) {
    $output_exec = [];
    $return_var_exec = 0;
    $output_zip = [];
    $return_var_zip = 0;

    // exec fonksiyonunun çalışıp çalışmadığını test et
    exec('echo test', $output_exec, $return_var_exec);

    // zip komutunun çalışıp çalışmadığını test et
    exec('zip -v', $output_zip, $return_var_zip);

    if ($return_var_exec === 0) {
        if ($return_var_zip === 0) {
            echo json_encode(['status' => 'success', 'message' => '<span class="glyphicon glyphicon-ok"></span> Exec ve Zip komutu kullanılabilir.']);
        } else {
            echo json_encode(['status' => 'warning', 'message' => '<span class="glyphicon glyphicon-ok"></span> Exec komutu kullanılabilir, ancak Zip komutu kullanılamıyor.']);
        }
    } else {
        echo json_encode(['status' => 'danger', 'message' => '<span class="glyphicon glyphicon-remove"></span> Exec komutu kullanılamıyor.']);
    }
} else {
    echo json_encode(['status' => 'danger', 'message' => 'Geçersiz istek.']);
}
?>
