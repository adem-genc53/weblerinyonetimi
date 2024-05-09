<?php 
// Bismillahirrahmanirrahim
require_once("includes/turkcegunler.php");

if(isset($_POST['ajaxtan'])){
    //echo '<pre>' . print_r($_POST, true) . '</pre>';
// Gönderilen gün değeri
$gun = isset($_POST['gun']) ? $_POST['gun'] : '-1';
// Gönderilen saat değeri
$saat = isset($_POST['saat']) ? $_POST['saat'] : '-1';
// Gönderilen dakika değeri
$dakika = isset($_POST['dakika']) ? $_POST['dakika'] : '-1';
// Gönderilen haftanın değeri
$haftanin_gunu = isset($_POST['haftanin_gunu']) ? $_POST['haftanin_gunu'] : [0=>-1];
}

if(isset($gun) && isset($saat) && isset($dakika) && isset($haftanin_gunu)){

// Şu anki tarihi ve saat bilgisini al
$bugun = new DateTime();

// Unix zaman damgasını depolamak için varsayılan tarih nesnesi oluştur
$tarih = new DateTime();

##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################

    function saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, $gun_bugun_mu){

if ($saat == -1 && $dakika == -1) { // SAAT && DAKİKA * YILDIZ SEÇİLİ İSE

                            if($gun_bugun_mu == 1){ // 1 gün bugün
                                $tarih->setTime($bugun->format('H'), $bugun->format('i')+1, 0); // SAAT VE DAKİKA -1 * YILDIZ VE GÜN BUGÜN OLDUĞU İÇİN DAKİKA +1 AYARLIYORUZ
                            }else if($gun_bugun_mu == 2){ // 2 gün bugün değil
                                $tarih->setTime($bugun->format('H'), $bugun->format('i'), 0); // // SAAT VE DAKİKA -1 * YILDIZ VE GÜN BUGÜN OLMADIĞI İÇİN DAKİKA +1 GEREKLİ DEĞİLDİR
                            }
                            //echo "1 Saat ve Dakika Kontrol Bölümü<br>";

} else if (strpos($saat, '/') !== false && strpos($dakika, '/') === false) { // ÖZEL SAAT ARALIK SEÇİLİ İSE && ÖZEL DAKİKA ARALIK SEÇİLİ DEĞİL İSE

                        list($eksibir, $ozelsaat) = explode('/', $saat); // ÖZEL SAAT ARALIK DEĞERLERİ PARÇALA

                            if(strpos($ozelsaat, '.') !== false && $bugun->format('i') < 30){ // GEÇERLİ DAKİKA 30 DAN KÜÇÜK İSE

                                $tarih->setTime($bugun->format('H'), 30, 0); // GEÇERLİ DAKİKA 30 DAN KÜÇÜK OLDUĞU İÇİN GEÇERLİ SAATİ VE DAKİKA 30 A AYARLIYORUZ
                                //echo "2 Saat ve Dakika Kontrol Bölümü<br>";

                            }else if(strpos($ozelsaat, '.') !== false && $bugun->format('i') > 30){ // GEÇERLİ DAKİKA 30 DAN BÜYÜK İSE

                                $tarih->setTime($bugun->format('H')+1, 0, 0); // GEÇERLİ DAKİKA 30 U GEÇTİĞİ İÇİN BİR SONRAKİ SAAT VE DAKİKA 0 AYARLIYORUZ
                                //echo "3 Saat ve Dakika Kontrol Bölümü<br>";

                            }else if($ozelsaat == 12){
                                    //echo "4 Saat ve Dakika Kontrol Bölümü<br>";
                                    // ÖZEL SAAT ARALIĞINDAKİ */12 DEĞER GEÇERLİ SAAT ÜZERİNDEN DEĞİL GÜNDÜZ ÖĞLE 12:00 VE GEÇE 00:00 OLARAK AYARLIYORUZ 
                                    if($bugun->format('H') >= 12){ // GEÇERLİ SAAT ÖĞLE 12 GEÇİYORSA
                                        $tarih->setTime(0, 0, 0); // ÖĞLE 12 GEÇTİĞİ İÇİN GECE 00:00 OLARAK AYARLIYORUZ
                                        if($gun == $bugun->format('d') || $gun == -1){
                                            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d')+1); // GECE 00:00 OLDUĞUNDAN BİR SONRAKİ GÜNE AYARLANIYOR
                                        }
                                    }else if($bugun->format('H') < 12){ // GEÇERLİ SAAT ÖĞLE 12 YE DAHA ZAMAN VARSA
                                        $tarih->setTime(12, 0, 0); // SAAT HENÜZ 12 OLMADIĞI İÇİN ÖĞLE 12:00 OLARAK AYARLIYORUZ
                                    }
                            } elseif (strpos($ozelsaat, '.') == false) {
                                if($dakika == -1){
                                    $tarih->setTime($bugun->format('H')+$ozelsaat, $bugun->format('i'), 0); // DAKİKA -1 * SEÇİLİ OLDUĞUNDAN GEÇERLİ DAKİKA AYARLIYORUZ
                                }else{
                                    $tarih->setTime($bugun->format('H')+$ozelsaat, $dakika, 0); // SEÇİLİ SAAT ARALIĞI KADAR SAATİ İLERİ ALIYORUZ & DAKİKA 0-59 ARASI SEÇİLİ OLDUĞUNDAN SEÇİLİ DAKİKA AYARLIYORUZ
                                }
                                //echo "5 Saat ve Dakika Kontrol Bölümü<br>";
                            }

} else if (strpos($saat, '/') === false && $saat == -1 && strpos($dakika, '/') !== false) { // SAAT ARALIĞI SEÇİLİ DEĞİL && DAKİKA ÖZEL ARALIK SEÇİLİ İSE

                            list($eksibir, $ozeldakika) = explode('/', $dakika);
                            if($gun_bugun_mu == 1){ // 1 gün bugün
                                $tarih->setTime($bugun->format('H'), $bugun->format('i')+$ozeldakika, 0); // SAAT -1, GÜN BUGÜN OLDUĞUNDA ÖZEL DAKİKA ARALIĞI KADAR DAKİKAYI İLERİ ALIYORUZ
                            }else if($gun_bugun_mu == 2){ // 2 gün bugün değil
                                $tarih->setTime($bugun->format('H'), $bugun->format('i'), 0); // SAAT -1, GÜN BUGÜN OLMADIĞI İÇİN GEÇERLİ DAKİKA AYARLIYORUZ
                            }
                            //echo "6 Saat ve Dakika Kontrol Bölümü<br>";

} else if (strpos($saat, '/') === false && $saat != -1 && $dakika != -1) { // SAAT NORMAL SEÇİLİ && DAKİKA NORMAL SEÇİLİ İSE

                            $tarih->setTime($saat, $dakika, 0); // SAAT VE DAKİKA NORMAL SEÇİLİ OLDUĞUNDAN SEÇİLİ SAAT VE DAKİKA AYARLIYORUZ
                            //echo "7 Saat ve Dakika Kontrol Bölümü<br>";

} else if ($saat == -1 && $dakika != -1) { // SAAT * YILDIZ && DAKİKA NORMAL SEÇİLİ İSE

                            if($gun_bugun_mu == 1){ // 1 gün bugün
                                if($dakika > $bugun->format('i')){ // SEÇİLEN SAAT ŞİMDİKİ SAATTEN BÜYÜK
                                    $tarih->setTime($bugun->format('H'), $dakika, 0); // SEÇİLEN DAKİKA ŞİMDİKİ DAKİKADAN BÜYÜK OLDUĞUNDAN +1 SAAT GEREKLİ DEĞİLDİR
                                }else{
                                    $tarih->setTime($bugun->format('H')+1, $dakika, 0); // SEÇİLEN DAKİKA ŞİMDİKİ DAKİKADAN KÜÇÜK OLDUĞUNDAN +1 SAAT GEREKLİDİR
                                }
                            }else if($gun_bugun_mu == 2){ // 2 gün bugün değil
                                $tarih->setTime($bugun->format('H'), $dakika, 0); // SONRAKİ GÜN OLDUĞUNDA SEÇİLEN DAKİKA VE GEÇERLİ SAAT AYARLIYORUZ
                            }
                            //echo "8 Saat ve Dakika Kontrol Bölümü<br>";

} else if ($saat != -1 && $dakika == -1) { // SAAT NORMAL SEÇİLİ && DAKİKA * YILDIZ SEÇİLİ İSE

                            if($gun_bugun_mu == 1){ // 1 gün bugün
                                if($saat > $bugun->format('H')){ // SEÇİLEN SAAT ŞİMDİKİ SAATTEN BÜYÜK
                                    $tarih->setTime($saat, $bugun->format('i'), 0); // SEÇİLEN SAAT ŞİMDİKİ SAATTEN BÜYÜK OLDUĞUNDAN +1 DAKİKA GEREKLİ DEĞİLDİR
                                }else{
                                    $tarih->setTime($saat, $bugun->format('i')+1, 0); // SEÇİLEN SAAT ŞİMDİKİ SAATTEN KÜÇÜK OLDUĞUNDAN +1 DAKİKA GEREKLİDİR
                                }
                            }else if($gun_bugun_mu == 2){ // 2 gün bugün değil
                                $tarih->setTime($saat, $bugun->format('i'), 0); // SONRAKİ GÜN OLDUĞUNDA SEÇİLEN SAAT VE GEÇERLİ DAKİKA AYARLIYORUZ
                            }
                            //echo "9 Saat ve Dakika Kontrol Bölümü<br>";

} else {
    // Diğer durumlar
            $tarih->setTime($bugun->format('H'), $bugun->format('i'), 0);
            //echo "Saat ve dakika hiçbir koşul karşılamıyor";
}

    return $tarih;
    }

