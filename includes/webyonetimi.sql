-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3306
-- Üretim Zamanı: 21 Kas 2023, 21:16:15
-- Sunucu sürümü: 8.1.0
-- PHP Sürümü: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `webyonetimi`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dovizkuru`
--

DROP TABLE IF EXISTS `dovizkuru`;
CREATE TABLE IF NOT EXISTS `dovizkuru` (
  `id` int NOT NULL AUTO_INCREMENT,
  `doviz_cinsi` int NOT NULL,
  `birime` int NOT NULL,
  `tcmb_kur` decimal(6,4) NOT NULL,
  `elle_kur` decimal(6,4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tcmb_kur` (`tcmb_kur`),
  UNIQUE KEY `elle_kur` (`elle_kur`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `dovizkuru`
--

INSERT INTO `dovizkuru` VALUES(1, 1, 3, 28.2188, 10.9607);
INSERT INTO `dovizkuru` VALUES(2, 2, 3, 29.7877, 12.4331);
INSERT INTO `dovizkuru` VALUES(3, 2, 1, 1.0556, 1.1333);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `genel_ayarlar`
--

DROP TABLE IF EXISTS `genel_ayarlar`;
CREATE TABLE IF NOT EXISTS `genel_ayarlar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zaman_dilimleri` json DEFAULT NULL,
  `secili_zaman_dilimi` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Europe/Istanbul',
  `haric_dizinler` json DEFAULT NULL,
  `karakter_setleri` json DEFAULT NULL,
  `secili_karakter_seti` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'utf8mb4',
  `sunucu` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `port` int DEFAULT NULL,
  `username` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `patch` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `genel_ayarlar`
--

INSERT INTO `genel_ayarlar` VALUES(1, '{\"UTC\": \"UTC +0:00\", \"Europe/Kiev\": \"Kiev +2:00\", \"Europe/Oslo\": \"Oslo +1:00\", \"Europe/Riga\": \"Riga +2:00\", \"Europe/Rome\": \"Rome +1:00\", \"Europe/Malta\": \"Malta +1:00\", \"Europe/Minsk\": \"Minsk +2:00\", \"Europe/Paris\": \"Paris +1:00\", \"Europe/Sofia\": \"Sofia +2:00\", \"Europe/Vaduz\": \"Vaduz +1:00\", \"Europe/Athens\": \"Athens +2:00\", \"Europe/Berlin\": \"Berlin +1:00\", \"Europe/Dublin\": \"Dublin +0:00\", \"Europe/Jersey\": \"Jersey +0:00\", \"Europe/Lisbon\": \"Lisbon +0:00\", \"Europe/London\": \"London +0:00\", \"Europe/Madrid\": \"Madrid +1:00\", \"Europe/Monaco\": \"Monaco +1:00\", \"Europe/Moscow\": \"Moscow +3:00\", \"Europe/Prague\": \"Prague +1:00\", \"Europe/Samara\": \"Samara +4:00\", \"Europe/Skopje\": \"Skopje +1:00\", \"Europe/Tirane\": \"Tirane +1:00\", \"Europe/Vienna\": \"Vienna +1:00\", \"Europe/Warsaw\": \"Warsaw +1:00\", \"Europe/Zagreb\": \"Zagreb +1:00\", \"Europe/Zurich\": \"Zurich +1:00\", \"Europe/Andorra\": \"Andorra +1:00\", \"Europe/Belfast\": \"Belfast +0:00\", \"Europe/Nicosia\": \"Nicosia +2:00\", \"Europe/Tallinn\": \"Tallinn +2:00\", \"Europe/Vatican\": \"Vatican +1:00\", \"Europe/Vilnius\": \"Vilnius +2:00\", \"Europe/Belgrade\": \"Belgrade +1:00\", \"Europe/Brussels\": \"Brussels +1:00\", \"Europe/Budapest\": \"Budapest +1:00\", \"Europe/Chisinau\": \"Chisinau +2:00\", \"Europe/Guernsey\": \"Guernsey +0:00\", \"Europe/Helsinki\": \"Helsinki +2:00\", \"Europe/Istanbul\": \"Istanbul +3:00\", \"Europe/Sarajevo\": \"Sarajevo +1:00\", \"Europe/Tiraspol\": \"Tiraspol +2:00\", \"Europe/Uzhgorod\": \"Uzhgorod +2:00\", \"Europe/Amsterdam\": \"Amsterdam +1:00\", \"Europe/Bucharest\": \"Bucharest +2:00\", \"Europe/Gibraltar\": \"Gibraltar +1:00\", \"Europe/Ljubljana\": \"Ljubljana +1:00\", \"Europe/Mariehamn\": \"Mariehamn +2:00\", \"Europe/Podgorica\": \"Podgorica +1:00\", \"Europe/Stockholm\": \"Stockholm +1:00\", \"Europe/Volgograd\": \"Volgograd +3:00\", \"Europe/Bratislava\": \"Bratislava +1:00\", \"Europe/Copenhagen\": \"Copenhagen +1:00\", \"Europe/Luxembourg\": \"Luxembourg +1:00\", \"Europe/San_Marino\": \"San_Marino +1:00\", \"Europe/Simferopol\": \"Simferopol +2:00\", \"Europe/Zaporozhye\": \"Zaporozhye +2:00\", \"Europe/Isle_of_Man\": \"Isle_of_Man +0:00\", \"Europe/Kaliningrad\": \"Kaliningrad +2:00\"}', 'Europe/Istanbul', '[\"webyonetimi\"]', '{\"gbk\": {\"gbk_chinese_ci\": \"GBK Simplified Chinese\"}, \"hp8\": {\"hp8_english_ci\": \"HP West European\"}, \"big5\": {\"big5_chinese_ci\": \"Big5 Traditional Chinese\"}, \"dec8\": {\"dec8_swedish_ci\": \"DEC West European\"}, \"sjis\": {\"sjis_japanese_ci\": \"Shift-JIS Japanese\"}, \"swe7\": {\"swe7_swedish_ci\": \"7bit Swedish\"}, \"ucs2\": {\"ucs2_general_ci\": \"UCS-2 Unicode\"}, \"ujis\": {\"ujis_japanese_ci\": \"EUC-JP Japanese\"}, \"ascii\": {\"ascii_general_ci\": \"US ASCII\"}, \"cp850\": {\"cp850_general_ci\": \"DOS West European\"}, \"cp852\": {\"cp852_general_ci\": \"DOS Central European\"}, \"cp866\": {\"cp866_general_ci\": \"DOS Russian\"}, \"cp932\": {\"cp932_japanese_ci\": \"SJIS for Windows Japanese\"}, \"euckr\": {\"euckr_korean_ci\": \"EUC-KR Korean\"}, \"greek\": {\"greek_general_ci\": \"ISO 8859-7 Greek\"}, \"koi8r\": {\"koi8r_general_ci\": \"KOI8-R Relcom Russian\"}, \"koi8u\": {\"koi8u_general_ci\": \"KOI8-U Ukrainian\"}, \"macce\": {\"macce_general_ci\": \"Mac Central European\"}, \"utf16\": {\"utf16_general_ci\": \"UTF-16 Unicode\"}, \"utf32\": {\"utf32_general_ci\": \"UTF-32 Unicode\"}, \"binary\": {\"binary\": \"Binary pseudo charset\"}, \"cp1250\": {\"cp1250_general_ci\": \"Windows Central European\"}, \"cp1251\": {\"cp1251_general_ci\": \"Windows Cyrillic\"}, \"cp1256\": {\"cp1256_general_ci\": \"Windows Arabic\"}, \"cp1257\": {\"cp1257_general_ci\": \"Windows Baltic\"}, \"gb2312\": {\"gb2312_chinese_ci\": \"GB2312 Simplified Chinese\"}, \"hebrew\": {\"hebrew_general_ci\": \"ISO 8859-8 Hebrew\"}, \"latin1\": {\"latin1_swedish_ci\": \"cp1252 West European\"}, \"latin2\": {\"latin2_general_ci\": \"ISO 8859-2 Central European\"}, \"latin5\": {\"latin5_turkish_ci\": \"ISO 8859-9 Turkish\"}, \"latin7\": {\"latin7_general_ci\": \"ISO 8859-13 Baltic\"}, \"tis620\": {\"tis620_thai_ci\": \"TIS620 Thai\"}, \"eucjpms\": {\"eucjpms_japanese_ci\": \"UJIS for Windows Japanese\"}, \"gb18030\": {\"gb18030_chinese_ci\": \"China National Standard GB18030\"}, \"geostd8\": {\"geostd8_general_ci\": \"GEOSTD8 Georgian\"}, \"keybcs2\": {\"keybcs2_general_ci\": \"DOS Kamenicky Czech-Slovak\"}, \"utf16le\": {\"utf16le_general_ci\": \"UTF-16LE Unicode\"}, \"utf8mb4\": {\"utf8mb3_general_ci\": \"UTF-8 Unicode\"}, \"utf8mb4\": {\"utf8mb4_0900_ai_ci\": \"UTF-8 Unicode\"}, \"armscii8\": {\"armscii8_general_ci\": \"ARMSCII-8 Armenian\"}, \"macroman\": {\"macroman_general_ci\": \"Mac West European\"}}', 'utf8mb4', 'ftp.domainadiniz.com', 21, 'ftpdeneme@domainadiniz.com', 'xxxxxxxxxx', '/');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `uyeler`
--

DROP TABLE IF EXISTS `uyeler`;
CREATE TABLE IF NOT EXISTS `uyeler` (
  `user_id` bigint NOT NULL AUTO_INCREMENT,
  `user_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_password_hash` char(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_group` int NOT NULL,
  `son_login` int NOT NULL DEFAULT '0',
  `last_login` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '1',
  `login1` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login2` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login3` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login4` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login5` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login6` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login7` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login8` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login9` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login10` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `email_user` (`user_email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `uyeler`
--

INSERT INTO `uyeler` VALUES(1, 'Adem GENÇ', '$2y$10$E4K/b8wcmEoSSZoFgUgiKeXmk6Sw3MITiOlC7qCzcMJhPvBerQ226', 'admin@gmail.com', 1, 0, '3', '1700595910', '1700595962', '1700074637', '1700114269', '1700222286', '1700296908', '1700370388', '1700474059', '1700545440', '1700589538');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `veritabanlari`
--

DROP TABLE IF EXISTS `veritabanlari`;
CREATE TABLE IF NOT EXISTS `veritabanlari` (
  `id` int NOT NULL AUTO_INCREMENT,
  `website_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `database_host` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `db_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `database_user` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `database_password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `selected` int NOT NULL DEFAULT '0',
  `islem` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `islemi_yapan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `database_name` (`db_name`),
  UNIQUE KEY `website_name` (`website_name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `zamanlanmisgorev`
--

DROP TABLE IF EXISTS `zamanlanmisgorev`;
CREATE TABLE IF NOT EXISTS `zamanlanmisgorev` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `gorev_adi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dosya_adi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sonraki_calisma` int NOT NULL,
  `haftanin_gunu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '-1',
  `gun` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `saat` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `dakika` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `aktif` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gunluk_kayit` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `yedekleme_gorevi` int NOT NULL,
  `ftp_yedekle` int NOT NULL DEFAULT '0',
  `google_yedekle` int NOT NULL DEFAULT '0',
  `uzak_sunucu_ici_dizin_adi` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `google_sunucu_korunacak_yedek` int NOT NULL DEFAULT '-1',
  `ftp_sunucu_korunacak_yedek` int NOT NULL DEFAULT '-1',
  `secilen_yedekleme_oneki` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `yerel_korunacak_yedek` int NOT NULL DEFAULT '-1',
  `gz` int NOT NULL DEFAULT '-1',
  `dbbakim` int NOT NULL DEFAULT '-1',
  `dblock` int NOT NULL DEFAULT '-1',
  `combine` int NOT NULL DEFAULT '-1',
  `elle` int NOT NULL DEFAULT '-1',
  `tablolar` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `secilen_yedekleme` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ozel_onek` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sonraki_calisma` (`sonraki_calisma`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `zamanlanmisgorev` VALUES(1, 'deneme', 'test_gorev.php', 1714813140, '-1', '-1', '-1', '-1/2', 'Aktif', 'Aktif', 3, 0, 0, NULL, -1, -1, NULL, -1, -1, -1, -1, -1, -1, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `zamanlanmisgorev_gunluk`
--

DROP TABLE IF EXISTS `zamanlanmisgorev_gunluk`;
CREATE TABLE IF NOT EXISTS `zamanlanmisgorev_gunluk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `calistirma_ciktisi` varchar(100) DEFAULT NULL,
  `gorev_adi` varchar(50) NOT NULL,
  `calistirilan_dosya` varchar(50) DEFAULT NULL,
  `calisma_zamani` int DEFAULT NULL,
  `calisma_suresi` varchar(20) DEFAULT NULL,
  `veritabani_yedekle` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `zamanlanmisgorev_gunluk` VALUES(1, 'USD den TL Güncellendi<br>EURO dan TL Güncellendi<br>EURO dan USD Güncellendi<br>', 'deneme', 'test_gorev.php', 1714813687, '00:00:00:00006', 0);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
