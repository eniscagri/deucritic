-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 20 Eki 2025, 01:10:31
-- Sunucu sürümü: 10.11.14-MariaDB
-- PHP Sürümü: 8.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `deucditiccom_deucritic`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `entries`
--

CREATE TABLE `entries` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `entries`
--

INSERT INTO `entries` (`id`, `topic_id`, `user_id`, `content`, `created_at`) VALUES
(20, 103, 6, 'Bu da ilk Onep Yorumu Olsun', '2025-07-04 00:03:07'),
(21, 116, 6, 'başlığı görünce tıklamazsam içim rahat etmiyor zaten.\r\n\r\nhem biraz dert dökelim, hem de kim kiminle, kim nereye gidiyor çözelim.\r\n\r\nbu başlık altı yoklama gibi olmuş, +1 yazıp geçmeyin, bi cümle bırakın.', '2025-07-04 15:10:45'),
(22, 113, 2, 'belki bir itirafta, belki bir sitemde,\r\nbelki bir gece \"beni biri anlasın\" diye.', '2025-07-04 15:12:22'),
(23, 114, 2, 'sıraya 11.29’da gir, 11.31’de artık tabak bile kalmaz.', '2025-07-04 15:13:25'),
(27, 119, 12, 'an itibariyle çeşme, ödemiş, buca, sarnıç yanıyor.\r\nşehrin her noktasında her köşesinde biri bitmeden başka yangın başlıyor. vali sabotaj yok elektrik trafosu diyor. zaten bu ülkenin başına ne geldiyse ne hikmetse hep trafo suçlu amk.\r\n', '2025-07-04 16:03:18'),
(28, 119, 13, 'itfaiye nereye gideceğini şaşırmış vaziyette. an itibariyle izmir\'in 6 ilçesinde yangın var. çeşme, urla, ödemiş, buca ve gaziemir\'deki yangın çok ciddi.', '2025-07-04 16:03:39'),
(29, 120, 5, 'kamuda çalışan mühendislerin hakları için bir entry giriliyor. ilk önce kendi meslektaşı karşı çıkıyor eleştiriyor. hem de hiç bilmeden ve düşünmeden.\r\n\r\nkamuda çalışan mühendislerin büyük çoğunluğu merkezi atamayla atanıyor. bazı arkadaşlar anlamayacağı için açıklayayım. mülakatsız yani torpil olmadan işe giriyor. aynı fakültelerden mezun insanların önce meslektaşlarının yanında olması lazım.', '2025-07-04 16:12:02'),
(30, 123, 4, 'Zar bile atılmıyor ki bence... direkt göz ucu bakışla karar veriliyor: Bu tipi bırakırım.\r\n\r\n', '2025-07-04 16:23:44'),
(31, 123, 3, 'Zar atma kısmı doğru da, hocanın zarı 8 yüzlüymüş. Biz anlamadık.', '2025-07-04 16:24:09'),
(32, 123, 6, 'Bazen de zar değil, kahve falı: Bu çocuk iki yaprakla kalır\r\n\r\n', '2025-07-04 16:24:35'),
(33, 123, 9, 'Bu dönem boyunca ortalamamı tombala belirledi hocam\r\n33 geldi, kaldım. 34 gelse geçiyordum. Eyvallah kral\r\n\r\n', '2025-07-04 16:25:10'),
(34, 124, 17, 'has büt veriyor mu', '2025-07-04 16:30:01'),
(38, 141, 2, 'yani üniversite değil sanki gizli görev planı açıklıyorlar. takvimi neden bu kadar saklıyorsunuz, elin nasa’sı mars’a insan gönderecek tarihini paylaştı siz hâlâ “duyurular yakında” diyorsunuz\r\nayıp', '2025-10-05 22:01:36'),
(39, 141, 6, 'modül 1: belirsizlikle yaşamak\r\nmodül 2: motivasyon kaybı ve kabul süreci', '2025-10-05 22:01:58'),
(41, 113, 6, 'siteye girdik çünkü merak ettik, zaman öldürmek istedik ya da bakalım ne varmış dedik', '2025-10-05 22:22:22'),
(42, 113, 9, 'merak', '2025-10-05 22:21:29'),
(43, 113, 17, 'birilerine laf sokmak istedik', '2025-10-05 22:29:09'),
(46, 152, 2, 'Adatepe coffyde her şeye  bekletiyor aw', '2025-10-19 00:13:32'),
(47, 153, 37, 'Hızlı ve güvenlikli diye yutturmaya çalıştıkları tek şey bir de biz öğrencilerin sabrı. O kadar fazla açık varki bunu kodlayan (vibe) coderla tanışmak isterdim.', '2025-10-19 02:46:12');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `entry_likes`
--

