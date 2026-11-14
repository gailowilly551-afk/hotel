<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

// Add Room handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
    } else {
        $roomNum = trim($_POST['room_number']);
        $typeId  = (int)$_POST['room_type_id'];
        $name    = trim($_POST['name']);
        $floor   = (int)$_POST['floor'];
        $cap     = (int)$_POST['capacity'];
        $price   = (float)$_POST['price_per_night'];
        $desc    = trim($_POST['description']);

        // Handle thumbnail upload
        $thumbnail = 'default-room.jpg';
        if (!empty($_FILES['thumbnail']['name'])) {
            $uploaded = uploadFile($_FILES['thumbnail'], 'rooms', ALLOWED_IMAGE_TYPES, MAX_UPLOAD_SIZE);
            if ($uploaded) {
                $thumbnail = $uploaded;
            } else {
                setFlash('error', 'Invalid image type or the file size exceeds 5MB.');
            }
        }

        dbInsert(
            "INSERT INTO rooms (room_number, room_type_id, name, floor, capacity, price_per_night, description, thumbnail, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available')",
            [$roomNum, $typeId, $name, $floor, $cap, $price, $desc, $thumbnail]
        );

        auditLog('Added Room', 'Admin Module', "Added Room $roomNum - $name");
        setFlash('success', 'New room added successfully!');
        header("Location: rooms.php");
        exit;
    }
}

// Delete Room handler — POST + CSRF-protected (hindi kayang i-trigger ng cross-site GET/`<img>`)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_room'])) {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
    } else {
        $delId   = (int)$_POST['room_id'];
        $delRoom = dbRow("SELECT thumbnail FROM rooms WHERE id = ?", [$delId]);
        dbExec("DELETE FROM rooms WHERE id = ?", [$delId]);
        // Tanggalin ang uploaded thumbnail file (iwas orphan files)
        if ($delRoom && strpos($delRoom['thumbnail'] ?? '', '/') !== false) {
            deleteUpload($delRoom['thumbnail']);
        }
        auditLog('Deleted Room', 'Admin Module', "Deleted Room ID $delId");
        setFlash('warning', 'Room deleted.');
    }
    header("Location: rooms.php");
    exit;
}

$rooms = dbRows("SELECT r.*, rt.name as type_name FROM rooms r JOIN room_types rt ON r.room_type_id = rt.id ORDER BY r.id DESC");
$roomTypes = dbRows("SELECT * FROM room_types");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Rooms — Admin Portal</title>
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
        <div class="topbar-title">Room and Dormitory Management</div>
      </div>
    </header>

    <div class="page-content">
      <?= renderFlash() ?>

      <div class="page-header">
        <div>
          <h1 class="page-header-title">Room List</h1>
          <div class="page-header-sub">Add, edit, or delete hotel inventory.</div>
        </div>
      </div>

      <!-- Add Room Accordion / Form -->
      <div class="glass-card" style="padding:24px;margin-bottom:28px;">
        <h3 style="font-family:var(--font-serif);font-size:1.15rem;color:var(--gold);margin-bottom:14px;"><i class="fa-solid fa-plus-circle"></i> Add New Room</h3>
        <form action="" method="POST" enctype="multipart/form-data" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:14px;">
          <?= csrfField() ?>
          <input type="hidden" name="add_room" value="1">
          <div>
            <label class="form-label">Room Number</label>
            <input type="text" name="room_number" class="form-control" placeholder="e.g., 105" required>
          </div>
          <div>
            <label class="form-label">Room Type</label>
            <select name="room_type_id" class="form-control" required>
              <?php foreach ($roomTypes as $tp): ?>
                <option value="<?= $tp['id'] ?>"><?= h($tp['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g., Deluxe Suite 105" required>
          </div>
          <div>
            <label class="form-label">Floor</label>
            <input type="number" name="floor" class="form-control" value="1" required>
          </div>
          <div>
            <label class="form-label">Capacity (Pax)</label>
            <input type="number" name="capacity" class="form-control" value="2" required>
          </div>
          <div>
            <label class="form-label">Price / Night (₱)</label>
            <input type="number" step="0.01" name="price_per_night" class="form-control" placeholder="1500.00" required>
          </div>
          <div>
            <label class="form-label">Thumbnail Image</label>
            <input type="file" name="thumbnail" class="form-control" accept="image/*">
            <small style="color:var(--text-muted);font-size:0.75rem;">JPG, PNG, WebP (Max 5MB)</small>
          </div>
          <div style="grid-column:1/-1;">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" style="min-height:60px;" placeholder="Describe the room, amenities, and view..."></textarea>
          </div>
          <button type="submit" class="btn btn-gold" style="grid-column:1/-1;width:fit-content;"><i class="fa-solid fa-save"></i> Save Room</button>
        </form>
      </div>

      <!-- Rooms Inventory Table -->
      <div class="card">
        <div class="card-body" style="padding:0;">
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th class="col-nowrap">No.</th>
                  <th>Name</th>
                  <th class="col-nowrap">Type</th>
                  <th class="col-nowrap">Floor</th>
                  <th class="col-nowrap">Capacity</th>
                  <th class="col-amount">Price / Night</th>
                  <th class="col-nowrap">Status</th>
                  <th class="col-nowrap">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rooms as $r): ?>
                  <tr>
                    <td class="col-nowrap"><strong>#<?= h($r['room_number']) ?></strong></td>
                    <td><?= h($r['name']) ?></td>
                    <td class="col-nowrap"><span class="badge badge-gold"><?= h($r['type_name']) ?></span></td>
                    <td class="col-nowrap">Floor <?= $r['floor'] ?></td>
                    <td class="col-nowrap"><?= $r['capacity'] ?> pax</td>
                    <td class="col-amount" style="color:var(--gold);font-weight:700;"><?= formatCurrency($r['price_per_night']) ?></td>
                    <td class="col-nowrap"><?= statusBadge($r['status']) ?></td>
                    <td class="col-nowrap">
                      <form action="rooms.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this room?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="delete_room" value="1">
                        <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                      </form>
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
