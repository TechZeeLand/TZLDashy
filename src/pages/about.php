<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAuth();
$pageTitle = 'About';
require_once __DIR__ . '/../partials/header.php';
?>
<main>
<div class="page-wrap" style="max-width:960px">

  <div class="info-hero">
    <div class="hero-icon"><i class="fa-solid fa-server"></i></div>
    <h1>TZLDashy</h1>
    <p>A beautiful, self-hosted server dashboard for managing apps, monitoring system health, and staying in control of your infrastructure.</p>
  </div>

  <div class="info-grid">
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-building"></i></div>
      <h3>Owner</h3>
      <p><a href="https://rayaz.org" target="_blank">rayaz.org</a><br>The platform behind TZLDashy.</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-code"></i></div>
      <h3>Creator</h3>
      <p><strong>TechZeeLand</strong><br>Passionate about open-source server tooling and homelabs.</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-brands fa-github"></i></div>
      <h3>Open Source</h3>
      <p>TZLDashy is fully open source under AGPL v3. Contributions, issues, and stars are welcome on GitHub.</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-brands fa-docker"></i></div>
      <h3>Docker Ready</h3>
      <p>Deploy in seconds with a single <code>docker compose up</code>. MariaDB included, zero configuration needed.</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-lock"></i></div>
      <h3>Secure by Default</h3>
      <p>Multi-user login with admin roles and TOTP authenticator app 2FA (RFC 6238).</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-chart-column"></i></div>
      <h3>Live Stats</h3>
      <p>Real-time CPU, RAM, disk, network, and temperature monitoring refreshed every 3 seconds.</p>
    </div>
  </div>

  <div class="settings-card" style="margin-top:0">
    <div class="settings-card-title"><i class="fa-solid fa-list-check"></i> Technical Details</div>
    <table class="data-table">
      <tbody>
        <tr><td style="color:var(--muted);width:160px">App Name</td><td><?= e(getSetting('app_name',null,'TZLDashy')) ?></td></tr>
        <tr><td style="color:var(--muted)">Version</td><td>1.0.0</td></tr>
        <tr><td style="color:var(--muted)">Stack</td><td>PHP 8.2, Nginx, MariaDB 10.11</td></tr>
        <tr><td style="color:var(--muted)">Dashboard Port</td><td>1011</td></tr>
        <tr><td style="color:var(--muted)">Terminal Port</td><td>2222 (ttyd)</td></tr>
        <tr><td style="color:var(--muted)">Owner</td><td><a href="https://rayaz.org" target="_blank">rayaz.org</a></td></tr>
        <tr><td style="color:var(--muted)">Creator</td><td>TechZeeLand</td></tr>
        <tr><td style="color:var(--muted)">License</td><td>GNU AGPL v3</td></tr>
        <tr><td style="color:var(--muted)">PHP Version</td><td><?= PHP_VERSION ?></td></tr>
        <tr><td style="color:var(--muted)">Server Time</td><td><?= date('Y-m-d H:i:s') ?></td></tr>
      </tbody>
    </table>
  </div>

</div>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