CREATE TABLE `entry_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL,
  `liked_at` datetime DEFAULT current_timestamp(),
  `created_t` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `entry_likes`
--

INSERT INTO `entry_likes` (`id`, `user_id`, `entry_id`, `liked_at`, `created_t`) VALUES
(10, 6, 20, '2025-07-04 00:03:09', 2025),
(14, 13, 29, '2025-07-04 19:25:44', 2025),
(18, 2, 33, '2025-07-04 19:26:23', 2025),
(19, 3, 33, '2025-07-04 19:26:31', 2025),
(20, 4, 33, '2025-07-04 19:26:38', 2025),
(24, 2, 38, '2025-10-05 22:02:02', 2025),
(25, 2, 39, '2025-10-05 22:02:03', 2025),
(33, 37, 47, '2025-10-19 02:48:24', 2025),
(34, 3, 47, '2025-10-19 02:48:24', 2025),
(35, 9, 47, '2025-10-19 02:48:24', 2025),
(36, 14, 47, '2025-10-19 02:48:24', 2025);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `followed_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `logins`
--

CREATE TABLE `logins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
  `content` text NOT NULL,
  `summary` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci;

--
-- Tablo döküm verisi `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `summary`, `created_at`) VALUES
(4, 'DeuCritic Ekibi Yeni Güncelleme Yayınladı', 'DeuCritic, kullanıcı deneyimini bir üst seviyeye taşımak amacıyla kapsamlı bir performans güncellemesi yayınladı.\r\nYeni sürümle birlikte platform artık çok daha hızlı yükleniyor ve mobil cihazlarda daha akıcı bir gezinme deneyimi sunuyor. Özellikle düşük internet bağlantısına sahip kullanıcılar için sayfa açılış hızında gözle görülür bir iyileştirme sağlandı.\r\n\r\nAyrıca yeni güncellemeyle birlikte hata yönetim sistemi geliştirildi, profil sayfaları yeniden tasarlandı ve yorum yükleme performansı optimize edildi. DeuCritic, kullanıcı geri bildirimlerini dikkate alarak her gün platformu daha iyi hale getirmeyi sürdürüyor.', 'DeuCritic performans güncellemesiyle mobilde daha akıcı hale geldi.', '2025-10-17 21:12:41');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(7, 2, '3a8b3e3bfad7374f1e015a8584cb7ba0ae56e3cb9860066c49cff786179e431f', '2025-10-06 02:54:06', '2025-10-06 01:54:06');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `entry_id` int(11) DEFAULT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `request`
--

