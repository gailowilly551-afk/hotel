<?php
// ============================================================
// MARVELOUS HOTEL — AI Chatbot Backend Engine
// ai/chatbot.php
// ============================================================

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/nvidia-client.php';

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if (empty($message)) {
    echo json_encode(['status' => 'error', 'response' => 'No message received.']);
    exit;
}

$userId = currentUserId();
$lowerMsg = mb_strtolower($message, 'UTF-8');

// Detect user language (English vs Tagalog/Filipino)
$lang = detectMessageLanguage($message);

// ------------------------------------------------------------
// HELPER: Extract Budget from Query (e.g. "1k", "1000", "₱1,000", "500 pesos")
// ------------------------------------------------------------
function extractBudgetFromQuery(string $msg): ?float {
    $clean = str_replace([",", "₱", "php", "pesos", "peso"], "", mb_strtolower($msg, "UTF-8"));
    if (preg_match("/\b(\d+(?:\.\d+)?)\s*(?:k|kilo)\b/i", $clean, $m)) {
        return (float)$m[1] * 1000.0;
    }
    if (preg_match("/\b(\d+(?:\.\d+)?)\s*libo\b/i", $clean, $m)) {
        return (float)$m[1] * 1000.0;
    }
    if (preg_match("/\b(\d{3,6})\b/", $clean, $m)) {
        return (float)$m[1];
    }
    return null;
}

