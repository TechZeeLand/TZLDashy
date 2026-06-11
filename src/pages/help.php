<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAuth();
$pageTitle = 'Help';
require_once __DIR__ . '/../partials/header.php';
?>
<main>
<div class="page-wrap" style="max-width:920px">

  <div class="info-hero">
    <div class="hero-icon"><i class="fa-solid fa-book-open"></i></div>
    <h1>Help Center</h1>
    <p>Learn how to get the most out of TZLDashy.</p>
  </div>

  <div class="info-grid">
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-house"></i></div>
      <h3>Home Tab</h3>
      <p>The Home slide shows your bookmark categories. Add categories with the folder+ icon, then add apps inside each. Drag cards to reorder them. Click the <i class="fa-solid fa-ellipsis-vertical"></i> menu on any card to edit or delete.</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-grip"></i></div>
      <h3>Apps Tab</h3>
      <p>The Apps slide is a flat grid of all your web applications. Use the <i class="fa-solid fa-plus"></i> button to add an app with a title, URL, and icon. All apps support drag-and-drop reordering.</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-chart-column"></i></div>
      <h3>Stats Tab</h3>
      <p>Real-time system metrics: CPU usage &amp; temperature, RAM, system disk, main storage usage, and live network speeds. Stats refresh every 3 seconds automatically.</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-terminal"></i></div>
      <h3>Terminal Tab</h3>
      <p>Embeds a browser terminal (ttyd). Configure the URL under Settings → General. Default port is <strong>2222</strong>.<br><code style="font-size:12px">docker compose up -d tdyl</code></p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-user"></i></div>
      <h3>Profile &amp; Security</h3>
      <p>Click your avatar (top-right) to access your profile. Update your name, email, and profile picture. Set up TOTP 2FA (authenticator app) under the Two-Factor Auth tab.</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-gear"></i></div>
      <h3>Settings</h3>
      <p>Customize your theme, accent colour, font, and language. Admin users also see General settings (app name, weather city, terminal URL) and User Management.</p>
    </div>
  </div>

  <div class="settings-card">
    <div class="settings-card-title"><i class="fa-brands fa-docker"></i> Docker Quick Start</div>
    <p style="color:var(--muted);margin-bottom:16px">Deploy TZLDashy on any machine with Docker installed:</p>
    <pre style="background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:16px;font-size:13px;overflow-x:auto;color:var(--accent)">curl -O https://raw.githubusercontent.com/TechZeeLand/tzldashy/main/docker-compose.yml
docker compose up -d</pre>
    <p style="color:var(--muted);margin-top:12px;font-size:13px">Then open <strong>http://your-server-ip:1011</strong> and complete the first-run setup.</p>
  </div>

  <div class="settings-card">
    <div class="settings-card-title"><i class="fa-solid fa-network-wired"></i> Default Ports</div>
    <table class="data-table">
      <thead><tr><th>Service</th><th>Port</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td>TZLDashy</td><td><strong>1011</strong></td><td>Main dashboard (Nginx)</td></tr>
        <tr><td>MariaDB</td><td><em>internal</em></td><td>Not exposed externally</td></tr>
        <tr><td>ttyd (terminal)</td><td><strong>2222</strong></td><td>Browser-based terminal</td></tr>
      </tbody>
    </table>
  </div>

  <div style="text-align:center;margin-top:32px">
    <a href="/pages/faq.php" class="btn" style="margin-right:10px">
      <i class="fa-solid fa-circle-question"></i> FAQ
    </a>
    <a href="/pages/contact.php" class="btn btn-primary">
      <i class="fa-solid fa-envelope"></i> Contact Support
    </a>
  </div>

</div>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
