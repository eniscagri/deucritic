<?php
/** Lightweight UI language support for the existing PHP site. User posts and comments are not translated. */
require_once __DIR__ . '/session.php';

$requestedLanguage = $_GET['lang'] ?? null;
if (in_array($requestedLanguage, ['tr', 'en'], true)) {
    $_SESSION['dc_lang'] = $requestedLanguage;
    if (!headers_sent()) {
        setcookie('dc_lang', $requestedLanguage, [
            'expires' => time() + 31536000,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

$cookieLanguage = $_COOKIE['dc_lang'] ?? null;
if (in_array($cookieLanguage, ['tr', 'en'], true) && !isset($_SESSION['dc_lang'])) {
    $_SESSION['dc_lang'] = $cookieLanguage;
}
if (!isset($_SESSION['dc_lang']) || !in_array($_SESSION['dc_lang'], ['tr', 'en'], true)) {
    $_SESSION['dc_lang'] = 'tr';
}

function dc_lang(): string
{
    return $_SESSION['dc_lang'] ?? 'tr';
}

function dc_lang_url(string $language): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    $path = $parts['path'] ?? '/';
    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }
    $query['lang'] = in_array($language, ['tr', 'en'], true) ? $language : 'tr';
    return $path . '?' . http_build_query($query) . (!empty($parts['fragment']) ? '#' . rawurlencode($parts['fragment']) : '');
}

function dc_english_ui_map(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [
        // Shared navigation and common actions
        'Ana Sayfa' => 'Home', 'Baba Sayfa' => 'Home', 'Biz Kimiz?' => 'About Us', 'Hakkında' => 'About',
        'Geliştirin' => 'Build With Us', 'Geliştirelim' => 'Build With Us', 'Geliştirme Talebi' => 'Feature Request',
        'S.S.S' => 'FAQ', 'Sıkça Sorulan Sorular (SSS)' => 'Frequently Asked Questions', 'Sözlük' => 'Community',
        'Keşfet' => 'Discover', 'Gündeme göz at' => 'Browse the feed', 'Gündem' => 'Feed', 'Son Onep\'ler' => 'Latest Posts',
        'Bir şey yaz' => 'Write a post', 'Sözlüğe katıl' => 'Join the community', 'Aramıza Katıl' => 'Join the community',
        'Katıl' => 'Join', 'Kayıt Ol' => 'Sign Up', 'Kayıt ol' => 'Sign Up', 'Giriş' => 'Log In', 'Giriş Yap' => 'Log In',
        'Çıkış Yap' => 'Log Out', 'Oturum Açık' => 'Signed In', 'Profilim' => 'My Profile', 'Mesajlar' => 'Messages',
        'Ayarlar' => 'Settings', 'Yanıtla' => 'Reply', 'Beğen' => 'Like', 'Beğenildi' => 'Liked', 'Paylaş' => 'Share',
        'Kaydet' => 'Save', 'İptal' => 'Cancel', 'Gönder' => 'Submit', 'Devam et' => 'Continue', 'Daha Fazla' => 'Load More',
        'Ara' => 'Search', 'Ara...' => 'Search...', 'Kapat' => 'Close', 'Sil' => 'Delete', 'Düzenle' => 'Edit',
        'Takip Et' => 'Follow', 'Takip Ediliyor' => 'Following', 'Takipçiler' => 'Followers', 'Takip Ettikleri' => 'Following',
        'Anonim' => 'Anonymous', 'Anonim Paylaş' => 'Post Anonymously', 'Özel İçerik' => 'Members Only',
        'Daha Fazla Gör' => 'See More', 'Daha az göster' => 'Show Less', 'Geri' => 'Back', 'İleri' => 'Next',
        'Yükleniyor...' => 'Loading...', 'Henüz içerik yok.' => 'No posts yet.', 'Henüz sonuç bulunamadı.' => 'No results found.',

        // Home page and feed
        'DOKUZ EYLÜL ÜNİVERSİTESİ' => 'DOKUZ EYLÜL UNIVERSITY', 'ÖĞRENCİ SÖZLÜĞÜ' => 'STUDENT COMMUNITY',
        'Tanıtım' => 'About DeuCritic', 'Deucritic Haberler' => 'DeuCritic News',
        'Kampüs konuşuyor.' => 'Campus is talking.', 'Söz sende.' => 'Your voice belongs.',
        'Ders arası fikirlerinden şehir gündemine — DEÜ öğrencilerinin sözlüğü.' => 'From between-class thoughts to city-wide conversations — a community for DEU students.',
        'Gündeme göz at ↓' => 'Browse the feed ↓', 'Bir şey yaz' => 'Write a post', 'Son Onep’ler' => 'Latest Posts',
        'Onep nedir?' => 'What is a Onep?', 'Yazı Paylaş' => 'Write a Post', 'Hikaye Paylaş' => 'Share a Story',
        'ANA FİKİR (BAŞLIK)' => 'MAIN IDEA (TITLE)', 'DETAYLAR' => 'DETAILS', 'Yola Çıksın!' => 'Post It!',
        'Takma ad veya DEÜ mailin...' => 'Username or DEU email...', 'Bir düşünceni paylaş...' => 'Share a thought...',
        'Başlık' => 'Title', 'İçerik' => 'Content', 'Konu başlığı' => 'Topic title', 'Gönderi paylaş' => 'Create a post',
        'Topluluk etkileşimi' => 'Community interaction', 'Kampüs gündemi' => 'Campus feed',

        // Authentication and account
        'Tekrar Hoş Geldin!' => 'Welcome Back!', 'Giriş Yap' => 'Log In', 'Hesabına erişerek kaldığın yerden devam et.' => 'Log in to pick up where you left off.',
        'TAKMA AD / OKUL E-POSTASI' => 'USERNAME / UNIVERSITY EMAIL', 'ŞİFRE' => 'PASSWORD', 'Şifremi Unuttum' => 'Forgot Password',
        'Şifremi Unuttum.' => 'Forgot Password?', 'Hesap Oluştur' => 'Create Account', 'Yeni misin?' => 'New here?',
        'Aramıza hoş geldin' => 'Welcome to the community', 'Hesabını oluştur' => 'Create your account',
        'Kullanıcı adı' => 'Username', 'E-posta' => 'Email', 'Okul e-postası' => 'University email', 'Şifre' => 'Password',
        'Şifreni tekrar gir' => 'Confirm your password', 'Ad Soyad' => 'Full Name', 'Profil fotoğrafı' => 'Profile Picture',
        'Beni hatırla' => 'Remember me', 'Giriş bilgilerin hatalı.' => 'Your login details are incorrect.',
        'Lütfen tüm alanları doldurun.' => 'Please complete all fields.', 'Geçersiz istek.' => 'Invalid request.',
        'Şifreyi değiştir' => 'Change Password', 'Yeni şifre' => 'New Password', 'Yeni şifreyi tekrar gir' => 'Confirm New Password',
        'Şifreyi Sıfırla' => 'Reset Password', 'Şifre sıfırlama bağlantısı gönder' => 'Send Password Reset Link',
        'Hesabını doğrula' => 'Verify Your Account', 'Doğrulama e-postası' => 'Verification Email',
        'Ücretsiz kayıt ol' => 'Sign up for free', 'Hemen Başla' => 'Get Started',

        // Shared about, policy and footer copy
        'Platform' => 'Platform', 'Yasal & İletişim' => 'Legal & Contact', 'Kullanım Koşulları' => 'Terms of Use',
        'Haberler & Paper' => 'News & Paper', 'Geliştirme Talebi' => 'Feature Request',
        'Dokuz Eylül Üniversitesi\'nin dijital hafızası.' => 'The digital memory of Dokuz Eylül University.',
        'Fikirlerin özgürce paylaşıldığı, kampüsün nabzının tutulduğu interaktif eleştiri, tartışma ve bilgi platformu.' => 'An independent student community for sharing ideas, following campus life, and joining conversations.',
        '© 2026 Deucritic Sözlük.' => '© 2026 DeuCritic.', 'an eniscagri production' => 'an Enis Çağrı production',
        'Yazılımcı, Öğrenci' => 'Developer, Student', 'Kolay Kullanım' => 'Easy to Use', 'Topluluk' => 'Community',

        // Discovery, topics and feed actions
        'Kampüste ara' => 'Search campus', 'Deucritic\'te ara' => 'Search DeuCritic', 'Arama sonuçları' => 'Search Results',
        'Sonuç bulunamadı' => 'No results found', 'Başlıklar' => 'Topics', 'Gönderiler' => 'Posts', 'Yorumlar' => 'Replies',
        'Yanıt yaz...' => 'Write a reply...', 'Yorum yaz...' => 'Write a comment...', 'Takip et' => 'Follow',
        'Takibi bırak' => 'Unfollow', 'Konuya git' => 'Open topic', 'Paylaşım tarihi' => 'Posted',
        'Tümünü Gör' => 'View All', 'Tümünü gör' => 'View All', 'Keşfetmeye Başla' => 'Start Exploring',
        'En Yeniler' => 'Newest', 'En Popüler' => 'Popular', 'Bugün' => 'Today', 'Dün' => 'Yesterday',
        'Şimdi' => 'Now', 'dakika önce' => 'minutes ago', 'saat önce' => 'hours ago', 'gün önce' => 'days ago',
        'Öne Çıkanlar' => 'Featured', 'Özel İçerik' => 'Members Only', 'Devamını okumak için katıl' => 'Join to read more',

        // Stories, notifications, messages, badges and profile
        'Hikayeler' => 'Stories', 'Hikaye ekle' => 'Add a Story', 'Hikaye paylaş' => 'Share a Story',
        'Bildirimler' => 'Notifications', 'Tümünü okundu olarak işaretle' => 'Mark all as read', 'Yeni bildirim yok' => 'No new notifications',
        'Mesaj gönder' => 'Send Message', 'Mesaj yaz...' => 'Write a message...', 'Yeni mesaj' => 'New Message',
        'Sohbetler' => 'Chats', 'Çevrimiçi' => 'Online', 'Çevrimdışı' => 'Offline', 'Mesaj bulunamadı' => 'No messages found',
        'Kazanılan Rozetler' => 'Earned Badges', 'Rozetler' => 'Badges', 'Tümünü Gör' => 'View All', 'Rozetlerini Keşfet' => 'Explore Your Badges',
        'Profilini Düzenle' => 'Edit Profile', 'Profil Düzenle' => 'Edit Profile', 'Profil Bilgileri' => 'Profile Information',
        'Profil fotoğrafını güncelle' => 'Update Profile Picture', 'Kapak fotoğrafı' => 'Cover Photo', 'Hakkımda' => 'About Me',
        'Kaydedildi' => 'Saved', 'Değişiklikleri Kaydet' => 'Save Changes', 'Üyelik tarihi' => 'Member since',
        'Takipçi' => 'Followers', 'Takip Edilen' => 'Following', 'Paylaşımlar' => 'Posts', 'Henüz rozet yok.' => 'No badges yet.',

        // Marketing/info pages and visible system feedback
        'Hiper-Lokal Filtreleme' => 'Hyperlocal Feed', 'Onep Trendleri' => 'Trending Posts', 'Akademik Feedback' => 'Academic Feedback',
        'Kampüsün Nabzı' => 'The Pulse of Campus', 'Tek Bir Fokus Noktasında.' => 'In One Place.', 'Kolektif Zeka Yakında' => 'Collective Intelligence — Coming Soon',
        'Ana Sayfaya Dön' => 'Back to Home', 'Geliştirici' => 'Developer', 'Commander Geliştirici Hakkında' => 'About the Developer',
        'Giriş başarılı.' => 'Login successful.', 'İşlem başarılı.' => 'Action completed successfully.', 'Bir hata oluştu.' => 'Something went wrong.',
        'Bağlantı kopyalandı!' => 'Link copied!', 'Yorumun gönderildi.' => 'Your reply was posted.', 'Paylaşımın gönderildi.' => 'Your post was published.',
        'Yükle' => 'Upload', 'Dosya seç' => 'Choose File', 'Fotoğraf seç' => 'Choose Photo', 'Onayla' => 'Confirm',

        // Install prompts and navigation labels
        'Tamamen  Ücretsiz' => 'Completely Free', 'Resmi uygulamayı yükleyerek en hızlı deneyime ve anlık bildirimlere sahip ol.' => 'Install the app for a faster experience and instant notifications.',
        'HEMEN YÜKLE' => 'INSTALL NOW', 'Tarayıcının altındaki' => 'Tap the', 'butonuna dokun.' => 'button at the bottom of your browser.',
        'Listeyi yukarı kaydırıp' => 'Scroll up the list and choose', 'seçeneğine tıkla.' => '.', 'Aramayı aç' => 'Open Search',
        'Trend Başlıklar:' => 'Trending Topics:', 'Yemekhane' => 'Dining Hall', 'Vizeler' => 'Midterms', 'İtiraf' => 'Confessions',
        'KAÇIŞ TUŞU' => 'LOG OUT', 'Hoş Geldin' => 'Welcome', 'Çıkış' => 'Log Out', 'Profil' => 'Profile',

        // Registration, profile setup and recovery
        'Topluluğa Katıl!' => 'Join the Community!', 'DEÜ\'nün özgür platformunda yerini al.' => 'Find your place in DEU\'s open community.',
        'Sadece DEÜ öğrencilerine özel.' => 'For Dokuz Eylül University students.', 'Kullanıcı Bilgileri' => 'Account Details',
        'E-Posta' => 'Email', 'Öğrenci numarası veya isim.soyisim formatında okul mailinizi girin.' => 'Enter your university email in student-number or name.surname format.',
        'Şifre Kurtarma E-postası' => 'Password Recovery Email', 'Kurtarma E-postası' => 'Recovery Email',
        'Takma Adın' => 'Your Username', 'Kısa Biyografi (İsteğe bağlı)' => 'Short Bio (Optional)',
        'Topluluğa katılmadan önce hesabına biraz renk katalım.' => 'Let’s add a little color to your profile before you join the community.',
        'JPEG, PNG veya WEBP (Maks 5MB)' => 'JPEG, PNG or WEBP (Max 5 MB)', 'Görünür Ad' => 'Display Name',
        'Okul' => 'University', 'Bölüm' => 'Department', 'Sınıf' => 'Year of Study', 'Hazırlık' => 'Preparatory Year',
        'Zaten üye misin?' => 'Already a member?', 'Profilini tamamla' => 'Complete Your Profile', 'Profil Fotoğrafı Ekle' => 'Add a Profile Picture',
        'Şifreni Mi Unuttun?' => 'Forgot Your Password?', 'Şifremi Unuttum' => 'Forgot Password',
        'Merak etme' => 'Don’t worry', 'yanında!' => 'is here to help!', 'Okul mailine değil, kayıt olurken verdiğin' => 'We’ll send the link to the',
        'kişisel adresine' => 'personal email address', 'link göndereceğiz.' => 'you provided when you signed up, not your university email.',
        'bize mail atabilirsin.' => 'you can email us.', 'Yeni Link İste' => 'Request a New Link',
        'Yeni Şifre Belirle' => 'Set a New Password', 'Güvenliğin bizim için önemli dayım.' => 'Your security matters to us.',
        'Mevcut Şifre' => 'Current Password', 'Yeni Şifre' => 'New Password', 'Şifre (Tekrar)' => 'Confirm Password',
        'Şifreyi Güncelle' => 'Update Password', 'Yeni Şifre (Tekrar)' => 'Confirm New Password', 'Göster' => 'Show',
        'Ana Sayfa\'ya Dön' => 'Return to Home', 'Anasayfa\'ya Dön' => 'Return to Home', 'Yeni Link İste' => 'Request a New Link',
        'Şifren başarıyla güncellendi!' => 'Your password has been updated!', 'Giriş yapabilirsin.' => 'You can now log in.',
        'Hoşgeldin' => 'Welcome', 'Takma Adın' => 'Your Username', 'Biyografi' => 'Bio',

        // Post composer, feed cards and story creation
        'YENİ ÖZELLİK' => 'NEW FEATURE', 'Deucritic\'te' => 'On DeuCritic', 'Rozet Dönemi Başladı!' => 'The Badge Era Has Begun!',
        'Kampüste ne kadar aktifsin? Gönderi paylaş, etkileşim kazan ve birbirinden özel rozetleri toplayarak profilini güçlendir. İlk rozetin seni bekliyor olabilir!' => 'How active are you on campus? Post, engage with the community and earn special badges to build your profile. Your first badge may be waiting for you!',
        'Rozetlerini Keşfet' => 'Explore Your Badges', 'Devamını okumak için katıl' => 'Join to read more', 'Özel İçerik' => 'Members Only',
        'Onep\'ler' => 'Posts', 'YENİ ÖZELLİK' => 'NEW FEATURE', 'Rozet' => 'Badge',
        'Yazı Paylaş' => 'Write a Post', 'Hikaye Paylaş' => 'Share a Story', 'Ana Fikir (Başlık)' => 'Main Idea (Title)',
        'Tıkla veya Sürükle Bırak' => 'Click or Drag and Drop', 'Fotoğraf veya Video (Max: 20 MB)' => 'Photo or Video (Max: 20 MB)',
        'Bu anı nasıl anlatırsın? ✨' => 'How would you describe this moment? ✨', 'Hikayeyi Paylaş' => 'Share Story',
        'Paylaşılıyor...' => 'Posting...', 'Gönderiliyor...' => 'Submitting...', 'Anonim (AnY) Paylaş' => 'Post Anonymously (AnY)',
        'Bugün kampüste ne fark ettin?' => 'What did you notice on campus today?', 'Sence de yemekhane sıraları...' => 'Do you think the dining hall lines...',
        'Finaller hakkında bir itirafın var mı?' => 'Have a confession about finals?', 'İzmir\'de öğrenci olmak...' => 'Being a student in Izmir...',
        'Biraz yavaş! Yeni bir Onep göndermek için' => 'Slow down! Please wait', 'saniye beklemelisin' => 'seconds before posting again',
        'Başlık boş bırakılamaz!' => 'The title cannot be empty!', 'Paylaşım topluluk kurallarına aykırı olduğu için engellendi' => 'Your post was blocked because it violates the community guidelines',
        'Harika! Onep başarıyla oluşturuldu' => 'Great! Your post was created successfully', 'Yeni Onep\'in başarıyla paylaşıldı!' => 'Your new post was published successfully!',
        'Desteklenmeyen dosya türü.' => 'Unsupported file type.', 'Dosya boyutu' => 'File size', 'büyük olamaz.' => 'is too large.',
        'İlk düşünceni paylaşarak konuşmayı başlat.' => 'Share the first thought and start the conversation.',
        'Burası çok sessiz.' => 'It’s quiet here.', 'Buralar Çok Sessiz' => 'It’s Quiet Around Here',
        'Gönderiler' => 'Posts', 'Yorum Yap' => 'Reply', 'Devamını Oku' => 'Read More',

        // Profiles, badges, messaging and common empty states
        'Takibi Bırak' => 'Unfollow', 'Takip Et' => 'Follow', 'Profilini Düzenle' => 'Edit Profile', 'Profili Düzenle' => 'Edit Profile',
        'Takma Ad' => 'Username', 'Okul E-postası' => 'University Email', 'GİZLİ' => 'PRIVATE', 'Sınıf' => 'Year',
        'Yeni Avatar Seç' => 'Choose a New Avatar', 'Henüz rozet kazanılmadı.' => 'No badges earned yet.', 'Rozetleri keşfet ve kazan!' => 'Explore and earn badges!',
        'Kazanılan Rozetler' => 'Earned Badges', 'Tüm Başarımlar' => 'All Achievements', 'Rozetlerim' => 'My Badges',
        'Topluluğun bir parçası ol, başarımları topla ve seviyeni göster!' => 'Be part of the community, collect achievements and show your level!',
        'Görünüşe Göre Buralar Boş' => 'Looks Like It’s Empty Here', 'Sisteme henüz hiç rozet eklenmemiş.' => 'No badges have been added yet.',
        'Yeni Rozet!' => 'New Badge!', 'Profil şu an bomboş, hemen bir şeyler karala!' => 'Your profile is empty for now. Write your first post!',
        'Henüz takipçisi yok.' => 'No followers yet.', 'Henüz kimseyi takip etmiyor.' => 'Not following anyone yet.',
        'Okul E-postası' => 'University Email', 'Kampüs Belirtilmedi' => 'No Campus Set', 'Takipçiler' => 'Followers',
        'Mesajlar' => 'Messages', 'Sohbet' => 'Chat', 'Aranıyor...' => 'Calling...', 'Reddet' => 'Decline',
        'Cevapla' => 'Answer', 'Bitir' => 'End', 'Sohbete başla' => 'Start a conversation', 'Yeni bir mesaj göndererek sohbete başla.' => 'Send a message to start a conversation.',
        'Henüz mesaj yok' => 'No messages yet', 'Mesaj yaz' => 'Write a message', 'Gönder' => 'Send',

        // Topic discovery, feature requests and help
        'Hiper-Lokal Filtreleme' => 'Hyperlocal Feed', 'Buca, Tınaztepe veya Balçova...' => 'Buca, Tınaztepe or Balçova...',
        'Sadece senin kampüsünde ne olup bittiğini, hangi mekanın parladığını süzerek getiriyoruz.' => 'See what’s happening around your campus and which places are getting noticed.',
        'Topluluğun en çok yanıt verdiği, vizelerden daha fazla konuşulan o "kritik" konuları algoritmanın en tepesine taşıyoruz.' => 'We bring the conversations and topics your community responds to into focus.',
        'Dersler, hocalar ve kampüs hayatı hakkında en "gerçek" yorumları, reklam karmaşasına girmeden keşfetmeni sağlıyoruz.' => 'Discover candid student perspectives on classes, instructors and campus life.',
        'Fikirlerini Paylaş' => 'Share Your Ideas', 'Deucritic\'i birlikte daha iyi bir yer haline getirelim.' => 'Let’s make DeuCritic better together.',
        'Özellik İsteği' => 'Feature Request', 'Üniversite Öner' => 'Suggest a University', 'VEYA' => 'OR',
        'Sıkça Sorulan Sorular (SSS)' => 'Frequently Asked Questions', 'Deucritic’e nasıl üye olabilirim?' => 'How do I join DeuCritic?',
        'Siteye gir, nick seç, okul postanı gir, şifreni uydur. \'Kayıt ol\'a bas. Hop, üyelik.' => 'Open the site, choose a username, enter your university email and create a password. Click “Sign Up” and you’re in.',
        'Onep nedir?' => 'What is a Onep?', 'O konuya dair kafana ilk geleni döktüğün cümlelerdir. Yani bir başlık altına yazılan yorum, görüş veya bilgi.' => 'A Onep is a thought, opinion or piece of information shared under a topic.',
        'Takma adımı değiştirebilir miyim?' => 'Can I change my username?', 'Evet, değiştirebilirsin. Profil ayarlarına girip yeni bir nick seçerek güncelleyebilirsin. Ancak çok sık değiştirirsen sistem seni uyarabilir.' => 'Yes. Go to your profile settings and choose a new username. Changing it too often may trigger a system warning.',
        'Rozetlerimi nasıl görebilirim?' => 'How can I see my badges?', 'Kendi profil sayfana giderek kazandığın tüm rozetleri görebilirsin. Rozetlerin üzerine gelerek açıklamalarını da inceleyebilirsin.' => 'Visit your profile to see the badges you have earned. Hover over a badge to read its description.',
        'Başlık ya da arkadaşımı takip etmek istiyorum ama olmuyor?' => 'Why can’t I follow a topic or friend?',
        'Onep karakter sınırı nedir?' => 'What is the character limit for a Onep?', 'Tek onep 500 karakter. Uzun yazarsan sistem bölüyor, kısa ve öz yazman daha iyi okunur.' => 'Each Onep can contain up to 500 characters. Short, clear posts are easier to read.',
        'Üyeliğimi silebilir miyim?' => 'Can I delete my account?', 'Profil fotoğrafımı değiştirebilir miyim?' => 'Can I change my profile picture?',
        'Başlığa resim/gif ekleyebilir miyim?' => 'Can I add an image or GIF to a topic?', 'Çok fazla spam onep atarsam ne olur?' => 'What happens if I post too much?',
        'Başlıklar' => 'Topics', 'Konuya göz at' => 'Browse Topics', 'Son Güncellemeler ve Haberler' => 'Latest Updates and News',
        'Tüm Haberler' => 'All News', 'Deucritic\'te Neler Oluyor?' => 'What’s Happening on DeuCritic?', 'Son Haberler' => 'Latest News',
        'Bağlantıya Git' => 'Open Link', 'Paylaş:' => 'Share:', 'Benzer Haberler' => 'Related News',

        // FAQ and account error copy
        'Onay maili gelmedi, napıcam?' => 'I didn’t receive the verification email. What should I do?',
        'Önce spam kutusuna bak.' => 'Check your spam folder first.', 'Şifremi unuttum ama değiştiremiyorum.' => 'I forgot my password and can’t reset it.',
        'Oneplerim niye görünmüyor?' => 'Why aren’t my posts visible?', 'Nickim çalınır mı, hesabım güvenli mi?' => 'Can someone steal my username? Is my account secure?',
        'Başka üniversiteler de Deucritic kullanabilir mi?' => 'Can students from other universities use DeuCritic?',
        'Takipçilerimi ve takip ettiklerimi nasıl kontrol edebilirim?' => 'How can I see my followers and who I follow?',
        'Hesabıma giriş yapınca “erişim kapatıldı” diyor. Bu ne demek?' => 'My account says “access restricted” when I log in. What does that mean?',
        'Önce spam kutusuna bak. Yoksa internet servis sağlayıcın \'bu mail fazla dürüst, ben bunu engelleyeyim\' demiş olabilir. Hiç gelmezse bana yaz, sonra nickini tekrar denersin.' => 'Check your spam folder first. If it is not there, your email provider may have filtered it. If it still does not arrive, contact us and try again.',
        'Bilgilerini korumak adına şifre sıfırlama sistemini şimdilik tatildeyiz 😅 Ama endişelenme bro, bize kısa bir mail atarsan sana ışık hızıyla şifre sıfırlama linki göndeririz. Kusura bakma :( ' => 'To protect your account, password resets are currently handled by support. Email us and we will send you a reset link as soon as possible. Sorry for the extra step.',
        'Genellikle hemencecik paylaşabilirsin, paylaşamazsan sistem seni önce bir bekleme odasına atıyor. Onay gelince bir anda hepsi ortaya çıkıyor, sürpriz yumurta gibi. Bekleme odasında da yoksa bana yaz sen aga.' => 'Posts may be held briefly for review. Once approved, they will appear in the feed. If a post is still missing after that, contact us.',
        'Profil sayfanda \'Takipçi\' ve \'Takip Ettikleri\' bölümlerine tıklayarak detaylı listeyi görebilirsin.' => 'Open the Followers and Following sections on your profile to see the full lists.',
        'Nazikçe söylemek gerekirse: ban yemişsin dostum. Sebebini öğrenmek için mailine bak, \'fuckulte\' diye koca bir dosya gelmiş olabilir.' => 'It usually means your account has been suspended. Check your email for more information about the reason.',
        'Taşındığı başlığın yanında yine o meşhur \'*\' var. Ona bas → eski başlığa dön → \'takip etme\'yi seç. Arkadaşını takip edeceksen arkadaşına tıkla → takip et butonuna bas. Eğer hiçbiri yoksa sistemde böyle şeyler henüz gelmemiştir. Az sabret :D' => 'To follow a topic, use the star beside it. To follow someone, open their profile and select Follow. If you do not see these options, that feature may not be available in that part of the site yet.',
        'Şu an bizde bir silme butonu yok, yazdığın onepi kesinlikle silmek istersen yaz, o bakar. Kısaca, \'yazdım oldu bitti\' mantığı var, ama acil bir durumsa bana yaz.' => 'There is no self-service delete button at the moment. If you need a post removed, contact the site team.',
        'Şu an sadece metin kabul ediyoruz dostum. Ama fikir güzel, ileride belki ekleriz. Onep’lerini güzel anlat, kelimelerle coştur.' => 'Posts currently support text. We may add image support in the future.',
        'Sistemimiz temel güvenlik önlemlerini alıyor ama şifreni paylaşma, basit şifre koyma. Nick bir dövme gibi, çalınmaz ama hesabın şifresiz kalırsa sorun çıkar. Genel olarak kişisel bilgilerine ben dahil kisme erişemez; merak etme :D' => 'Use a strong, unique password and never share it. Keep personal information out of public posts. Contact us if you notice anything unusual with your account.',
        'Sistem seni önce yavaşlatır, sonra gerekirse uyarır. Ama merak etme, kimseyi rahatsız etmedikçe kimse banlamaz.' => 'The system may slow down repeated posting and issue a warning if needed. Please avoid spam and respect other members.',
        'Şimdilik DEU öğrencileri için, ama eğer arkadaşın davet ederse veya sistem açılırsa o da katılabilir. Sürpriz olabilir, dikkat! 😎' => 'DeuCritic is currently intended for Dokuz Eylül University students. Any eligibility changes will be announced on the site.',
        'Evet, ama bunu yapmak geri dönüşü olmayan bir işlemdir. Hesabını silmek için destek ekibine ya da admin\'e mail atman gerekiyor.' => 'Yes. Account deletion is permanent. Contact the support team to request it.',
        'Evet, profil sayfandan yeni bir fotoğraf yükleyebilirsin. AnY başlıklarda profil fotoğrafın görünmez, merak etme.' => 'Yes, you can upload a new picture from your profile. Your profile photo is hidden in anonymous topics.',
        'Anonim değilse herkes görür. AnY (anonim) başlıklarda ise sadece başlığı yazan hiçbir şekilde görünmez; diğer kullanıcılar AnY onep yazmış olsa bile, o onep profilinde görünür.' => 'Non-anonymous posts are visible to everyone. In anonymous topics, the topic author stays anonymous; other members’ posts may still appear on their profiles.',
        'Takip işlemi başarısız.' => 'Could not follow this account.', 'Sunucu hatası.' => 'Server error.', 'Silmek istediğine emin misin?' => 'Are you sure you want to delete this?',

        // Additional interface strings from the current public pages
        'Deucritic | Kampüsün Özgür Sesi' => 'DeuCritic | The Voice of Campus', 'Kampüsün Özgür Sesi' => 'The Voice of Campus',
        'Takma Ad / Okul E-Postası' => 'Username / University Email', 'Gündemi yakala, tartışmalara yön ver.' => 'Catch up on campus and shape the conversation.',
        'Kampüse Dönüş' => 'Back to Campus', 'Tekrar Hoş Geldin!' => 'Welcome Back!', 'Hoşgeldin - DeuCritic' => 'Welcome to DeuCritic',
        'Seni aramızda görmek harika!' => 'We’re glad you’re here!', 'Hesabına biraz renk katalım.' => 'Let’s add some color to your profile.',
        'Öğrenci numarası veya isim.soyisim formatında okul mailinizi girin.' => 'Enter your university email using your student number or name.surname.',
        'Kişisel e-posta adresi' => 'Personal Email Address', 'İsteğe bağlı' => 'Optional', 'Zorunlu' => 'Required',
        'Parola en az 8 karakter olmalı.' => 'Your password must be at least 8 characters.', 'Şifreler eşleşmiyor.' => 'Passwords do not match.',
        'Kullanım Koşulları\'nı kabul ediyorum.' => 'I agree to the Terms of Use.', 'Kullanım Koşulları ve Gizlilik Politikası' => 'Terms of Use and Privacy Policy',
        'Lütfen okul e-postanı doğrula.' => 'Please verify your university email.', 'Doğrulama bağlantısı gönderildi.' => 'A verification link has been sent.',
        'Okul e-posta adresini doğrula' => 'Verify Your University Email', 'Doğrulama bağlantısı geçersiz veya süresi dolmuş.' => 'This verification link is invalid or has expired.',
        'Şifreyi değiştirmek için önce giriş yapmalısın.' => 'Please log in before changing your password.',
        'Şifreyi Sıfırlama' => 'Password Reset', 'Şifreni sıfırlamak için e-posta adresini gir.' => 'Enter your email address to reset your password.',
        'Şifren başarıyla güncellendi! 😎' => 'Your password has been updated! 😎', 'Güvenliğin bizim için önemli dayım.' => 'Your security matters to us.',
        'İzmir' => 'Izmir', 'DEÜ öğrencileri için bağımsız öğrenci topluluğu' => 'Independent student community for DEU students',
        'Deucritic Sözlük Hakkında' => 'About the DeuCritic Community', 'Deucritic Sözlük, Üniversite tayfası için hazırlanmış, tam anlamıyla ortamın nabzını tutan bir platform.' => 'DeuCritic is a community platform made for university students to share what is happening around campus.',
        'Okulda ne var ne yok, dedikodu, öneri, geyik... Hepsi burada dönüyor.' => 'Campus stories, recommendations and conversations all come together here.',
        'Kolay Kullanım' => 'Easy to Use', 'Kayıt olmak, içerik atmak çocuk oyuncağı. Takılmak serbest, ortam samimi.' => 'Signing up and sharing is simple. Join in and make yourself at home.',
        'Geyik ve Mizah' => 'Humor and Fun', 'Mizahın dibine vur! Geyik yap, eğlen, üniversite hayatını renklendir.' => 'Share a laugh, have fun and add a little color to university life.',
        'Gel, muhabbetin dibine vur! Fikirlerini paylaş, yeni kankalar edin, üniversite hayatını efsaneleştir.' => 'Share your ideas, meet people and make your university experience your own.',
        'Commander Geliştirici Hakkında' => 'About the Developer',
        'YENİ ÖZELLİK' => 'NEW FEATURE', 'Rozet Dönemi Başladı!' => 'The Badge Era Has Begun!',
        'Kampüste ne kadar aktifsin?' => 'How active are you on campus?', 'İlk rozetin seni bekliyor olabilir!' => 'Your first badge may be waiting for you!',
        'Geliştirelim' => 'Build With Us', 'S.S.S' => 'FAQ', 'Sadece metin kabul ediyoruz dostum.' => 'Only text posts are supported right now.',
        'Yemekhaneye yer' => 'Dining Hall Spots', 'Anonim paylaşım' => 'Anonymous Posting', 'Kampüs konuşuyor' => 'Campus Is Talking',
        'AnY Kullanıcı' => 'Anonymous User', 'AnY' => 'Anonymous', 'Kampüste ne var ne yok?' => 'What’s happening on campus?',
        'Düşünceni paylaş' => 'Share a thought', 'Yanıtını paylaş' => 'Post your reply', 'Yorumunu paylaş' => 'Post your comment',
        'Yorumun başarıyla eklendi.' => 'Your reply was added.', 'Gönderi başarıyla silindi.' => 'The post was deleted.',
        'Henüz paylaşım yok.' => 'No posts yet.', 'Henüz hikaye yok.' => 'No stories yet.', 'İçerik bulunamadı.' => 'Content not found.',
        'Yayınlandı' => 'Published', 'Taslak' => 'Draft', 'Bugün' => 'Today', 'Dün' => 'Yesterday',
        'İstekler' => 'Requests', 'Yeni istek oluştur' => 'Submit a Request', 'Başlığını gir' => 'Enter a title',
        'Açıklama' => 'Description', 'İsteğini gönder' => 'Submit Your Request', 'Önerini paylaş' => 'Share Your Suggestion',
        'Üniversite adı' => 'University Name', 'Kampüs adı' => 'Campus Name', 'İstek başarıyla gönderildi.' => 'Your request was submitted.',
        'Özellik önerisi' => 'Feature Suggestion', 'Hata bildir' => 'Report a Bug', 'Bir şey mi ters gitti?' => 'Something not working?',
        'Yeni gönderi' => 'New Post', 'Öne çıkar' => 'Featured', 'Son güncellemeler' => 'Latest Updates',
        'Hikayeler' => 'Stories', 'Kapat' => 'Close', 'Tüm Onep’ler' => 'All Posts', 'Tüm Onep\'ler' => 'All Posts',
        'Dersler' => 'Courses', 'Hocalar' => 'Instructors', 'Kampüs hayatı' => 'Campus Life', 'Öğrenci deneyimi' => 'Student Experience',
        'Arama Sonuçları' => 'Search Results', 'Kampüsün gündemi' => 'Campus Feed', 'Aradığını bul' => 'Find what you’re looking for',
        'Konu başlıklarında ve öğrenci profillerinde ara.' => 'Search topics and student profiles.', 'Kullanıcı ara' => 'Search people',
        'Başlık, konu veya kullanıcı ara...' => 'Search titles, topics or people...', 'Keşfe hazır mısın?' => 'Ready to explore?',
        'Bir kullanıcı adı ya da ilgi alanı yazarak başla.' => 'Start with a username or an interest.',
        'Bu aramayla eşleşme yok' => 'No matches for this search', 'Yazımı kontrol et veya daha kısa bir kelime dene.' => 'Check the spelling or try a shorter search.',
        'Konu başlıkları' => 'Topics', 'Öğrenci profilleri' => 'Student Profiles', 'sonuç' => 'results',
        'Yeni Sohbet Başlat' => 'Start a New Chat', 'Yeni Sohbet' => 'New Chat', 'Kullanıcı adı yaz...' => 'Enter a username...',
        'Bulunan Sonuçlar' => 'Search Results', 'Aradığın kişiyi bulamadık...' => 'We couldn’t find that person...',
        'Mesajlaşmak istediğin arkadaşının kullanıcı adını yazabilirsin.' => 'Enter the username of the person you want to message.',
        'Kişi veya mesaj ara...' => 'Search people or messages...', 'Henüz mesajınız yok.' => 'You don’t have any messages yet.',
        'Sohbeti başlatın...' => 'Start a conversation...', 'Profili Gör' => 'View Profile', 'Fotoğraf seçildi' => 'Photo selected',
        'Göndermek için bir şeyler yazın.' => 'Write a message to send.', 'Yeni Mesaj Gönder' => 'Send a New Message',
        'Uçtan Uca Şifreli' => 'End-to-End Encrypted', 'Bağlanıyor...' => 'Connecting...', 'Mikrofon İzni Gerekli!' => 'Microphone Permission Required!',
        'Mikrofon erişimi reddedildi!' => 'Microphone access was denied!', 'Ulaşılamıyor...' => 'Unavailable...',
        'Gelen Arama' => 'Incoming Call', 'Cevapsız Sesli Arama' => 'Missed Voice Call', 'Sesli Arama Başlatıldı' => 'Voice Call Started',
        'Lütfen sadece fotoğraf formatında bir dosya seçin.' => 'Please select an image file only.', 'İnternet bağlantınızı kontrol edip tekrar deneyin.' => 'Check your internet connection and try again.',
        'Oto-çalma engellendi.' => 'Autoplay was blocked.', 'Bugün' => 'Today', 'Dün' => 'Yesterday', 'Geçen hafta' => 'Last week', 'gün önce' => 'days ago',
        'Yıl' => 'Year', 'Gün Aktiflik' => 'Active Days', 'Paylaşım' => 'Post', 'Beğeni' => 'Like', 'Tek Post Beğenisi' => 'Likes on One Post',
        'Tebrikler!' => 'Congratulations!', 'Rozet ismine göre ara...' => 'Search badges...', 'Kazanılanlar' => 'Earned',
        'Kişide' => 'members', 'Henüz kazanılmadı' => 'Not earned yet', 'Yeni rozet kazandın!' => 'You earned a new badge!',
        'Takipçilerinle anını paylaş' => 'Share a moment with your followers', 'Tıkla veya Sürükle' => 'Click or Drag and Drop',
        'Fotoğraf veya Video (Max: 10MB)' => 'Photo or Video (Max: 10 MB)', 'İptal Et' => 'Cancel', 'Dosya boyutu' => 'File size',
        'Desteklenmeyen dosya türü.' => 'Unsupported file type.', 'Biraz yavaş 🙂 Tekrar dene.' => 'Slow down 🙂 Please try again.',
        'Güvenlik doğrulaması başarısız.' => 'Security verification failed.', 'Özellik alanı boş olamaz.' => 'The feature request cannot be empty.',
        'İsteğin alındı 🎉' => 'Your request has been received 🎉', 'Okul adı zorunlu.' => 'University name is required.',
        'Önerin kaydedildi 🙌' => 'Your suggestion has been saved 🙌', 'İstekler | Deucritic' => 'Requests | DeuCritic',
        'Keşke şöyle bir özellik olsa dediğin şey nedir?' => 'What feature would you like to see?', 'İsteği Gönder' => 'Submit Request',
        'Hangi üniversite eklensin?' => 'Which university should we add?', 'Neden bu üniversite? (Opsiyonel)' => 'Why this university? (Optional)',
        'Üniversiteyi Öner' => 'Suggest University', 'Bu konuda ne düşünüyorsun?' => 'What do you think about this?',
        'Sence haklı mı? Neden?' => 'Do you think they’re right? Why?', 'Katılıyor musun yoksa karşı mı çıkıyorsun?' => 'Do you agree or disagree?',
        'Kendi deneyimini paylaşmak ister misin?' => 'Would you like to share your experience?', 'Farklı bir bakış açısı sunabilir misin?' => 'Can you offer a different perspective?',
        'Onep çok kısa! En az 5 karakter yazmalısın.' => 'Your post is too short. Please write at least 5 characters.',
        'Bilinmeyen bir hata oluştu.' => 'An unknown error occurred.', 'Bağlantı hatası oluştu.' => 'A connection error occurred.',
        'Gizlilik Açık' => 'Privacy On', 'Fotoğraf Ekle' => 'Add Photo', 'Oneple' => 'Post', 'Arama sonuçları' => 'Search Results',
    ];
    uksort($map, static fn($a, $b) => strlen($b) <=> strlen($a));
    return $map;
}

function dc_translate_html_response(string $html): string
{
    if (!preg_match('/^\s*(?:<!doctype\s+html|<html\b)/i', $html)) {
        return $html;
    }
    $lang = dc_lang();
    $html = preg_replace('/(<html\b[^>]*\blang\s*=\s*["\'])[^"\']+(["\'])/i', '$1' . $lang . '$2', $html, 1) ?? $html;
    if (!stripos($html, 'aria-label="Language"') && !stripos($html, "aria-label='Language'")) {
        $tr = htmlspecialchars(dc_lang_url('tr'), ENT_QUOTES, 'UTF-8');
        $en = htmlspecialchars(dc_lang_url('en'), ENT_QUOTES, 'UTF-8');
        $trClass = $lang === 'tr' ? 'background:#1d352f;color:#fff' : 'color:#53635c';
        $enClass = $lang === 'en' ? 'background:#1d352f;color:#fff' : 'color:#53635c';
        $switch = '<nav aria-label="Language" style="position:fixed;right:16px;bottom:16px;z-index:99999;display:flex;gap:2px;padding:4px;border:1px solid #e3dccd;border-radius:12px;background:#fffdf8;box-shadow:0 5px 18px #101b3a20;font:700 11px Arial,sans-serif"><a lang="tr" href="' . $tr . '" style="padding:7px 9px;border-radius:8px;text-decoration:none;' . $trClass . '">TR</a><a lang="en" href="' . $en . '" style="padding:7px 9px;border-radius:8px;text-decoration:none;' . $enClass . '">EN</a></nav>';
        $html = preg_replace('/(<body\b[^>]*>)/i', '$1' . $switch, $html, 1) ?? $html;
    }
    if ($lang !== 'en') {
        return $html;
    }
    $userContent = [];
    $html = preg_replace_callback('/<(span|p|a|div|h[1-6])\b(?=[^>]*\bdata-dc-user-content\b)[^>]*>.*?<\/\1>/is', static function ($match) use (&$userContent) {
        $token = 'DCUSERCONTENT' . count($userContent) . 'TOKEN';
        $userContent[$token] = $match[0];
        return $token;
    }, $html) ?? $html;
    $html = strtr($html, dc_english_ui_map());
    return $userContent ? strtr($html, $userContent) : $html;
}

ob_start('dc_translate_html_response');
