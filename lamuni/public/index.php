<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/Auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) {
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        header('Location: /lamuni/public/admin/dashboard.php');
    } else {
        header('Location: /lamuni/public/dashboard.php');
    }
    exit;
}

$lang = ($_COOKIE['lm_lang'] ?? 'en') === 'ha' ? 'ha' : 'en';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lamuni — Clearer loan management in English and Hausa</title>
  <meta name="description" content="Apply for a loan, follow every decision, and track repayments in English or Hausa with Lamuni's transparent loan management system.">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lamuni/public/assets/css/style.css?v=2">
</head>
<body class="landing-page">

<nav class="navbar landing-nav">
  <div class="container">
    <a class="navbar-brand" href="/lamuni/public/" aria-label="Lamuni home">Lamu<span>ni</span></a>
    <div class="landing-nav-links">
      <a href="#how-it-works" data-en="How it works" data-ha="Yadda yake aiki">How it works</a>
      <a href="#features" data-en="Features" data-ha="Abubuwan amfani">Features</a>
      <div class="lang-toggle" aria-label="Choose language">
        <button class="lang-btn" type="button" data-lang="en" aria-label="English">EN</button>
        <button class="lang-btn" type="button" data-lang="ha" aria-label="Hausa">HA</button>
      </div>
      <a href="/lamuni/public/login.php" class="btn btn-outline-primary btn-sm"
         data-en="Sign in" data-ha="Shiga">Sign in</a>
    </div>
  </div>
</nav>

