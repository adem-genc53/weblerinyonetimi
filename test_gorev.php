<?php 
/**
 * Eğer görevi yürütecek bir dosya oluşturmak istiyorsanız sayfada aşağıdaki bir çıktı vermesi gerekiyor
 * Aşağıdaki dizide yer alan value 'Güncellendi' metnin olması zorunludur, bunun sebebi sayfanın başarılı 
 * olduğunu anlaşılması ve sonucu günlüğü yazılması ve bir sonraki çalışacak zamana ayarlanmasını sağlayacaktır.
 * Key bölümündeki 'Deneme' metni görevin adını yazabilirsiniz buda günlükteki çıktı mesajında 'Deneme Güncellendi' diye görünecektir.
 * Keyleri belirlemeseniz '0 Güncellendi', '1 Güncellendi' gibi key sayıları görünecektir.
 * Eğer günlükte daha fazla çıktı mesajı görünmesini istiyorsanız birden fazla dizi ekleyebilirsiniz
 * Örnek 'array('USD den TL'=>'Güncellendi', 'EURO dan TL'=>'Güncellendi', 'EURO dan USD'=>'Güncellendi')' dizideki 'Güncellendi' metnin hangi 
 * sırada olması önemli değil önemli olan herhangi bir value de olması yeterlidir.
 * Dizideki keylere ve valuelere sayfanın çalışma sonucu birden fazla çıktı üretiyorsa bu çıktıları dizi içine tanımlayabilirsiniz
 * 
 * GÜNLÜKTE ALAĞIDAKİ GİBİ GÖRÜNECEKTİR
 * 
 * USD den TL Güncellendi
 * EURO dan TL Güncellendi
 * EURO dan USD Güncellendi
 */

    $sonuc = array('USD den TL'=>'Güncellendi', 'EURO dan TL'=>'Güncellendi', 'EURO dan USD'=>'Güncellendi');
    $json_sonuc = json_encode($sonuc, JSON_UNESCAPED_UNICODE);
?>
<div style="display:none"><?php echo $json_sonuc; ?></div>