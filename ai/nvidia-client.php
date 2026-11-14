<?php
// ============================================================
// MARVELOUS HOTEL — NVIDIA NIM AI Engine Integration
// ai/nvidia-client.php
// Powered by NVIDIA NIM (google/diffusiongemma-26b-a4b-it)
// ============================================================

if (!defined('SITE_ROOT')) {
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/functions.php';
}

if (!function_exists('detectMessageLanguage')) {
    /**
     * Accurately detects whether a message is in English or Tagalog/Filipino.
     * Returns 'tl' for Tagalog/Taglish, 'en' for English.
     */
    function detectMessageLanguage(string $message): string {
        $clean = mb_strtolower(trim($message), 'UTF-8');
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean);
        $words = array_filter(explode(' ', $clean));
        if (empty($words)) return 'en';

        // Characteristic Tagalog words and markers
        $tagalogMarkers = [
            'ang', 'mga', 'ng', 'sa', 'at', 'ay', 'na', 'pa', 'po', 'opo', 'ho', 'oho',
            'ba', 'ko', 'mo', 'ka', 'niya', 'nila', 'kami', 'tayo', 'kayo', 'natin', 'namin',
            'ano', 'anong', 'paano', 'pano', 'magkano', 'kano', 'saan', 'san', 'kailan', 'kelan',
            'sino', 'bakit', 'meron', 'mayroon', 'may', 'wala', 'walang', 'merong',
            'pwede', 'pede', 'maaari', 'maari', 'dapat', 'gusto', 'nais', 'kailangan',
            'kwarto', 'kuwarto', 'oras', 'bayad', 'magbayad', 'bayaran', 'araw', 'gabi',
            'tanghali', 'umaga', 'hapon', 'salamat', 'kamusta', 'kumusta', 'tulong',
            'rekomenda', 'mura', 'murang', 'mahal', 'tulugan', 'gamit', 'dito', 'doon', 'dyan',
            'ninyo', 'inyo', 'aming', 'atin', 'nandito', 'nasaan', 'tumatanggap', 'subalit',
            'pero', 'habang', 'kapag', 'kung', 'tapos', 'tsaka', 'kasi', 'kase', 'naman', 'nga'
        ];

        $tagalogCount = 0;
        foreach ($words as $word) {
            if (in_array($word, $tagalogMarkers, true)) {
                $tagalogCount++;
            }
        }
        if ($tagalogCount === 0) {
            foreach ($words as $word) {
                if (preg_match('/^(mag|nag|pag|um|makapag|napaka|pinaka)/i', $word) && strlen($word) > 4) {
                    $tagalogCount++;
                }
            }
        }

        return ($tagalogCount > 0) ? 'tl' : 'en';
    }
}

/**
 * Send a message to NVIDIA NIM API and return the assistant response.
 *
 * @param string $userMessage
 * @param array $options
 * @return string|null Response string on success, or null on error/fallback
 */
