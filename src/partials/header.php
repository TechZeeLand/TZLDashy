<?php
// partials/header.php — included at top of every authenticated page
// Expects $pageTitle to be set by the including file
if (!isset($pageTitle)) $pageTitle = APP_NAME;
$user  = Auth::user();
$theme = getThemeVars($user['id'] ?? null);
$accentHex = $theme['accent'] ?: '#00ffbf';
$fontName  = $theme['font']   ?: 'Alata';
$themeClass = $theme['theme'] === 'light' ? 'light' : '';
// Build custom CSS vars overrides
$customCss = '';
if ($theme['primary'])   $customCss .= "--accent-color:{$theme['primary']};--accent:{$theme['primary']};";
if ($theme['secondary']) $customCss .= "--secondary:{$theme['secondary']};";
$customCss .= "--accent-color:{$accentHex};--accent:{$accentHex};";
?>
<!DOCTYPE html>
<html lang="en" class="<?= $themeClass ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> – <?= e(APP_NAME) ?></title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <link rel="stylesheet" href="/assets/css/app.css">
  <script src="https://kit.fontawesome.com/43a3c20016.js" crossorigin="anonymous" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
  <?php if ($customCss): ?>
  <style>:root{<?= $customCss ?>}</style>
  <?php endif; ?>
  <?php if ($fontName && $fontName !== 'Alata'): ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($fontName) ?>:wght@400;700&display=swap" rel="stylesheet">
  <style>body{font-family:'<?= e($fontName) ?>',sans-serif!important;}</style>
  <?php endif; ?>
</head>
<body class="<?= $themeClass ?>">

<!-- HEADER -->
<header class="app-header">
  <div class="header-left">
    <span class="app-logo">⚙️ <span class="app-name"><?= e(APP_NAME) ?></span></span>
  </div>

  <div class="header-right">
    <!-- Shutdown -->
    <form method="POST" action="/api/system.php" class="inline-form"
          onsubmit="return confirm('Shutdown the server?')">
      <input type="hidden" name="action" value="system_shutdown">
      <button type="submit" class="icon-btn" title="Shutdown">
        <i class="fa-solid fa-power-off"></i>
      </button>
    </form>
    <!-- Restart -->
    <form method="POST" action="/api/system.php" class="inline-form"
          onsubmit="return confirm('Restart the server?')">
      <input type="hidden" name="action" value="system_reboot">
      <button type="submit" class="icon-btn" title="Restart">
        <i class="fa-solid fa-rotate-right"></i>
      </button>
    </form>
    <!-- Settings -->
    <a href="/pages/settings.php" class="icon-btn" title="Settings">
      <i class="fa-solid fa-gear"></i>
    </a>
    <!-- Profile avatar -->
    <a href="/pages/profile.php" class="avatar-btn" title="Profile">
      <?php if (!empty($user['avatar'])): ?>
        <img src="/uploads/avatars/<?= e($user['avatar']) ?>" alt="<?= e($user['name']) ?>">
      <?php else: ?>
        <span class="avatar-initials"><?= e(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?></span>
      <?php endif; ?>
    </a>
  </div>
</header>
