<?php
// ============================================================
// MARVELOUS HOTEL — Secure File Serve
// serve-file.php
// Serves files from outside webroot with access control
// ============================================================

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

$fileParam = $_GET['file'] ?? '';

if (!$fileParam) {
    http_response_code(400);
    die('Missing file parameter');
}

// Sanitize path - prevent directory traversal
$fileParam = ltrim($fileParam, '/');
$fileParam = str_replace('..', '', $fileParam);

// Validate file exists
$securePath = SECURE_UPLOADS_PATH;
$fullPath = $securePath . $fileParam;

if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    die('File not found');
}

// Verify the path is within secure uploads directory
$realSecure = realpath($securePath);
$realFile = realpath($fullPath);

if (!$realFile || strpos($realFile, $realSecure) !== 0) {
    http_response_code(403);
    die('Access denied');
}

// ---- Access control: payment proofs & identity documents (owner / admin / staff only) ----
$isProof = (strpos($fileParam, 'proofs/') === 0);
$isDoc   = (strpos($fileParam, 'documents/') === 0);

if ($isProof || $isDoc) {
    // If not logged in in current active portal session, check other portal session cookies (admin/staff/user)
    if (empty($_SESSION['user_id'])) {
        $otherPortals = ['MH_ADMIN_SESS', 'MH_STAFF_SESS', 'MH_USER_SESS'];
        $currentName = session_name();
        foreach ($otherPortals as $pName) {
            if ($pName !== $currentName && !empty($_COOKIE[$pName])) {
                @session_write_close();
                session_name($pName);
                @session_start();
                if (!empty($_SESSION['user_id'])) {
                    break;
                }
            }
        }
    }

    $currentUserId = $_SESSION['user_id'] ?? null;
    $currentUserRole = $_SESSION['user_role'] ?? null;

    if (!$currentUserId) {
        http_response_code(401);
        die('Login required');
    }

    if ($isProof && !in_array($currentUserRole, ['admin', 'staff'], true)) {
        $owner = dbRow("SELECT id FROM payments WHERE proof_image = ? AND user_id = ?", [$fileParam, $currentUserId]);
        if (!$owner) {
            http_response_code(403);
            die('Access denied');
        }
    }

    if ($isDoc && !in_array($currentUserRole, ['admin', 'staff'], true)) {
        $owner = dbRow("SELECT id FROM users WHERE id_document = ? AND id = ?", [$fileParam, $currentUserId]);
        if (!$owner) {
            http_response_code(403);
            die('Access denied');
        }
    }
}

// Check if file is an image for inline display
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $fullPath);
finfo_close($finfo);

// Determine if we should force download or display inline
$forceDownload = isset($_GET['download']) || !str_starts_with($mime, 'image/');

// Get file size
$fileSize = filesize($fullPath);

// Set headers
header('Content-Type: ' . $mime);
header('Content-Length: ' . $fileSize);
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');

if ($forceDownload) {
    $originalName = basename($fileParam);
    header('Content-Disposition: attachment; filename="' . $originalName . '"');
} else {
    header('Content-Disposition: inline');
}

// Stream file
readfile($fullPath);
exit;