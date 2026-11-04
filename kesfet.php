<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keşfet | Deucritic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        body { 
            background-color: #f2f4f6; 
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .deu-card {
            background: #ffffff;
            border-radius: 50px;
            box-shadow: 0 30px 60px -12px rgba(0,0,0,0.05);
        }

        .logic-card {
            background: #f8fafc;
            border-radius: 32px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .logic-card:hover {
            background: #ffffff;
            border-color: #f8981d;
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(248, 152, 29, 0.1);
        }

        .orange-button {
            background: #1a1a1a;
            transition: all 0.3s ease;
        }

        .orange-button:hover {
            background: #f8981d;
            transform: scale(1.05);
        }

        /* Arka plan dalgalanma efekti */
        .bg-pattern {
            background-image: radial-gradient(#d1d5db 1px, transparent 1px);
            background-size: 30px 30px;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-pattern">

    <div class="max-w-6xl w-full">
        <div class="deu-card p-10 md:p-20 relative overflow-hidden">
            
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#f8981d]/10 to-transparent rounded-full -mr-20 -mt-20 blur-3xl"></div>

            <div class="relative z-10 text-center">
                <div class="inline-flex items-center gap-2 bg-slate-100 px-5 py-2 rounded-full mb-8">
                    <span class="w-2 h-2 bg-[#f8981d] rounded-full animate-pulse"></span>
                    <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-[0.2em]">Kolektif Zeka Yakında</span>
                </div>

                <h1 class="text-5xl md:text-7xl font-[800] text-[#1a1a1a] mb-8 leading-[1.1] tracking-tight">
                    Kampüsün Nabzı <br>
                    <span class="text-[#f8981d]">Tek Bir Fokus</span> Noktasında.
                </h1>

                <p class="text-slate-500 text-lg md:text-xl max-w-3xl mx-auto mb-16 leading-relaxed">
                    Sıradan bir akış değil; Dokuz Eylül’ün akademik ağırlığını, sosyal enerjisini ve öğrenci deneyimini harmanlayan **Deucritic Engine** ile tanışmaya hazır ol.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20 text-left">
                    
                    <div class="logic-card p-8">
                        <div class="text-[#f8981d] text-3xl mb-6"><i class="fas fa-layer-group"></i></div>
                        <h3 class="text-xl font-bold text-[#1a1a1a] mb-3">Hiper-Lokal Filtreleme</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Buca, Tınaztepe veya Balçova... Sadece senin kampüsünde ne olup bittiğini, hangi mekanın parladığını süzerek getiriyoruz.
                        </p>
                    </div>

                    <div class="logic-card p-8">
                        <div class="text-[#f8981d] text-3xl mb-6"><i class="fas fa-bolt"></i></div>
                        <h3 class="text-xl font-bold text-[#1a1a1a] mb-3">Onep Trendleri</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Topluluğun en çok yanıt verdiği, vizelerden daha fazla konuşulan o "kritik" konuları algoritmanın en tepesine taşıyoruz.
                        </p>
                    </div>

                    <div class="logic-card p-8">
                        <div class="text-[#f8981d] text-3xl mb-6"><i class="fas fa-fingerprint"></i></div>
                        <h3 class="text-xl font-bold text-[#1a1a1a] mb-3">Akademik Feedback</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Dersler, hocalar ve kampüs hayatı hakkında en "gerçek" yorumları, reklam karmaşasına girmeden keşfetmeni sağlıyoruz.
                        </p>
                    </div>

                </div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-8 pt-10 border-t border-slate-50">
                    <a href="index.php" class="orange-button text-white px-12 py-4 rounded-[20px] font-bold shadow-xl shadow-orange-200/20">
                        Ana Sayfaya Dön
                    </a>

                    <div class="flex items-center gap-8">
                        <a href="https://instagram.com/eniscagri" target="_blank" class="flex flex-col items-end group">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-[#f8981d] transition">Lead Developer</span>
                            <span class="text-sm font-bold text-slate-800">@eniscagri</span>
                        </a>
                        
                        <a href="https://instagram.com/deucritic" target="_blank" class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-slate-400 hover:text-[#f8981d] hover:shadow-md transition-all border border-slate-50">
                            <i class="fab fa-instagram text-2xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
       <?php include 'footer.php'; ?>
    </div>

</body>
</html>