<main>
  <section class="landing-hero">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <div class="hero-eyebrow">
            <span class="hero-eyebrow-dot"></span>
            <span data-en="A simpler way to stay on top of borrowing"
                  data-ha="Hanya mai sauƙi don kula da lamuni">A simpler way to stay on top of borrowing</span>
          </div>
          <h1>
            <span data-en="Loans, made" data-ha="Lamuni cikin">Loans, made</span>
            <span class="hero-title-accent" data-en="clear." data-ha="fahimta.">clear.</span>
          </h1>
          <p class="hero-description"
             data-en="Apply online, follow your application as it is reviewed, and see your repayments in one clear dashboard — in English or Hausa."
             data-ha="Nemi lamuni ta yanar gizo, bibiyi yadda ake duba buƙatarku, kuma ku ga biyan kuɗinku a wuri ɗaya — da Turanci ko Hausa.">
            Apply online, follow your application as it is reviewed, and see your repayments in one clear dashboard — in English or Hausa.
          </p>
          <div class="hero-actions landing-actions">
            <a href="/lamuni/public/register.php" class="btn btn-primary"
               data-en="Create your account" data-ha="Ƙirƙiri asusunku">Create your account</a>
            <a href="#how-it-works" class="btn btn-quiet"
               data-en="See how it works ↓" data-ha="Duba yadda yake aiki ↓">See how it works ↓</a>
          </div>
          <p class="hero-footnote" data-en="Free to create an account · Reviewed by a real administrator"
             data-ha="Ƙirƙirar asusu kyauta ce · Mai gudanarwa ne zai duba buƙatarku">
            Free to create an account · Reviewed by a real administrator
          </p>
        </div>

        <div class="col-lg-6">
          <div class="hero-visual" aria-label="Illustration of a loan dashboard">
            <div class="visual-orbit visual-orbit-one"></div>
            <div class="visual-orbit visual-orbit-two"></div>
            <div class="loan-preview">
              <div class="preview-topline">
                <span class="preview-brand">Lamuni <span>overview</span></span>
                <span class="preview-status"><i></i> Active</span>
              </div>
              <p class="preview-label" data-en="Outstanding balance" data-ha="Ragowar kuɗin da ake bi">Outstanding balance</p>
              <div class="preview-balance">₦112,500<span>.00</span></div>
              <div class="preview-progress-label">
                <span data-en="Repayment progress" data-ha="Ci gaban biyan kuɗi">Repayment progress</span>
                <strong>25%</strong>
              </div>
              <div class="progress preview-progress" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" aria-label="Repayment progress">
                <div class="progress-bar" style="width:25%"></div>
              </div>
              <div class="preview-meta">
                <span data-en="3 of 12 monthly payments" data-ha="An biya 3 cikin 12">3 of 12 monthly payments</span>
                <span>₦12,500 / month</span>
              </div>
              <div class="preview-divider"></div>
              <div class="preview-next">
                <div class="preview-next-icon">₦</div>
                <div>
                  <strong data-en="Every payment, accounted for" data-ha="Ana rubuta kowane biyan kuɗi">Every payment, accounted for</strong>
                  <span data-en="Your history stays in one place" data-ha="Tarihin ku yana nan a wuri ɗaya">Your history stays in one place</span>
                </div>
                <span class="preview-check">✓</span>
              </div>
            </div>
            <div class="visual-note">
              <span class="visual-note-icon">✓</span>
              <span data-en="Clear status updates" data-ha="Bayyanannen matsayin buƙata">Clear status updates</span>
            </div>
            <p class="visual-caption" data-en="Illustrative demo account" data-ha="Misalin asusun gwaji">Illustrative demo account</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="trust-strip" aria-label="Product highlights">
    <div class="container">
      <div class="trust-item"><span class="trust-check">✓</span><span data-en="English + Hausa" data-ha="Turanci + Hausa">English + Hausa</span></div>
      <div class="trust-item"><span class="trust-check">✓</span><span data-en="Clear repayment history" data-ha="Tarihin biyan kuɗi a bayyane">Clear repayment history</span></div>
      <div class="trust-item"><span class="trust-check">✓</span><span data-en="No interest in this demo" data-ha="Babu ruwa a wannan gwajin">No interest in this demo</span></div>
      <div class="trust-item"><span class="trust-check">✓</span><span data-en="Human-reviewed applications" data-ha="Mutum ne ke duba buƙatu">Human-reviewed applications</span></div>
    </div>
  </section>

  <section class="landing-section" id="how-it-works">
    <div class="container">
      <div class="section-heading">
        <span class="section-kicker" data-en="A clear path forward" data-ha="Hanya bayyananniya">A clear path forward</span>
        <h2 data-en="From application to paid in full." data-ha="Daga neman lamuni har zuwa biya.">From application to paid in full.</h2>
        <p data-en="Know what happens at each stage, without chasing paperwork or guessing your balance."
           data-ha="Ku san abin da ke faruwa a kowane mataki, ba tare da neman takardu ko hasashen ragowar kuɗi ba.">
          Know what happens at each stage, without chasing paperwork or guessing your balance.
        </p>
      </div>
      <div class="row g-3 process-grid">
        <div class="col-md-4">
          <article class="process-card">
            <span class="process-number">01</span>
            <div class="process-icon">↗</div>
            <h3 data-en="Apply in minutes" data-ha="Nema cikin mintuna">Apply in minutes</h3>
            <p data-en="Create an account and tell us how much you need, for how long, and why."
               data-ha="Ƙirƙiri asusu kuma bayyana adadin da kuke buƙata, tsawon lokaci da dalili.">
              Create an account and tell us how much you need, for how long, and why.
            </p>
          </article>
        </div>
        <div class="col-md-4">
          <article class="process-card">
            <span class="process-number">02</span>
            <div class="process-icon">◎</div>
            <h3 data-en="Get a clear decision" data-ha="Samu amsa bayyananniya">Get a clear decision</h3>
            <p data-en="An administrator reviews the request. Your dashboard shows when its status changes."
               data-ha="Mai gudanarwa zai duba buƙatar. Allon aikinku zai nuna lokacin da matsayi ya canza.">
              An administrator reviews the request. Your dashboard shows when its status changes.
            </p>
          </article>
        </div>
        <div class="col-md-4">
          <article class="process-card">
            <span class="process-number">03</span>
            <div class="process-icon">✓</div>
            <h3 data-en="Track every payment" data-ha="Bibiyi kowane biya">Track every payment</h3>
            <p data-en="See your outstanding balance, monthly amount, notifications, and payment history."
               data-ha="Duba ragowar kuɗi, adadin wata-wata, sanarwa da tarihin biyan kuɗi.">
              See your outstanding balance, monthly amount, notifications, and payment history.
            </p>
          </article>
        </div>
      </div>
    </div>
  </section>

  <section class="landing-section features-section" id="features">
    <div class="container">
      <div class="section-heading">
        <span class="section-kicker" data-en="Everything in one place" data-ha="Komai a wuri ɗaya">Everything in one place</span>
        <h2 data-en="A better view of your loan." data-ha="Kyakkyawan tsarin ganin lamuninku.">A better view of your loan.</h2>
      </div>
      <div class="row g-3">
        <div class="col-md-6 col-lg-3">
          <article class="feature-card"><div class="feature-icon">▤</div>
            <h3 data-en="Your own dashboard" data-ha="Allon aikinku">Your own dashboard</h3>
            <p data-en="Applications, balances, and repayment history are easy to find."
               data-ha="Buƙatu, ragowar kuɗi da tarihin biya suna da sauƙin samu.">Applications, balances, and repayment history are easy to find.</p>
          </article>
        </div>
        <div class="col-md-6 col-lg-3">
          <article class="feature-card"><div class="feature-icon">◷</div>
            <h3 data-en="Timely updates" data-ha="Sabbin bayanai akan lokaci">Timely updates</h3>
            <p data-en="Get a notification when your application is decided or a payment is recorded."
               data-ha="Samu sanarwa idan an yanke hukunci ko an rubuta biyan kuɗi.">Get a notification when your application is decided or a payment is recorded.</p>
          </article>
        </div>
        <div class="col-md-6 col-lg-3">
          <article class="feature-card"><div class="feature-icon">◉</div>
            <h3 data-en="Easy-to-follow terms" data-ha="Sharuɗɗa masu sauƙin fahimta">Easy-to-follow terms</h3>
            <p data-en="Choose a repayment period from 3 to 60 months and see the monthly amount."
               data-ha="Zaɓi lokacin biya daga watanni 3 zuwa 60 kuma ku ga adadin kowane wata.">Choose a repayment period from 3 to 60 months and see the monthly amount.</p>
          </article>
        </div>
        <div class="col-md-6 col-lg-3">
          <article class="feature-card"><div class="feature-icon">文</div>
            <h3 data-en="English or Hausa" data-ha="Turanci ko Hausa">English or Hausa</h3>
            <p data-en="Switch languages whenever you need, across the borrower experience."
               data-ha="Canza yare a duk lokacin da kuke so yayin amfani da tsarin.">Switch languages whenever you need, across the borrower experience.</p>
          </article>
        </div>
      </div>
    </div>
  </section>

  <section class="clarity-section">
    <div class="container">
      <div class="clarity-panel">
        <div class="clarity-mark">i</div>
        <div>
          <span class="section-kicker" data-en="No surprises" data-ha="Babu abin mamaki">No surprises</span>
          <h2 data-en="Simple repayments. Clear records." data-ha="Sauƙin biya. Bayyanannen rikodi.">Simple repayments. Clear records.</h2>
          <p data-en="This project demo calculates equal monthly repayments with no interest. You can see every recorded payment and the remaining balance from your dashboard."
             data-ha="Wannan gwajin yana lissafa biyan kuɗi iri ɗaya kowane wata ba tare da ruwa ba. Za ku iya ganin kowane biya da ragowar kuɗi a allon aikinku.">
            This project demo calculates equal monthly repayments with no interest. You can see every recorded payment and the remaining balance from your dashboard.
          </p>
        </div>
        <a href="/lamuni/public/register.php" class="clarity-link" data-en="Get started →" data-ha="Fara yanzu →">Get started →</a>
      </div>
    </div>
  </section>

  <section class="prototype-note">
    <div class="container">
      <strong data-en="Academic project prototype" data-ha="Gwajin aikin makaranta">Academic project prototype</strong>
      <span data-en="Lamuni demonstrates loan application and tracking workflows. It does not issue real funds or verify BVN details."
            data-ha="Lamuni yana nuna tsarin neman lamuni da bibiyarsa. Ba ya bayar da kuɗi na gaske ko tabbatar da bayanan BVN.">
        Lamuni demonstrates loan application and tracking workflows. It does not issue real funds or verify BVN details.
      </span>
    </div>
  </section>

  <section class="closing-cta">
    <div class="container">
      <span class="section-kicker" data-en="Take the next step" data-ha="Ɗauki mataki na gaba">Take the next step</span>
      <h2 data-en="Clarity starts with one account." data-ha="Fahimta tana farawa da asusu ɗaya.">Clarity starts with one account.</h2>
      <p data-en="Create your borrower account and manage every step in one place."
         data-ha="Ƙirƙiri asusun ɗan aro kuma ku sarrafa kowane mataki a wuri ɗaya.">
        Create your borrower account and manage every step in one place.
      </p>
      <a href="/lamuni/public/register.php" class="btn btn-primary"
         data-en="Create a free account" data-ha="Ƙirƙiri asusu kyauta">Create a free account</a>
    </div>
  </section>
</main>

<footer class="landing-footer">
  <div class="container">
    <a class="navbar-brand" href="/lamuni/public/">Lamu<span>ni</span></a>
    <span data-en="© <?= date('Y') ?> Lamuni · A project prototype"
          data-ha="© <?= date('Y') ?> Lamuni · Gwajin aiki">© <?= date('Y') ?> Lamuni · A project prototype</span>
    <a href="/lamuni/public/admin/login.php" data-en="Administrator" data-ha="Mai gudanarwa">Administrator</a>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/lamuni/public/assets/js/app.js?v=2"></script>
</body>
</html>
