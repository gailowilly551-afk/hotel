<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

// Save settings handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
    } else {
        if (!empty($_POST['settings']) && is_array($_POST['settings'])) {
            foreach ($_POST['settings'] as $key => $val) {
                dbExec("UPDATE settings SET `value` = ? WHERE `key` = ?", [trim($val), $key]);
            }
            auditLog('Updated System Settings', 'Admin Module', 'Modified global hotel configuration');
            setFlash('success', 'Hotel configuration updated successfully!');
        }
    }
    header("Location: settings.php");
    exit;
}

$settings = dbRows("SELECT * FROM settings ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Settings — Admin Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body style="background:var(--navy);">

<div class="dashboard-layout">
<?php require_once __DIR__ . '/../includes/components/sidebar.php';
  renderSidebar('admin'); ?>
  <main class="dashboard-main">
    <header class="topbar">
      <div style="display:flex;align-items:center;gap:12px;">
        <button class="admin-mobile-toggle" data-sidebar-toggle><i class="fa-solid fa-bars"></i></button>
        <div class="topbar-title">Global Configuration &amp; Policies</div>
      </div>
    </header>

    <div class="page-content">
      <?= renderFlash() ?>

      <div class="page-header">
        <div>
          <h1 class="page-header-title">Hotel and System Settings</h1>
          <div class="page-header-sub">Configure contacts, GCash number for payments, check-in/out hours, and hotel details.</div>
        </div>
      </div>

      <div class="glass-card" style="padding:32px;max-width:800px;">
        <form action="" method="POST">
          <?= csrfField() ?>
          <input type="hidden" name="save_settings" value="1">
          
          <?php foreach ($settings as $s): ?>
            <div class="form-group">
              <label class="form-label">
                <?= ucwords(str_replace('_', ' ', $s['key'])) ?>
                <?php if ($s['description']): ?>
                  <span style="font-weight:400;color:var(--text-muted);">(<?= h($s['description']) ?>)</span>
                <?php endif; ?>
              </label>
              <input type="text" name="settings[<?= h($s['key']) ?>]" class="form-control" value="<?= h($s['value']) ?>">
            </div>
          <?php endforeach; ?>

          <button type="submit" class="btn btn-gold" style="margin-top:10px;"><i class="fa-solid fa-save"></i> Save All Settings</button>
        </form>
      </div>

    </div>
  </main>
</div>

<script src="../assets/js/core.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>
