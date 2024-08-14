
-- WebSiteler Yönetimi Scripti
-- WebSiteler Yönetimi Script Versiyonu: 2.0.1

-- Anamakine: webyonetimi
-- Yedekleme Zamanı: 2024-07-23 12:46:22
-- MySQL Sunucu Sürümü: 8.3.0
-- PHP Sürümü: 8.1.28
-- Karakter Seti: utf8mb4

-- Veritabanı: `github_webyonetimi`


-- Tablolar:
-- Tablo Adı: dovizkuru: 3 kayıt
-- Tablo Adı: genel_ayarlar: 1 kayıt
-- Tablo Adı: user_logins: 10 kayıt
-- Tablo Adı: uyeler: 1 kayıt
-- Tablo Adı: veritabanlari: 0 kayıt
-- Tablo Adı: zamanlanmisgorev: 1 kayıt
-- Tablo Adı: zamanlanmisgorev_gunluk: 1 kayıt


SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;
SET time_zone = '+03:00';

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;




-- ------------------------------------------------------
-- Tablo için tablo yapısı `dovizkuru`
-- ------------------------------------------------------
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------
-- Tablonun veri dökümü `dovizkuru`
-- ------------------------------------------------------
INSERT INTO `dovizkuru` VALUES(1, 1, 3, '28.2188', '10.9607');
INSERT INTO `dovizkuru` VALUES(2, 2, 3, '29.7877', '12.4331');
INSERT INTO `dovizkuru` VALUES(3, 2, 1, '1.0556', '1.1333');


-- ------------------------------------------------------
-- Tablo için tablo yapısı `genel_ayarlar`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `genel_ayarlar`;
CREATE TABLE IF NOT EXISTS `genel_ayarlar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zaman_dilimi` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Europe/Istanbul',
  `haric_dizinler` json DEFAULT NULL,
  `karakter_seti` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'utf8mb4',
  `sunucu` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `port` int DEFAULT NULL,
  `username` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '/',
  `zip_tercihi` int NOT NULL DEFAULT '1',
  `gorevi_calistir` int NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------
-- Tablonun veri dökümü `genel_ayarlar`
-- ------------------------------------------------------
INSERT INTO `genel_ayarlar` VALUES(1, 'Europe/Istanbul', NULL, 'utf8mb4', NULL, 21, NULL, NULL, '/', 1, 1);


-- ------------------------------------------------------
-- Tablo için tablo yapısı `user_logins`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `user_logins`;
CREATE TABLE IF NOT EXISTS `user_logins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `login_time` bigint NOT NULL,
  `log_in_from` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------
-- Tablonun veri dökümü `user_logins`
-- ------------------------------------------------------
INSERT INTO `user_logins` VALUES(38, 1, 1721643956, 'Giriş ile');
INSERT INTO `user_logins` VALUES(39, 1, 1721727382, 'Giriş ile');
INSERT INTO `user_logins` VALUES(30, 1, 1721581327, 'Beni Hatırla ile');
INSERT INTO `user_logins` VALUES(31, 1, 1721581412, 'Beni Hatırla ile');
INSERT INTO `user_logins` VALUES(32, 1, 1721581682, 'Giriş ile');
INSERT INTO `user_logins` VALUES(33, 1, 1721581730, 'Giriş ile');
INSERT INTO `user_logins` VALUES(34, 1, 1721639724, 'Giriş ile');
INSERT INTO `user_logins` VALUES(35, 1, 1721643485, 'Giriş ile');
INSERT INTO `user_logins` VALUES(36, 1, 1721643511, 'Giriş ile');
INSERT INTO `user_logins` VALUES(37, 1, 1721643553, 'Giriş ile');


-- ------------------------------------------------------
-- Tablo için tablo yapısı `uyeler`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `uyeler`;
CREATE TABLE IF NOT EXISTS `uyeler` (
  `user_id` bigint NOT NULL AUTO_INCREMENT,
  `user_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_password_hash` char(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_group` int NOT NULL,
  `remember_me_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `email_user` (`user_email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------
-- Tablonun veri dökümü `uyeler`
-- ------------------------------------------------------
INSERT INTO `uyeler` VALUES(1, 'Adem GENÇ', '$2y$10$uY/PW6S17AXp0s/xvu73qusf52qkOUrdueLkXvqjHdXXYhhaZuhgi', 'admin@gmail.com', 1, 'e24d3c696497212ba5efba4e8ac3d8abe7e79ae48b9bc28f9d8c73ea81637f6a', '0000-00-00 00:00:00');


-- ------------------------------------------------------
-- Tablo için tablo yapısı `veritabanlari`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `veritabanlari`;
CREATE TABLE IF NOT EXISTS `veritabanlari` (
  `id` int NOT NULL AUTO_INCREMENT,
  `website_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `database_host` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `db_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `database_user` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `database_password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `port` int NOT NULL DEFAULT '3306',
  `charset` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `selected` int NOT NULL DEFAULT '0',
  `islem` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `islemi_yapan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `database_name` (`db_name`),
  UNIQUE KEY `website_name` (`website_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------
-- Tablo için tablo yapısı `zamanlanmisgorev`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `zamanlanmisgorev`;
CREATE TABLE IF NOT EXISTS `zamanlanmisgorev` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `gorev_adi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dosya_adi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sonraki_calisma` int NOT NULL,
  `haftanin_gunu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '-1',
  `gun` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `saat` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dakika` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
  `tablo_guncelmi_denetle` int NOT NULL DEFAULT '0',
  `secilen_yedekleme` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ozel_onek` int NOT NULL DEFAULT '0',
  `isleniyor` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sonraki_calisma` (`sonraki_calisma`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------
-- Tablonun veri dökümü `zamanlanmisgorev`
-- ------------------------------------------------------
INSERT INTO `zamanlanmisgorev` VALUES(1, 'deneme', 'test_gorev.php', 1718799060, '-1', '-1', '-1', '-1/2', 'Pasif', 'Aktif', 3, 0, 0, NULL, -1, -1, NULL, -1, -1, -1, -1, -1, -1, NULL, 0, NULL, 0, 0);


-- ------------------------------------------------------
-- Tablo için tablo yapısı `zamanlanmisgorev_gunluk`
-- ------------------------------------------------------
DROP TABLE IF EXISTS `zamanlanmisgorev_gunluk`;
CREATE TABLE IF NOT EXISTS `zamanlanmisgorev_gunluk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `calistirma_ciktisi` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gorev_adi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `calistirilan_dosya` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `calisma_zamani` int DEFAULT NULL,
  `calisma_suresi` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `veritabani_yedekle` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------
-- Tablonun veri dökümü `zamanlanmisgorev_gunluk`
-- ------------------------------------------------------
INSERT INTO `zamanlanmisgorev_gunluk` VALUES(1, 'USD den TL Güncellendi<br>EURO dan TL Güncellendi<br>EURO dan USD Güncellendi<br>', 'deneme', 'test_gorev.php', 1714813687, '00:00:00:00006', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
