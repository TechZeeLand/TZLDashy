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
    <div class="hero-icon"><i class="fa-solid fa-circle-question"></i></div>
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
     'Go to your Profile (click your avatar in the top-right), then choose "Two-Factor Auth". Scan the QR code with an authenticator app like Google Authenticator, Aegis, or Authy, then enter the 6-digit code to confirm.'],
    ['How do I add more users?',
     'Admin users can manage users from Settings → Users. Click "Add User", fill in the details, and choose a role. Admins cannot delete other admin accounts — demote to User role first.'],
    ['What is the terminal feature?',
     'The Terminal tab embeds a browser-based terminal (ttyd) running on port 2222. The URL is configurable under Settings → General → Terminal URL. Default: http://your-server:2222'],
    ['How do I change the accent colour or theme?',
     'Go to Settings → Appearance. Pick from the colour swatches or use the colour picker for a custom hex. You can also toggle between Dark and Light mode. Each user has their own theme settings.'],
    ['What ports does TZLDashy use?',
     'TZLDashy itself runs on port 1011. The ttyd terminal runs on port 2222. MariaDB is internal only. All ports can be changed in docker-compose.yml.'],
    ['I forgot my admin password. How do I reset it?',
     'Connect directly to MariaDB: docker exec -it tzldashy_db mariadb -u tzldashy -p tzldashy. Then run: UPDATE users SET password="<bcrypt_hash>" WHERE email="your@email.com"; Generate a bcrypt hash with cost 12 using PHP\'s password_hash().'],
    ['Where are uploaded app icons stored?',
     'Icons are stored in the /app/public/Logos directory inside the container, which is mapped to a Docker volume (tzldashy_logos). Avatars are in tzldashy_uploads.'],
    ['Is TZLDashy open source?',
     'Yes! TZLDashy is licensed under GNU AGPL v3 and available on GitHub at github.com/TechZeeLand/tzldashy. Contributions, bug reports, and feature requests are very welcome.'],
  ];
  foreach ($faqs as $i => [$q, $a]):
  ?>
  <div class="faq-item" id="faq-<?= $i ?>">
    <div class="faq-question" onclick="toggleFaq(this)">
      <span><?= e($q) ?></span>
      <i class="fa-solid fa-chevron-down faq-arrow"></i>
    </div>
    <div class="faq-answer"><?= e($a) ?></div>
  </div>
  <?php endforeach; ?>

  <div style="text-align:center;margin-top:40px;padding:30px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius)">
    <div style="font-size:32px;margin-bottom:10px;color:var(--accent)"><i class="fa-solid fa-comments"></i></div>
    <div style="font-size:17px;font-weight:700;margin-bottom:8px">Still have questions?</div>
    <p style="color:var(--muted);margin-bottom:16px">We're happy to help via the contact page.</p>
    <a href="/pages/contact.php" class="btn btn-primary">Contact Us</a>
  </div>

</div>
</main>
<script src="/assets/js/app.js" defer></script>
<script>
function toggleFaq(el) { el.parentElement.classList.toggle('open'); }
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
