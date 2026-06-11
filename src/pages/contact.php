<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAuth();

$user    = Auth::user();
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? $user['name']);
    $email   = trim($_POST['email']   ?? $user['email']);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$subject || !$message) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($message) < 20) {
        $error = 'Message must be at least 20 characters.';
    } else {
        Database::query(
            "INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)",
            [$name, $email, $subject, $message]
        );
        $success = 'Your message has been sent. We\'ll get back to you soon!';
    }
}

$pageTitle = 'Contact Us';
require_once __DIR__ . '/../partials/header.php';
?>
<main>
<div class="page-wrap" style="max-width:760px">

  <div class="info-hero">
    <div class="hero-icon"><i class="fa-solid fa-envelope"></i></div>
    <h1>Contact Us</h1>
    <p>Have a question, bug report, or feature request? We'd love to hear from you.</p>
  </div>

  <?php if ($success): ?>
  <div class="alert alert-success" style="margin-bottom:24px">
    <i class="fa-solid fa-circle-check"></i> <?= e($success) ?>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-error" style="margin-bottom:24px">
    <i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?>
  </div>
  <?php endif; ?>

  <div class="settings-card">
    <div class="settings-card-title"><i class="fa-solid fa-paper-plane"></i> Send a Message</div>
    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Your Name <span class="req">*</span></label>
          <input class="form-input" type="text" name="name" value="<?= e($_POST['name'] ?? $user['name']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Your Email <span class="req">*</span></label>
          <input class="form-input" type="email" name="email" value="<?= e($_POST['email'] ?? $user['email']) ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Subject <span class="req">*</span></label>
        <select class="form-select" name="subject">
          <option value="">– Select a topic –</option>
          <option value="Bug Report"       <?= ($_POST['subject']??'')==='Bug Report'       ?'selected':'' ?>>Bug Report</option>
          <option value="Feature Request"  <?= ($_POST['subject']??'')==='Feature Request'  ?'selected':'' ?>>Feature Request</option>
          <option value="General Question" <?= ($_POST['subject']??'')==='General Question' ?'selected':'' ?>>General Question</option>
          <option value="Security Issue"   <?= ($_POST['subject']??'')==='Security Issue'   ?'selected':'' ?>>Security Issue</option>
          <option value="Other"            <?= ($_POST['subject']??'')==='Other'            ?'selected':'' ?>>Other</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Message <span class="req">*</span></label>
        <textarea class="form-textarea" name="message" rows="6"
                  placeholder="Describe your issue or question in detail…" required><?= e($_POST['message'] ?? '') ?></textarea>
        <div class="form-hint">Minimum 20 characters.</div>
      </div>
      <button type="submit" class="btn btn-primary btn-full">
        <i class="fa-solid fa-paper-plane"></i> Send Message
      </button>
    </form>
  </div>

  <div class="info-grid" style="margin-top:24px">
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
      <h3>Email</h3>
      <p><a href="mailto:mail@rayaz.org">mail@rayaz.org</a><br>For direct correspondence.</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-brands fa-github"></i></div>
      <h3>GitHub</h3>
      <p>Open issues and PRs on <a href="https://github.com/TechZeeLand/tzldashy" target="_blank">GitHub</a>. Bug reports and feature requests are welcome!</p>
    </div>
    <div class="info-card">
      <div class="info-icon"><i class="fa-solid fa-clock"></i></div>
      <h3>Response Time</h3>
      <p>We typically respond within 24–48 hours on business days.</p>
    </div>
  </div>

</div>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
