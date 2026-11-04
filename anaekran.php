<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deucritic'i Ana Ekrana Ekleme Rehberi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind konfigürasyonu (isteğe bağlı, özel renkler için)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'deucritic-primary': '#3b82f6', // Örnek ana renk (mavi)
                        'deucritic-dark': '#1e293b',    // Örnek koyu renk
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800">

    <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1 text-sm font-semibold text-deucritic-primary bg-blue-100 rounded-full mb-3">
                İpucu & Rehber
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-deucritic-dark tracking-tight mb-4">
                Uygulama İndirmeden <span class="text-deucritic-primary">Deucritic'i</span> Cebine Taşı!
            </span></h1>
            <p class="max-w-2xl mx-auto text-xl text-gray-600">
                Hem Android hem de iOS cihazınızda, sanki bir uygulama gibi deucritic.com'a ana ekranınızdan tek tıkla ulaşabilirsiniz. İşte adım adım nasıl yapacağınız:
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">

            <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 transform transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
                <div class="flex items-center mb-6">
                    <div class="p-3 bg-deucritic-dark text-white rounded-xl mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m17.432 0C18.42 18.02 15.42 19.5 12 19.5c-3.42 0-6.42-1.48-8.716-4.247m17.432 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918"></path></svg>
                    </div>
                    <h2 class="text-3xl font-bold text-deucritic-dark">iOS (Apple) Cihazlar için</h2>
                </div>
                <p class="text-gray-600 mb-8 italic">Safari tarayıcısını kullanarak bu işlemi yapmalısınız.</p>
                <ol class="space-y-6 text-lg text-gray-700 list-decimal list-inside marker:text-deucritic-primary marker:font-bold">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-blue-50 text-deucritic-primary font-bold rounded-full mr-4 mt-0.5">1</span>
                        <span>Cihazınızda <strong>Safari</strong> tarayıcısını açın ve <strong>deucritic.com</strong>'a gidin.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-blue-50 text-deucritic-primary font-bold rounded-full mr-4 mt-0.5">2</span>
                        <span>Tarayıcının alt kısmında bulunan <strong>"Paylaş"</strong> simgesine dokunun (yukarı doğru ok bulunan bir kare).</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-blue-50 text-deucritic-primary font-bold rounded-full mr-4 mt-0.5">3</span>
                        <span>Açılan menüde yukarı doğru kaydırın ve <strong>"Ana Ekrana Ekle"</strong> seçeneğini bulun ve dokunun.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-blue-50 text-deucritic-primary font-bold rounded-full mr-4 mt-0.5">4</span>
                        <span>İsterseniz kısayol adını düzenleyin ve sağ üst köşedeki <strong>"Ekle"</strong> butonuna basın.</span>
                    </li>
                </ol>
                <div class="mt-10 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <p class="text-sm text-gray-500">
                        <strong class="text-gray-700">Sonuç:</strong> Deucritic logosu, diğer uygulamalarınızın yanında ana ekranınızda belirecektir.
                    </p>
                </div>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 transform transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
                <div class="flex items-center mb-6">
                    <div class="p-3 bg-green-500 text-white rounded-xl mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 002-2H7a2 2 0 00-2 2v14a2 2 0 002 2zM9 5a1 1 0 100 2h6a1 1 0 100-2H9z"></path></svg>
                    </div>
                    <h2 class="text-3xl font-bold text-deucritic-dark">Android Cihazlar için</h2>
                </div>
                <p class="text-gray-600 mb-8 italic">Google Chrome tarayıcısını kullanarak bu işlemi yapmalısınız.</p>
                <ol class="space-y-6 text-lg text-gray-700 list-decimal list-inside marker:text-green-600 marker:font-bold">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-green-50 text-green-600 font-bold rounded-full mr-4 mt-0.5">1</span>
                        <span>Cihazınızda <strong>Google Chrome</strong> tarayıcısını açın ve <strong>deucritic.com</strong>'a gidin.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-green-50 text-green-600 font-bold rounded-full mr-4 mt-0.5">2</span>
                        <span>Tarayıcının sağ üst köşesindeki <strong>üç noktaya</strong> (...) dokunun.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-green-50 text-green-600 font-bold rounded-full mr-4 mt-0.5">3</span>
                        <span>Menüden <strong>"Ana Ekrana Ekle"</strong> seçeneğine dokunun.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-green-50 text-green-600 font-bold rounded-full mr-4 mt-0.5">4</span>
                        <span>Açılan pencerede kısayol adını doğrulayın ve <strong>"Ekle"</strong> butonuna basın. Ardından, isterseniz manuel olarak yerleştirin veya "Otomatik olarak ekle"ye dokunun.</span>
                    </li>
                </ol>
                <div class="mt-10 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <p class="text-sm text-gray-500">
                        <strong class="text-gray-700">Sonuç:</strong> Deucritic simgesi, ana ekranınızda veya uygulama çekmecenizde bir uygulama gibi görünecektir.
                    </p>
                </div>
            </div>

        </div>

        <div class="mt-20 text-center bg-deucritic-dark p-12 rounded-3xl text-white shadow-xl">
            <h3 class="text-2xl font-semibold mb-4">Neden Bu Yöntem?</h3>
            <p class="max-w-3xl mx-auto text-lg text-gray-300">
                Uygulama indirmeden bu yöntemi kullanmak, cihazınızda depolama alanı tasarrufu sağlar, güncellemelerle uğraşmanıza gerek kalmaz ve web sitemizin en güncel sürümüne her zaman hızlıca erişmenizi sağlar.
            </p>
            <a href="https://deucritic.com" class="inline-block mt-8 px-8 py-3 bg-deucritic-primary text-white font-semibold rounded-xl transition hover:bg-blue-600 shadow-md">
                Deucritic'e Git
            </a>
        </div>

    </div>

</body>
</html>