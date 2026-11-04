<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>eniscagricom Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 text-slate-800 font-sans">

<div class="flex h-screen overflow-hidden">

  <!-- SIDEBAR -->
  <aside class="w-72 bg-white border-r border-slate-200 flex flex-col">

    <!-- Logo -->
    <div class="p-6 flex items-center gap-3">
      <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-black text-lg shadow-lg">
        G
      </div>
      <span class="text-xl font-bold tracking-tight">eniscagricom</span>
    </div>

    <!-- Nav -->
    <nav class="px-4 space-y-2">
      <a class="flex items-center gap-3 px-4 py-3 bg-indigo-50 text-indigo-600 rounded-xl font-semibold">
        Dashboard
      </a>
      <a class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-100 rounded-xl transition">
        Mülkler
      </a>
      <a class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-100 rounded-xl transition">
        Analiz
      </a>
    </nav>

    <!-- Bottom Card -->
   <div class="mt-auto p-4">
  <div class="p-5 rounded-[2rem] bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-2xl border border-slate-700/50">
    
    <!-- Deaktif Bölümü -->
    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 italic">Deaktif Sunucular</p>
    <ul class="mt-2 space-y-1 opacity-50">
      <li class="text-[10px] text-slate-400 flex items-center gap-2">
        <span class="h-1 w-1 bg-rose-500 rounded-full"></span>
        HPE ML110 Gen11 3408U 32/2x480G
      </li>
      <li class="text-[10px] text-slate-400 flex items-center gap-2">
        <span class="h-1 w-1 bg-rose-500 rounded-full"></span>
        HPE ML350 Gen11 4510 64/2×960
      </li>
    </ul>

    <div class="h-px bg-slate-700/50 my-4"></div>

    <!-- Aktif Bölümü -->
    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500 italic">Aktif Altyapı (Live)</p>
    <ul class="mt-2 space-y-2">
      <li class="text-[10px] text-slate-300 flex justify-between items-center group">
        <span class="flex items-center gap-2 font-medium">
          <span class="h-1 w-1 bg-emerald-500 rounded-full animate-pulse"></span>
          Dell PowerEdge R760 2x Silver 4410Y
        </span>
     
      </li>
      
      <li class="text-[10px] text-slate-300 flex justify-between items-center group">
        <span class="flex items-center gap-2 font-medium">
          <span class="h-1 w-1 bg-emerald-500 rounded-full animate-pulse"></span>
          HPE ProLiant DL380 Gen11 4410Y 64GB
        </span>

      </li>

      <li class="text-[10px] text-slate-300 flex justify-between items-center group">
        <span class="flex items-center gap-2 font-medium">
          <span class="h-1 w-1 bg-emerald-500 rounded-full animate-pulse"></span>
          Supermicro Hyper SuperServer 221H
        </span>
       
      </li>

      <li class="text-[10px] text-slate-300 flex justify-between items-center group">
        <span class="flex items-center gap-2 font-medium">
          <span class="h-1 w-1 bg-emerald-500 rounded-full animate-pulse"></span>
          Lenovo ThinkSystem SR650 V3 32-Core
        </span>
     
      </li>

      <li class="text-[10px] text-slate-300 flex justify-between items-center group">
        <span class="flex items-center gap-2 font-medium">
          <span class="h-1 w-1 bg-emerald-500 rounded-full animate-pulse"></span>
          Dell PowerEdge R660 High-Density
        </span>
      
      </li>

      <li class="text-[10px] text-slate-300 flex justify-between items-center group">
        <span class="flex items-center gap-2 font-medium">
          <span class="h-1 w-1 bg-emerald-500 rounded-full animate-pulse"></span>
          HPE DL360 Gen11 Gold 6414U 128GB
        </span>
      
      </li>

      <li class="text-[10px] text-slate-300 flex justify-between items-center group">
        <span class="flex items-center gap-2 font-medium">
          <span class="h-1 w-1 bg-emerald-500 rounded-full animate-pulse"></span>
          Cisco UCS C240 M7 SFF Server
        </span>
       
      </li>
    </ul>


  </div>