##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
    function haftaKontrolu($bugun, $tarih, $haftanin_gunu, $gun, $saat, $dakika) {

        $bugunun_gunu = $bugun->format('N');
        $bugunun_saati = $bugun->format('H');
        $bugunun_dakikasi = $bugun->format('i');
        // Eğer seçilen haftanın günleri dizi içinde bugün yoksa dizideki ilk günü ver
        $h_gunu_ver = $haftanin_gunu[0];
        $haftanin_isimleri = array(1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday', 6=>'Saturday', 7=>'Sunday');

        foreach($haftanin_gunu AS $h_gunu){
            if($h_gunu >$bugunun_gunu){
                $h_gunu_ver = $h_gunu;
                break;
            }
        }

        // 1. SEÇENEK KURALI------------(SEÇİLENİN İÇİNDE BUGÜN YOKSA SIRADAKİ HAFTANIN GÜNÜNÜ AYARLAR)
        // Seçilen hafta gün(leri) içinde BUGÜN MEVCUT DEĞİL İSE
        if(!in_array($bugunun_gunu, $haftanin_gunu) 

        // 2. SEÇENEK KURALI------------(SEÇİLENİN İÇİNDE BUGÜN VARSA, ŞİMDİKİ SAAT GEÇTİ İSE, "DAKİKAYI DİKKATE ALMAYA GEREK YOK" SIRADAKİ HAFTANIN GÜNÜNÜ AYARLAR)
        // OR Seçilen hafta gün(leri) içinde BUGÜN MEVCUT İSE
        // AND Özel saat aralığı seçili DEĞİL
        // AND Seçilen saat 0-23 arası ise 
        // AND Seçilen saat bugünkü saatten KÜÇÜK İSE
        || (in_array($bugunun_gunu, $haftanin_gunu) && (strpos($saat, '/') === false && $saat != -1 && $saat > -1 && $saat < $bugunun_saati

        // 3. SEÇENEK KURALI------------(SEÇİLENİN İÇİNDE BUGÜN VARSA, SEÇİLEN SAAT İLE ŞİMDİKİ SAAT EŞİT İSE, SEÇİLEN DAKİKA ŞİMDİKİ DAKİKADAN KÜÇÜK VEYA EŞİT İSE SIRADAKİ HAFTANIN GÜNÜNÜ AYARLAR)
        // OR Seçilen hafta gün(leri) içinde BUGÜN MEVCUT İSE
        // AND Özel saat aralığı seçili DEĞİL
        // AND Seçilen saat 0-23 arası ise
        // AND seçilen saat bugünkü saat ile EŞİT İSE
        // AND seçilen dakika 0-59 arası ise
        // AND seçilen dakika bugünkü dakikadan küçük veya EŞİT İSE
        || (in_array($bugunun_gunu, $haftanin_gunu) && strpos($saat, '/') === false && $saat != -1 && $saat > -1 && $saat == $bugunun_saati && $dakika != -1 && $dakika > -1 && $dakika <= $bugunun_dakikasi)))){

            $tarih->modify("next ".$haftanin_isimleri[$h_gunu_ver]);

            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
            // echo "1 Haftanın Günü: haftanın günü ayarlandı<br>";
            // Haftanın günü ayarlandığında saat ve dakika 00:00 sıfılandığı için tekrar geçerli saati ve dakikayı tanımlıyoruz
            //$tarih->setTime($bugun->format('H'), $bugun->format('i'));

        }else{
            // 4. SEÇENEK KURALI------------(SEÇİLEN İÇİNDE BUGÜN VAR, SAAT VEYA DAKİKA VEYA HER İKİSİ -1 * YILDIZ SEÇİLİ OLDUĞUNDAN, SEÇİLEN SAAT VE VEYA DAKİKA BÜYÜK İSE)

            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            // echo "2 Haftanın Günü: haftanın günü bugün olarak ayarlandı<br>";
        }
    return $tarih;
    }
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
    function gunKontrolu($bugun, $tarih, $gun, $saat, $dakika){

        // ÖZEL GÜN ARALIĞI SEÇİLİDİR
        if(strpos($gun, '/') !== false){

            list($eksibir, $ozelgun) = explode('/', $gun); // ÖZEL GÜN ARALIĞI */2 DEĞERİ PARÇALAYALIM
            $tarih->modify(+$ozelgun.' day'); // GÜN ARALIĞI DEĞERİ KADAR GÜNÜ İLERİ AYARLIYORUZ

            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
            // echo "Gün aralığı seçeneği ile +{$ozelgun} gün olarak ayarlandı<br>";

##############################################################################################################################################################

        }else if(strpos($gun, '/') === false && $gun != -1 && $gun > 0){ // NORMAL 1-31 GÜN DEĞERİ SEÇİLDİR

//echo '}else if($gun != -1 && $gun > 0){<br>';

##############################################################################################################################################################

    if ($bugun->format('d') == $gun) { // SEÇİLEN GÜN İLE GEÇERLİ GÜN EŞİT İSE SAAT VE DAKİKA DİKKATE ALARAK İŞLEM YAPILACAK

            // SEÇİLEN SAAT BUGÜNKÜ SAATTEN KÜÇÜK OLDUĞUNDAN DAKİKA DİKKATE ALINMADAN SONRAKİ AYIN SEÇİLEN GÜNÜNE, SEÇİLEN SAATE VE SEÇİLEN DAKİKAYA AYARLA
            if ($saat != -1 && $saat < $bugun->format('H')
                // VEYA SEÇİLEN SAAT BUGÜNKÜ SAAT İLE EŞİT İSE VE SEÇİLEN DAKİKA BUGÜNKÜ DAKİKADAN EŞİT VEYA KÜÇÜK İSE SONRAKİ AYIN SEÇİLEN GÜNÜNE, SEÇİLEN SAATE VE SEÇİLEN DAKİKAYA AYARLA
                || $saat != -1 && $saat == $bugun->format('H') && $dakika != -1 && $dakika <= $bugun->format('i')) {

            if (strpos($saat, '/') === false) {
                $tarih->setDate($bugun->format('Y'), $bugun->format('m')+1, $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil

                //echo "SEÇİLEN SAAT KÜÇÜK İSE, VEYA SEÇİLEN SAAT EŞİT VE SEÇİLEN DAKİKA EŞİT VEYA KÜÇÜK İSE SONRAKİ AYIN SEÇİLEN GÜNÜNE, SEÇİLEN SAATE VE SEÇİLEN DAKİKAYA AYARLANDI<br>";
                
            } else {
                $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil

                //echo "GÜN BUGÜN SEÇİLEN SAAT ARALIĞI KADAR +SAAT AYARLANDI<br>";
            }

                // EĞER SEÇİLEN GEÇERLİ AYIN SON GÜNÜNDEN BÜYÜK SEÇİLİ İSE BU FAZLA GÜN SONRAKİ AYIN GÜNÜNE İLAVE EDİLİR VE SONRAKİ AYIN 1., VEYA 2., VEYA 3. GÜN OLARAK GÖRÜNÜR
                // HALBUKİ BURADA ÖRNEK GÜN 31 SEÇİLİYOR İSE AY SONU SEÇİLİYOR ANLAMINA GELİR ANCAK GEÇERLİ AY 29 İLE ÇIKIYORSA BURADA ARTAN 2 GÜN SONRAKİ AYIN 2. GÜNÜNE AYARLANIR
                // AŞAĞIDAKİ KURAL SEÇİLEN GÜN BU AYIN SON GÜNÜNDEN BÜYÜK OLDUĞUNDAN SONRAKİ AYA AYARLANACAĞI İÇİN -1 İLE AY'I GERİ ALIYORUZ VE AYIN GÜNÜNÜ (int)$bugun->format('t') DEĞİŞKEN İLE BELİRLEYEREK AYARLIYORUZ
                if($gun > (int)$bugun->format('t')){
                    $tarih->setDate($bugun->format('Y'), $bugun->format('m')-1, (int)$bugun->format('t'));
                    //echo "1 SEÇİLEN GÜN BU AYIN SON GÜNÜNDEN BÜYÜK OLDUĞUNDA -1 İLE AY GERİ ALIYORUZ VE BU AYIN ".(int)$bugun->format('t').". GÜNÜNE AYARLANDI<br>";
                }

                // SEÇİLEN SAAT BUGÜNKÜ SAATTEN BÜYÜK OLDUĞUNDA SEÇİLEN GELECEK SAATE VE SEÇİLEN DAKİKAYA AYARLA
            } elseif ($saat != -1 && $saat > $bugun->format('H')
                // SEÇİLEN SAAT BUGÜNKÜ SAAT İLE EŞİT VE SEÇİLEN DAKİKA BUGÜNKÜ DAKİKADAN BÜYÜK OLDUĞUNDAN GELECEK DAKİKAYA AYARLA
                || $saat != -1 && $saat == $bugun->format('H') && ($dakika != -1 && $dakika > $bugun->format('i') || $dakika == -1)
                || ($saat == -1 && $dakika == -1)
                || $saat == -1 && $dakika != -1) {
            
                $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil

                //echo "SEÇİLEN SAAT BÜYÜK İSE, <br>VEYA SEÇİLEN SAAT EŞİT VE SEÇİLEN DAKİKA BÜYÜK VEYA DAKİKA -1 İSE <br>VEYA SAAT VE DAKİKA -1 İSE VEYA SAAT -1 VE DAKİKA 0-59 SEÇİLİ İSE <br>GÜN BUGÜN, SEÇİLEN SAATE VE SEÇİLEN DAKİKAYA AYARLANDI<br>";

            }

    // SEÇİLEN GÜN BUGÜNDEN KÜÇÜK OLDUĞUNDAN SONRAKİ AYIN GÜNÜNE AYARLA
    } elseif ($bugun->format('d') > $gun) {

        $tarih->setDate($bugun->format('Y'), $bugun->format('m')+1, $gun);
        $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil

        //echo "SEÇİLEN GÜN BUGÜNDEN KÜÇÜK OLDUĞUNDAN SAAT VE DAKİKA DİKKATE ALINMADAN SONRAKİ AYIN GÜNÜNE AYARLANDI<br>";

    // SEÇİLEN GÜN BUGÜNDEN BÜYÜK OLDUĞUNDAN BU AYIN SEÇİLEN GÜNÜNE AYARLA
    } elseif ($bugun->format('d') < $gun) {

        $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $gun);
        $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil

        //echo "SEÇİLEN GÜN BUGÜNDEN BÜYÜK OLDUĞUNDAN SAAT VE DAKİKA DİKKATE ALINMADAN BU AYIN SEÇİLEN GÜNÜNE AYARLANDI<br>";

        // EĞER SEÇİLEN GEÇERLİ AYIN SON GÜNÜNDEN BÜYÜK SEÇİLİ İSE BU FAZLA GÜN SONRAKİ AYIN GÜNÜNE İLAVE EDİLİR VE SONRAKİ AYIN 1., VEYA 2., VEYA 3. GÜN OLARAK GÖRÜNÜR
        // HALBUKİ BURADA ÖRNEK GÜN 31 SEÇİLİYOR İSE AY SONU SEÇİLİYOR ANLAMINA GELİR ANCAK GEÇERLİ AY 29 İLE ÇIKIYORSA BURADA ARTAN 2 GÜN SONRAKİ AYIN 2. GÜNÜNE AYARLANIR
        // AŞAĞIDAKİ KURAL SEÇİLEN GÜN BU AYIN SON GÜNÜNDEN BÜYÜK OLDUĞUNDAN SONRAKİ AYA AYARLANACAĞI İÇİN -1 İLE AY'I GERİ ALIYORUZ VE AYIN GÜNÜNÜ (int)$bugun->format('t') DEĞİŞKEN İLE BELİRLEYEREK AYARLIYORUZ
        if($gun > (int)$bugun->format('t')){
            $tarih->setDate($tarih->format('Y'), $tarih->format('m')-1, (int)$bugun->format('t'));
            //echo "2 SEÇİLEN GÜN BU AYIN SON GÜNÜNDEN BÜYÜK OLDUĞUNDA -1 İLE AY GERİ ALIYORUZ VE BU AYIN ".(int)$bugun->format('t').". GÜNÜNE AYARLANDI<br>";
        }

    }

##############################################################################################################################################################

        }else if($gun == -1){

// echo '}else if($gun == -1){<br>';


    // SAAT VE DAKİKA ARALIKLARI SEÇİL Mİ?
    $saatCheck = strpos($saat, '/') === false; // sonuç 1, aralık seçili değildir
    $dakikaCheck = strpos($dakika, '/') === false; // sonuç 1, aralık seçili değildir

    // SAAT VE DAKİKA -1 SEÇİLİ İSE BUGÜNKÜ SAAT VE DAKİKAYI VER, DEĞİL İSE SEÇİLEN SAAT VE DAKİKAYI VER
    $saatValue = $saat == -1 ? $bugun->format('H') : $saat;
    $dakikaValue = $dakika == -1 ? $bugun->format('i') : $dakika;


    // SAAT VE DAKİKA ARALIKLARI SEÇİLİ DEĞİLDİR
    if ($saatCheck && $dakikaCheck) {
        if ($saat == -1 && $dakika == -1) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "GÜN BUGÜN AYARLA (HEPSİ -1 OLDUĞUNDAN HER GÜN, HER SAAT, HER DAKİKA AYARLANACAK)";

        } elseif ($saat != -1 && $saatValue == $bugun->format('H') && $dakika == -1) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "GÜN BUGÜN AYARLA (SEÇİLEN SAAT BUGÜNKÜ SAAT İLE EŞİT VE DAKİKA -1 OLDUĞUNDAN BUGÜNÜ +1 DAKİKA AYARLANACAK)";

        } elseif ($saat != -1 && $saatValue == $bugun->format('H') && $dakika != -1 && $dakikaValue <= $bugun->format('i')) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d')+1);
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
            //echo "SONRAKİ GÜNE AYARLA (SEÇİLEN SAAT EŞİT VE SEÇİLEN DAKİKA BUGÜNKÜ DAKİKADAN KÜÇÜK VEYA EŞİT OLDUĞUNDAN SONRAKİ GÜNE AYARLANACAK)";

        } elseif ($saat != -1 && $saatValue == $bugun->format('H') && $dakika != -1 && $dakikaValue > $bugun->format('i')) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "GÜN BUGÜN AYARLA (SEÇİLEN SAAT EŞİT VE SEÇİLEN DAKİKA BUGÜNKÜ DAKİKADAN BÜYÜK OLDUĞUNDAN BUGÜNE AYARLANACAK)";

        } elseif ($saat != -1 && $saatValue > $bugun->format('H')) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "GÜN BUGÜN AYARLA (SEÇİLEN SAAT BUGÜNKÜ SAATTEN BÜYÜK OLDUĞUNDAN DAKİKA DİKKATE ALINMADAN BUGÜNE AYARLANACAK)";

        } elseif ($saat != -1 && $saatValue < $bugun->format('H')) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d')+1);
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
            //echo "SONRAKİ GÜNE AYARLA (SEÇİLEN SAAT BUGÜNKÜ SAATTEN KÜÇÜK OLDUĞUNDAN DAKİKA DİKKATE ALINMADAN SONRAKİ GÜNE AYARLANACAK)";

        } elseif ($saat == -1 && $dakika != -1 && $dakikaValue > $bugun->format('i')) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "GÜN BUGÜN AYARLA (HER SAAT VE SEÇİLEN DAKİKA BUGÜNKÜ DAKİKADAN BÜYÜK OLDUĞUNDAN SEÇİLEN DAKİKA AYARLANACAK)";

        } elseif ($saat == -1 && $dakika != -1 && $dakikaValue <= $bugun->format('i')) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "GÜN BUGÜN AYARLA (HER SAAT VE SEÇİLEN DAKİKA BUGÜNKÜ DAKİKADAN KÜÇÜK OLDUĞUNDAN SONRAKİ SAATE VE SEÇİLEN DAKİKA AYARLANACAK)";

        } else {
             echo "Diğer durumlar";
        }

    } elseif ($saatCheck && !$dakikaCheck) { // SAAT ARALIĞI SEÇİLİ DEĞİLDİR, && DAKİKA ARALIĞI SEÇİLİDİR
        if ($saat == -1) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "GÜN BUGÜN AYARLA (SEÇİLEN SAAT -1 VE SEÇİLEN DAKİKA ARALIĞI OLDUĞUNDAN DAKİKA ARALIĞI KADAR ARTIRILACAK VE BUGÜNE AYARLANACAK)";

        } elseif ($saat != -1 && $saat > $bugun->format('H')) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "GÜN BUGÜN AYARLA (SEÇİLEN SAAT BUGÜNKÜ SAATTEN BÜYÜK OLDUĞUNDAN İLERKİ SAATE AYARLANACAK)";

        } elseif ($saat != -1 && $saat <= $bugun->format('H')) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d')+1);
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
            //echo "SONRAKİ GÜNE AYARLA (SEÇİLEN SAAT BUGÜNKÜ SAATTEN KÜÇÜK VEYA EŞİT OLDUĞUNDAN SONRAKİ GÜNE AYARLANACAK)";

        } elseif ($saat != -1 && $saat == $bugun->format('H')) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "GÜN BUGÜN AYARLA (SEÇİLEN SAAT BUGÜNKÜ SAAT İLE EŞİT OLDUĞUNDAN BUGÜNE AYARLANACAK)";

        }
    } elseif (!$saatCheck && $dakikaCheck) { // SAAT ARALIĞI SEÇİLİDİR, && DAKİKA ARALIĞI SEÇİLİ DEĞİLDİR
        if ($dakika == -1) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "SAAT ARALIĞI VE DAKİKA -1 SEÇİLİ, ANCAK SAAT ARALIĞI SEÇİLİ İKEN DAKİKA -1 SEÇİLMESİNE İZİN VERMİYORUZ ANCAK KURALIN BULUNMASINDA FAYDA VARDIR";

        } elseif ($dakika != -1 && $dakika > -1) {

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "GÜN BUGÜN AYARLA (SAAT ARALIĞI SEÇİLİ, DAKİKA 0-59 ARASI SEÇİLİ OLDUĞUNDAN SAAT ARALIĞI KADAR ARTIRILACAK VE SEÇİLEN DAKİKA AYARLANACAK)";

        }
    } elseif (!$saatCheck && !$dakikaCheck) { // SAAT VE DAKİKA ARALIKLARI SEÇİLİDİR

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $gun, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            //echo "SAAT VE DAKİKA ARALIKLARI SEÇİLİ. HER İKİSİNİN SEÇİLMESİNE İZİN VERMİYORUZ ANCAK KURALIN BULUNMASINDA FAYDA VARDIR";
    }


        } // }else if($gun == -1){
        return $tarih;
    } // function gunKontrolu($bugun, $tarih, $gun, $saat, $dakika){

##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################

    // HAFTANIN GÜN(LERİ) SEÇİLİ İSE HAFTANIN GÜN(LERİ) İŞLEMLERİNE BAŞLA
    if (!in_array("-1", $haftanin_gunu)){

        $tarih = haftaKontrolu($bugun, $tarih, $haftanin_gunu, $gun, $saat, $dakika);

    }else{ // HAFTANIN GÜNÜ -1 * YILDIZ SEÇİLİ İSE GÜN İŞLEMLERİNE BAŞLA

        $tarih = gunKontrolu($bugun, $tarih, $gun, $saat, $dakika);

    }
        $tarih->setTimezone(new DateTimeZone('UTC'));

    if(isset($_POST['ajaxtan'])){
        echo date_tr('j F Y l, H:i', $tarih->format('U'));
    }else{
        $sonraki_calisma = $tarih->format('U');
    }
} // if(isset($gun) && isset($saat) && isset($dakika) && isset($haftanin_gunu)){
?>