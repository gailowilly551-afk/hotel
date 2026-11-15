<?php
// ============================================================
// MARVELOUS HOTEL — Forgot Password
// auth/forgot-password.php
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email.php';
require_once __DIR__ . '/../includes/rate-limiter.php';

redirectIfLoggedIn();

applyAuthRateLimit('forgot');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';

    if (!verifyCsrfToken($token)) {
        $error = 'Invalid security session token. Please try again.';
    } elseif (empty($email) && empty($phone)) {
        $error = 'Please enter your registered email address or phone number.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        $user = null;
        if (!empty($email)) {
            $user = dbRow("SELECT id, first_name, last_name, email, phone, role FROM users WHERE email = ? AND role = 'user'", [$email]);
        } elseif (!empty($phone)) {
            $user = dbRow("SELECT id, first_name, last_name, email, phone, role FROM users WHERE phone = ? AND role = 'user'", [$phone]);
        }

        if ($user) {
            $targetEmail = $user['email'];
            $targetPhone = $user['phone'] ?? $phone;
            $userId = (int)$user['id'];

            // 1. Insert into password_reset_requests for Admin popup
            dbExec(
                "INSERT INTO password_reset_requests (user_id, email, phone, status, requested_at) VALUES (?, ?, ?, 'pending', NOW())",
                [$userId, $targetEmail, $targetPhone]
            );

            // 2. Insert into notifications table for Admin
            $adminUser = dbRow("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            if ($adminUser) {
                createNotification(
                    (int)$adminUser['id'],
                    'Password Reset Request',
                    "Guest {$user['first_name']} {$user['last_name']} ({$targetEmail}) requested a password reset.",
                    'alert',
                    SITE_URL . '/admin/users.php?tab=resets',
                    'fa-key'
                );
            }

            auditLog('Password Reset Requested', 'User Auth', "Reset request submitted to Admin for {$targetEmail}");

            // 3. Send real email via SMTP if configured or attempted
            $emailSent = false;
            $resetToken = bin2hex(random_bytes(32));
            $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            dbExec("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?", [$resetToken, $resetExpires, $userId]);
            $resetLink = SITE_URL . '/auth/reset-password.php?token=' . $resetToken;
            $userName = formatFullName($user);
            $htmlBody = emailTemplateResetPassword($resetLink, $userName, 1);
            $textBody = "Password Reset Request\n\nHello {$userName},\n\nClick the link below to reset your password:\n{$resetLink}\n\nThis link will expire in 1 hour.\n\n" . SITE_NAME;
            $sendErr = null;
            $emailSent = sendEmail($targetEmail, 'Password Reset Link — ' . SITE_NAME, $htmlBody, $textBody, $sendErr);

            if ($emailSent) {
                $success = "A password reset link has been dispatched to <strong>" . h($targetEmail) . "</strong>! Please check your Inbox and Spam/Junk folder.";
            } else {
                $success = 'Password reset request sent! Our Admin has received your alert. The Admin will assign and send your new temporary password, or you may ask frontdesk assistance.';
            }
        } else {
            // Reassuring notice even if email wasn't found to prevent enumeration
            $success = 'If the account exists, your request has been forwarded to the Hotel Admin to dispatch your new password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password — Marvelous Hotel</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:var(--navy);">

  <div class="glass-card" style="width:100%;max-width:440px;padding:36px;box-shadow:var(--shadow-deep);position:relative;">
    <div style="text-align:center;margin-bottom:28px;">
      <a href="../index.php" style="display:inline-flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div style="width:40px;height:40px;background:linear-gradient(135deg,var(--gold),var(--gold-dark));border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--navy);font-weight:800;font-size:1.2rem;">M</div>
        <div style="text-align:left;line-height:1.1;">
          <div style="font-family:var(--font-serif);font-size:1.1rem;font-weight:700;color:var(--gold);">Marvelous Hotel</div>
          <div style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">& Dormitory</div>
        </div>
      </a>
      <h2 style="font-family:var(--font-serif);font-size:1.5rem;color:#fff;">Forgot Password?</h2>
      <p style="font-size:0.85rem;color:var(--text-muted);margin-top:4px;">Enter your email to get a reset link</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="flash-message flash-error" style="margin-bottom:20px;">
        <i class="fa-solid fa-circle-xmark"></i>
        <span><?= h($error) ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="flash-message flash-success" style="margin-bottom:20px;">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= h($success) ?></span>
      </div>
    <?php endif; ?>

    <form action="" method="POST">
      <?= csrfField() ?>

      <div class="form-group">
        <label class="form-label">Registered Email Address <span class="required">*</span></label>
        <div class="form-icon-wrap">
          <i class="fa-solid fa-envelope form-icon"></i>
          <input type="email" name="email" class="form-control" placeholder="name@email.com" value="<?= h($_POST['email'] ?? ($_GET['email'] ?? '')) ?>" required autofocus autocapitalize="none" autocorrect="off" spellcheck="false" autocomplete="email">
        </div>
      </div>

      <button type="submit" class="btn btn-gold btn-block" style="margin-top:10px;">
        <i class="fa-solid fa-paper-plane"></i> Send Reset Link
      </button>
    </form>

    <div style="margin-top:24px;text-align:center;font-size:0.88rem;color:var(--text-muted);border-top:1px solid var(--border-subtle);padding-top:20px;">
      Remembered your password? <a href="login.php" style="color:var(--gold);font-weight:700;">Log In &rarr;</a>
    </div>
  </div>

<script src="../assets/js/core.js"></script>
</body>
</html>