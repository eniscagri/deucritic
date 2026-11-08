<?php
// DeuCritic promotional landing page. Upload this file to the site root as tanitim.php.
?><!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DeuCritic — Kampüs konuşuyor. Söz sende.</title>
  <meta name="description" content="DeuCritic, Dokuz Eylül Üniversitesi öğrencilerinin fikirlerini, deneyimlerini ve kampüs yaşamını paylaşabildiği bağımsız bir öğrenci topluluğudur.">
  <meta property="og:title" content="DeuCritic — Kampüs konuşuyor. Söz sende.">
  <meta property="og:description" content="Fikirlerini paylaş, kampüs sohbetlerini keşfet ve topluluğun bir parçası ol.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://deucritic.com/tanitim.php">
  <meta name="theme-color" content="#f4f0e6">
  <style>
    :root{--cream:#f4f0e6;--paper:#fffdf8;--green:#1b3932;--green2:#24483e;--red:#bd5038;--gold:#d5b94a;--navy:#101b3a;--blue:#526fe8;--ink:#1d2d28;--muted:#68736d;--line:#e3dccd;--shadow:0 20px 55px rgba(25,48,40,.12)}
    *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--cream);color:var(--ink);font-family:Inter,"Aptos",Arial,sans-serif;-webkit-font-smoothing:antialiased}a{color:inherit;text-decoration:none}button{font:inherit}.wrap{width:min(1160px,calc(100% - 44px));margin:auto}.topbar{height:82px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(27,57,50,.12)}.brand{display:flex;align-items:center;gap:12px;font-weight:750;font-size:20px;letter-spacing:-.6px}.brand-mark{width:42px;height:42px;border-radius:12px;background:var(--green);display:grid;place-items:center;color:var(--gold);font:700 34px/1 Georgia,serif}.brand small{display:block;margin-top:2px;color:var(--red);font-size:9px;letter-spacing:3px}.nav{display:flex;align-items:center;gap:26px;color:#53635c;font-size:14px}.nav a:hover{color:var(--red)}.nav-actions{display:flex;align-items:center;gap:10px}.lang{border:1px solid var(--line);border-radius:999px;background:transparent;padding:9px 12px;color:var(--green);font-weight:700;font-size:12px;cursor:pointer}.lang.active{background:var(--green);color:#fff}.join{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:12px 18px;border-radius:12px;background:var(--green);color:white;font-weight:700;font-size:14px;box-shadow:0 7px 18px rgba(27,57,50,.14)}.join:hover{background:var(--green2);transform:translateY(-1px)}
    .hero{padding:74px 0 82px;display:grid;grid-template-columns:1fr .96fr;gap:58px;align-items:center}.eyebrow{display:inline-flex;gap:9px;align-items:center;color:var(--red);font-size:11px;font-weight:800;letter-spacing:2px;text-transform:uppercase}.eyebrow:before{content:"";width:8px;height:8px;border-radius:50%;background:var(--red)}h1,h2,h3,p{margin-top:0}.hero h1{font:700 clamp(48px,6.3vw,76px)/1.02 Georgia,"Times New Roman",serif;letter-spacing:-3px;color:var(--green);margin:22px 0 22px}.hero h1 span{color:var(--red);font-style:italic}.lead{max-width:510px;font-size:18px;line-height:1.7;color:#66716a}.hero-actions{display:flex;align-items:center;gap:20px;margin-top:31px}.textlink{font-size:14px;font-weight:700;color:var(--green)}.textlink span{color:var(--red);margin-left:6px}.independent{margin-top:28px;color:#788078;font-size:12px;line-height:1.6}.visual{position:relative;border-radius:26px;padding:13px;background:#e7e0d2;border:1px solid #ded6c5;box-shadow:var(--shadow);transform:rotate(1deg)}.visual img{display:block;width:100%;height:auto;border-radius:17px}.visual-tag{position:absolute;left:28px;bottom:25px;background:rgba(255,253,248,.94);padding:10px 14px;border-radius:999px;color:var(--green);font-size:11px;font-weight:750;box-shadow:0 5px 18px #101b3a22}
    .proof{border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:23px 0;display:flex;justify-content:center;gap:45px;flex-wrap:wrap;color:var(--muted);font-size:13px}.proof span{display:flex;align-items:center;gap:9px}.proof b{color:var(--red);font-size:18px}
    .section{padding:96px 0}.section-head{text-align:center;max-width:690px;margin:0 auto 45px}.section-head .eyebrow{justify-content:center}.section h2{font:700 clamp(36px,4.5vw,52px)/1.12 Georgia,"Times New Roman",serif;letter-spacing:-1.8px;color:var(--green);margin:16px 0}.section-head p{font-size:16px;line-height:1.7;color:var(--muted);margin-bottom:0}.features{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}.feature{background:var(--paper);border:1px solid var(--line);border-radius:18px;padding:25px 22px 24px;min-height:211px;box-shadow:0 8px 25px rgba(25,48,40,.035)}.icon{width:46px;height:46px;display:grid;place-items:center;border-radius:14px;background:#f1eee4;color:var(--red);font-size:22px;margin-bottom:22px}.feature h3{font-size:17px;color:var(--green);margin-bottom:9px}.feature p{font-size:13px;line-height:1.6;color:var(--muted);margin-bottom:0}
    .story{background:var(--green);color:#fff;border-radius:28px;padding:62px 64px;display:grid;grid-template-columns:1fr .8fr;gap:50px;align-items:center;overflow:hidden;position:relative}.story:after{content:"";position:absolute;width:350px;height:350px;border:1px solid rgba(213,185,74,.24);border-radius:50%;right:-74px;top:-190px;box-shadow:0 0 0 34px rgba(213,185,74,.035),0 0 0 70px rgba(213,185,74,.03)}.story h2{font:700 clamp(36px,4.1vw,50px)/1.12 Georgia,serif;letter-spacing:-1.4px;margin:16px 0 18px}.story p{color:#d9e1da;line-height:1.8;font-size:16px;margin-bottom:0}.story-note{position:relative;z-index:1;padding:26px 27px;border:1px solid rgba(255,255,255,.2);border-radius:18px;background:rgba(255,255,255,.06);font:italic 23px/1.45 Georgia,serif;color:#f2e8c6}.cta{padding:90px 0 94px;text-align:center}.cta h2{font:700 clamp(38px,5vw,58px)/1.1 Georgia,serif;color:var(--green);letter-spacing:-2px;margin-bottom:17px}.cta p{color:var(--muted);font-size:17px;line-height:1.65;margin:0 auto 27px;max-width:560px}.cta .join{padding:15px 24px}.footer{border-top:1px solid var(--line);padding:24px 0 34px;display:flex;justify-content:space-between;align-items:center;gap:20px;color:#7a8179;font-size:12px}.footer-links{display:flex;gap:20px}.footer-links a:hover{color:var(--red)}
    [data-lang="en"]{display:none}body.en [data-lang="tr"]{display:none}body.en [data-lang="en"]{display:revert}.nav span[data-lang],.hero-actions span[data-lang],.footer span[data-lang]{display:inline}body.en .nav span[data-lang="tr"],body.en .hero-actions span[data-lang="tr"],body.en .footer span[data-lang="tr"]{display:none}body.en .nav span[data-lang="en"],body.en .hero-actions span[data-lang="en"],body.en .footer span[data-lang="en"]{display:inline}
    @media(max-width:900px){.hero{grid-template-columns:1fr;gap:35px;padding:58px 0}.hero-copy{max-width:660px}.visual{max-width:720px;margin:auto}.features{grid-template-columns:repeat(2,1fr)}.story{padding:44px;grid-template-columns:1fr;gap:25px}.story-note{max-width:600px}}
    @media(max-width:600px){.wrap{width:min(100% - 30px,1160px)}.topbar{height:70px}.nav{display:none}.nav-actions{gap:6px}.join{padding:10px 12px;font-size:12px}.brand{font-size:17px}.brand-mark{width:38px;height:38px}.hero{padding:48px 0 54px}.hero h1{font-size:50px;letter-spacing:-2px}.lead{font-size:16px}.hero-actions{gap:15px;flex-wrap:wrap}.visual{padding:8px;border-radius:20px}.visual img{border-radius:13px}.proof{gap:13px 23px;font-size:11px}.section{padding:70px 0}.section-head{margin-bottom:30px}.features{gap:10px}.feature{padding:19px 16px;min-height:195px}.feature h3{font-size:15px}.feature p{font-size:12px}.story{padding:31px 24px;border-radius:21px}.story-note{font-size:19px;padding:20px}.cta{padding:67px 0}.footer{align-items:flex-start;flex-direction:column}.footer-links{flex-wrap:wrap;gap:15px}}
    @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*:before,*:after{transition:none!important}}
  </style>
</head>
<body>
  <header class="wrap topbar">
    <a class="brand" href="/index.php" aria-label="DeuCritic ana sayfa"><span class="brand-mark">d.</span><span>DeuCritic<small>SÖZLÜK</small></span></a>
    <nav class="nav" aria-label="Main navigation">
      <a href="#neler-var"><span data-lang="tr">Neler var?</span><span data-lang="en">Explore</span></a>
      <a href="#neden"><span data-lang="tr">Biz kimiz?</span><span data-lang="en">Our story</span></a>
      <a href="/kesfet.php"><span data-lang="tr">Keşfet</span><span data-lang="en">Discover</span></a>
    </nav>
    <div class="nav-actions">
      <button class="lang active" type="button" data-set-lang="tr" aria-pressed="true">TR</button>
      <button class="lang" type="button" data-set-lang="en" aria-pressed="false">EN</button>
      <a class="join" href="/register.php"><span data-lang="tr">Katıl <span aria-hidden="true">↗</span></span><span data-lang="en">Join <span aria-hidden="true">↗</span></span></a>
    </div>
  </header>

  <main>
    <section class="wrap hero" aria-labelledby="hero-title">
      <div class="hero-copy">
        <div class="eyebrow"><span data-lang="tr">Dokuz Eylül Üniversitesi öğrencileri için</span><span data-lang="en">For Dokuz Eylül University students</span></div>
        <h1 id="hero-title"><span data-lang="tr">Kampüs konuşuyor.<br><span>Söz sende.</span></span><span data-lang="en">A campus full of voices.<br><span>Your voice belongs.</span></span></h1>
        <p class="lead"><span data-lang="tr">Fikirlerini, deneyimlerini ve kampüs hayatını paylaş. Sohbetlere katıl, farklı bakış açıları keşfet ve topluluğunla bağlantı kur.</span><span data-lang="en">Share ideas, experiences and campus life. Join conversations, discover new perspectives and connect with your student community.</span></p>
        <div class="hero-actions"><a class="join" href="/register.php"><span data-lang="tr">Topluluğa katıl <span aria-hidden="true">→</span></span><span data-lang="en">Join the community <span aria-hidden="true">→</span></span></a><a class="textlink" href="/kesfet.php"><span data-lang="tr">Sohbetleri keşfet</span><span data-lang="en">Explore conversations</span><span aria-hidden="true">↘</span></a></div>
        <p class="independent"><span data-lang="tr">Bağımsız, öğrenciler tarafından şekillenen bir dijital topluluk.</span><span data-lang="en">An independent digital community shaped by students.</span></p>
      </div>
      <div class="visual"><img src="/assets/deucritic-hero.svg" alt="DeuCritic kampüs topluluğu tanıtım görseli"><span class="visual-tag"><span data-lang="tr">Fikirler · Deneyimler · Kampüs yaşamı</span><span data-lang="en">Ideas · Experiences · Campus life</span></span></div>
    </section>

    <div class="proof wrap" aria-label="Community values"><span><b>✳</b><span data-lang="tr">Özgürce paylaş</span><span data-lang="en">Share openly</span></span><span><b>⌕</b><span data-lang="tr">Sohbetleri keşfet</span><span data-lang="en">Discover conversations</span></span><span><b>♡</b><span data-lang="tr">Birbirine bağlan</span><span data-lang="en">Connect with others</span></span></div>

    <section class="wrap section" id="neler-var">
      <div class="section-head"><div class="eyebrow"><span data-lang="tr">Kampüsün dijital buluşma noktası</span><span data-lang="en">A digital home for campus life</span></div><h2><span data-lang="tr">Kampüs hayatı tek bir yerden.</span><span data-lang="en">Campus life, in one place.</span></h2><p><span data-lang="tr">Gündelik fikirlerden önemli konulara kadar, DEÜ öğrencilerinin konuştuğu şeylere katıl.</span><span data-lang="en">From everyday thoughts to bigger topics, take part in the conversations DEU students are having.</span></p></div>
      <div class="features">
        <article class="feature"><div class="icon" aria-hidden="true">◌</div><h3><span data-lang="tr">Açık tartışmalar</span><span data-lang="en">Open discussions</span></h3><p><span data-lang="tr">Kampüs yaşamı hakkında sohbet başlat ve farklı fikirleri dinle.</span><span data-lang="en">Start conversations about campus life and hear different perspectives.</span></p></article>
        <article class="feature"><div class="icon" aria-hidden="true">◎</div><h3><span data-lang="tr">Anonim paylaşım</span><span data-lang="en">Anonymous sharing</span></h3><p><span data-lang="tr">Düşüncelerini paylaşırken anonim kalmayı seçebilirsin.</span><span data-lang="en">Choose to share your thoughts anonymously.</span></p></article>
        <article class="feature"><div class="icon" aria-hidden="true">▦</div><h3><span data-lang="tr">Kampüs gündemi</span><span data-lang="en">Campus conversations</span></h3><p><span data-lang="tr">Öğrencilerin konuştuğu konuları ve öne çıkan başlıkları takip et.</span><span data-lang="en">Keep up with topics and conversations across campus.</span></p></article>
        <article class="feature"><div class="icon" aria-hidden="true">♡</div><h3><span data-lang="tr">Topluluk etkileşimi</span><span data-lang="en">Community interaction</span></h3><p><span data-lang="tr">Yanıt ver, beğen ve ortak ilgi alanları etrafında buluş.</span><span data-lang="en">Reply, react and connect around shared interests.</span></p></article>
      </div>
    </section>

    <section class="wrap" id="neden">
      <div class="story"><div><div class="eyebrow" style="color:#e0c95e"><span data-lang="tr">Neden DeuCritic?</span><span data-lang="en">Why DeuCritic?</span></div><h2><span data-lang="tr">Üniversite, derslerden ibaret değil.</span><span data-lang="en">University is more than classes.</span></h2><p><span data-lang="tr">Üniversiteler topluluklar, hikâyeler ve duyulmayı hak eden fikirlerle yaşar. DeuCritic, öğrencilerin kampüs deneyimlerini paylaşabildiği bağımsız bir alan sunar.</span><span data-lang="en">Universities are communities, stories and ideas that deserve to be heard. DeuCritic is an independent space for students to share their campus experiences.</span></p></div><div class="story-note"><span data-lang="tr">“Kampüsün sesi, onu yaşayan öğrencilerden gelir.”</span><span data-lang="en">“A campus finds its voice through the students who live it.”</span></div></div>
    </section>

    <section class="wrap cta"><div class="eyebrow" style="justify-content:center"><span data-lang="tr">Sıradaki söz senin</span><span data-lang="en">The next voice can be yours</span></div><h2><span data-lang="tr">Sohbetin bir parçası ol.</span><span data-lang="en">Be part of the conversation.</span></h2><p><span data-lang="tr">Fikrini paylaş, başkalarının deneyimlerini keşfet ve DEÜ topluluğuyla bağ kur.</span><span data-lang="en">Share an idea, discover what others think and connect with the DEU community.</span></p><a class="join" href="/register.php"><span data-lang="tr">DeuCritic'e katıl ↗</span><span data-lang="en">Join DeuCritic ↗</span></a></section>
  </main>

  <footer class="wrap footer"><span data-lang="tr">© <span id="year"></span> DeuCritic · Öğrenciler için bağımsız topluluk</span><span data-lang="en">© <span id="year-en"></span> DeuCritic · Independent student community</span><div class="footer-links"><a href="/index.php"><span data-lang="tr">Ana sayfa</span><span data-lang="en">Home</span></a><a href="/about.php"><span data-lang="tr">Biz kimiz?</span><span data-lang="en">About</span></a><a href="/terms.php"><span data-lang="tr">Kullanım koşulları</span><span data-lang="en">Terms</span></a></div></footer>
  <script>
    (function(){
      const buttons=document.querySelectorAll('[data-set-lang]');
      function setLang(lang){document.body.classList.toggle('en',lang==='en');document.documentElement.lang=lang;buttons.forEach(button=>{const active=button.dataset.setLang===lang;button.classList.toggle('active',active);button.setAttribute('aria-pressed',String(active));});try{localStorage.setItem('deucritic-promo-lang',lang);}catch(e){}}
      buttons.forEach(button=>button.addEventListener('click',()=>setLang(button.dataset.setLang)));
      let saved='tr';try{saved=localStorage.getItem('deucritic-promo-lang')||'tr';}catch(e){}setLang(saved);
      const year=new Date().getFullYear();document.getElementById('year').textContent=year;document.getElementById('year-en').textContent=year;
    })();
  </script>
</body>
</html>
