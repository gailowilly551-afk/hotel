<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

$aiLogs = dbRows("SELECT a.*, u.email FROM ai_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Logs &amp; Monitoring — Admin Portal</title>
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
        <div class="topbar-title">AI Chatbot Management &amp; Intelligence Logs</div>
      </div>
    </header>

    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-header-title">AI Chat Logs &amp; Threat Detection</h1>
          <div class="page-header-sub">Analyzes visitor questions, AI responses, and automatically blocked bad intent / hacking attempts.</div>
        </div>
      </div>

      <!-- Threat logs and monitoring table -->

      <div class="card">
        <div class="card-body" style="padding:0;">
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Time</th>
                  <th>User</th>
                  <th>User Message</th>
                  <th>AI Response</th>
                  <th>Type (Intent)</th>
                  <th>Flagged</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($aiLogs as $log): ?>
                  <tr style="<?= $log['flagged'] ? 'background:rgba(233,69,96,0.06);' : '' ?>">
                    <td><?= formatDate($log['created_at'], 'M d, Y h:i A') ?></td>
                    <td><?= h($log['email'] ?: 'Visitor') ?></td>
                    <td><strong><?= h($log['user_message']) ?></strong></td>
                    <td style="font-size:0.8rem;color:var(--text-muted);max-width:300px;"><?= strip_tags($log['ai_response']) ?></td>
                    <td>
                      <?php
                        $intentRaw = (string)($log['intent'] ?? '');
                        $intentClean = str_ireplace(['nvidia_nim', 'nvidia'], ['Live AI', 'AI'], $intentRaw);
                        $intentClean = str_ireplace(['budget_recommend_nvidia', 'budget_recommend_ai'], 'Room Recommender (AI)', $intentClean);
                        $intentClean = str_replace('_', ' ', $intentClean);
                        $intentLabel = strtoupper($intentClean ?: 'INQUIRY');
                      ?>
                      <span class="badge badge-info"><?= h($intentLabel) ?></span>
                    </td>
                    <td>
                      <?php if ($log['flagged']): ?>
                        <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= h($log['flag_reason']) ?></span>
                      <?php else: ?>
                        <span class="badge badge-success">Normal</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="../assets/js/core.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>
