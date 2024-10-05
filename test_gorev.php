<?php 
/**
 * Kendinize özel bir dosya oluşturup görev zamanlayıcı ile dosyayı çalıştırıp görevi yerine getirebilirsiniz.
 * Aşağıdaki fonksiyon içine özel php kodlarınızı oluşturabilirsiniz.
 * $PDOdb değişkeni ile veritabanı başlantısı kurabilirsiniz.
 * Görev başarılı olduğunda 'status' => 'success' metin dizi olarak çıktı vermesi gerekiyor ki 
 * görevin başarılı olduğunu program anlasın sonucu günlüğe yazsın ve bir sonraki çalışma zamanına güncellesin.
 * Aşağıdaki örnekteki gibi birden fazla dizi olarak çıktı oluşturabilirsiniz ve çıktılar günlüğe yazılacaktır ve
 * günlüğü kontrol ederken görevde nelerin yerine geldiğini görme imkanı sağlayacaktır.
 * aşağıdaki "namespace TEST;" buradaki "TEST" metni dosya adının başında alt tireye kadar olan metin olması gerekiyor, örnek: test_gorev.php
 */
namespace TEST;
    function ozelCalistirilacakDosya($PDOdb){

    $sonuc_cikti_mesaji = [];

        $sonuc_cikti_mesaji[] = [
            'status' => 'success',
            'message' => '<span style="color:green;">Özel Dosya  Başarıyla Çalıştırıldı</span>'
        ];
        $sonuc_cikti_mesaji[] = [
            'status' => 'success',
            'message' => '<span style="color:green;">Değiştirildi</span>'
        ];
        $sonuc_cikti_mesaji[] = [
            'status' => 'success',
            'message' => '<span style="color:green;">Güncellendi</span>'
        ];
        $sonuc_cikti_mesaji[] = [
            'status' => 'success',
            'message' => '<span style="color:green;">Silindi</span>'
        ];
        $sonuc_cikti_mesaji[] = [
            'status' => 'success',
            'message' => '<span style="color:green;">E-Posta Gönderildi</span>'
        ];
    return $sonuc_cikti_mesaji;
    }
?>