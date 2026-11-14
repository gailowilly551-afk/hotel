<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('user');

$recommendations = [];
$searched = false;
$budget = 0.0;
$guests = 1;
$preference = 'any';

function renderAmenityTag(string $amenity): string {
    $icon = 'fa-circle-check';
    $lower = strtolower($amenity);
    if (strpos($lower, 'wifi') !== false) $icon = 'fa-wifi';
    elseif (strpos($lower, 'air') !== false || strpos($lower, 'ac') !== false) $icon = 'fa-snowflake';
    elseif (strpos($lower, 'tv') !== false) $icon = 'fa-tv';
    elseif (strpos($lower, 'shower') !== false || strpos($lower, 'bath') !== false) $icon = 'fa-shower';
    elseif (strpos($lower, 'lock') !== false) $icon = 'fa-lock';
    elseif (strpos($lower, 'ref') !== false) $icon = 'fa-box-archive';
    elseif (strpos($lower, 'desk') !== false) $icon = 'fa-laptop';
    return '<span class="room-amenity-tag"><i class="fa-solid ' . $icon . '"></i> ' . htmlspecialchars($amenity) . '</span>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (verifyCsrfToken($token)) {
        $searched = true;
        $budget = (float)($_POST['budget'] ?? 0);
        $guests = (int)($_POST['guests'] ?? 1);
        $preference = $_POST['preference'] ?? 'any';

        $sql = "SELECT r.*, rt.name as type_name FROM rooms r JOIN room_types rt ON r.room_type_id = rt.id WHERE r.status = 'available' AND r.capacity >= ?";
        $params = [$guests];

        if ($budget > 0) {
            $sql .= " AND r.price_per_night <= ?";
            $params[] = $budget;
        }

        if ($preference === 'budget') {
            $sql .= " ORDER BY r.price_per_night ASC LIMIT 6";
        } elseif ($preference === 'luxury') {
            $sql .= " ORDER BY r.price_per_night DESC LIMIT 6";
        } else {
            $sql .= " ORDER BY r.capacity ASC, r.price_per_night ASC LIMIT 6";
        }

        $recommendations = dbRows($sql, $params);
        auditLog('Used AI Room Recommendation', 'AI Module', "Preference: $preference, Budget: $budget, Guests: $guests");
    }
} else {
    // Initial display: show top curated rooms so the page is never blank
    $recommendations = dbRows("
        SELECT r.*, rt.name as type_name 
        FROM rooms r 
        JOIN room_types rt ON r.room_type_id = rt.id 
        WHERE r.status = 'available' 
        ORDER BY r.price_per_night ASC 
        LIMIT 6
    ");
}

$unreadNotifs = unreadNotifCount(currentUserId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Room Recommendation — Marvelous Hotel</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/user.css">
</head>
<body style="background:var(--navy);">

<div class="dashboard-layout">
  <?php 
    require_once __DIR__ . '/../includes/components/sidebar.php';
    renderSidebar('user'); 
  ?>

  <main class="dashboard-main">
    <header class="topbar">
      <div style="display:flex;align-items:center;gap:12px;">
        <button class="user-mobile-toggle" data-sidebar-toggle><i class="fa-solid fa-bars"></i></button>
        <div class="topbar-title">Smart AI Room Matcher</div>
      </div>
      <div class="topbar-actions">
        <a href="notifications.php" class="topbar-notif" title="Notifications">
          <i class="fa-solid fa-bell"></i>
          <?php if ($unreadNotifs > 0): ?>
            <span class="badge-dot"></span>
          <?php endif; ?>
        </a>
      </div>
    </header>

    <div class="page-content">
      <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
          <h1 class="page-header-title">Smart AI Room Matcher ✨</h1>
          <div class="page-header-sub">Let our intelligent matcher suggest the optimal room or dormitory bed for your budget and group size.</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <span class="badge" style="background:rgba(212,175,55,0.15);color:var(--gold);border:1px solid rgba(212,175,55,0.3);padding:6px 12px;font-size:0.8rem;">
            <i class="fa-solid fa-wand-magic-sparkles"></i> AI Recommender v2
          </span>
        </div>
      </div>

      <!-- AI Questionnaire Form -->
      <div class="glass-card" style="padding:28px;margin-bottom:32px;border:1px solid rgba(212,175,55,0.22);background:rgba(21,31,56,0.75);">
        <form action="" method="POST" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:18px;align-items:flex-end;">
          <?= csrfField() ?>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" style="display:flex;align-items:center;gap:6px;font-size:0.85rem;">
              <i class="fa-solid fa-peso-sign" style="color:var(--gold);"></i>
              Budget Limit (₱ / Night)
            </label>
            <input type="number" name="budget" class="form-control" placeholder="e.g. 1500 (Leave empty for any)" value="<?= $budget > 0 ? h($budget) : '' ?>">
          </div>

          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" style="display:flex;align-items:center;gap:6px;font-size:0.85rem;">
              <i class="fa-solid fa-users" style="color:var(--gold);"></i>
              Number of Pax (Persons)
            </label>
            <input type="number" name="guests" class="form-control" value="<?= h($guests) ?>" min="1" max="10" required>
          </div>

          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" style="display:flex;align-items:center;gap:6px;font-size:0.85rem;">
              <i class="fa-solid fa-sliders" style="color:var(--gold);"></i>
              Stay Preference
            </label>
            <select name="preference" class="form-control" style="cursor:pointer;">
              <option value="any" <?= $preference === 'any' ? 'selected' : '' ?>>⚖️ Balanced (Best Overall Value)</option>
              <option value="budget" <?= $preference === 'budget' ? 'selected' : '' ?>>🎒 Budget &amp; Dormitory Beds (₱350+)</option>
              <option value="luxury" <?= $preference === 'luxury' ? 'selected' : '' ?>>👑 Deluxe &amp; Family Suites</option>
            </select>
          </div>

          <div>
            <button type="submit" class="btn btn-gold btn-block" style="height:46px;display:flex;align-items:center;justify-content:center;gap:8px;font-weight:700;">
              <i class="fa-solid fa-wand-magic-sparkles"></i> Find My Room
            </button>
          </div>
        </form>
      </div>

      <!-- Recommendation Results Section -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <h2 style="font-family:var(--font-serif);font-size:1.35rem;color:#fff;margin:0;display:flex;align-items:center;gap:8px;">
          <span><?= $searched ? 'AI Suggestions for You' : 'Top Curated AI Recommendations' ?></span>
          <span style="font-size:0.82rem;font-family:var(--font-sans);font-weight:400;color:var(--text-muted);">(<?= count($recommendations) ?> found)</span>
        </h2>
        <?php if ($searched): ?>
          <a href="ai-recommend.php" style="font-size:0.82rem;color:var(--gold);text-decoration:none;display:flex;align-items:center;gap:5px;">
            <i class="fa-solid fa-rotate-left"></i> Reset Filter
          </a>
        <?php endif; ?>
      </div>

      <div class="rooms-grid">
        <?php if (empty($recommendations)): ?>
          <div class="empty-state" style="grid-column:1/-1;padding:48px;text-align:center;background:var(--navy-card);border:1px solid var(--border);border-radius:var(--radius-lg);">
            <div class="empty-state-icon" style="font-size:2.8rem;color:var(--gold);margin-bottom:12px;">
              <i class="fa-solid fa-robot"></i>
            </div>
            <div class="empty-state-title" style="font-size:1.2rem;font-weight:700;color:#fff;margin-bottom:6px;">No rooms matched your criteria</div>
            <div class="empty-state-desc" style="color:var(--text-muted);font-size:0.88rem;max-width:440px;margin:0 auto 20px;">
              Try raising your budget limit or adjusting your pax count to see more recommendations.
            </div>
            <a href="ai-recommend.php" class="btn btn-gold btn-sm">
              <i class="fa-solid fa-rotate-left"></i> View All Available Rooms
            </a>
          </div>
        <?php else: ?>
          <?php foreach ($recommendations as $index => $rec): 
              $amenitiesList = json_decode($rec['amenities'] ?? '[]', true) ?: [];
              $matchBadge = ($index === 0) 
                  ? '✨ AI Top Match' 
                  : (($rec['price_per_night'] <= 500) ? '💡 Best Budget Pick' : '⭐ Recommended');
          ?>
            <div class="room-card">
              <div class="room-card-img">
                <img src="<?= roomThumbUrl($rec['thumbnail']) ?>" alt="<?= h($rec['name']) ?>" onerror="this.src='../assets/images/rooms/default-room.jpg'">
                <div class="room-card-badge">
                  <span class="badge badge-gold" style="font-size:0.74rem;padding:4px 9px;font-weight:700;box-shadow:0 4px 10px rgba(0,0,0,0.6);">
                    <?= $matchBadge ?>
                  </span>
                </div>
              </div>

              <div class="room-card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                  <span style="font-size:0.75rem;color:var(--gold);text-transform:uppercase;font-weight:700;letter-spacing:0.5px;">
                    <?= h($rec['type_name'] ?? ($rec['is_dorm'] ? 'Dormitory' : 'Room')) ?>
                  </span>
                  <span style="font-size:0.75rem;color:var(--text-muted);display:flex;align-items:center;gap:4px;">
                    <i class="fa-solid fa-users"></i> Max <?= $rec['capacity'] ?> Pax
                  </span>
                </div>

                <h3 class="room-card-name"><?= h($rec['name']) ?></h3>

                <?php if (!empty($amenitiesList)): ?>
                  <div class="room-amenity-tags">
                    <?php foreach (array_slice($amenitiesList, 0, 3) as $am): ?>
                      <?= renderAmenityTag($am) ?>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <div class="room-card-footer">
                  <div>
                    <div class="room-price-main"><?= formatCurrency($rec['price_per_night']) ?></div>
                    <div class="room-price-sub"><?= $rec['is_dorm'] ? 'per bed / night' : 'per night' ?></div>
                  </div>
                  <a href="booking.php?room_id=<?= $rec['id'] ?>" class="btn btn-gold btn-sm" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;">
                    <span>Book Now</span>
                    <i class="fa-solid fa-arrow-right" style="font-size:0.72rem;"></i>
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<script src="../assets/js/core.js"></script>
<script src="../assets/js/user.js"></script>
</body>
</html>