function queryNvidiaAI(string $userMessage, array $options = []): ?string {
    $defaultKey = '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';
    $defaultModel = 'google/diffusiongemma-26b-a4b-it';
    $apiKey = $defaultKey;
    $model  = $defaultModel;

    try {
        if (function_exists('getSetting')) {
            $apiKey = getSetting('ai_nvidia_key', $defaultKey) ?: $defaultKey;
            $model  = getSetting('ai_nvidia_model', $defaultModel) ?: $defaultModel;
        }
    } catch (\Throwable $e) {
        $apiKey = $defaultKey;
        $model  = $defaultModel;
    }

    $url = 'https://integrate.api.nvidia.com/v1/chat/completions';

    if (empty($apiKey)) {
        return null;
    }

    // Language detection: enforce exact match
    $lang = $options['lang'] ?? detectMessageLanguage($userMessage);
    if ($lang === 'en') {
        $langDirective = <<<LANG
============================================================
CRITICAL LANGUAGE DIRECTIVE (MANDATORY RULE):
THE USER IS ASKING IN ENGLISH.
YOU MUST RESPOND 100% IN NATURAL, POLITE, PROFESSIONAL ENGLISH.
DO NOT USE ANY TAGALOG OR FILIPINO WORDS (ABSOLUTELY NO 'po', 'opo', 'kumusta', 'magandang araw', etc.).
KEEP ALL POLICIES, INSTRUCTIONS, AND GREETINGS ENTIRELY IN ENGLISH.
============================================================
LANG;
    } else {
        $langDirective = <<<LANG
============================================================
CRITICAL LANGUAGE DIRECTIVE (MANDATORY RULE):
ANG USER AY NAGTATANONG SA TAGALOG / FILIPINO.
SAGUTIN ITO NANG BUO SA NATURAL, MAGALANG, AT MALINAW NA TAGALOG / FILIPINO (O TAGLISH).
GUMAMIT NG MAGAGALANG NA PANANALITA TULAD NG 'PO' AT 'OPO'.
============================================================
LANG;
    }

    // Fetch rooms that are currently booked / confirmed / paid / checked-in safely
    $bookedRoomNames = [];
    try {
        if (function_exists('isDbAvailable') && isDbAvailable() && function_exists('dbRows')) {
            $bookedRows = dbRows("
                SELECT DISTINCT r.name, r.room_number 
                FROM rooms r 
                JOIN bookings b ON r.id = b.room_id 
                WHERE b.status IN ('confirmed', 'paid', 'checked_in') OR r.status IN ('occupied', 'reserved')
            ");
            foreach ($bookedRows as $br) {
                $bookedRoomNames[] = $br['name'] . ' (Room ' . $br['room_number'] . ')';
            }
        }
    } catch (\Throwable $e) {}

    $bookedNotice = !empty($bookedRoomNames) 
        ? "STRICTLY BOOKED / UNAVAILABLE ROOMS: " . implode(', ', $bookedRoomNames) . ". These rooms are already booked or occupied. DO NOT recommend or suggest these rooms to any user under any circumstances!"
        : "All active rooms currently listed as available can be booked.";

    $systemPrompt = <<<PROMPT
{$langDirective}

You are Marvi, the friendly, polite, and helpful AI concierge and assistant of Marvelous Hotel and Dormitory.

HOTEL INFORMATION & STRICT POLICIES:
1. PAYMENT METHODS:
   - We accept STRICTLY PayMaya (Maya) and GCash ONLY.
   - We DO NOT accept Bank Transfer or over-the-counter cash.
   - Official Maya (PayMaya) QR & Account: WILLY GAILO (@willygailojr / +63 *** *** 6963), with InstaPay QR scannable via Maya, GCash (Scan QR), or PH bank apps.
   - Official GCash: 0912 345 6789 (Marvelous Hotel Admin).
   - How to Pay:
     a. Scan the Maya InstaPay QR code using Maya or GCash (Scan QR) OR send directly via GCash.
     b. Copy the Transaction Reference Number and take a screenshot of the receipt.
     c. Go to your User Dashboard -> "My Bookings" -> click "Pay", enter the Reference Number, and upload the screenshot.
     d. The hotel administration will verify it promptly.

2. HOW TO BOOK A ROOM:
   - Go to "Browse Rooms" on the website or log in to your User Account.
   - Complete your Account Profile (upload profile picture, address location, and valid ID document).
   - Select your preferred available room type or dormitory bed.
   - Choose your Check-in and Check-out dates.
   - Click "Book Now / Confirm Booking".
   - Pay using GCash or PayMaya and upload your receipt in the dashboard.

3. ROOM AVAILABILITY & STRICT LOCKING POLICY:
   - {$bookedNotice}

4. SCHEDULE & POLICIES:
   - Standard Check-in Time: 2:00 PM (14:00).
   - Standard Check-out Time: 12:00 PM (12:00 NN / Tanghali).
   - Early check-in or late check-out requests can be made upon booking, subject to room availability.

5. ROOMS, RATES & BUDGET RECOMMENDATIONS:
   - Dormitory Bed (Female Wing D01 & Male Wing D02): ₱350/night (Air conditioning, personal lockers, WiFi, shared bathroom).
   - Standard Room 101 & 102: ₱800/night (Private room, Air Con, TV, WiFi, Hot Shower, Refrigerator, Garden/Street view, up to 2 guests).
   - Deluxe Room 201 & 202: ₱1,500 - ₱1,600/night (King-size bed, Smart TV, Safe box, Balcony, Breakfast, up to 2 guests).
   - Studio Unit S01: ₱1,800/night (Kitchenette, workspace, Smart TV, Hot Shower).
   - Family Room 401: ₱2,200/night (Two double beds, spacious, up to 4 guests).
   - Executive Suite 301: ₱3,500/night (Luxury suite, hot tub, mini bar, balcony, panoramic view).

   * CRITICAL BUDGET MATCHING RULE:
   - When a user asks what room is best for a specific budget (e.g., "1k ano maganda room", "may 1k ako ano magandang kwarto", "1000 budget"):
     1. Strongly recommend the **Standard Room 101 or 102 (₱800/night)** as the best choice. Explain that it is a private room with Air Conditioning, WiFi, Hot Shower, and TV, perfectly within their ₱1,000 budget, leaving them with ₱200 savings!
     2. Mention that if they want a super budget-friendly stay, the **Dormitory Bed (₱350/night)** is also available.
     3. Direct them to book the Standard Room immediately.

6. AMENITIES:
   - Free High-Speed Fiber WiFi (up to 200 Mbps) in all rooms, dorms, and lobby.
   - 24/7 Security and Frontdesk assistance.

7. STRICT SECURITY & SAFETY GUARDRAILS (CONFIDENTIAL):
   - You are STRICTLY a hotel hospitality AI assistant. You must ONLY answer questions regarding hotel bookings, rooms, rates, amenities, check-in/out schedules, payment methods (GCash & Maya), and guest policies.
   - NEVER disclose, explain, or mention technical details such as server ports (e.g. 8080, 8081, 8082), server IP addresses, databases, source code files, API keys, or web hosting configurations.
   - NEVER teach, assist with, or discuss hacking, pen-testing, port scanning, URL tampering, vulnerability discovery, SQL injection, bypasses, or server attacks under any circumstances.
   - If a guest asks about forgotten passwords or account recovery: Politely instruct them to use the official "Forgot Password?" option on the Login page or visit the Frontdesk. Never instruct users to manipulate URLs, inspect network traffic, or modify browser settings.
   - If a user asks for administrative credentials, admin URLs, or staff access: Politely state that administrative areas are restricted to authorized hotel personnel only.

GUIDELINES:
- Keep your answers concise, clear, accurate, and welcoming.
- You can use clean HTML formatting such as <strong>, <br>, or <ul><li> where helpful for readable lists.
- Never recommend booked/occupied rooms to guests asking for available rooms.
- Never give out fake policies or accept payment channels other than GCash and PayMaya.
PROMPT;

    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage]
        ],
        'max_tokens' => 512,
        'temperature' => 0.6,
        'top_p' => 0.95
    ];

    $jsonPayload = json_encode($payload);
    $headers = [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    // Method 1: cURL (Preferred)
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $jsonPayload,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if (!$err && $httpCode === 200 && !empty($response)) {
            $data = json_decode($response, true);
            if (!empty($data['choices'][0]['message']['content'])) {
                return trim($data['choices'][0]['message']['content']);
            }
        }
    }

    // Method 2: Stream Context Fallback (if cURL extension is missing or fails)
    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers) . "\r\n",
            'content' => $jsonPayload,
            'timeout' => 30,
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);

    $raw = @file_get_contents($url, false, $context);
    if ($raw !== false && !empty($raw)) {
        $data = json_decode($raw, true);
        if (!empty($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }
    }

    return null;
}
