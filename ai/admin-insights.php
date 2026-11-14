<?php
// ============================================================
// MARVELOUS HOTEL — Admin Executive AI Insights Engine
// ai/admin-insights.php
// ============================================================

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/nvidia-client.php';

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin authorization required.']);
    exit;
}

// 1. Compute financial and operational analytics
$totalRevenue = dbRow("SELECT SUM(amount) as total FROM payments WHERE status = 'verified'")['total'] ?? 0;
$monthRevenue = dbRow("SELECT SUM(amount) as total FROM payments WHERE status = 'verified' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")['total'] ?? 0;

$mayaRevenue = dbRow("SELECT SUM(amount) as total FROM payments WHERE status = 'verified' AND method = 'maya'")['total'] ?? 0;
$gcashRevenue = dbRow("SELECT SUM(amount) as total FROM payments WHERE status = 'verified' AND method = 'gcash'")['total'] ?? 0;

$totalRooms = dbRow("SELECT COUNT(*) as cnt FROM rooms")['cnt'] ?? 1;
$occupiedRooms = dbRow("SELECT COUNT(*) as cnt FROM rooms WHERE status = 'occupied'")['cnt'] ?? 0;
$occupancyRate = round(($occupiedRooms / max(1, $totalRooms)) * 100, 1);

$totalBookings = dbRow("SELECT COUNT(*) as cnt FROM bookings")['cnt'] ?? 0;
$confirmedBookings = dbRow("SELECT COUNT(*) as cnt FROM bookings WHERE status = 'confirmed'")['cnt'] ?? 0;

// 2. Query NVIDIA NIM Live AI Engine
$prompt = "You are Marvi Executive AI, the Business Intelligence advisor for Marvelous Hotel and Dormitory Management. "
    . "Provide a concise, high-level Strategic Executive Digest in professional Taglish for the Hotel Administrator based on the following hotel data:\n"
    . "- Total All-Time Verified Revenue: PHP " . number_format($totalRevenue, 2) . "\n"
    . "- This Month Revenue: PHP " . number_format($monthRevenue, 2) . "\n"
    . "- PayMaya (Maya) Channel Share: PHP " . number_format($mayaRevenue, 2) . "\n"
    . "- GCash Channel Share: PHP " . number_format($gcashRevenue, 2) . "\n"
    . "- Current Room Occupancy Rate: {$occupancyRate}% ({$occupiedRooms} of {$totalRooms} rooms occupied)\n"
    . "- Total Confirmed Bookings: {$confirmedBookings} of {$totalBookings}\n\n"
    . "Structure into 3 brief sections: 1. Financial Performance & Payment Channels, 2. Occupancy & Operations, 3. Strategic Action Item (promo or pricing suggestion). "
    . "Keep it executive, data-driven, and actionable. Use bullet points.";

$aiReport = null;
if (getSetting('ai_enabled', '1')) {
    $aiReply = queryNvidiaAI($prompt);
    if (!empty($aiReply)) {
        $aiReport = nl2br(preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $aiReply));
    }
}

// 3. Programmatic Fallback
if (empty($aiReport)) {
    $aiReport = "<strong>📈 Executive AI Performance Digest (" . date('F Y') . ")</strong><br><br>";
    $aiReport .= "<strong>1. Pananalapi & Payment Channels:</strong> Ang kabuuang naipong kita ay umabot sa <strong>PHP " . number_format($totalRevenue, 2) . "</strong>. Sa mga payment methods, nakapagtala ang Maya ng <strong>PHP " . number_format($mayaRevenue, 2) . "</strong> at GCash ng <strong>PHP " . number_format($gcashRevenue, 2) . "</strong>.<br>";
    $aiReport .= "<strong>2. Room Occupancy:</strong> Kasalukuyang nasa <strong>{$occupancyRate}%</strong> ang occupancy ({$occupiedRooms} sa {$totalRooms} kabuuang kwarto).<br>";
    $aiReport .= "<strong>3. Strategic Recommendation:</strong> ";
    if ($occupancyRate < 50) {
        $aiReport .= "Medyo mababa ang occupancy ngayong araw. Inirerekomenda ang pag-activate ng discount promo (hal. WELCOME10 o SUMMER500) para sa dormitory at standard rooms.";
    } else {
        $aiReport .= "Maganda ang takbo ng bookings at mataas ang occupancy. Panatilihing mabilis ang room turnover ng housekeeping.";
    }
}

echo json_encode([
    'success' => true,
    'report' => $aiReport,
    'metrics' => [
        'total_revenue' => $totalRevenue,
        'month_revenue' => $monthRevenue,
        'occupancy_rate' => $occupancyRate,
        'maya_revenue' => $mayaRevenue,
        'gcash_revenue' => $gcashRevenue
    ]
]);
