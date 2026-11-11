<?php
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/config.php';

$topicId = filter_input(INPUT_GET, 'topic', FILTER_VALIDATE_INT);
$topic = null;
if ($topicId && $topicId > 0) {
    $topicStmt = $pdo->prepare('SELECT id, title FROM topics WHERE id = ?');
    $topicStmt->execute([$topicId]);
    $topic = $topicStmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$returnTo = $topic ? 'topic.php?id=' . (int) $topic['id'] . '#comments' : 'index.php';
$loginUrl = 'login.php?redirect=' . rawurlencode($returnTo);
$registerUrl = 'register.php?redirect=' . rawurlencode($returnTo);

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . $returnTo);
    exit;
}

$error = isset($_SESSION['error']) ? (string) $_SESSION['error'] : '';
unset($_SESSION['error']);
$escape = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4efe4">
    <meta name="description" content="Deucritic'te kampüs gündemine katıl, düşünceni paylaş ve topluluğun parçası ol.">
    <title><?= $topic ? $escape($topic['title']) . ' | Deucritic' : 'Topluluğa katıl | Deucritic' ?></title>
    <link rel="icon" href="/gorsel/favicon.ico?v=20261104">
    <link rel="stylesheet" href="/css/brand.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(ellipse at 12% 8%, rgb(233 198 90 / .16), transparent 34rem),
                radial-gradient(ellipse at 95% 92%, rgb(184 72 52 / .10), transparent 32rem),
                #f4efe4 !important;
        }
        .gate-shell {
            box-shadow: 0 32px 90px -42px rgb(29 53 47 / .38), 0 8px 26px rgb(29 53 47 / .06);
        }
        .gate-art {
            background:
                radial-gradient(circle at 85% 12%, rgb(233 198 90 / .3), transparent 14rem),
                linear-gradient(145deg, #1d352f, #31483f 68%, #53645a);
        }
        .gate-art h1, .gate-art .dc-display { color: #fff !important; }
        .gate-art .gate-feature { background: rgb(255 253 247 / .08) !important; }
        .gate-mark {
            box-shadow: 0 14px 36px rgb(0 0 0 / .18);
        }
        .gate-button { transition: transform .18s ease, background .18s ease, box-shadow .18s ease; }
        .gate-button:hover { transform: translateY(-2px); }
        .gate-feature { border: 1px solid rgb(255 253 247 / .18); background: rgb(255 253 247 / .08); }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body class="flex min-h-screen items-center justify-center p-0 text-[#1d352f] sm:p-5">
    <main class="gate-shell grid min-h-[min(760px,100vh)] w-full max-w-6xl overflow-hidden bg-[#fffdf7] sm:min-h-0 sm:rounded-[28px] md:grid-cols-[.92fr_1.08fr]">
        <section class="gate-art relative flex flex-col justify-between overflow-hidden px-6 py-7 text-white sm:px-10 sm:py-9 md:px-12 md:py-11">
            <div class="pointer-events-none absolute -bottom-28 -left-20 h-80 w-80 rounded-full border border-white/10"></div>
            <div class="pointer-events-none absolute -bottom-16 -left-8 h-56 w-56 rounded-full border border-white/10"></div>

            <a href="/index.php" class="relative z-10 inline-flex w-fit items-center gap-3 rounded-xl text-white no-underline" aria-label="Deucritic ana sayfa">
                <span class="gate-mark grid h-12 w-12 place-items-center rounded-2xl bg-[#fffdf7] p-2">
                    <img src="/gorsel/deucritic-mark.svg" alt="" class="h-full w-full object-contain">
                </span>
                <span class="text-xl font-extrabold tracking-tight text-white">Deucritic</span>
            </a>

            <div class="relative z-10 my-10 max-w-lg md:my-0">
                <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold tracking-wide text-[#f4d98a]">
                    <i class="fa-solid fa-lock text-[10px]" aria-hidden="true"></i>
                    DEÜ topluluğuna özel
                </p>
                <h1 class="dc-display max-w-lg text-4xl leading-[1.08] text-white sm:text-5xl">
                    Kampüsün sesi,<br>seninle tamam.
                </h1>
                <p class="mt-5 max-w-md text-sm leading-7 text-white/75 sm:text-base">
                    Gündemi keşfet, farklı fikirlerle tanış ve düşünceni güvenli bir toplulukta paylaş.
                </p>

                <?php if ($topic): ?>
                    <div class="mt-8 rounded-2xl border border-black/15 bg-white/10 p-4 backdrop-blur-sm">
                        <p class="text-[11px] font-bold uppercase tracking-[.16em] text-[#f4d98a]">Devam etmek istediğin konu</p>
                        <p class="mt-2 line-clamp-3 text-lg font-bold leading-snug text-white"><?= $escape($topic['title']) ?></p>
                        <p class="mt-2 text-xs text-white/65">Giriş yaptığında bu konuya geri döneceksin.</p>
                    </div>
                <?php else: ?>
                    <div class="mt-8 grid gap-3 sm:grid-cols-2">
                        <div class="gate-feature rounded-2xl p-4">
                            <span class="mb-3 grid h-9 w-9 place-items-center rounded-xl bg-[#e9c65a] text-[#1d352f]">
                                <i class="fa-solid fa-comments" aria-hidden="true"></i>
                            </span>
                            <p class="font-bold text-white">Gerçek tartışmalar</p>
                            <p class="mt-1 text-xs leading-5 text-white/65">Kampüs gündemine toplulukla birlikte katıl.</p>
                        </div>
                        <div class="gate-feature rounded-2xl p-4">
                            <span class="mb-3 grid h-9 w-9 place-items-center rounded-xl bg-[#d8785d] text-white">
                                <i class="fa-solid fa-user-secret" aria-hidden="true"></i>
                            </span>
                            <p class="font-bold text-white">Paylaşımda tercih senin</p>
                            <p class="mt-1 text-xs leading-5 text-white/65">Uygun alanlarda anonim paylaşım seçeneğini kullan.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <p class="relative z-10 text-xs text-white/50">© <?= date('Y') ?> Deucritic · Fikirlerin için bir topluluk.</p>
        </section>

        <section class="flex flex-col justify-center px-6 py-9 sm:px-10 sm:py-12 md:px-14 lg:px-20">
            <div class="mx-auto w-full max-w-md">
                <nav class="mb-10 flex items-center justify-between gap-4">
                    <span class="text-xs font-bold uppercase tracking-[.15em] text-[#65736b]">Topluluğa katıl</span>
                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <a href="/sss.php" class="text-[#65736b] hover:text-[#b84834]">Yardım</a>
                        <a href="/about.php" class="text-[#65736b] hover:text-[#b84834]">Hakkında</a>
                    </div>
                </nav>

                <?php if ($error !== ''): ?>
                    <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800"><?= $escape($error) ?></div>
                <?php endif; ?>

                <p class="text-xs font-extrabold uppercase tracking-[.18em] text-[#b84834]">Sohbete devam et</p>
                <h2 class="dc-display mt-3 text-3xl leading-tight sm:text-4xl">
                    <?= $topic ? 'Fikrini paylaşmaya hazır mısın?' : 'Gündeme sen de katıl.' ?>
                </h2>
                <p class="mt-4 text-sm leading-6 text-[#65736b]">
                    <?= $topic
                        ? 'Bu konuşmayı okumak ve yanıt yazmak için hesabına giriş yap veya Deucritic’e katıl.'
                        : 'İçerikleri görmek ve konuşmalara katılmak için Deucritic hesabınla devam et.' ?>
                </p>

                <div class="mt-8 space-y-3">
                    <a href="<?= $escape($loginUrl) ?>" class="gate-button flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#1d352f] px-5 py-3 text-sm font-extrabold text-white no-underline shadow-lg shadow-[#1d352f]/15 hover:bg-[#b84834] hover:text-white">
                        Giriş yap
                        <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                    </a>
                    <a href="<?= $escape($registerUrl) ?>" class="gate-button flex min-h-12 w-full items-center justify-center rounded-xl border border-[#ded6c5] bg-white px-5 py-3 text-sm font-bold text-[#1d352f] no-underline hover:border-[#b84834] hover:text-[#b84834]">
                        DEÜ hesabınla kayıt ol
                    </a>
                </div>

                <div class="mt-7 flex items-start gap-3 rounded-xl bg-[#f4efe4] p-4">
                    <i class="fa-solid fa-shield-halved mt-0.5 text-[#b84834]" aria-hidden="true"></i>
                    <p class="text-xs leading-5 text-[#65736b]">Topluluk güvenliği için kayıt sırasında DEÜ e-posta adresi gerekir. Kişisel bilgilerini paylaşmadan önce görünürlük seçeneklerini kontrol et.</p>
                </div>

                <p class="mt-8 text-center text-xs text-[#65736b]">
                    Bir sorunun mu var?
                    <a href="/sss.php" class="font-bold text-[#b84834] underline underline-offset-2">Yardım merkezine göz at</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
