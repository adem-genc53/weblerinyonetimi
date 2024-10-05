<?php 
// Bismillahirrahmanirrahim

namespace DOVIZ;

function tcmbDovizKurlariCek() {
    $url = "https://www.tcmb.gov.tr/kurlar/today.xml";
    
    // Hata yönetimi için deneme bloğu
    try {
        $xml = @simplexml_load_file($url);
        if ($xml === false) {
            throw new Exception('TCMB XML dosyası alınamadı.');
        }
        return $xml;
    } catch (Exception $e) {
        // Hata mesajını döndür
        return false;
    }
}

function ozelCalistirilacakDosya($PDOdb) {
    // TCMB'den XML verisini çekelim
    $xml = tcmbDovizKurlariCek();

    // XML çekilemediyse hata verelim
    if ($xml === false) {
        return ["TCMB'den XML dosyası çekilemedi."];
    }

    $dolar_satis    = null;
    $euro_satis     = null;
    $bireuro_dolar  = null;
/*
    // Dolar ve Euro için efektif satış kurları
    $dolar_satis = (float) $xml->Currency[0]->BanknoteSelling; // USD
    $euro_satis = (float) $xml->Currency[3]->BanknoteSelling; // EUR
    $bireuro_dolar = (float) $xml->Currency[3]->CrossRateOther; // EUR/USD çapraz kuru
*/
    foreach ($xml->Currency as $currency) {
        $currencyCode = (string) $currency['CurrencyCode'];
        
        if ($currencyCode == 'USD') {
            $dolar_satis = (float) $currency->BanknoteSelling;
        }

        if ($currencyCode == 'EUR') {
            $euro_satis = (float) $currency->BanknoteSelling;
            $bireuro_dolar = (float) $currency->CrossRateOther; // EUR/USD çapraz kuru
        }
    }

    $sonuc_cikti_mesaji = [];
    $kurlar = [];

    // Dolar ve Euro kurlarını kontrol et ve diziye ekle
    if ($dolar_satis > 0) {
        $kurlar[] = ['id' => 1, 'doviz_cinsi' => 1, 'birime' => 3, 'tcmb_kur' => $dolar_satis, 'birim' => 'USD den TL'];
    }
    if ($euro_satis > 0) {
        $kurlar[] = ['id' => 2, 'doviz_cinsi' => 2, 'birime' => 3, 'tcmb_kur' => $euro_satis, 'birim' => 'EURO dan TL'];
    }
    if ($bireuro_dolar > 0) {
        $kurlar[] = ['id' => 3, 'doviz_cinsi' => 2, 'birime' => 1, 'tcmb_kur' => $bireuro_dolar, 'birim' => 'EURO dan USD'];
    }

    if (isset($dolar_satis) || isset($euro_satis) || isset($bireuro_dolar)) {

            $check = $PDOdb->prepare("SELECT COUNT(*) AS num FROM dovizkuru WHERE doviz_cinsi=? AND birime=? AND tcmb_kur=? AND id=?");

            if (is_array($kurlar) || is_object($kurlar)) {
                foreach ($kurlar as $value) {
                    $id             = $value['id'];
                    $doviz_cinsi    = $value['doviz_cinsi'];
                    $birime         = $value['birime'];
                    $tcmb_kur       = $value['tcmb_kur'];
                    $birimne        = $value['birim'];

                    $check->bindParam(1, $doviz_cinsi, \PDO::PARAM_STR);
                    $check->bindParam(2, $birime, \PDO::PARAM_STR);
                    $check->bindParam(3, $tcmb_kur, \PDO::PARAM_STR);
                    $check->bindParam(4, $id, \PDO::PARAM_INT);
                    $check->execute();
                    $row = $check->fetch(\PDO::FETCH_ASSOC);

                    if ($row['num'] == 0) {
                        try {
                            $sorgu = $PDOdb->prepare("UPDATE dovizkuru SET 
                                doviz_cinsi=?, 
                                birime=?, 
                                tcmb_kur=? 
                                WHERE id=? ");
                            
                            $sorgu->bindParam(1, $doviz_cinsi, \PDO::PARAM_STR);
                            $sorgu->bindParam(2, $birime, \PDO::PARAM_STR);
                            $sorgu->bindParam(3, $tcmb_kur, \PDO::PARAM_STR);
                            $sorgu->bindParam(4, $id, \PDO::PARAM_INT);
                            $sorgu->execute();

                            if ($sorgu->rowCount() > 0) {
                                $sonuc_cikti_mesaji[] = [
                                    'status' => 'success',
                                    'message' => '<span style="color:green;">Özel Dosya Kur Güncelleme Başarıyla Çalıştırıldı</span>'
                                ];
                                $sonuc_cikti_mesaji[] = [
                                    'status' => 'success',
                                    'message' => '<span style="color:green;">' . $birimne . ' Güncellendi</span>'
                                ];
                            } else {
                                $sonuc_cikti_mesaji[] = [
                                    'status' => 'error',
                                    'message' => '<span style="color:red;"> ' . $birimne . ' Güncellenemedi</span>'
                                ];
                            }
                        } catch (\PDOException $e) {
                            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
                            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                                $sonuc_cikti_mesaji[] = [
                                    'status' => 'error',
                                    'message' => '<span style="color:red;">Özel Dosya Duplicate</span>'
                                ];
                                $sonuc_cikti_mesaji[] = [
                                    'status' => 'error',
                                    'message' => '<span style="color:red;"> ' . $birimne . ' Duplicate</span>'
                                ];
                            } else {
                                throw $e;
                            }
                        }
                    } else {
                        $sonuc_cikti_mesaji[] = [
                            'status' => 'success',
                            'message' => '<span style="color:green;">Özel Dosya Kur Güncelleme Başarıyla Çalıştırıldı</span>'
                        ];
                        $sonuc_cikti_mesaji[] = [
                            'status' => 'error',
                            'message' => '<span style="color:red;"> ' . $birimne . ' Zaten Güncel</span>'
                        ];
                    }
                }
            }
    }

    return $sonuc_cikti_mesaji;
}

?>