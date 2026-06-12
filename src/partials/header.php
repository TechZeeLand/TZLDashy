<?php
// partials/header.php — included at top of every authenticated page
if (!isset($pageTitle)) $pageTitle = APP_NAME;
$user  = Auth::user();
$theme = getThemeVars($user['id'] ?? null);
$accentHex = $theme['accent'] ?: '#00ffbf';
$fontName  = $theme['font']   ?: 'Alata';
$themeClass = $theme['theme'] === 'light' ? 'light' : '';
// Build custom CSS overrides
$customCss = "--accent:{$accentHex};--accent-dark:" . adjustBrightness($accentHex, -30) . ";";
if ($theme['primary'])   $customCss .= "--accent:{$theme['primary']};";
if ($theme['secondary']) $customCss .= "--secondary:{$theme['secondary']};";
$customCss .= "--accent-color:{$accentHex};";

?>
<!DOCTYPE html>
<html lang="en" class="<?= $themeClass ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> – <?= e(APP_NAME) ?></title>
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="/assets/css/app.css">
  <script src="https://kit.fontawesome.com/86c0c1c09a.js" crossorigin="anonymous" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
  <style>:root{<?= $customCss ?>}</style>
  <?php if ($fontName && $fontName !== 'Alata'): ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($fontName) ?>:wght@400;700&display=swap" rel="stylesheet">
  <style>body{font-family:'<?= e($fontName) ?>',sans-serif!important;}</style>
  <?php endif; ?>
  <?php if ($themeClass === 'light'): ?>
  <style>html,body{--bg:#f0f2f5;--card:#fff;--card2:#f7f7f7;--header-bg:#fff;--border:#e0e0e0;--text:#111;--muted:#666;--hover:#f0f0f0;--input-bg:#f7f7f7;--shadow:0 4px 24px rgba(0,0,0,.1);}</style>
  <?php endif; ?>
</head>
<body class="<?= $themeClass ?>">

<!-- HEADER -->
<header class="app-header">
  <div class="header-left">
    <a href="/" class="app-logo">
      <i class="fa-solid fa-server"></i>
      <span class="app-name"><?= e(APP_NAME) ?></span>
    </a>
  </div>

  <div class="header-right">
    <!-- Reboot -->
    <form method="POST" action="/api/system.php" class="inline-form"
          onsubmit="return confirm('Restart the server?')">
      <input type="hidden" name="action" value="system_reboot">
      <button type="submit" class="icon-btn" title="Restart server">
        <i class="fa-solid fa-rotate-right"></i>
      </button>
    </form>
    <!-- Shutdown -->
    <form method="POST" action="/api/system.php" class="inline-form"
          onsubmit="return confirm('Shutdown the server?')">
      <input type="hidden" name="action" value="system_shutdown">
      <button type="submit" class="icon-btn" title="Shutdown server">
        <i class="fa-solid fa-power-off"></i>
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