</div>

  </aside>


  <!-- MAIN -->
  <div class="flex-1 flex flex-col overflow-hidden">

    <!-- HEADER -->
    <header class="h-20 bg-white/70 backdrop-blur border-b border-slate-200 flex items-center justify-between px-8">

      <div>
        <h1 class="text-2xl font-bold">Ağ Genel Bakışı</h1>
        <p class="text-xs text-slate-400">Son güncelleme: şimdi</p>
      </div>

      <div class="flex items-center gap-4">

        <div class="relative">
          <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          🔔
        </div>

        <div class="flex items-center gap-3">
          <div class="text-right">
            <p class="text-sm font-bold">Enis Çağrı</p>
            <p class="text-xs text-emerald-500">ADMIN</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-indigo-500"></div>
        </div>

      </div>

    </header>


    <!-- CONTENT -->
    <main class="p-8 overflow-y-auto space-y-8">

      <!-- STATS -->
   <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

  <!-- Toplam Trafik Kartı -->
  <div class="relative bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500 group overflow-hidden">
    <div class="relative z-10">
      <div class="flex justify-between items-start">
        <p class="text-xs font-black uppercase tracking-[0.15em] text-slate-400">Toplam Kullanıcı Sayısı</p>
        <div class="p-2 bg-indigo-50 rounded-xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        </div>
      </div>
      <h2 class="text-4xl font-black mt-3 tracking-tighter text-slate-900 italic">759<span class="text-xl text-slate-600 ml-1">BİN</span></h2>
      <div class="flex items-center gap-2 mt-4">
        <span class="flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded-full border border-emerald-100">
          ↑ 8.2%
        </span>
        <span class="text-[10px] text-slate-400 font-medium italic">vs son 30 gün</span>
      </div>
    </div>
    <!-- Dekoratif Arka Plan Efekti -->
    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
  </div>

  <!-- Adsense Gelir Kartı (Maskelenmiş) -->
  <div class="relative bg-slate-900 p-6 rounded-[2.5rem] shadow-2xl transition-all duration-500 group overflow-hidden border border-slate-800">
    <div class="relative z-10">
      <div class="flex justify-between items-start">
        <p class="text-xs font-black uppercase tracking-[0.15em] text-indigo-400/80">Net Kazanç</p>
        <div class="p-2 bg-white/5 rounded-xl text-indigo-400 border border-white/10 group-hover:border-indigo-500/50 transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
      </div>
      <!-- Gizli Gelir Görünümü -->
      <h2 class="text-4xl font-black mt-3 tracking-tighter text-white italic"><span class="blur-[7px] select-none">3.332.967</span><span class="text-xl text-white/40 ml-1">TL</span></h2>
      <div class="mt-4 flex items-center gap-2">
        <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
          <div class="h-full bg-indigo-500 w-[72%] shadow-[0_0_12px_rgba(99,102,241,0.5)]"></div>
        </div>
        <span class="text-[10px] font-bold text-indigo-400 italic">Target</span>
      </div>
    </div>
    <!-- Mesh Gradient Arka Plan -->
    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-600/10 rounded-full blur-[60px]"></div>
  </div>

  <!-- Aktif Domain Kartı -->
  <div class="relative bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-500 group">
    <div class="flex justify-between items-start">
      <p class="text-xs font-black uppercase tracking-[0.15em] text-slate-400">Aktif Mülkler</p>
      <span class="px-2 py-1 bg-slate-900 text-white text-[10px] font-black rounded-lg italic shadow-lg">Canlı</span>
    </div>
    <h2 class="text-4xl font-black mt-3 tracking-tighter text-slate-900 italic">18<span class="text-sm text-slate-300 ml-2 font-bold uppercase tracking-widest">Domains</span></h2>
    <div class="mt-4 flex -space-x-2">
      <!-- Küçük Domain Simgeleri/Placeholder -->
      <div class="h-6 w-6 rounded-full border-2 border-white bg-indigo-100 flex items-center justify-center text-[8px] font-black">M</div>
      <div class="h-6 w-6 rounded-full border-2 border-white bg-rose-100 flex items-center justify-center text-[8px] font-black">G</div>
      <div class="h-6 w-6 rounded-full border-2 border-white bg-emerald-100 flex items-center justify-center text-[8px] font-black">T</div>
      <div class="h-6 w-6 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[8px] font-black italic">+15</div>
    </div>
  </div>

  <!-- CTR Kartı -->
  <div class="relative bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-500 group">
    <div class="flex justify-between items-start">
      <p class="text-xs font-black uppercase tracking-[0.15em] text-slate-400">Reklam Performansları</p>
      <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
    </div>
    <div class="flex items-end gap-2 mt-3">
      <h2 class="text-4xl font-black tracking-tighter text-slate-900 italic">3.4<span class="text-xl text-slate-300 ml-0.5">%</span></h2>
      <p class="text-[10px] font-bold text-emerald-500 mb-1.5 uppercase italic">Optimal</p>
    </div>
    <div class="mt-4 flex gap-1 items-end h-6">
      <!-- Küçük Sparkline Grafiği (Basit CSS) -->
      <div class="flex-1 bg-slate-50 h-3 rounded-sm group-hover:h-5 transition-all duration-500"></div>
      <div class="flex-1 bg-slate-50 h-4 rounded-sm group-hover:h-2 transition-all duration-500 delay-75"></div>
      <div class="flex-1 bg-indigo-100 h-2 rounded-sm group-hover:h-6 transition-all duration-500 delay-100"></div>
      <div class="flex-1 bg-indigo-500 h-5 rounded-sm group-hover:h-3 transition-all duration-500 delay-150"></div>
    </div>
  </div>

