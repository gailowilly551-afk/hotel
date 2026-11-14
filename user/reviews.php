<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('user');

$userId = currentUserId();

// Eligible bookings for review (confirmed, checked_in, or checked_out bookings without existing review)
$eligibleBookings = dbRows("
    SELECT b.*, r.name as room_name 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    LEFT JOIN reviews rev ON b.id = rev.booking_id 
    WHERE b.user_id = ? AND b.status IN ('confirmed', 'checked_in', 'checked_out') AND rev.id IS NULL
    ORDER BY b.id DESC
", [$userId]);

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
    } else {
        $bId     = (int)($_POST['booking_id'] ?? 0);
        $rating  = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        $title   = trim($_POST['title'] ?? '');

        if ($rating < 1 || $rating > 5 || empty($comment)) {
            setFlash('error', 'Please provide a star rating (1-5) and write your review comment.');
        } else {
            $roomId = null;
            $bookingId = null;
            $bookingCode = '';

            if ($bId > 0) {
                $bk = dbRow("SELECT * FROM bookings WHERE id = ? AND user_id = ?", [$bId, $userId]);
                if ($bk) {
                    $bookingId = $bk['id'];
                    $roomId = $bk['room_id'];
                    $bookingCode = $bk['booking_code'];
                }
            } else {
                // If no specific booking chosen, pick their latest room if exists
                $latestBk = dbRow("SELECT room_id FROM bookings WHERE user_id = ? ORDER BY id DESC LIMIT 1", [$userId]);
                if ($latestBk) {
                    $roomId = $latestBk['room_id'];
                }
            }

            dbInsert(
                "INSERT INTO reviews (user_id, room_id, booking_id, rating, title, comment, status) 
                 VALUES (?, ?, ?, ?, ?, ?, 'approved')",
                [$userId, $roomId, $bookingId, $rating, $title ?: null, $comment]
            );

            auditLog('Submitted Room Review', 'User Module', "Review submitted by User #$userId" . ($bookingCode ? " for Booking #$bookingCode" : ''));
            setFlash('success', 'Maraming salamat! Naipasa na ang iyong review at makikita na ito sa Customer Reviews.');
            header("Location: reviews.php");
            exit;
        }
    }
}

// User past reviews
$myReviews = dbRows("
    SELECT rev.*, r.name as room_name 
    FROM reviews rev 
    JOIN rooms r ON rev.room_id = r.id 
    WHERE rev.user_id = ? 
    ORDER BY rev.id DESC
", [$userId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Reviews — Marvelous Hotel</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/user.css">
</head>
<body style="background:var(--navy);">

<div class="dashboard-layout">
<?php require_once __DIR__ . '/../includes/components/sidebar.php';
  renderSidebar('user'); ?>
  <main class="dashboard-main">
    <header class="topbar">
      <div style="display:flex;align-items:center;gap:12px;">
        <button class="user-mobile-toggle" data-sidebar-toggle><i class="fa-solid fa-bars"></i></button>
        <div class="topbar-title">Customer Feedback and Reviews</div>
      </div>
    </header>

    <div class="page-content">
      <?= renderFlash() ?>

      <div class="page-header">
        <div>
          <h1 class="page-header-title">Leave a Review</h1>
          <div class="page-header-sub">Share your stay experience to help other customers.</div>
        </div>
      </div>

      <!-- Review Form -->
      <div class="glass-card" style="padding:24px;margin-bottom:28px;">
        <h3 style="font-family:var(--font-serif);font-size:1.15rem;color:var(--gold);margin-bottom:14px;"><i class="fa-solid fa-pen"></i> Write Your Review</h3>
        <form action="" method="POST">
          <?= csrfField() ?>
          <input type="hidden" name="submit_review" value="1">
          <div class="form-group">
            <label class="form-label">Select Your Stay / Room</label>
            <select name="booking_id" class="form-control">
              <?php if (!empty($eligibleBookings)): ?>
                <?php foreach ($eligibleBookings as $el): ?>
                  <option value="<?= $el['id'] ?>">Booking #<?= h($el['booking_code']) ?> - <?= h($el['room_name']) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
              <option value="0">General Stay &amp; Customer Experience</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Rating (Stars)</label>
            <select name="rating" class="form-control" required>
              <option value="5" selected>⭐⭐⭐⭐⭐ (5 Stars - Excellent)</option>
              <option value="4">⭐⭐⭐⭐ (4 Stars - Good)</option>
              <option value="3">⭐⭐⭐ (3 Stars - Average)</option>
              <option value="2">⭐⭐ (2 Stars - Needs Improvement)</option>
              <option value="1">⭐ (1 Star - Not Satisfied)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Your Comment and Feedback</label>
            <textarea name="comment" class="form-control" rows="4" placeholder="Tell us about your experience with our rooms, staff, and amenities..." required></textarea>
          </div>
          <button type="submit" class="btn btn-gold"><i class="fa-solid fa-paper-plane"></i> Submit &amp; Post Review</button>
        </form>
      </div>

      <!-- Previous Reviews List -->
      <div class="card">
        <div class="card-header"><h2 class="card-title"><i class="fa-solid fa-comment-dots" style="color:var(--gold);margin-right:8px;"></i> My Previous Reviews</h2></div>
        <div class="card-body" style="padding:0;">
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Room</th>
                  <th>Rating</th>
                  <th>Comment</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($myReviews)): ?>
                  <tr><td colspan="4" class="text-center" style="padding:30px;color:var(--text-muted);">You have not written any reviews yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($myReviews as $rv): ?>
                    <tr>
                      <td><strong><?= h($rv['room_name']) ?></strong></td>
                      <td><?= starRating($rv['rating']) ?></td>
                      <td style="color:var(--text-muted);font-style:italic;">"<?= h($rv['comment']) ?>"</td>
                      <td><?= formatDate($rv['created_at']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="../assets/js/core.js"></script>
<script src="../assets/js/user.js"></script>
</body>
</html>
