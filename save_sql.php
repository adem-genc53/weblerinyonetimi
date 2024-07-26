<?php
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
/*
    $data = json_decode(file_get_contents('php://input'), true);
        $content = $data['content'];
        $dosyaYoluVeAdi = $data['dosyayoluveadi'];

    echo '<pre>' . print_r($content, true) . '</pre>';
    exit;
*/

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['content']) && isset($data['dosyayoluveadi'])) {
        $content = $data['content'];
        $dosyaYoluVeAdi = $data['dosyayoluveadi'];

        // Dosya uzantısını kontrol etme
        if (substr($dosyaYoluVeAdi, -7) !== '.sql.gz' && substr($dosyaYoluVeAdi, -4) !== '.sql') {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz dosya uzantısı. Dosya .sql veya .sql.gz olmalı.']);
            exit;
        }

        if (createDirectory(dirname($dosyaYoluVeAdi))) {
            if (substr($dosyaYoluVeAdi, -7) === '.sql.gz') {
                @$file = gzopen($dosyaYoluVeAdi, 'w');
                if ($file) {
                    gzwrite($file, $content);
                    gzclose($file);
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Dosya açılamadı']);
                }
            } else {
                @$file = fopen($dosyaYoluVeAdi, 'w');
                if ($file) {
                    fwrite($file, $content);
                    fclose($file);
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Dosya açılamadı']);
                }
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dizin oluşturulamadı']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Eksik veri, dosya yolu veya içerik biri eksik']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'POST istek değil']);
}

// Dizin oluşturma fonksiyonu
function createDirectory($dirPath) {
    if (!is_dir($dirPath)) {
        return mkdir($dirPath, 0777, true);
    }
    return true;
}
?>