</div>


      <!-- TABLE -->
      <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

        <div class="p-6 flex justify-between items-center">
          <h3 class="font-bold text-lg">Domain Performansı</h3>
          <input placeholder="Ara..." class="px-4 py-2 bg-slate-100 rounded-xl text-sm outline-none">
        </div>

        <table class="w-full text-sm">
          <thead class="text-slate-400 text-xs uppercase">
            <tr>
              <th class="p-4 text-left">Domain</th>
              <th>Kullanıcı</th>
              <th>Erkek</th>
              <th>Kadın</th>
               <th style="text-left">Son 15 Gün</th>
                <th>Durum</th>
            </tr>
          </thead>

          <tbody>

<tbody class="divide-y divide-slate-100">

  <!-- 1. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">me<span class="opacity-20 text-xs">*****</span>.com</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">312.450</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">140.600 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">171.850 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5 text-center font-black italic text-slate-900 tracking-tighter">12.***.** TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-bold uppercase tracking-widest">High</span>
    </td>
  </tr>

  <!-- 2. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">go<span class="opacity-20 text-xs">*****</span>.net</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">285.120</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">190.030 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">95.090 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5  text-center font-black italic text-slate-900 tracking-tighter">9.***.** TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-bold uppercase tracking-widest">High</span>
    </td>
  </tr>

  <!-- 3. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">ti<span class="opacity-20 text-xs">*****</span>.com</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">154.300</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">72.500 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">81.800 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5 text-center  font-black italic text-slate-900 tracking-tighter">4.***.** TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-bold uppercase tracking-widest">Stable</span>
    </td>
  </tr>

  <!-- 4. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">bl<span class="opacity-20 text-xs">*****</span>.com</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">210.900</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">105.450 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">105.450 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5  text-center font-black italic text-slate-900 tracking-tighter">7.***.** TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-bold uppercase tracking-widest">High</span>
    </td>
  </tr>

  <!-- 5. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">xa<span class="opacity-20 text-xs">*****</span>.com</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">89.400</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">34.000 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">55.400 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5 text-center   font-black italic text-slate-900 tracking-tighter">2.***.** TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-full text-[10px] font-bold uppercase tracking-widest">Low</span>
    </td>
  </tr>

  <!-- 6. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">ne<span class="opacity-20 text-xs">*****</span>.com</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">342.800</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">168.000 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">174.800 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5 text-center  font-black italic text-slate-900 tracking-tighter">15.***.** TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-bold uppercase tracking-widest">Peak</span>
    </td>
  </tr>

  <!-- 7. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">so<span class="opacity-20 text-xs">*****</span>.com</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">225.000</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">110.250 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">114.750 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5 text-center  font-black italic text-slate-900 tracking-tighter">8.***.** TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-bold uppercase tracking-widest">High</span>
    </td>
  </tr>

  <!-- 8. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">li<span class="opacity-20 text-xs">*****</span>.net</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">198.650</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">89.300 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">109.350 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5 text-center  font-black italic text-slate-900 tracking-tighter">6.***.** TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-bold uppercase tracking-widest">Stable</span>
    </td>
  </tr>

  <!-- 9. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">fa<span class="opacity-20 text-xs">*****</span>.org</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">267.300</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">147.015 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">120.285 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5  text-center font-black italic text-slate-900 tracking-tighter">10.***.** TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-bold uppercase tracking-widest">High</span>
    </td>
  </tr>

  <!-- 10. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">ve<span class="opacity-20 text-xs">*****</span>.com</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">115.200</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">60.000 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">55.200 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5  text-center font-black italic text-slate-900 tracking-tighter">3.***.** TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-full text-[10px] font-bold uppercase tracking-widest">Low</span>
    </td>
  </tr>

  <!-- 11. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">te<span class="opacity-20 text-xs">*****</span>.me</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">290.450</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">145.225 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">145.225 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5  text-center font-black italic text-slate-900 tracking-tighter">11.340.00 TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-bold uppercase tracking-widest">High</span>
    </td>
  </tr>

  <!-- 12. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">ar<span class="opacity-20 text-xs">*****</span>.com</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">142.100</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">71.050 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">71.050 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5  text-center font-black italic text-slate-900 tracking-tighter">5.200.10 TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-bold uppercase tracking-widest">Stable</span>
    </td>
  </tr>

  <!-- 13. Domain -->
  <tr class="group bg-white hover:bg-indigo-50/30 transition-all duration-300">
    <td class="px-6 py-5 font-mono font-bold text-slate-700">ze<span class="opacity-20 text-xs">*****</span>.tv</td>
    <td class="px-6 py-5 text-center font-semibold text-slate-600 italic">206.330</td>
    <td class="px-6 py-5 text-center text-blue-500 font-medium">98.440 <span class="text-[10px] text-slate-300 ml-1">♂</span></td>
    <td class="px-6 py-5 text-center text-rose-400 font-medium">107.890 <span class="text-[10px] text-slate-300 ml-1">♀</span></td>
    <td class="px-6 py-5 text-center  font-black italic text-slate-900 tracking-tighter">7.950.00 TL</td>
    <td class="px-6 py-5 text-center">
      <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-bold uppercase tracking-widest">High</span>
    </td>
  </tr>
