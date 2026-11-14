<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Staff accounts are created exclusively by the Admin via admin/staff.php.
// The public staff self-registration portal is permanently closed.
header('Location: ../staff/login.php');
exit;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotelCode   = trim($_POST['hotel_code'] ?? '');
    $firstName   = trim($_POST['first_name'] ?? '');
    $lastName    = trim($_POST['last_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    // Hotel Employee Passcode (can also be changed in settings)
    $validPasscode = 'MARVELOUS_STAFF_PASS';

    $nameError = validatePersonName($firstName, $lastName);

    if ($hotelCode !== $validPasscode) {
        $error = 'Incorrect Hotel Staff Authorization Passcode. Ask the management / Admin for the passcode.';
    } elseif ($nameError !== null) {
        $error = $nameError;
    } elseif (empty($email) || empty($password)) {
        $error = 'Please fill in all required information.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid Gmail or Email format.';
    } elseif (strlen($password) < 8) {
        $error = 'The password must be at least 8 characters long.';
    } elseif ($password !== $confirmPass) {
        $error = 'Your password confirmation does not match.';
    } else {
        $exists = dbRow("SELECT id FROM users WHERE email = ?", [$email]);
        if ($exists) {
            $error = 'This Gmail/Email is already registered in the system.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
            $staffId = dbInsert(
                "INSERT INTO users (first_name, last_name, email, phone, password, role, status, email_verified) 
                 VALUES (?, ?, ?, ?, ?, 'staff', 'active', 1)",
                [$firstName, $lastName, $email, $phone, $hashed]
            );

            auditLog('Manual Staff Registration', 'Auth', "Staff registered: $email");
            setFlash('success', 'Your Staff account was registered successfully! You can now log in with your Gmail.');
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Account Registration — Marvelous Hotel</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:var(--navy);">

  <div class="glass-card" style="width:100%;max-width:540px;padding:36px;box-shadow:var(--shadow-deep);border-color:rgba(78,154,241,0.3);">
    <div style="text-align:center;margin-bottom:24px;">
      <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:12px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.4rem;margin-bottom:12px;">👨‍💼</div>
      <h2 style="font-family:var(--font-serif);font-size:1.5rem;color:#fff;">Staff Frontdesk Registration</h2>
      <p style="font-size:0.85rem;color:var(--text-muted);margin-top:4px;">Enter your own Gmail and Password for Hotel Frontdesk Staff</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="flash-message flash-error"><i class="fa-solid fa-circle-xmark"></i> <span><?= h($error) ?></span></div>
    <?php endif; ?>

    <form action="" method="POST">
      <div class="form-group" style="background:rgba(78,154,241,0.08);border:1px solid rgba(78,154,241,0.25);border-radius:10px;padding:12px 14px;">
        <label class="form-label" style="color:var(--accent);">Staff Authorization Passcode <span class="required">*</span></label>
        <input type="password" name="hotel_code" class="form-control" placeholder="Enter the Passcode from the Admin" required>
        <small style="color:var(--text-muted);font-size:0.75rem;">For official employees only (Default Passcode: <code>MARVELOUS_STAFF_PASS</code>)</small>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group">
          <label class="form-label">First Name <span class="required">*</span></label>
          <input type="text" name="first_name" class="form-control" placeholder="Name" value="<?= h($_POST['first_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Last Name <span class="required">*</span></label>
          <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="<?= h($_POST['last_name'] ?? '') ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Staff Gmail / Email Address <span class="required">*</span></label>
        <div class="form-icon-wrap">
          <i class="fa-solid fa-envelope form-icon"></i>
          <input type="email" name="email" class="form-control" placeholder="staff.name@gmail.com" value="<?= h($_POST['email'] ?? '') ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Contact Number</label>
        <div class="form-icon-wrap">
          <i class="fa-solid fa-phone form-icon"></i>
          <input type="tel" name="phone" class="form-control" placeholder="09123456789" value="<?= h($_POST['phone'] ?? '') ?>">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group">
          <label class="form-label">Staff Password <span class="required">*</span></label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm <span class="required">*</span></label>
          <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
        </div>
      </div>

      <button type="submit" class="btn btn-gold btn-block" style="margin-top:10px;">
        <i class="fa-solid fa-id-card"></i> Register Staff Account
      </button>
    </form>

    <div style="text-align:center;margin-top:20px;font-size:0.85rem;">
      <a href="login.php" style="color:var(--gold);"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
    </div>
  </div>

<script src="../assets/js/core.js"></script>
</body>
</html>
