<?php
// ============================================================
// MARVELOUS HOTEL — Staff Operations AI Co-Pilot
// ai/staff-assistant.php
// ============================================================

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/nvidia-client.php';

if (!isLoggedIn() || (!isStaff() && !isAdmin())) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// 1. Gather live operational metrics
$today = date('Y-m-d');

$arrivals = dbRows("
    SELECT b.booking_code, b.num_guests, r.room_number, r.name as room_name, u.first_name, u.last_name, b.status
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN users u ON b.user_id = u.id
    WHERE b.check_in = ? AND b.status IN ('confirmed', 'checked_in')
", [$today]);

$departures = dbRows("
    SELECT b.booking_code, r.room_number, r.name as room_name, u.first_name, u.last_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN users u ON b.user_id = u.id
    WHERE b.check_out = ? AND b.status = 'checked_in'
", [$today]);

$dirtyRooms = dbRows("
    SELECT room_number, name, floor, status 
    FROM rooms 
    WHERE status IN ('dirty', 'maintenance')
    ORDER BY floor ASC, room_number ASC
");

$pendingBookingsCount = dbRow("SELECT COUNT(*) as cnt FROM bookings WHERE status = 'pending'")['cnt'] ?? 0;

$arrivalsCount = count($arrivals);
$departuresCount = count($departures);
$dirtyCount = count($dirtyRooms);

// 2. Format a smart briefing prompt for NVIDIA NIM AI
$prompt = "You are Marvi AI, the Operations Assistant for Marvelous Hotel and Dormitory Frontdesk Staff. "
    . "Create a warm, organized, and encouraging Shift Operational Briefing in professional Taglish for the staff on duty today ($today).\n"
    . "Operational Data:\n"
    . "- Expected Arrivals Today: $arrivalsCount guests.\n"
    . "- Expected Departures (Check-outs) Today: $departuresCount guests.\n"
    . "- Rooms needing cleaning / Housekeeping priority: $dirtyCount rooms.\n"
    . "- Bookings pending frontdesk review: $pendingBookingsCount bookings.\n\n"
    . "Provide 3 concise bulleted sections: 1. Highlights for Today, 2. Housekeeping Priority, 3. Frontdesk Reminder. "
    . "Keep it brief, practical, and inspiring. Use standard text with clean bullet points.";

$aiText = null;
if (getSetting('ai_enabled', '1')) {
    $aiReply = queryNvidiaAI($prompt);
    if (!empty($aiReply)) {
        $aiText = nl2br(preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $aiReply));
    }
}

// 3. Fallback: Dynamic programmatic briefing if AI offline
if (empty($aiText)) {
    $aiText = "<strong>📋 Frontdesk Shift Operational Briefing (" . date('F d, Y') . ")</strong><br><br>";
    $aiText .= "<strong>1. Mga Inaasahang Bisita (Arrivals):</strong> May <strong>{$arrivalsCount}</strong> reservation na nakatakdang mag-check in simula 2:00 PM.<br>";
    $aiText .= "<strong>2. Mga Check-out Ngayong Araw:</strong> May <strong>{$departuresCount}</strong> kwarto na nakatakdang mag-check out bago mag-12:00 NN.<br>";
    $aiText .= "<strong>3. Housekeeping & Rooms:</strong> May <strong>{$dirtyCount}</strong> kwartong kailangang linisin o i-sanitize bago ibigay sa mga bagong dating.<br>";
    if ($pendingBookingsCount > 0) {
        $aiText .= "<strong>4. Pending Queue:</strong> May <strong>{$pendingBookingsCount}</strong> booking na naghihintay ng confirmation.<br>";
    }
    $aiText .= "<br><em>Paalala: Siguraduhing na-verify ang valid ID at resibo ng guest bago ibigay ang room keycard. Magandang araw at masayang paglilingkod!</em>";
}

echo json_encode([
    'success' => true,
    'briefing' => $aiText,
    'metrics' => [
        'arrivals' => $arrivalsCount,
        'departures' => $departuresCount,
        'dirty' => $dirtyCount,
        'pending' => $pendingBookingsCount
    ]
]);
