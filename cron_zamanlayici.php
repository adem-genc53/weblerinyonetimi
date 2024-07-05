<?php 
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
if (!function_exists('saatDakikaKontrolu')) {
    function saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, $gun_bugun_mu){

        if(strpos($saat, '/') !== false && strpos($dakika, '/') == false){ // ÖZEL SAAT ARALIK SEÇİLİ İSE && ÖZEL DAKİKA ARALIK SEÇİLİ DEĞİL İSE

                            list($eksibir, $ozelsaat) = explode('/', $saat); // ÖZEL SAAT ARALIK DEĞERLERİ PARÇALA

                            if(strpos($ozelsaat, '.') !== false && $bugun->format('i') < 30){ // GEÇERLİ DAKİKA 30 DAN KÜÇÜK İSE

                                $tarih->setTime($bugun->format('H'), 30, 0); // SAATİ GEÇERLİ SAAT, DAKİKA İSE 30 OLARAK AYARLIYORUZ
                                // echo "2.1 Saat ve Dakika Kontrol Bölümü<br>";

                            }else if(strpos($ozelsaat, '.') !== false && $bugun->format('i') > 30){ // GEÇERLİ DAKİKA 30 DAN BÜYÜK İSE

                                $tarih->setTime($bugun->format('H'), 0, 0); // SAATİ 00, DAKİKAYI 00 OLARAK AYARLIYORUZ
                                // echo "2.2 Saat ve Dakika Kontrol Bölümü<br>";

                            }else if($ozelsaat == 12){
                                    // echo "2.3 Saat ve Dakika Kontrol Bölümü<br>";
                                    // ÖZEL SAAT ARALIĞINDAKİ */12 DEĞER GEÇERLİ SAAT ÜZERİNDEN DEĞİL GÜNDÜZ ÖĞLE 12:00 VE GEÇE 00:00 OLARAK AYARLIYORUZ 
                                    if($bugun->format('H') > 12){ // GEÇERLİ SAAT ÖĞLE 12 GEÇİYORSA
                                        $tarih->setTime(0, 0, 0); // ÖĞLE 12 GEÇTİĞİ İÇİN GECE 00:00 OLARAK AYARLIYORUZ
                                    }else if($bugun->format('H') < 12){ // GEÇERLİ SAAT ÖĞLE 12 YE DAHA ZAMAN VARSA
                                        $tarih->setTime(12, 0, 0); // SAAT HENÜZ 12 OLMADIĞI İÇİN ÖĞLE 12:00 OLARAK AYARLIYORUZ
                                    }
                            }else{
                                if($dakika == -1){
                                    $tarih->setTime($bugun->format('H')+$ozelsaat, $bugun->format('i'), 0); // DAKİKA -1 * SEÇİLİ OLDUĞUNDAN GEÇERLİ DAKİKA AYARLIYORUZ
                                }else{
                                    $tarih->setTime($bugun->format('H')+$ozelsaat, $dakika, 0); // SEÇİLİ SAAT ARALIĞI KADAR SAATİ İLERİ ALIYORUZ & DAKİKA 0-59 ARASI SEÇİLİ OLDUĞUNDAN SEÇİLİ DAKİKA AYARLIYORUZ
                                }
                                // echo "2.4 Saat ve Dakika Kontrol Bölümü<br>";
                            }

        }else if(strpos($saat, '/') == false && $saat == -1 && strpos($dakika, '/') !== false){ // SAAT ARALIĞI SEÇİLİ DEĞİL && DAKİKA ÖZEL ARALIK SEÇİLİ İSE

                            list($eksibir, $ozeldakika) = explode('/', $dakika);
                            if($gun_bugun_mu == 1){ // 1 gün bugün
                                $tarih->setTime($bugun->format('H'), $bugun->format('i')+$ozeldakika, 0); // SAAT -1, GÜN BUGÜN OLDUĞUNDA ÖZEL DAKİKA ARALIĞI KADAR DAKİKAYI İLERİ ALIYORUZ
                            }else if($gun_bugun_mu == 2){ // 2 gün bugün değil
                                $tarih->setTime($bugun->format('H'), $bugun->format('i'), 0); // SAAT -1, GÜN BUGÜN OLMADIĞI İÇİN GEÇERLİ DAKİKA AYARLIYORUZ
                            }
                            // echo "3 Saat ve Dakika Kontrol Bölümü<br>";

        }else if($saat == -1 && $dakika == -1){ // SAAT && DAKİKA * YILDIZ SEÇİLİ İSE

                            if($gun_bugun_mu == 1){ // 1 gün bugün
                                $tarih->setTime($bugun->format('H'), $bugun->format('i')+1, 0); // SAAT VE DAKİKA -1 * YILDIZ VE GÜN BUGÜN OLDUĞU İÇİN DAKİKA +1 AYARLIYORUZ
                            }else if($gun_bugun_mu == 2){ // 2 gün bugün değil
                                $tarih->setTime($bugun->format('H'), $bugun->format('i'), 0); // // SAAT VE DAKİKA -1 * YILDIZ VE GÜN BUGÜN OLMADIĞI İÇİN DAKİKA +1 GEREKLİ DEĞİLDİR
                            }
                            // echo "4 Saat ve Dakika Kontrol Bölümü<br>";

        }else if(strpos($saat, '/') == false && $saat != -1 && $saat > -1 && strpos($dakika, '/') == false && $dakika != -1 && $dakika > -1){ // SAAT && DAKİKA NORMAL SEÇİLİ İSE

                            $tarih->setTime($saat, $dakika, 0); // SAAT VE DAKİKA NORMAL SEÇİLİ OLDUĞUNDAN SEÇİLİ SAAT VE DAKİKA AYARLIYORUZ
                            // echo "5 Saat ve Dakika Kontrol Bölümü<br>";

        }else if($saat == -1 && strpos($dakika, '/') == false && $dakika != -1 && $dakika > -1){ // SAAT * YILDIZ && DAKİKA NORMAL SEÇİLİ İSE

                            if($gun_bugun_mu == 1){ // 1 gün bugün
                                if($dakika > $bugun->format('i')){ // SEÇİLEN SAAT ŞİMDİKİ SAATTEN BÜYÜK
                                    $tarih->setTime($bugun->format('H'), $dakika, 0); // SEÇİLEN DAKİKA ŞİMDİKİ DAKİKADAN BÜYÜK OLDUĞUNDAN +1 SAAT GEREKLİ DEĞİLDİR
                                }else{
                                    $tarih->setTime($bugun->format('H')+1, $dakika, 0); // SEÇİLEN DAKİKA ŞİMDİKİ DAKİKADAN KÜÇÜK OLDUĞUNDAN +1 SAAT GEREKLİDİR
                                }
                            }else if($gun_bugun_mu == 2){ // 2 gün bugün değil
                                $tarih->setTime($bugun->format('H'), $dakika, 0); // SONRAKİ GÜN OLDUĞUNDA SEÇİLEN DAKİKA VE GEÇERLİ SAAT AYARLIYORUZ
                            }
                            // echo "6 Saat ve Dakika Kontrol Bölümü<br>";

        }else if(strpos($saat, '/') == false && $saat != -1 && $saat > -1 && $dakika == -1){ // SAAT NORMAL SEÇİLİ && DAKİKA * YILDIZ SEÇİLİ İSE

                            if($gun_bugun_mu == 1){ // 1 gün bugün
                                if($saat > $bugun->format('H')){ // SEÇİLEN SAAT ŞİMDİKİ SAATTEN BÜYÜK
                                    $tarih->setTime($saat, $bugun->format('i'), 0); // SEÇİLEN SAAT ŞİMDİKİ SAATTEN BÜYÜK OLDUĞUNDAN +1 DAKİKA GEREKLİ DEĞİLDİR
                                }else{
                                    $tarih->setTime($saat, $bugun->format('i')+1, 0); // SEÇİLEN SAAT ŞİMDİKİ SAATTEN KÜÇÜK OLDUĞUNDAN +1 DAKİKA GEREKLİDİR
                                }
                            }else if($gun_bugun_mu == 2){ // 2 gün bugün değil
                                $tarih->setTime($saat, $bugun->format('i'), 0); // SONRAKİ GÜN OLDUĞUNDA SEÇİLEN SAAT VE GEÇERLİ DAKİKA AYARLIYORUZ
                            }
                            // echo "7 Saat ve Dakika Kontrol Bölümü<br>";

        }else if(strpos($saat, '/') !== false && strpos($dakika, '/') !== false){ // ÖZEL SAAT VE DAKİKA ARALIĞI SEÇİLİ İSE

                            list($eksibir, $ozelsaat) = explode('/', $saat); // ÖZEL SAAT ARALIK DEĞERLERİ PARÇALA

                            if(strpos($ozelsaat, '.') !== false && $bugun->format('i') < 30){ // GEÇERLİ DAKİKA 30 DAN KÜÇÜK İSE

                                $tarih->setTime($bugun->format('H'), 30, 0); // SAATİ GEÇERLİ SAAT, DAKİKA İSE 30 OLARAK AYARLIYORUZ
                                // echo "8.1 Saat ve Dakika Kontrol Bölümü<br>";

                            }else if(strpos($ozelsaat, '.') !== false && $bugun->format('i') > 30){ // GEÇERLİ DAKİKA 30 DAN BÜYÜK İSE

                                $tarih->setTime($bugun->format('H'), 0, 0); // SAATİ 00, DAKİKAYI 00 OLARAK AYARLIYORUZ
                                // echo "8.2 Saat ve Dakika Kontrol Bölümü<br>";

                            }else if($ozelsaat == 12){
                                    // echo "8.3 Saat ve Dakika Kontrol Bölümü<br>";
                                    // ÖZEL SAAT ARALIĞINDAKİ */12 DEĞER GEÇERLİ SAAT ÜZERİNDEN DEĞİL GÜNDÜZ ÖĞLE 12:00 VE GEÇE 00:00 OLARAK AYARLIYORUZ 
                                    if($bugun->format('H') > 12){ // GEÇERLİ SAAT ÖĞLE 12 GEÇİYORSA
                                        $tarih->setTime(0, 0, 0); // ÖĞLE 12 GEÇTİĞİ İÇİN GECE 00:00 OLARAK AYARLIYORUZ
                                    }else if($bugun->format('H') < 12){ // GEÇERLİ SAAT ÖĞLE 12 YE DAHA ZAMAN VARSA
                                        $tarih->setTime(12, 0, 0); // SAAT HENÜZ 12 OLMADIĞI İÇİN ÖĞLE 12:00 OLARAK AYARLIYORUZ
                                    }
                            }else{
                                    if($dakika == -1){
                                        $tarih->setTime($bugun->format('H')+$ozelsaat, $bugun->format('i'), 0); // DAKİKA -1 * SEÇİLİ OLDUĞUNDAN GEÇERLİ DAKİKA AYARLIYORUZ
                                    }else if(strpos($dakika, '/') == false && $dakika != -1 && $dakika > -1){
                                        $tarih->setTime($bugun->format('H')+$ozelsaat, $dakika, 0); // SEÇİLİ SAAT ARALIĞI KADAR SAATİ İLERİ ALIYORUZ & DAKİKA 0-59 ARASI SEÇİLİ OLDUĞUNDAN SEÇİLİ DAKİKA AYARLIYORUZ
                                    }else if(strpos($dakika, '/') !== false){
                                        list($eksibir, $ozeldakika) = explode('/', $dakika);
                                        if($gun_bugun_mu == 1){ // 1 gün bugün
                                            $tarih->setTime($bugun->format('H'), $bugun->format('i')+$ozeldakika, 0); // SAAT -1, GÜN BUGÜN OLDUĞUNDA ÖZEL DAKİKA ARALIĞI KADAR DAKİKAYI İLERİ ALIYORUZ
                                        }else if($gun_bugun_mu == 2){ // 2 gün bugün değil
                                            $tarih->setTime($bugun->format('H'), $bugun->format('i'), 0); // SAAT -1, GÜN BUGÜN OLMADIĞI İÇİN GEÇERLİ DAKİKA AYARLIYORUZ
                                        }
                                    }
                                // echo "8 Saat ve Dakika Kontrol Bölümü<br>";
                            }

            // echo "8 Özel saat ve dakika aralığı seçilidir. ancak hem saat aralığı hem dakika aralığı uygun değildir<br>";
        }

    return $tarih;
    }
}
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
if (!function_exists('haftaKontrolu')) {
    function haftaKontrolu($bugun, $tarih, $haftanin_gunu, $saat, $dakika) {

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
        || (in_array($bugunun_gunu, $haftanin_gunu) && (strpos($saat, '/') == false && $saat != -1 && $saat > -1 && $saat < $bugunun_saati

        // 3. SEÇENEK KURALI------------(SEÇİLENİN İÇİNDE BUGÜN VARSA, SEÇİLEN SAAT İLE ŞİMDİKİ SAAT EŞİT İSE, SEÇİLEN DAKİKA ŞİMDİKİ DAKİKADAN KÜÇÜK VEYA EŞİT İSE SIRADAKİ HAFTANIN GÜNÜNÜ AYARLAR)
        // OR Seçilen hafta gün(leri) içinde BUGÜN MEVCUT İSE
        // AND Özel saat aralığı seçili DEĞİL
        // AND Seçilen saat 0-23 arası ise
        // AND seçilen saat bugünkü saat ile EŞİT İSE
        // AND seçilen dakika 0-59 arası ise
        // AND seçilen dakika bugünkü dakikadan küçük veya EŞİT İSE
        || (in_array($bugunun_gunu, $haftanin_gunu) && strpos($saat, '/') == false && $saat != -1 && $saat > -1 && $saat == $bugunun_saati && $dakika != -1 && $dakika > -1 && $dakika <= $bugunun_dakikasi)))){

            $tarih->modify("next ".$haftanin_isimleri[$h_gunu_ver]);

            $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
            // echo "1 Haftanın Günü: haftanın günü ayarlandı<br>";
            // Haftanın günü ayarlandığında saat ve dakika 00:00 sıfılandığı için tekrar geçerli saati ve dakikayı tanımlıyoruz
            //$tarih->setTime($bugun->format('H'), $bugun->format('i'));

        }else{
            // 4. SEÇENEK KURALI------------(SEÇİLEN İÇİNDE BUGÜN VAR, SAAT VEYA DAKİKA VEYA HER İKİSİ -1 * YILDIZ SEÇİLİ OLDUĞUNDAN, SEÇİLEN SAAT VE VEYA DAKİKA BÜYÜK İSE)

            $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            // echo "2 Haftanın Günü: haftanın günü bugün olarak ayarlandı<br>";
        }
    return $tarih;
    }
}
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
if (!function_exists('gunKontrolu')) {
    function gunKontrolu($bugun, $tarih, $gun, $saat, $dakika){

        // ÖZEL GÜN ARALIĞI SEÇİLİDİR
        if(strpos($gun, '/') !== false){

            list($eksibir, $ozelgun) = explode('/', $gun); // ÖZEL GÜN ARALIĞI */2 DEĞERİ PARÇALAYALIM
            $tarih->modify(+$ozelgun.' day'); // GÜN ARALIĞI DEĞERİ KADAR GÜNÜ İLERİ AYARLIYORUZ

            $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
            // echo "Gün aralığı seçeneği ile +{$ozelgun} gün olarak ayarlandı<br>";

##############################################################################################################################################################

        }else if($gun != -1 && $gun > 0){ // NORMAL 1-31 GÜN DEĞERİ SEÇİLDİR
// echo '}else if($gun != -1 && $gun > 0){<br>';
##############################################################################################################################################################

            // IF Seçilen gün bugün İSE
            // AND Saat aralığı seçili DEĞİL İSE
            // AND seçilen saat şimdiki saatten BÜYÜK İSE
            // OR seçilen saat şimdiki saat ile EŞİT İSE
            // AND Dakika aralığı seçili DEĞİL İSE
            // AND seçilen dakika şimdiki dakikadan BÜYÜK veya EŞİT İSE

            // (SEÇİLEN ZAMANA DAHA VAKİT OLDUĞUNDAN BUGÜNÜ AYARLIYORUZ)
            if($bugun->format('d') == $gun && strpos($saat, '/') == false && $saat != -1 && $saat > $bugun->format('H') || ($saat != -1 && $saat == $bugun->format('H') && strpos($dakika, '/') == false && $dakika != -1 && $dakika >= $bugun->format('i'))){

                $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $gun);

                $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
                // echo "1 Gün bugün, Seçilen saat eşit veya büyüktür, Seçilen dakika eşit veya büyüktür. Seçilen gün, saat ve dakika ayarlandı.<br>";

            // ELSE IF Seçilen gün bugün İSE
            // AND Saat aralığı seçili DEĞİL İSE
            // AND seçilen saat -1 İSE
            // AND seçilen dakika -1 İSE

            // (GÜN BUGÜN VE SEÇİLEN SAAT -1 SEÇİLEN DAKİKA -1 OLDUĞUNDAN BUGÜNÜ VE +1 OLARAK AYARLIYORUZ)
            }else if($bugun->format('d') == $gun && strpos($saat, '/') == false && $saat == -1 && strpos($dakika, '/') == false && $dakika == -1){

                $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $gun);

                $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
                // echo "2 Gün bugün, Seçilen saat -1, Seçilen dakika -1. Seçilen gün, geçerli saat ve +1 dakika ayarlandı.<br>";

            // ELSE IF Seçilen gün bugün İSE
            // AND Saat aralığı seçili DEĞİL İSE
            // AND seçilen saat şimdiki saatten KÜÇÜK İSE
            // OR seçilen saat şimdiki saat ile EŞİT İSE
            // AND Dakika aralığı seçili DEĞİL İSE
            // AND seçilen dakika şimdiki dakikadan KÜÇÜK veya EŞİT İSE

            // (SEÇİLEN GÜN BUGÜN İLE EŞİT ANCAK SAAT GEÇTİ VEYA SAAT EŞİT VE DAKİKA EŞİT VEYA GEÇTİĞİ İÇİN BİR SONRAKİ AYIN GÜNÜNE AYARLIYORUZ)
            }else if($bugun->format('d') == $gun && strpos($saat, '/') == false && $saat != -1 && $saat < $bugun->format('H') || ($saat != -1 && $saat == $bugun->format('H') && strpos($dakika, '/') == false && $dakika != -1 && $dakika <= $bugun->format('i'))){

                $tarih->setDate($bugun->format('Y'), $bugun->format('m')+1, $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
                // echo "3 Gün bugün, ancak saat veya dakika geçtiği için sonraki ayın gününe ayarlandı<br>";

            // ELSE IF Seçilen gün bugünden KÜÇÜK İSE
            // AND Saat aralığı seçili DEĞİL İSE
            // AND Dakika aralığı seçili DEĞİL İSE
            // (SEÇİLEN GÜN GEÇTİĞİ İÇİN BİR SONRAKİ AYIN BUGÜNÜ AYARLIYORUZ. GÜN GEÇTİĞİ İÇİN SAAT VE DAKİKA DİKKATE ALINMIYORUZ)
            }else if($bugun->format('d') > $gun && strpos($saat, '/') == false && strpos($dakika, '/') == false){

                $tarih->setDate($bugun->format('Y'), $bugun->format('m')+1, $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
                // echo "4 Gün bugün değil, gün geçtiği için sonraki ayın gününe ayarlandı<br>";

            // ELSE IF Seçilen gün bugünden BÜYÜK İSE
            // AND Saat aralığı seçili DEĞİL İSE
            // AND Dakika aralığı seçili DEĞİL İSE
            // (SEÇİLEN GÜN BUGÜNDEN BÜYÜK OLDUĞUNDAN BU AYIN SEÇİLEN GELECEK GÜNE AYARLIYORUZ. BU DURUMDA SAAT VE DAKİKA DİKKATE ALMIYORUZ)
            }else if($bugun->format('d') < $gun && strpos($saat, '/') == false && strpos($dakika, '/') == false){

                $ayin_son_gunu = (int)$bugun->format('t'); // Ayın son gününü al

                // Seçilen güne daha zaman olduğundan bu ayın seçilen gününe ayarlıyoruz
                $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
                // echo "5 Seçilen güne daha zaman olduğundan bu ayın seçilen gününe ayarlıyoruz<br>";

                // Örnek seçilen gün 31 ancak bu geçerli ay 31 den küçük sayıda çıkıyorsa otomatikman sonraki ayın gününe ayarlayacaktır
                // Bu geçerli ayın son gününe ayarlamak için ayarlanan ay ile bugünün ayı eşit değil ise -1 ay ile geri alıp ayın son gününe ayarlıyoruz
                if($bugun->format('m') != $tarih->format('m')){
                    $tarih->setDate($tarih->format('Y'), $tarih->format('m')-1, $ayin_son_gunu);
                    $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
                    // echo "6 Gün bugün değil, seçilen gelecek gün bu ayın son gününden fazla olduğundan bu ayın {$ayin_son_gunu}. gününe ayrlıyoruz<br>";
                }
            
            // ELSE IF Seçilen gün bugünden KÜÇÜK İSE
            // AND Saat aralığı SEÇİLİ İSE
            // AND Dakika aralığı seçili DEĞİL İSE
            // (SEÇİLEN GÜN BUGÜNDEN KÜÇÜK OLDUĞUNDAN BİR SONRAKİ AYIN GÜNÜNE AYARLIYORUZ. VE SAAT ARALIĞI SEÇİLİDİR)
            }else if($bugun->format('d') > $gun && strpos($saat, '/') !== false && strpos($dakika, '/') == false){

                $tarih->setDate($bugun->format('Y'), $bugun->format('m')+1, $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
                // echo "7 Gün bugün değil, gün geçtiği için sonraki ayın gününe ayarlandı<br>";

            // Seçilen gün bugünden BÜYÜK İSE
            // AND Saat aralığı SEÇİLİ İSE
            // AND Dakika aralığı seçili DEĞİL İSE
            // (SEÇİLEN GÜN BUGÜNDEN BÜYÜK OLDUĞUNDAN SEÇİLEN GELENE GÜNE AYARLIYORUZ. VE SAAT ARALIĞI SEÇİLİDİR)
            }else if($bugun->format('d') < $gun && strpos($saat, '/') !== false && strpos($dakika, '/') == false){

                $ayin_son_gunu = (int)$bugun->format('t'); // Ayın son gününü al

                // Seçilen güne daha zaman olduğunda bu ayın seçilen gününe ayarlıyoruz
                $tarih->setDate($tarih->format('Y'), $tarih->format('m'), $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
                // echo "8 Seçilen güne daha zaman olduğunda bu ayın seçilen gününe ayarlıyoruz<br>";

                // Örnek seçilen gün 31 ancak bu geçerli ay 31 den küçük sayıda çıkıyorsa otomatikman sonraki ayın gününe ayarlayacaktır
                // Bu geçerli ayın son gününe ayarlamak için ayarlanan ay ile bugünün ayı eşit değil ise -1 ay ile geri alıp ayın son gününe ayarlıyoruz
                if($bugun->format('m') != $tarih->format('m')){
                    $tarih->setDate($bugun->format('Y'), $bugun->format('m')-1, $ayin_son_gunu);
                    $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
                    // echo "9 Gün bugün değil, seçilen gelecek gün bu ayın son gününden fazla olduğundan bu ayın {$ayin_son_gunu}. gününe ayrlıyoruz<br>";
                }

            }else if($bugun->format('d') == $gun && strpos($saat, '/') !== false){

                $tarih->setDate($tarih->format('Y'), $tarih->format('m'), $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil

                // echo "10 <br>";

            }else if($bugun->format('d') < $gun && strpos($saat, '/') !== false){

                $tarih->setDate($tarih->format('Y'), $tarih->format('m'), $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil

                // echo "11 <br>";

            }else if($bugun->format('d') > $gun && strpos($saat, '/') !== false){

                $tarih->setDate($tarih->format('Y'), $tarih->format('m'), $gun);
                $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil

                // echo "12 <br>";

            }

##############################################################################################################################################################

        }else if($gun == -1){

// echo '}else if($gun == -1){<br>';

        // IF saat aralığı SEÇİLİ DEĞİL İSE
        // AND seçilen saat -1 DEĞİL İSE
        // AND seçilen saat bugünkü saatten BÜYÜK İSE
        // OR seçilen saat bugünkü saat ile EŞİT İSE
        // AND dakikada aralığı seçili DEĞİL İSE
        // AND dakika -1 seçili DEĞİL İSE
        // AND secilen dakika bugünkü dakika ile EŞİT veya BÜYÜK İSE
        // (GÜN BUGÜN, SEÇİLEN SAAT BÜYÜK İSE veya SAAT EŞİT İSE DAKİKA EŞİT VEYA BÜYÜK OLDUĞUNDAN GELECEK SAATE/DAKİKAYA AYARLIYORUZ)
        if(strpos($saat, '/') == false && $saat != -1 && $bugun->format('H') < $saat || $bugun->format('H') == $saat && strpos($dakika, '/') == false && $dakika != -1 && $bugun->format('i') <= $dakika){
##############################################################################################################################################################

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            // echo "13 Seçili gün -1, saat 0-23 seçili ve saat geçmedi, dakika  geçerli gün ayarlandı<br>";

##############################################################################################################################################################
        // ELSE IF saat aralığı SEÇİLİ DEĞİL İSE
        // AND seçilen saat -1 DEĞİL İSE
        // AND seçilen saat bugünkü saatten KÜÇÜK İSE
        // OR seçilen saat bugünkü saat ile EŞİT İSE
        // AND dakikada aralığı seçili DEĞİL İSE
        // AND dakika -1 seçili DEĞİL İSE
        // AND secilen dakika bugünkü dakika ile EŞİT veya KÜÇÜK İSE
        // (GÜN BUGÜN, SEÇİLEN SAAT KÜÇÜK OLDUĞUNDAN, veya SAAT EŞİT İSE DAKİKA EŞİT VEYA KÜÇÜK OLDUĞUNDAN SONRAKİ GÜNE AYARLIYORUZ)
        }else if(strpos($saat, '/') == false && $saat != -1 && $bugun->format('H') > $saat || $bugun->format('H') == $saat && strpos($dakika, '/') == false && $dakika != -1 && $bugun->format('i') >= $dakika){
##############################################################################################################################################################

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d')+1);
            $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
            // echo "14 Seçili gün -1, seçilen saat şimdiki saate göre geçtiği için +1 gün ayarlandı<br>";

##############################################################################################################################################################
        // ELSE IF saat aralığı SEÇİLİ DEĞİL İSE
        // AND seçilen saat -1 DEĞİL İSE
        // AND seçilen saat bugünkü saatten BÜYÜK İSE
        // AND dakikada aralığı seçili DEĞİL İSE
        // AND seçilen dakika -1 İSE
        // (GÜN BUGÜN, SEÇİLEN SAAT GEÇERLİ SAATTEN BÜYÜK OLDUĞUNDAN GELECEK SAATE AYARLIYORUZ)
        }else if(strpos($saat, '/') == false && $saat != -1 && $bugun->format('H') < $saat && strpos($dakika, '/') == false && $dakika == -1){
##############################################################################################################################################################

            $tarih->setDate($tarih->format('Y'), $tarih->format('m'), $tarih->format('d'));
            $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            // echo "15 Seçili gün -1, saat 0-23 seçili ve saat geçmedi, dakika  geçerli gün ayarlandı<br>";

##############################################################################################################################################################
        // ELSE IF saat aralığı SEÇİLİ DEĞİL İSE
        // AND seçilen saat -1 DEĞİL İSE
        // AND seçilen saat bugünkü saatten KÜÇÜK veyaz EŞİT İSE
        // AND dakikada aralığı seçili DEĞİL İSE
        // AND seçilen dakika -1 İSE
        // (GÜN BUGÜN, SEÇİLEN SAAT GEÇERLİ SAATTEN KÜÇÜK OLDUĞUNDAN BİR SONRAKİ AYARLIYORUZ)
        }else if(strpos($saat, '/') == false && $saat != -1 && $bugun->format('H') >= $saat && strpos($dakika, '/') == false && $dakika == -1){
##############################################################################################################################################################

            $tarih->setDate($bugun->format('Y'), $bugun->format('m'), $bugun->format('d')+1);
            $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 2); // 1 gün bugün, 2 gün bugün değil
            // echo "16 Seçili gün -1, seçilen saat şimdiki saate göre geçtiği için +1 gün ayarlandı<br>";

##############################################################################################################################################################
        // ELSE IF saat aralığı SEÇİLİ DEĞİL İSE
        // AND seçilen saat -1 SEÇİLİ İSE
        // AND dakikada aralığı seçili DEĞİL İSE
        // AND seçilen dakika -1 İSE
        // (GÜN BUGÜN, SAAT VE DAKİKA -1 * YILDIZ SEÇİLİ OLDUĞUNDAN HER SAAT HER DAKİKA AYARLIYORUZ)
        }else if(strpos($saat, '/') == false && $saat == -1 && strpos($dakika, '/') == false && $dakika == -1){
##############################################################################################################################################################

            $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            // echo "17 Saat veya dakika aralığı seçili<br>";

##############################################################################################################################################################
        // ELSE IF saat aralığı SEÇİLİ DEĞİL İSE
        // AND seçilen saat -1 SEÇİLİ İSE
        // AND dakikada aralığı seçili DEĞİL İSE
        // AND seçilen dakika -1 SEÇİLİ DEĞİL İSE
        // (GÜN BUGÜN, SAAT HER SAATTE VE SEÇİLEN DAKİKA OLARAK AYARLIYORUZ)
        }else if(strpos($saat, '/') == false && $saat == -1 && strpos($dakika, '/') == false && $dakika != -1){
##############################################################################################################################################################

            $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            // echo "18 Saat veya dakika aralığı seçili<br>";

##############################################################################################################################################################
        // ELSE IF saat aralığı SEÇİLİ İSE
        // OR dakika aralığı SEÇİLİ İSE
        }else if(strpos($saat, '/') !== false || strpos($dakika, '/') !== false){
##############################################################################################################################################################

            $tarih = saatDakikaKontrolu($bugun, $tarih, $saat, $dakika, 1); // 1 gün bugün, 2 gün bugün değil
            // echo "19 Saat veya dakika aralığı seçili<br>";
        }

##############################################################################################################################################################

        } // }else if($gun == -1){
        return $tarih;
    } // function gunKontrolu($bugun, $tarih, $gun, $saat, $dakika){
}
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
##############################################################################################################################################################
if(isset($_POST['ajaxtan'])){

require_once('includes/connect.php');
    // // echo '<pre>' . print_r($_POST, true) . '</pre>';

	// Gönderilen gün değeri
	$gun = isset($_POST['gun']) ? $_POST['gun'] : '-1';
	// Gönderilen saat değeri
	$saat = isset($_POST['saat']) ? $_POST['saat'] : '-1';
	// Gönderilen dakika değeri
	$dakika = isset($_POST['dakika']) ? $_POST['dakika'] : '-1';
	// Gönderilen haftanın değeri
	$haftanin_gunu = isset($_POST['haftanin_gunu']) ? $_POST['haftanin_gunu'] : [0=>-1];

    // Şu anki tarihi ve saat bilgisini al
    $bugun = new DateTime('now', new DateTimeZone($genel_ayarlar['zaman_dilimi']));

    // Unix zaman damgasını depolamak için varsayılan tarih nesnesi oluştur
    $tarih = new DateTime('now', new DateTimeZone($genel_ayarlar['zaman_dilimi']));

    // HAFTANIN GÜN(LERİ) SEÇİLİ İSE HAFTANIN GÜN(LERİ) İŞLEMLERİNE BAŞLA
    if (!in_array("-1", $haftanin_gunu)){

        $tarih = haftaKontrolu($bugun, $tarih, $haftanin_gunu, $saat, $dakika);

    }else{ // HAFTANIN GÜNÜ -1 * YILDIZ SEÇİLİ İSE GÜN İŞLEMLERİNE BAŞLA

        $tarih = gunKontrolu($bugun, $tarih, $gun, $saat, $dakika);

    }

        $unixtime = $tarih->setTimezone(new DateTimeZone('UTC'))->format('U');

require_once("includes/turkcegunler.php");

        echo date_tr('j F Y l, H:i', $unixtime);
}

?>