CREATE TABLE `request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `feature` text NOT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `site_title` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `settings`
--

INSERT INTO `settings` (`id`, `site_title`, `admin_email`) VALUES
(1, 'Deucritic', 'bilgi@eniscagri.com');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `topics`
--

CREATE TABLE `topics` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `anonymous` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `topics`
--

INSERT INTO `topics` (`id`, `title`, `created_by`, `created_at`, `description`, `anonymous`) VALUES
(103, 'Merhaba Dünya', 6, '2025-07-03 23:52:08', 'başlarda \"aman yine mi sözlük sitesi\" dedim ama sonra bir girdim...\r\nbaşlıklar kampüs kokuyor, onepler kantin dedikodusu gibi akıyor.\r\nburada tasarlarken içimden \"ulan bu ortam tutar ha\" dedirtti.\r\ntasarım sade, onep kutusu davetkâr, profil sayfaları neredeyse linkedin\'den cool.\r\nkimin kime yürüdüğü, hocaların en bilinen taktikleri, final haftası dramları... her şey burada olacak gibi.\r\nilk onepimdir. bu sözlüğü sevdik, umarım siz de seversiniz gencolar...\r\nbir gün bu site patlarsa \"ben oradaydım\" diyebilirim. 🫡', 0),
(113, 'bu siteye neden girdik?', 6, '2025-07-04 15:09:17', 'hepimiz bir gün DEU sözlüğe düşecektik, yaz kaderini', 1),
(114, 'deu yemekhanesinde hayatta kalma taktikleri', 6, '2025-07-04 15:09:17', 'aç kalma sanatı 101: 12:30 kuyruklarına saygı duruşu', 0),
(116, 'başlığı görünce gelenler', 6, '2025-07-04 15:09:17', 'meraktan ölen kediler cemiyeti toplansın', 0),
(118, 'sülaledeki en başarılı kişi ve mesleği', 4, '2025-07-04 16:00:15', 'akrabalar arasında en çok başarıya ulaşmış kişinin sahip olduğu meslektir.\n\nbenim sülale içinde başarıya yakın pek kimse yoktur. o kadar başarısızdırlar ki, ufacık şeylerden bile mutlu olmayı öğrenmişlerdir. demek ki, bu da bir yaşam tarzı haline geliyor zamanla. en başarılımız olan hala oğlu öğretmen olabildi. ilerisi yok maalesef.\n\nben ise ola ola neyse, maaş da abimden daha çok kazandırıyo işte.', 0),
(119, '3 temmuz 2025 izmir yangını', 15, '2025-07-04 16:02:45', '1922 izmir yangınından 100 kat daha büyük bir felaketi yaşıyoruz. 1922 yangını dört gün sürdü ve şehir merkezinde 260 hektarlık bir alanı yok etti. konutlar, çarşılar, liman çevresi ve kentsel doku etkilendi. ancak 2025 temmuz’unda başlayan ve dokuz gündür durmaksızın devam eden yangınlar, yalnızca şehirleri değil, doğayı, tarımı, canlı hayatı ve kırsal yaşamı yok ediyor.', 0),
(120, 'kamu mühendisi maaşı', 7, '2025-07-04 16:11:41', 'şunu gerçekten anlamıyorum. haklari için mücadele eden insanlara karşı neden bu kadar katisiniz.bu ulkede siyasetçiler dışında kimse hakettiği maaşı alamıyor.ne emekli,ne asgari ücretli ne öğretmenler ne de mühendisler.o ne iş yapıyor sürekli yatıyor muhabbeti çok sıktı artık.torpille giren yok mu var tabi.ama hakkıyla girenden fazla değil.herkesin iyi yaşamaya ve hakettigini almaya hakkı var.sizinde öyle,artık barişin bu insanlarla.destek olmak istemiyorsaniz kendiniz için savaşın', 0),
(121, 'Dersin 10. dakikasında ‘bu soruyu finalde sorarım’ diyen hoca', 5, '2025-07-04 16:21:50', 'O hoca tipi var ya… daha ısınmadan, daha adını bile öğrenmeden “final” kelimesi ağzından düşmez. Milleti germek için doğmuş, sanki finali kendi girecekmiş gibi heyecanlı.', 0),
(123, 'Bazı hocalar not verirken zar atıyor gibime geliyor. 1 gelirse AA, 6 gelirse kal.\r\n\r\n', 14, '2025-07-04 16:23:00', 'Sınavdan çıktın, cevapları karşılaştırdın, umutlusun. Ama hocanın sisteminde \"şansa bakılır\", ne yaptığın değil, kime denk geldiğin önemli.', 1),
(124, 'Bütlerde ff alan adam herif', 3, '2025-07-04 16:29:03', 'Finalde geçerim ya deyip riske girenlerin, notlar açıklandığında büt kapısında sıra olanların ortak başlığını açmış bulunmaktayım \"1 puanla kaldım” isyanıyla buraya düşer.Büt haftasıysa boku yemiş olur', 0),
(133, 'Dokuz Eylül Rektörü', 6, '2025-07-04 16:51:13', 'Prof. Dr. BAYRAM YILMAZ', 1),
(138, 'Onep\'in ne olduğunu bilmeyenlere?', 6, '2025-10-01 13:13:53', 'onep dediğin şey, başlık altına yazılan her türlü zırva, anı, geyik, gündelik hayattan kırıntı ya da “ben de bi şey yazdım işte” tarzı paylaşımdır.\r\n\r\nbaşlık → konuyu açar\r\nonep → o konuya dair kafana ilk geleni döktüğün cümlelerdir.\r\n\r\nörnek:\r\nbaşlık: ilk aşk\r\nonep: ortaokulda kantin sırasında göz göze gelmiştik. sandığım kadar epik değilmiş ama o zaman kalbim yerinden çıkacak gibi atıyordu.\r\n', 0),
(141, 'deu\'nun hala sınav takvimlerini açıklamamış olması hk.', 2, '2025-10-05 21:59:05', 'hazırlıksız yakalanma sanatı 101', 0),
(152, 'Law cafede kart çekilmemesi', 32, '2025-10-18 16:44:17', 'Belli bir tutarın altında kart çekmelere her şeyi nakit istemeleri', 0),
(153, 'Deysis Si(s)temi? ', 37, '2025-10-19 02:42:30', 'Deysis sistemi sözde mi var, sanki her şeyi çözecekmiş gibi tanıtılıyor. Kullanıcılar takılıyor, sistem hatalarla dolu. Arayüz chatgpt, şeffaflık yok, sistem tanırsa okey yoksa sıkıntı. Bir de üstelik “Otomatik verimlilik” demişler, gerçek hayatta sadece kafa karışıklığı ve zaman kaybı. Lan, potansiyel vardı, çöpe gitti.', 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `topic_followers`
--

CREATE TABLE `topic_followers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `followed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `bio` varchar(255) NOT NULL,
  `school` varchar(100) DEFAULT NULL,
  `grade_level` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_photo` varchar(555) NOT NULL,
  `twitter` varchar(555) NOT NULL,
  `last_login` date NOT NULL DEFAULT current_timestamp(),
  `last_active` datetime DEFAULT NULL,
  `password_reset_token` varchar(64) DEFAULT NULL,
  `password_reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `bio`, `school`, `grade_level`, `created_at`, `profile_photo`, `twitter`, `last_login`, `last_active`, `password_reset_token`, `password_reset_expiry`) VALUES
(2, 'faang', 'faang@facebook.com', '$2y$10$rWNGGseRbiNRFysg044jsOnxTIjFSiBDTMiPuaxP4drPU9kfIB3BW', 'pinksiz pinkli', 'Hemşirelik', '8', '2025-06-30 19:12:47', 'uploads/profile_photos/profile_2_1759701206.jpg', '', '2025-07-04', NULL, NULL, NULL),
(3, 'keremkaranlik', 'kerem@ogr.deu.edu.tr', '$2y$10$examplepasshash03', 'gece 3’te bile yazı yazarım', 'Sosyoloji', '1', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(4, 'busebaska', 'buse@ogr.deu.edu.tr', '$2y$10$examplepasshash04', 'kafamda binbir düşünce', 'Edebiyat', '2', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(5, 'furkansifirbir', 'furkan@ogr.deu.edu.tr', '$2y$10$examplepasshash05', 'biraz yazılım, biraz dert', 'Yazılım Müh.', '1', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(6, 'admin', 'admin@mail.com', '$2y$10$zBQ6ctX0HiEsGAL7schrWeS0zNeDmbeRJVGbF149ozaoCScBp4M2m', '', 'Fuckilte', '2', '2025-07-03 23:50:08', 'uploads/profile_photos/profile_6_1751586618.png', '', '2025-07-04', NULL, NULL, NULL),
(7, 'berkbitik', 'berk@ogr.deu.edu.tr', '$2y$10$examplepasshash07', 'final haftası = ben', 'İşletme', '3', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(9, 'enesdusunur', 'enes@ogr.deu.edu.tr', '$2y$10$examplepasshash09', 'şu an düşündüğüm şey bile deep', 'Hukuk', '3', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(10, 'nazlimavi', 'nazli@ogr.deu.edu.tr', '$2y$10$examplepasshash10', 'her şeye duygusal bakan kız', 'Elektrik Elektronik ', '2', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(12, 'elifcakil', 'elif@ogr.deu.edu.tr', '$2y$10$examplepasshash12', 'kahve+kitap+deu', 'İngiliz Dili', '2', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(13, 'batuhanrandom', 'batuhan@ogr.deu.edu.tr', '$2y$10$examplepasshash13', 'şaka mısınız ya', 'Uluslararası İlişkiler', '3', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(14, 'dilarabastan', 'dilara@ogr.deu.edu.tr', '$2y$10$examplepasshash14', 'ben yazmazsam kim yazacak', 'Hemşirelik', '2', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(15, 'mertteklif', 'mert@ogr.deu.edu.tr', '$2y$10$examplepasshash15', 'sözlükte biri var, adını bilmiyorum', 'Elektrik Müh.', '1', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(17, 'melisneon', 'melis@ogr.deu.edu.tr', '$2y$10$examplepasshash02', 'en son ne zaman uyudum hatırlamıyorum', 'Maliye', '3', '2025-07-04 15:54:35', '', '', '2025-07-04', NULL, NULL, NULL),
(20, 'prens', '2023473064@ogr.deu.edu.tr', '$2y$10$Uoaj2jJj2K1xR9eQL62q1eKVIxV/23toZNt1IhzCip0HMkVwgiJR2', '', 'Maliye', '2', '2025-07-04 17:22:49', '', '', '2025-07-04', NULL, NULL, NULL),
(24, 'lntapetti', '2022473170@ogr.deu.edu.tr', '$2y$10$ZLkLZUu7hbUJoXtIONwb.eERFIX5w5vKpORscmRIqoc5jT42eKjmy', '', 'Maliye', '', '2025-10-01 08:40:13', '', '', '2025-10-01', NULL, NULL, NULL),
(25, 'mehmetcan', '2023473076@deu.edu.tr', '$2y$10$FvKb5YKKUiT2gWjekEQVDu6X.4v0cenzQJh8IbSj3v6pNTWeoa1W6', '', 'Maliye', '3', '2025-10-01 09:54:39', '', '', '2025-10-01', NULL, NULL, NULL),
(30, 'Maniacfrkn', '2023729053@ogr.deu.edu.tr', '$2y$10$jJuZINvpk397o9Di6KDJEOSowxEudvVJHHOyUwsBtNfyCnQiE9mi.', '', 'Meslek Yüksek Okulu', '2', '2025-10-18 11:12:31', 'uploads/profile_photos/profile_30_1760785992.jpg', '', '2025-10-18', NULL, NULL, NULL),
(31, 'Rumeysa', '2023473049@ogr.deu.edu.tr', '$2y$10$RLxDO7XBwdv/veaHr2PASeQvVVy1ShE3J3efMz4w7kNEFg3IPkxWy', '', 'Maliye', '3', '2025-10-18 12:27:44', '', '', '2025-10-18', NULL, NULL, NULL),
(32, 'Seninanonim', '2024300320@ogr.deu.edu.tr', '$2y$10$StapDAMGay87yPByb4re1OHfsSb2eKAJx7rFFD9LjPbJcgxCem4KO', '', 'Hukuk', '2', '2025-10-18 16:43:18', '', '', '2025-10-18', NULL, NULL, NULL),
(37, 'eniscagri', '2023473085@ogr.deu.edu.tr', '$2y$10$QM9sMG46kXEf22dh3AGqGuCehez/W.uLUl6S/6l/OFD5a3rQKwOg.', 'I\'m coding stuff...', 'Maliye', '3', '2003-09-21 22:30:04', 'uploads/profile_photos/profile_37_1760841538.jpg', '', '2025-10-19', NULL, NULL, NULL);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `entries`
--
ALTER TABLE `entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `entry_likes`
--
ALTER TABLE `entry_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`entry_id`),
  ADD KEY `entry_id` (`entry_id`);

--
-- Tablo için indeksler `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `follower_followed` (`follower_id`,`followed_id`),
  ADD KEY `followed_id` (`followed_id`);

--
-- Tablo için indeksler `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entry_id` (`entry_id`),
  ADD KEY `reported_by` (`reported_by`);

--
-- Tablo için indeksler `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_created_by` (`created_by`);

--
-- Tablo için indeksler `topic_followers`
--
ALTER TABLE `topic_followers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`user_id`,`topic_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `entries`
--
ALTER TABLE `entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Tablo için AUTO_INCREMENT değeri `entry_likes`
--
ALTER TABLE `entry_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Tablo için AUTO_INCREMENT değeri `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `logins`
--
ALTER TABLE `logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `request`
--
ALTER TABLE `request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- Tablo için AUTO_INCREMENT değeri `topic_followers`
--
ALTER TABLE `topic_followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `entries`
--
ALTER TABLE `entries`
  ADD CONSTRAINT `entries_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`),
  ADD CONSTRAINT `entries_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `entry_likes`
--
ALTER TABLE `entry_likes`
  ADD CONSTRAINT `entry_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entry_likes_ibfk_2` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`followed_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `logins`
--
ALTER TABLE `logins`
  ADD CONSTRAINT `logins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topics_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `topic_followers`
--
ALTER TABLE `topic_followers`
  ADD CONSTRAINT `topic_followers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topic_followers_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
