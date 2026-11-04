<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <title>Deucritic — Maintenance</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <style>
        *{
            -webkit-tap-highlight-color: transparent;
        }

        body{
            font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Inter",sans-serif;
            overflow:hidden;
            background:#050505;
        }

        .noise{
            position:absolute;
            inset:0;
            opacity:.035;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'%3E%3Cg fill='%23ffffff'%3E%3Ccircle cx='3' cy='3' r='1'/%3E%3C/g%3E%3C/svg%3E");
        }

        .grid{
            background-image:
            linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);

            background-size:42px 42px;
        }

        .glass{
            background:rgba(255,255,255,.06);
            backdrop-filter:blur(30px);
            -webkit-backdrop-filter:blur(30px);
            border:1px solid rgba(255,255,255,.08);
            box-shadow:
            0 10px 40px rgba(0,0,0,.45),
            inset 0 1px 0 rgba(255,255,255,.04);
        }

        .glow{
            filter:blur(120px);
        }

        .logo-glow{
            box-shadow:
            0 0 50px rgba(255,255,255,.08),
            inset 0 1px 0 rgba(255,255,255,.08);
        }

        .line{
            background:linear-gradient(
                90deg,
                transparent,
                rgba(255,255,255,.12),
                transparent
            );
        }

        .status-dot{
            box-shadow:0 0 18px rgba(74,222,128,.9);
        }

        .fade-up{
            animation:fadeUp 1s ease forwards;
        }

        @keyframes fadeUp{
            from{
                opacity:0;
                transform:translateY(16px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }
    </style>
    <link rel="stylesheet" href="/css/brand.css">
</head>

<body class="min-h-screen text-white flex items-center justify-center px-5 relative">

    <!-- Background -->
    <div class="absolute inset-0 overflow-hidden">

        <div class="absolute top-[-180px] left-[-120px] w-[420px] h-[420px] rounded-full bg-zinc-500/10 glow"></div>

        <div class="absolute bottom-[-180px] right-[-120px] w-[420px] h-[420px] rounded-full bg-white/5 glow"></div>

        <div class="absolute inset-0 grid opacity-30"></div>

        <div class="noise"></div>

    </div>

    <!-- Content -->
    <div class="relative z-10 w-full max-w-md fade-up">

        <!-- Top -->
        <div class="flex justify-center mb-8">

            <div class="glass rounded-[30px] px-6 py-5 w-full">

                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-4">

                        <div class="w-14 h-14 rounded-[22px] bg-white text-black flex items-center justify-center text-2xl font-bold logo-glow">
                            D
                        </div>

                        <div>

                            <div class="text-[21px] font-semibold tracking-tight">
                                Deucritic
                            </div>

                            <div class="text-sm text-white/40">
                                Anonymous Student Network
                            </div>

                        </div>

                    </div>

                    <div class="flex items-center gap-2">

                        <div class="w-2.5 h-2.5 rounded-full bg-green-400 status-dot animate-pulse"></div>

                        <span class="text-xs text-white/45">
                            Live Maintenance
                        </span>

                    </div>

                </div>

            </div>

        </div>

        <!-- Main Card -->
        <div class="glass rounded-[36px] p-7 relative overflow-hidden">

            <div class="absolute top-0 left-0 right-0 h-px line"></div>

            <div class="mb-6">

                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-white/10 bg-white/5 text-xs text-white/50 mb-5">

                    <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>

                    Platform is being upgraded

                </div>

                <h1 class="text-[38px] leading-[1] tracking-[-0.03em] font-semibold mb-5">
                    Daha hızlı.<br>
                    Daha stabil.<br>
                    Daha modern.
                </h1>

                <p class="text-[15px] leading-7 text-white/50">
                    Deucritic’i Dokuz Eylül öğrencileri için daha akıcı,
                    daha güvenli ve daha premium hale getiriyoruz.
                </p>

            </div>

            <!-- Divider -->
            <div class="h-px bg-white/10 mb-6"></div>

            <!-- System -->
            <div class="space-y-4">

                <div class="flex items-center justify-between">

                    <div>
                        <div class="text-sm font-medium">
                            Sistem Güncellemesi
                        </div>

                        <div class="text-xs text-white/35 mt-1">
                            Sunucu optimizasyonları uygulanıyor
                        </div>
                    </div>

                    <div class="text-sm text-green-400">
                        Aktif
                    </div>

                </div>

                <div class="flex items-center justify-between">

                    <div>
                        <div class="text-sm font-medium">
                            Altyapı İyileştirmeleri
                        </div>

                        <div class="text-xs text-white/35 mt-1">
                            Performans sistemi güncelleniyor
                        </div>
                    </div>

                    <div class="text-sm text-yellow-300">
                        Devam ediyor
                    </div>

                </div>

                <div class="flex items-center justify-between">

                    <div>
                        <div class="text-sm font-medium">
                            Güvenlik Katmanı
                        </div>

                        <div class="text-xs text-white/35 mt-1">
                            Koruma sistemleri optimize ediliyor
                        </div>
                    </div>

                    <div class="text-sm text-green-400">
                        Aktif
                    </div>

                </div>

            </div>

        </div>

        <!-- Footer -->
        <div class="mt-7 flex items-center justify-center gap-2 text-xs text-white/25">

            <span>© 2026 Deucritic</span>

            <span>•</span>

            <span>Sadece DEÜ öğrencileri için</span>

        </div>

    </div>

</body>
</html>