<tr class="bg-slate-950 text-white font-bold relative overflow-hidden group">
  <!-- Sol Bölüm: Başlık ve Marka -->
  <td class="px-8 py-8 rounded-bl-[2.5rem] relative z-10 border-r border-white/5">
    <div class="flex flex-col">
      <div class="flex items-center gap-2 mb-1">
        <span class="h-2 w-2 rounded-full bg-indigo-500 shadow-[0_0_8px_#6366f1]"></span>
        <span class="text-indigo-400 text-[10px] uppercase tracking-[0.3em] font-black">Global Analytics</span>
      </div>
      <span class="text-2xl tracking-tighter italic font-black">Ağ Toplamı</span>
    </div>
  </td>

  <!-- Orta Bölüm: Kullanıcı Dağılımı (Kritik Veri) -->
  <td class="px-8 py-8 text-center relative z-10 border-r border-white/5">
    <div class="flex flex-col items-center">
      <span class="text-3xl tracking-tighter font-black italic text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400">
        2.840.000
      </span>
      <div class="flex gap-3 mt-3">
        <!-- Enis Çağrı Mülkleri -->
        <div class="flex flex-col items-start px-3 py-1.5 bg-indigo-500/10 rounded-xl border border-indigo-500/20">
          <span class="text-[9px] text-indigo-400 uppercase font-black tracking-tighter">eniscagri Properties</span>
          <span class="text-sm font-bold tabular-nums italic">759.311</span>
        </div>
        <!-- Müşteri/Dış Trafik -->
        <div class="flex flex-col items-start px-3 py-1.5 bg-slate-800/40 rounded-xl border border-slate-700/30">
          <span class="text-[9px] text-slate-500 uppercase font-black tracking-tighter">Global Clients</span>
          <span class="text-sm font-bold tabular-nums italic text-slate-300">2.080.689</span>
        </div>
      </div>
    </div>
  </td>

  <!-- Orta Alan: Sistem Notu -->
  <td colspan="2" class="px-8 py-8 text-center relative z-10 border-r border-white/5">
    <div class="opacity-30 group-hover:opacity-60 transition-opacity duration-700">
      <p class="text-[10px] uppercase tracking-[0.4em] font-light italic">Database Synchronized</p>
      <div class="mt-2 flex justify-center gap-1">
        <div class="w-8 h-0.5 bg-indigo-500"></div>
        <div class="w-4 h-0.5 bg-slate-600"></div>
        <div class="w-2 h-0.5 bg-slate-800"></div>
      </div>
    </div>
  </td>

  <!-- Sağ Bölüm: Finansal Özet -->
  <td class="px-8 py-8 text-right relative z-10">
    <div class="flex flex-col items-end">
      <span class="text-[10px] text-emerald-500 font-black uppercase tracking-widest mb-1">Total Revenue</span>
      <div class="flex items-baseline gap-1">
        <span class="text-3xl italic text-emerald-400 tracking-tighter font-black">3.105.332,80</span>
        <span class="text-xs font-bold text-emerald-600/60 uppercase">TL</span>
      </div>
    </div>
  </td>

  <!-- Sağ Köşe Süsleme -->
  <td class="px-4 py-8 rounded-br-[2.5rem]"></td>

  <!-- Arka Plan Glow Efekti -->
  <div class="absolute -left-20 top-0 w-64 h-full bg-indigo-600/10 blur-[80px] pointer-events-none"></div>
</tr>
</tbody>

          </tbody>
        </table>

      </div>

    </main>

  </div>

</div>

</body>
</html>