// ------------------------------------------------------------
// HELPER: Fetch Best Room for Budget with Resilient Fallbacks
// ------------------------------------------------------------
function getBestRoomForBudget(?float $budget): array {
    $room = null;
    $cheaperAlt = null;

    if ($budget !== null && isDbAvailable() && function_exists('dbRows')) {
        try {
            $candidates = dbRows("
                SELECT r.*, rt.name as type_name 
                FROM rooms r 
                JOIN room_types rt ON r.room_type_id = rt.id 
                WHERE r.status = 'available' 
                  AND r.price_per_night <= ?
                  AND r.id NOT IN (
                      SELECT room_id FROM bookings WHERE status IN ('confirmed', 'paid', 'checked_in')
                  )
                ORDER BY r.price_per_night DESC
            ", [$budget]);

            if (!empty($candidates)) {
                $room = $candidates[0]; // best/highest quality room that fits budget
                if (count($candidates) > 1) {
                    $cheaperAlt = end($candidates); // lowest cost option (e.g. dorm)
                }
            }
        } catch (\Throwable $e) {}
    }

    // Default Seed Fallbacks if DB is offline or table empty
    if (!$room) {
        if ($budget === null || $budget >= 800.0) {
            $room = [
                'id' => 1,
                'name' => 'Standard Room 101',
                'type_name' => 'Standard Room',
                'price_per_night' => 800.00,
                'floor' => 1,
                'capacity' => 2,
                'amenities' => '["WiFi","Air Con","TV","Hot Shower","Ref"]',
                'description' => 'A cozy and clean standard room on the ground floor with garden view.'
            ];
            $cheaperAlt = [
                'id' => 7,
                'name' => 'Dormitory Bed - Female Wing',
                'type_name' => 'Dormitory',
                'price_per_night' => 350.00,
                'floor' => 1,
                'capacity' => 1,
                'amenities' => '["WiFi","Air Con","Locker","Shared Bathroom"]',
                'description' => 'Clean dormitory bed with lockers and shared bathroom.'
            ];
        } else {
            $room = [
                'id' => 7,
                'name' => 'Dormitory Bed',
                'type_name' => 'Dormitory',
                'price_per_night' => 350.00,
                'floor' => 1,
                'capacity' => 1,
                'amenities' => '["WiFi","Air Con","Locker","Shared Bathroom"]',
                'description' => 'Budget friendly bed with air conditioning and WiFi.'
            ];
        }
    }

    return ['best' => $room, 'cheaper' => $cheaperAlt];
}

// ------------------------------------------------------------
// HELPER: Build Interactive Clickable Room Card HTML
// ------------------------------------------------------------
function buildInteractiveRoomCard(array $room, ?float $budget = null, string $lang = 'tl'): string {
    $roomId = (int)($room['id'] ?? 1);
    $name = htmlspecialchars($room['name'] ?? 'Standard Room 101');
    $typeName = htmlspecialchars($room['type_name'] ?? 'Standard Room');
    $price = (float)($room['price_per_night'] ?? 800.0);
    $floor = (int)($room['floor'] ?? 1);
    $capacity = (int)($room['capacity'] ?? 2);
    
    // Parse amenities safely
    $rawAmenities = $room['amenities'] ?? [];
    if (is_string($rawAmenities)) {
        $rawAmenities = json_decode($rawAmenities, true) ?: ['WiFi', 'Air Con', 'Hot Shower'];
    }
    $amenityList = implode(' · ', array_slice((array)$rawAmenities, 0, 4));

    // Calculate budget savings if budget is given
    $savingsHtml = '';
    $badgeText = ($lang === 'en') ? 'Top Value Pick' : 'Pinakamagandang Rekomendasyon';
    if ($budget !== null && $budget >= $price) {
        $diff = $budget - $price;
        if ($diff > 0) {
            $savingsLabel = ($lang === 'en') ? "Save ₱" . number_format($diff, 2) : "May sukli kang ₱" . number_format($diff, 2);
            $savingsHtml = '<span class="savings-pill"><i class="fa-solid fa-piggy-bank"></i> ' . $savingsLabel . '!</span>';
        }
        $badgeText = ($lang === 'en') ? "Fits your ₱" . number_format($budget, 0) . " budget" : "Pasok sa ₱" . number_format($budget, 0) . " badyet";
    }

    $bookUrl = SITE_URL . '/user/booking.php?room_id=' . $roomId;
    $viewUrl = SITE_URL . '/rooms.php?q=' . urlencode($room['name'] ?? 'Standard');
    $imgUrl  = roomThumbUrl($room['thumbnail'] ?? '');

    $bookBtnText = ($lang === 'en') 
        ? "Book {$name} (₱" . number_format($price, 0) . ")" 
        : "I-book ang {$name} (₱" . number_format($price, 0) . ")";

    $viewBtnText = ($lang === 'en') ? "View Room Details" : "Tingnan ang Detalye";

    return <<<HTML
<div class="ai-room-card">
  <div class="ai-room-card-img-wrap">
    <img src="{$imgUrl}" alt="{$name}" class="ai-room-card-img" onerror="this.src='../assets/images/rooms/default-room.jpg'">
    <div class="ai-room-card-badge"><i class="fa-solid fa-star"></i> {$badgeText}</div>
  </div>
  <div class="ai-room-card-name">🏨 {$name} <span style="font-size:0.8rem;color:var(--text-muted);font-weight:normal;">({$typeName})</span></div>
  <div class="ai-room-card-price">₱{$price} / night {$savingsHtml}</div>
  <div class="ai-room-card-desc">Floor {$floor} · Up to {$capacity} Pax<br>{$amenityList}</div>
  <div class="ai-room-card-actions">
    <a href="{$bookUrl}" class="ai-room-btn-book">
      <i class="fa-solid fa-calendar-check"></i> {$bookBtnText}
    </a>
    <a href="{$viewUrl}" class="ai-room-btn-view">
      <i class="fa-solid fa-eye"></i> {$viewBtnText}
    </a>
  </div>
</div>
HTML;
}

// 1. Check for Bad / Malicious Intent (Hacking, Injection, Exploits)
$badData = json_decode(file_get_contents(__DIR__ . '/training-data/bad-intent-keywords.json'), true);
$isFlagged = false;
$flagReason = '';

foreach ($badData['bad_keywords'] as $badWord) {
    if (str_contains($lowerMsg, $badWord)) {
        $isFlagged = true;
        $flagReason = "Detected restricted term: $badWord";
        break;
    }
}

if ($isFlagged) {
    $warnMsg = ($lang === 'en')
        ? "Warning: Your query was flagged for containing restricted terms. Our system monitors and logs security threats."
        : ($badData['warning_message'] ?? "Babala: Ang iyong mensahe ay na-flag dahil sa mga ipinagbabawal na salita.");

    // Log suspicious query in ai_logs and audit_logs safely
    if (isDbAvailable()) {
        try {
            dbExec(
                "INSERT INTO ai_logs (user_id, user_message, ai_response, intent, flagged, flag_reason) VALUES (?, ?, ?, 'security_alert', 1, ?)",
                [$userId, $message, $warnMsg, $flagReason]
            );
            auditLog('Security Violation in AI Chat', 'AI Module', "User/IP attempted query: {$message} | {$flagReason}");
        } catch (\Throwable $e) {}
    }

    echo json_encode([
        'status'  => 'warning',
        'is_alert'=> true,
        'response'=> $warnMsg,
        'lang'    => $lang
    ]);
    exit;
}

// ------------------------------------------------------------
// 1.5 CHECK MY BOOKING STATUS (For Logged-in Users)
// ------------------------------------------------------------
$isBookingQuery = str_contains($lowerMsg, 'booking ko') || str_contains($lowerMsg, 'status ng booking')
    || str_contains($lowerMsg, 'my booking') || str_contains($lowerMsg, 'check my reservation')
    || str_contains($lowerMsg, 'kamusta ang booking') || str_contains($lowerMsg, 'ano status ng room ko');

if ($isBookingQuery) {
    if (!$userId) {
        $aiResponse = ($lang === 'en')
            ? "To view your active booking status, please <a href='" . SITE_URL . "/auth/login.php' style='color:var(--gold);font-weight:700;'>Log in to your account</a> first!"
            : "Upang makita ang status ng iyong reservation, mangyaring <a href='" . SITE_URL . "/auth/login.php' style='color:var(--gold);font-weight:700;'>Mag-login sa iyong account</a> muna!";
        $intent = 'my_booking_guest';
    } else {
        $latestBooking = dbRow("
            SELECT b.*, r.name as room_name, r.room_number 
            FROM bookings b 
            JOIN rooms r ON b.room_id = r.id 
            WHERE b.user_id = ? 
            ORDER BY b.id DESC LIMIT 1
        ", [$userId]);

        if ($latestBooking) {
            $code = htmlspecialchars($latestBooking['booking_code']);
            $rName = htmlspecialchars($latestBooking['room_name']);
            $rNum = htmlspecialchars($latestBooking['room_number']);
            $status = strtoupper($latestBooking['status']);
            $checkIn = formatDate($latestBooking['check_in']);
            $checkOut = formatDate($latestBooking['check_out']);
            $detailUrl = SITE_URL . "/user/booking-detail.php?id=" . $latestBooking['id'];

            if ($lang === 'en') {
                $aiResponse = "📋 <strong>Here is your latest booking:</strong><br><br>"
                    . "• <strong>Booking Code:</strong> #{$code}<br>"
                    . "• <strong>Room:</strong> {$rName} (Room #{$rNum})<br>"
                    . "• <strong>Dates:</strong> {$checkIn} &rarr; {$checkOut}<br>"
                    . "• <strong>Current Status:</strong> <span style='color:var(--gold);font-weight:700;'>{$status}</span><br><br>"
                    . "<a href='{$detailUrl}' class='btn btn-gold btn-sm' style='display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:6px;font-size:0.8rem;'>"
                    . "<i class='fa-solid fa-receipt'></i> View Full Booking Voucher</a>";
            } else {
                $aiResponse = "📋 <strong>Ito po ang iyong pinakabagong reservation:</strong><br><br>"
                    . "• <strong>Booking Code:</strong> #{$code}<br>"
                    . "• <strong>Kwarto:</strong> {$rName} (Room #{$rNum})<br>"
                    . "• <strong>Petsa:</strong> {$checkIn} hanggang {$checkOut}<br>"
                    . "• <strong>Status:</strong> <span style='color:var(--gold);font-weight:700;'>{$status}</span><br><br>"
                    . "<a href='{$detailUrl}' class='btn btn-gold btn-sm' style='display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:6px;font-size:0.8rem;'>"
                    . "<i class='fa-solid fa-receipt'></i> Tingnan ang Booking Voucher</a>";
            }
        } else {
            $aiResponse = ($lang === 'en')
                ? "You don't have any reservations yet. Would you like to <a href='" . SITE_URL . "/rooms.php' style='color:var(--gold);font-weight:700;'>browse our available rooms</a>?"
                : "Wala ka pa pong aktibong reservation. Nais mo bang <a href='" . SITE_URL . "/rooms.php' style='color:var(--gold);font-weight:700;'>tingnan ang ating mga bakanteng kwarto</a>?";
        }
        $intent = 'my_booking_status';
    }
}

// ------------------------------------------------------------
// 2. DETECT BUDGET RECOMMENDATION INTENT (e.g. "1k ano maganda room")
// ------------------------------------------------------------
$detectedBudget = extractBudgetFromQuery($message);
$isRoomQuery = str_contains($lowerMsg, 'room') || str_contains($lowerMsg, 'kwarto') || str_contains($lowerMsg, 'kuwarto') 
    || str_contains($lowerMsg, 'maganda') || str_contains($lowerMsg, 'rekomenda') || str_contains($lowerMsg, 'recommend')
    || str_contains($lowerMsg, 'suggest') || str_contains($lowerMsg, 'badyet') || str_contains($lowerMsg, 'budget')
    || str_contains($lowerMsg, 'pera') || str_contains($lowerMsg, 'stay') || str_contains($lowerMsg, 'tulugan');

$aiEnabled = getSetting('ai_enabled', '1');
$aiResponse = null;
$intent = 'inquiry';

// If user explicitly asks for a budget room (e.g. 1k budget room)
if ($detectedBudget !== null && $isRoomQuery) {
    $roomMatch = getBestRoomForBudget($detectedBudget);
    $bestRoom = $roomMatch['best'];
    $cheaperRoom = $roomMatch['cheaper'];

    $roomCardHtml = buildInteractiveRoomCard($bestRoom, $detectedBudget, $lang);

    // Try Live NVIDIA NIM AI first if enabled, and append interactive card
    if ($aiEnabled) {
        $nvidiaReply = queryNvidiaAI($message, ['lang' => $lang]);
        if (!empty($nvidiaReply)) {
            $sanitizedReply = preg_replace('/\b(?:port\s*)?(?:8080|8081|8082)\b/i', '[redacted]', $nvidiaReply);
            $sanitizedReply = preg_replace('/\b(?:admin\/[a-z0-9_-]+\.php|includes\/[a-z0-9_-]+\.php|\.env|config\.php)\b/i', '[restricted]', $sanitizedReply);
            $formattedReply = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $sanitizedReply);
            $formattedReply = nl2br($formattedReply);
            $aiResponse = $formattedReply . "<br>" . $roomCardHtml;
            $intent = 'budget_recommend_ai';
        }
    }

    // Local Intelligent Engine (Primary Fallback)
    if (empty($aiResponse)) {
        $diff = $detectedBudget - (float)$bestRoom['price_per_night'];
        $diffText = ($diff > 0) ? " (at may sukli ka pang <strong>₱" . number_format($diff, 2) . "</strong>)" : "";

        if ($lang === 'en') {
            $aiResponse = "For your budget of <strong>₱" . number_format($detectedBudget, 2) . "</strong>, the best room I highly recommend is our <strong>" . htmlspecialchars($bestRoom['name']) . "</strong> at only <strong>₱" . number_format($bestRoom['price_per_night'], 2) . "/night</strong>" . ($diff > 0 ? " (saving you <strong>₱" . number_format($diff, 2) . "</strong>!)" : "!") . "<br><br>"
                . "✨ <strong>Why it's the best pick:</strong><br>"
                . "• Full private room with Air Conditioning, High-Speed WiFi, TV, and Hot Shower.<br>"
                . "• Maximum comfort and privacy well within your ₱" . number_format($detectedBudget, 0) . " budget.<br>";
            if ($cheaperRoom && $cheaperRoom['id'] !== $bestRoom['id']) {
                $aiResponse .= "• <em>Looking for a cheaper option?</em> We also have our <strong>" . htmlspecialchars($cheaperRoom['name']) . "</strong> starting at only ₱" . number_format($cheaperRoom['price_per_night'], 2) . "/night!<br>";
            }
            $aiResponse .= "<br>Click the direct link below to book this room right away:" . $roomCardHtml;
        } else {
            $aiResponse = "Para sa iyong badyet na <strong>₱" . number_format($detectedBudget, 2) . "</strong>, ang <strong>pinakamagandang kwarto</strong> na nararapat para sa iyo ay ang aming <strong>" . htmlspecialchars($bestRoom['name']) . "</strong> sa halagang <strong>₱" . number_format($bestRoom['price_per_night'], 2) . "/gabi</strong> lamang" . $diffText . "!<br><br>"
                . "✨ <strong>Bakit ito ang pinakamagandang piliin para sa 1k?</strong><br>"
                . "• May sarili kang pribadong kwarto na may Air Conditioning, High-Speed WiFi, TV, at Hot Shower.<br>"
                . "• Kumportableng-kumportable at pasok na pasok sa iyong ₱" . number_format($detectedBudget, 0) . " na badyet nang hindi ka nagigipit.<br>";
            if ($cheaperRoom && $cheaperRoom['id'] !== $bestRoom['id']) {
                $aiResponse .= "• <em>Kung nais mo naman ng mas mura pa:</em> Mayroon din tayong <strong>" . htmlspecialchars($cheaperRoom['name']) . "</strong> sa halagang ₱" . number_format($cheaperRoom['price_per_night'], 2) . "/gabi lamang.<br>";
            }
            $aiResponse .= "<br>Pindutin ang direct button sa ibaba upang agad ma-book ang kwartong ito:" . $roomCardHtml;
        }
        $intent = 'budget_recommend_local';
    }
}

// ------------------------------------------------------------
// 3. REGULAR QUERIES (NVIDIA NIM or FAQ MATCH)
// ------------------------------------------------------------
if (empty($aiResponse)) {
    if ($aiEnabled) {
        $nvidiaReply = queryNvidiaAI($message, ['lang' => $lang]);
        if (!empty($nvidiaReply)) {
            $sanitizedReply = preg_replace('/\b(?:port\s*)?(?:8080|8081|8082)\b/i', '[redacted]', $nvidiaReply);
            $sanitizedReply = preg_replace('/\b(?:admin\/[a-z0-9_-]+\.php|includes\/[a-z0-9_-]+\.php|\.env|config\.php)\b/i', '[restricted]', $sanitizedReply);
            $formattedReply = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $sanitizedReply);
            $formattedReply = nl2br($formattedReply);
            $aiResponse = $formattedReply;
            $intent = 'ai_live';
        }
    }

    // Local Knowledge Base (Bilingual FAQs & General Room Recommender)
    if (empty($aiResponse)) {
        $faqs = json_decode(file_get_contents(__DIR__ . '/training-data/faq.json'), true);
        if (is_array($faqs)) {
            foreach ($faqs as $faq) {
                foreach ($faq['keywords'] as $kw) {
                    if (str_contains($lowerMsg, $kw)) {
                        $aiResponse = ($lang === 'en')
                            ? ($faq['response_en'] ?? $faq['response_tl'] ?? $faq['response'] ?? '')
                            : ($faq['response_tl'] ?? $faq['response_en'] ?? $faq['response'] ?? '');
                        $intent = 'faq_match';
                        break 2;
                    }
                }
            }
        }

        // General Room Recommendation trigger without specific budget
        if (!$aiResponse && (str_contains($lowerMsg, 'recommend') || str_contains($lowerMsg, 'suggest') || str_contains($lowerMsg, 'rekomenda') || str_contains($lowerMsg, 'bagay'))) {
            $defaultMatch = getBestRoomForBudget(1000.0);
            $card = buildInteractiveRoomCard($defaultMatch['best'], 1000.0, $lang);
            if ($lang === 'en') {
                $aiResponse = "Based on our current availability, we highly recommend our <strong>" . htmlspecialchars($defaultMatch['best']['name']) . "</strong> for only ₱" . number_format($defaultMatch['best']['price_per_night'], 2) . "/night! It provides exceptional comfort and great value." . $card;
            } else {
                $aiResponse = "Base sa aking pagsusuri, subukan ang aming <strong>" . htmlspecialchars($defaultMatch['best']['name']) . "</strong> sa halagang ₱" . number_format($defaultMatch['best']['price_per_night'], 2) . "/gabi lamang! Napakaganda at komportable nito para sa iyong pamamalagi." . $card;
            }
            $intent = 'room_recommend';
        }

        // Default Fallback Response
        if (!$aiResponse) {
            if ($lang === 'en') {
                $aiResponse = "Hello! I am Marvi, your AI concierge at Marvelous Hotel. Feel free to ask me about room recommendations for your budget (e.g. '1k budget room'), how to pay via GCash/PayMaya, or check-in and check-out policies!";
            } else {
                $aiResponse = "Kumusta! Ako si Marvi, ang AI assistant ng Marvelous Hotel. Maaari mo akong tanungin tungkol sa mairerekomendang kwarto para sa iyong badyet (halimbawa: <em>'1k ano maganda room'</em>), paano magbayad (GCash at PayMaya ONLY), o oras ng check-in/check-out!";
            }
            $intent = 'fallback';
        }
    }
}

// Log conversation to database safely without crashing if DB is offline
if (isDbAvailable()) {
    try {
        dbExec(
            "INSERT INTO ai_logs (user_id, user_message, ai_response, intent, flagged) VALUES (?, ?, ?, ?, 0)",
            [$userId, $message, $aiResponse, $intent]
        );
    } catch (\Throwable $e) {}
}

echo json_encode([
    'status'   => 'success',
    'is_alert' => false,
    'response' => $aiResponse,
    'source'   => $intent,
    'lang'     => $lang
]);
