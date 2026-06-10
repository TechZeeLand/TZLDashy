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
    <div class="hero-icon">📖</div>
    <h1>Help Center</h1>
    <p>Learn how to get the most out of TZLDashy.</p>
  </div>

  <div class="info-grid">
    <div class="info-card">
      <div class="info-icon">🏠</div>
      <h3>Home Tab</h3>
      <p>The Home slide shows your bookmark categories. Add categories with the folder+ icon, then add apps inside each. Drag cards to reorder them. Click the ⋮ menu on any card to edit or delete.</p>
    </div>
    <div class="info-card">
      <div class="info-icon">📦</div>
      <h3>Apps Tab</h3>
      <p>The Apps slide is a flat grid of all your web applications. Use the + button to add an app with a title, URL, and icon. All apps support drag-and-drop reordering.</p>
    </div>
    <div class="info-card">
      <div class="info-icon">📊</div>
      <h3>Stats Tab</h3>
      <p>Real-time system metrics: CPU usage & temperature, RAM, SSD, RAID disk usage, and live network speeds. Stats refresh every 3 seconds automatically.</p>
    </div>
    <div class="info-card">
      <div class="info-icon">💻</div>
      <h3>Terminal Tab</h3>
      <p>Embeds a browser terminal (ttyd/Wetty). Configure the URL under Settings → General. Run ttyd on your server:<br><code style="font-size:12px">ttyd -p 7681 bash</code></p>
    </div>
    <div class="info-card">
      <div class="info-icon">👤</div>
      <h3>Profile & Security</h3>
      <p>Click your avatar (top-right) to access your profile. Update your name, email, and profile picture. Set up 2FA (email OTP or authenticator app) under the Two-Factor Auth tab.</p>
    </div>
    <div class="info-card">
      <div class="info-icon">⚙️</div>
      <h3>Settings</h3>
      <p>Customize your theme, accent colour, font, and language. Admin users also see General settings (app name, weather city, terminal URL) and User Management.</p>
    </div>
  </div>

  <div class="settings-card">
    <div class="settings-card-title">🐳 Docker Quick Start</div>
    <p style="color:var(--muted);margin-bottom:16px">Deploy TZLDashy on any machine with Docker installed:</p>
    <pre style="background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:16px;font-size:13px;overflow-x:auto;color:var(--accent)">curl -O https://raw.githubusercontent.com/TechZeeLand/tzldashy/main/docker-compose.yml
# Edit .env with your settings
docker compose up -d</pre>
    <p style="color:var(--muted);margin-top:12px;font-size:13px">Then open <strong>http://your-server-ip:1011</strong> and complete the first-run setup.</p>
  </div>

  <div class="settings-card">
    <div class="settings-card-title">🔑 Default Ports</div>
    <table class="data-table">
      <thead><tr><th>Service</th><th>Port</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td>TZLDashy</td><td><strong>1011</strong></td><td>Main dashboard (Nginx)</td></tr>
        <tr><td>phpMyAdmin</td><td><strong>8094</strong></td><td>Database management UI</td></tr>
        <tr><td>MariaDB</td><td><em>internal</em></td><td>Not exposed externally</td></tr>
        <tr><td>ttyd (optional)</td><td><strong>7681</strong></td><td>Browser terminal</td></tr>
      </tbody>
    </table>
  </div>

  <div style="text-align:center;margin-top:32px">
    <a href="/pages/faq.php" class="btn" style="margin-right:10px">❓ FAQ</a>
    <a href="/pages/contact.php" class="btn btn-primary">✉️ Contact Support</a>
  </div>

</div>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
