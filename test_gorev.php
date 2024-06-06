<?php 
/**
 * Kendinize özel bir dosya oluşturup görev zamanlayıcı ile dosyayı çalıştırıp görevi yerine getirebilirsiniz.
 * Aşağıdaki fonksiyon içine özel php kodlarınızı oluşturabilirsiniz.
 * $PDOdb değişkeni ile veritabanı başlantısı kurabilirsiniz.
 * Görev başarılı olduğunda bu 'Özel Dosya Başarıyla Çalıştırıldı' metin dizi olarak çıktı vermesi gerekiyor ki 
 * görevin başarılı olduğunu program anlasın sonucu günlüğe yazsın ve bir sonraki çalışma zamanına güncellesin.
 * Aşağıdaki örnekteki gibi birden fazla dizi olarak çıktı oluşturabilirsiniz ve çıktılar günlüğe yazılacaktır ve
 * günlüğü kontrol ederken görevde nelerin yerine geldiğini görme imkanı sağlar.
 * aşağıdaki "namespace TEST;" buradaki "TEST" metni dosya adının başında alt tireye kadar olan metin olması gerekiyor örnek: test_gorev.php
 * Eğer class ve kütüphane kullanıyorsanız doviz_kurlar.php dosyaya göz atabilirsiniz.
 */
namespace TEST;
    function ozelCalistirilacakDosya($PDOdb){

        $sonuc = [];
        $sonuc[] = 'Değiştirildi';
        $sonuc[] = 'Güncellendi';
        $sonuc[] = 'Silindi';
        $sonuc[] = 'E-Posta Gönderildi';
        $sonuc[] = 'Özel Dosya Başarıyla Çalıştırıldı';
        return $sonuc;
    }
?>