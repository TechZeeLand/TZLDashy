<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAuth();
$pageTitle = 'FAQ';
require_once __DIR__ . '/../partials/header.php';
?>
<main>
<div class="page-wrap" style="max-width:780px">

  <div class="info-hero">
    <div class="hero-icon">❓</div>
    <h1>Frequently Asked Questions</h1>
    <p>Everything you need to know about TZLDashy.</p>
  </div>

  <?php
  $faqs = [
    ['How do I add a new app to the dashboard?',
     'Go to the Home tab (Bookmarks) and click the folder+ icon to create a category first. Then click the + button inside the category to add an app. You\'ll need a title, URL, and an icon image. You can also add apps to the Apps slide using the + button there.'],
    ['Can I drag and drop to rearrange apps?',
     'Yes! All app cards and categories support drag-and-drop reordering. Just grab a card and drag it to the desired position. Changes are saved automatically.'],
    ['How do I enable Two-Factor Authentication?',
     'Go to your Profile (click your avatar in the top-right), then choose "Two-Factor Auth". You can enable either Email OTP (a code sent to your email on every login) or an Authenticator App like Google Authenticator or Aegis.'],
    ['How do I add more users?',
     'Admin users can manage users from Settings → User Management. Click "Add User", fill in the details, and choose a role. Admin users can create other admins, but cannot delete admin accounts — only demote them to User first.'],
    ['What is the terminal feature?',
     'The Terminal tab embeds a browser-based terminal (like ttyd or Wetty) running on your server. You need to run ttyd separately and configure its URL in Settings → General → Terminal URL.'],
    ['How do I change the accent colour or theme?',
     'Go to Settings → Appearance. Pick from the colour swatches or use the colour picker for a custom hex. You can also toggle between Dark and Light mode. Each user has their own theme settings.'],
    ['What ports does TZLDashy use?',
     'TZLDashy itself runs on port 1011. phpMyAdmin is available on port 8094. These are configured in the docker-compose.yml and can be changed.'],
    ['I forgot my admin password. How do I reset it?',
     'Connect to your database via phpMyAdmin (port 8094) and update the password hash in the users table. Generate a bcrypt hash with cost 12 (you can use online tools or PHP\'s password_hash). Alternatively, add a DB_ADMIN_RESET env var to trigger a setup re-run.'],
    ['Where are uploaded app icons stored?',
     'Icons are stored in the /app/public/Logos directory inside the container, which is mapped to a Docker volume (tzldashy_logos). Avatars are in /app/public/uploads/avatars (tzldashy_uploads).'],
    ['Is TZLDashy open source?',
     'Yes! TZLDashy is MIT licensed and available on GitHub. Contributions, bug reports, and feature requests are very welcome.'],
  ];
  foreach ($faqs as $i => [$q, $a]):
  ?>
  <div class="faq-item" id="faq-<?= $i ?>">
    <div class="faq-question" onclick="toggleFaq(this)">
      <span><?= e($q) ?></span>
      <span class="faq-arrow">▼</span>
    </div>
    <div class="faq-answer"><?= e($a) ?></div>
  </div>
  <?php endforeach; ?>

  <div style="text-align:center;margin-top:40px;padding:30px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius)">
    <div style="font-size:28px;margin-bottom:10px">🤔</div>
    <div style="font-size:17px;font-weight:700;margin-bottom:8px">Still have questions?</div>
    <p style="color:var(--muted);margin-bottom:16px">We're happy to help via the contact page.</p>
    <a href="/pages/contact.php" class="btn btn-primary">Contact Us</a>
  </div>

</div>
</main>
<script src="/assets/js/app.js" defer></script>
<script>
function toggleFaq(el) {
  el.parentElement.classList.toggle('open');
}
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
