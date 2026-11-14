#!/usr/bin/env bash
# ==============================================================================
# 🏨 MARVELOUS HOTEL & DORMITORY — ONE-CLICK CAPSTONE LIVE SHARING SCRIPT
# File: scripts/share-capstone.sh (Symlinked to project root)
# Supports: Cloudflare Tunnel, Ngrok Fallback, Local LAN Wi-Fi, & Terminal QR Code
# ==============================================================================

set -e

# 1. Resolve Project Root Directory dynamically
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do
    DIR="$(cd -P "$(dirname "$SOURCE")" && pwd)"
    SOURCE="$(readlink "$SOURCE")"
    [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE"
done
CURRENT_DIR="$(cd -P "$(dirname "$SOURCE")" && pwd)"

if [ -f "$CURRENT_DIR/index.php" ]; then
    HOTEL_DIR="$CURRENT_DIR"
elif [ -f "$CURRENT_DIR/../index.php" ]; then
    HOTEL_DIR="$(cd "$CURRENT_DIR/.." && pwd)"
else
    HOTEL_DIR="/opt/lampp/htdocs/hotel"
fi

cd "$HOTEL_DIR"

# Parse Command-Line Arguments
ACTION="${1:-}"

if [ "$ACTION" = "--stop" ] || [ "$ACTION" = "stop" ]; then
    echo "🛑 Isinasara ang lahat ng aktibong tunnels (Cloudflare / Ngrok)..."
    pkill -f "cloudflared tunnel" 2>/dev/null || true
    pkill -f "ngrok http" 2>/dev/null || true
    rm -f "$HOTEL_DIR/LIVE_URL.txt" /tmp/hotel_tunnel.pid /tmp/cloudflared.log /tmp/ngrok.log
    echo "✅ Nakasara na ang lahat ng live tunnels."
    exit 0
fi

if [ "$ACTION" = "--status" ] || [ "$ACTION" = "status" ]; then
    if pgrep -f "cloudflared tunnel" &>/dev/null || pgrep -f "ngrok http" &>/dev/null; then
        echo "🟢 May aktibong live tunnel na tumatakbo!"
        echo ""
        if [ -f "$HOTEL_DIR/LIVE_URL.txt" ]; then
            cat "$HOTEL_DIR/LIVE_URL.txt"
        elif [ -f /tmp/cloudflared.log ]; then
            ACTIVE_URL=$(grep -o 'https://[-a-zA-Z0-9@:%._\+~#=]*\.trycloudflare\.com' /tmp/cloudflared.log | tail -n 1 || true)
            echo "URL: $ACTIVE_URL"
        fi
    else
        echo "🔴 Walang tumatakbong live tunnel sa ngayon."
        echo "Patakbuhin: ./share-capstone.sh  (o ./share-capstone.sh --daemon para hindi mamatay kapag sinara ang terminal)"
    fi
    exit 0
fi

IS_DAEMON=0
if [ "$ACTION" = "--daemon" ] || [ "$ACTION" = "-d" ] || [ "$ACTION" = "daemon" ]; then
    IS_DAEMON=1
fi

clear 2>/dev/null || true
echo "================================================================================"
echo "🏨 INIHAHANDA ANG MARVELOUS HOTEL CAPSTONE LIVE PRESENTATION & CLOUD TUNNEL..."
echo "================================================================================"

# 2. Ensure Permissions on uploads and logs
echo "🛡️  Sinusuri ang File Permissions sa uploads at logs..."
chmod -R 777 "$HOTEL_DIR/uploads" "$HOTEL_DIR/logs" "$HOTEL_DIR/storage" 2>/dev/null || true

# 3. Detect Local Network IP for LAN / Wi-Fi Sharing
LOCAL_IP=$(hostname -I 2>/dev/null | awk '{print $1}' || ip -4 addr show scope global 2>/dev/null | grep -oP '(?<=inet\s)\d+(\.\d+){3}' | head -n 1 || true)
if [ -z "$LOCAL_IP" ]; then
    LOCAL_IP="127.0.0.1"
fi

# 4. Check & Start XAMPP Apache & MySQL Services
echo "🔍 Sinusuri ang XAMPP Web Server at Database..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/hotel/index.php 2>/dev/null || curl -s -o /dev/null -w "%{http_code}" http://localhost/index.php 2>/dev/null || echo "down")

if [ "$HTTP_STATUS" != "200" ] && [ "$HTTP_STATUS" != "302" ]; then
    echo "⚙️  Sinisimulan ang XAMPP stack (Apache & MySQL)..."
    if [ -x "/opt/lampp/lampp" ]; then
        if sudo -n true 2>/dev/null; then
            sudo /opt/lampp/lampp start >/dev/null 2>&1 || true
        else
            echo "willy" | sudo -S /opt/lampp/lampp start >/dev/null 2>&1 || sudo /opt/lampp/lampp start >/dev/null 2>&1 || true
        fi
        sleep 2
    elif command -v systemctl &>/dev/null; then
        sudo systemctl start apache2 mariadb 2>/dev/null || sudo service apache2 start 2>/dev/null || true
    fi
fi

# 5. Run Database Migrations Automatically
echo "🔄 Sinusuri at inilalapat ang Database Migrations..."
if [ -x "/opt/lampp/bin/php" ]; then
    /opt/lampp/bin/php migrate.php >/dev/null 2>&1 || true
elif command -v php &>/dev/null; then
    php migrate.php >/dev/null 2>&1 || true
fi

# 6. Detect Apache Port and Base Path dynamically
TARGET_PORT="80"
BASE_PATH="/hotel"

if curl -s -o /dev/null -f "http://localhost:8080/index.php" 2>/dev/null; then
    TARGET_PORT="8080"
    BASE_PATH=""
elif curl -s -o /dev/null -f "http://localhost:8080/hotel/index.php" 2>/dev/null; then
    TARGET_PORT="8080"
    BASE_PATH="/hotel"
elif curl -s -o /dev/null -f "http://localhost/index.php" 2>/dev/null && ! curl -s -o /dev/null -f "http://localhost/hotel/index.php" 2>/dev/null; then
    TARGET_PORT="80"
    BASE_PATH=""
fi

# 7. Clean up old tunnel processes & logs
pkill -f "cloudflared tunnel" 2>/dev/null || true
pkill -f "ngrok http" 2>/dev/null || true
rm -f /tmp/cloudflared.log /tmp/ngrok.log

# 8. Determine Tunnel Engine (Cloudflare vs Ngrok)
TUNNEL_URL=""
TUNNEL_ENGINE=""
TUNNEL_PID=""

cleanup() {
    echo ""
    echo "🛑 Isinasara ang live tunnel process..."
    if [ -n "$TUNNEL_PID" ]; then
        kill "$TUNNEL_PID" 2>/dev/null || true
    fi
    pkill -f "cloudflared tunnel" 2>/dev/null || true
    pkill -f "ngrok http" 2>/dev/null || true
    rm -f "$HOTEL_DIR/LIVE_URL.txt" /tmp/hotel_tunnel.pid 2>/dev/null || true
    echo "✅ Sarado na ang live link. Salamat at tagumpay ang presentation!"
    exit 0
}
trap cleanup SIGINT SIGTERM

# Locate Cloudflare binary if available
CLOUDFLARED_BIN=""
if command -v cloudflared &>/dev/null; then
    CLOUDFLARED_BIN="cloudflared"
elif [ -x "/tmp/cloudflared" ]; then
    CLOUDFLARED_BIN="/tmp/cloudflared"
elif [ -x "$HOME/bin/cloudflared" ]; then
    CLOUDFLARED_BIN="$HOME/bin/cloudflared"
elif [ -x "$HOME/.local/bin/cloudflared" ]; then
    CLOUDFLARED_BIN="$HOME/.local/bin/cloudflared"
elif [ -x "/usr/local/bin/cloudflared" ]; then
    CLOUDFLARED_BIN="/usr/local/bin/cloudflared"
fi

# Auto-download standalone Cloudflare binary if missing
if [ -z "$CLOUDFLARED_BIN" ]; then
    echo "📥 Cloudflared binary hindi nakita sa PATH. Sinusubukang i-download sa /tmp/..."
    if command -v curl &>/dev/null; then
        curl -fsSL --connect-timeout 8 -o /tmp/cloudflared https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 2>/dev/null && chmod +x /tmp/cloudflared 2>/dev/null || true
    elif command -v wget &>/dev/null; then
        wget -q --timeout=8 -O /tmp/cloudflared https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 2>/dev/null && chmod +x /tmp/cloudflared 2>/dev/null || true
    fi
    if [ -x "/tmp/cloudflared" ]; then
        CLOUDFLARED_BIN="/tmp/cloudflared"
        echo "✅ Handa na ang Cloudflare Tunnel binary."
    fi
fi

# Try Cloudflare first if available
if [ -n "$CLOUDFLARED_BIN" ]; then
    echo "🌐 Ikinokonekta sa Internet via Secure High-Speed Cloudflare Tunnel (Port $TARGET_PORT)..."
    "$CLOUDFLARED_BIN" tunnel --url "http://localhost:$TARGET_PORT" \
        --proxy-keepalive-connections 100 \
        --proxy-keepalive-timeout 90s \
        --proxy-tcp-keepalive 30s \
        --retries 5 \
        --logfile /tmp/cloudflared.log >/dev/null 2>&1 &
    TUNNEL_PID=$!
    TUNNEL_ENGINE="Cloudflare"

    for i in {1..20}; do
        if [ -f /tmp/cloudflared.log ]; then
            TUNNEL_URL=$(grep -o 'https://[-a-zA-Z0-9@:%._\+~#=]*\.trycloudflare\.com' /tmp/cloudflared.log | tail -n 1 || true)
            if [ -n "$TUNNEL_URL" ]; then
                break
            fi
        fi
        sleep 1
    done
fi

# Fallback to Ngrok if Cloudflare is not available or timed out
if [ -z "$TUNNEL_URL" ] && command -v ngrok &>/dev/null; then
    if [ -n "$TUNNEL_PID" ]; then
        kill "$TUNNEL_PID" 2>/dev/null || true
    fi
    echo "⚡ Cloudflare unavailable, lumilipat sa Ngrok Tunnel (Port $TARGET_PORT)..."
    ngrok http "$TARGET_PORT" --log=stdout > /tmp/ngrok.log 2>&1 &
    TUNNEL_PID=$!
    TUNNEL_ENGINE="Ngrok"

    for i in {1..20}; do
        if [ -f /tmp/ngrok.log ]; then
            TUNNEL_URL=$(grep -o 'https://[-a-zA-Z0-9@:%._\+~#=]*\.ngrok-free\.app' /tmp/ngrok.log | tail -n 1 || true)
            if [ -z "$TUNNEL_URL" ]; then
                TUNNEL_URL=$(curl -s http://127.0.0.1:4040/api/tunnels 2>/dev/null | grep -o 'https://[^"]*\.ngrok[^"]*' | head -n 1 || true)
            fi
            if [ -n "$TUNNEL_URL" ]; then
                break
            fi
        fi
        sleep 1
    done
fi

# If neither cloud tunnel succeeded, fallback to local network Wi-Fi link
if [ -z "$TUNNEL_URL" ]; then
    echo "⚠️  Walang cloud tunnel na kumonekta. Gagamitin ang Local Network (Wi-Fi) IP bilang default."
    TUNNEL_URL="http://${LOCAL_IP}:${TARGET_PORT}"
    TUNNEL_ENGINE="Local Wi-Fi / LAN"
fi

MAIN_LINK="${TUNNEL_URL}${BASE_PATH}/"
ROOMS_LINK="${TUNNEL_URL}${BASE_PATH}/rooms.php"
USER_PORTAL="${TUNNEL_URL}${BASE_PATH}/user/dashboard.php"
STAFF_LOGIN="${TUNNEL_URL}${BASE_PATH}/staff/login.php"
ADMIN_LOGIN="${TUNNEL_URL}${BASE_PATH}/admin/login.php"
LAN_LINK="http://${LOCAL_IP}:${TARGET_PORT}${BASE_PATH}/"

# Write active links to LIVE_URL.txt for convenience
cat << EOF > "$HOTEL_DIR/LIVE_URL.txt"
================================================================================
🏨 MARVELOUS HOTEL & DORMITORY — LIVE CAPSTONE PRESENTATION LINKS
Nilikha noong: $(date)
Active Engine: $TUNNEL_ENGINE
================================================================================

🌐 1. PUBLIC CLOUD SHAREABLE LINK (Internet / Mobile Data):
   👉 Main Hotel Page: $MAIN_LINK
   👉 Admin Login:     $ADMIN_LOGIN
   👉 Staff Login:     $STAFF_LOGIN
   👉 User Dashboard:  $USER_PORTAL

📶 2. LOCAL WI-FI / LAN LINK (Same Network):
   👉 Local URL:       $LAN_LINK

--------------------------------------------------------------------------------
📌 TEST ACCOUNTS & PASSWORDS:
• 👑 Super Admin: admin@marveloushotel.com / password
• 🛎️ Front Desk:  staff@marveloushotel.com / password
• 👤 Guest (KYC):  lan@gmail.com / password
================================================================================
EOF
chmod 666 "$HOTEL_DIR/LIVE_URL.txt" 2>/dev/null || true

# 9. Display Presentation Dashboard
clear 2>/dev/null || true
echo "================================================================================"
echo "🎉 🏨 MARVELOUS HOTEL & DORMITORY — LIVE CAPSTONE PRESENTATION IS ACTIVE!"
echo "================================================================================"
echo "⚡ Active Engine: $TUNNEL_ENGINE"
echo ""
echo "🔗 1. PUBLIC SHAREABLE CLOUD LINK (Kahit anong internet o phone data):"
echo "   👉 $MAIN_LINK"
echo ""
if [ "$LOCAL_IP" != "127.0.0.1" ]; then
echo "📶 2. LOCAL WI-FI / LAN LINK (Para sa mga phone/laptop sa parehong Wi-Fi):"
echo "   👉 $LAN_LINK"
echo ""
fi

# Print QR code if qrencode is installed
if command -v qrencode &>/dev/null; then
    echo "📱 I-SCAN ANG QR CODE GAMIT ANG PHONE CAMERA O QR SCANNER:"
    qrencode -t UTF8 "$MAIN_LINK"
    echo ""
fi

echo "--------------------------------------------------------------------------------"
echo "📌 MGA DIRECT PORTAL LINKS, ROLES, AT TEST CREDENTIALS:"
echo "--------------------------------------------------------------------------------"
echo ""
echo "1. 🌐👤 GUEST & PUBLIC PORTAL (Hotel Website, Rooms Catalog, Booking & Payments):"
echo "   Main Page:    $MAIN_LINK"
echo "   Browse Rooms: $ROOMS_LINK"
echo "   Dashboard:    $USER_PORTAL"
echo "   Test Account: lan@gmail.com (Verified Guest)"
echo "   Password:     password"
echo "   Features:     • Full KYC Identity Verification Requirement before booking"
echo "                 • Instant Payment Methods Pop-up Modal (PayMaya QR & GCash)"
echo "                 • Live Stay & Pricing Calculator (Nights x Rate)"
echo "                 • Unified 1-click Reservation + Payment Proof Submission"
echo "                 • Booking Voucher with Exact Activity Timestamps (Date/Time/Year)"
echo "                 • AI Concierge Chatbot assistant & dynamic room search"
echo ""
echo "2. 🛎️ STAFF / FRONTDESK PORTAL (Daily Operations & Delegated Approvals):"
echo "   URL:          $STAFF_LOGIN"
echo "   Email:        staff@marveloushotel.com"
echo "   Password:     password"
echo "   Features:     • Frontdesk Check-in / Check-out with automatic timestamps"
echo "                 • Live Housekeeping & Rooms Status Board"
echo "                 • Delegated Payment Verification (Receipt Inspector & Approval)"
echo "   Security:     User account management is strictly restricted to Super Admin."
echo ""
echo "3. 👑 SUPER ADMIN PORTAL (Full Executive Control & System Audit):"
echo "   URL:          $ADMIN_LOGIN"
echo "   Email:        admin@marveloushotel.com"
echo "   Password:     password"
echo "   Features:     • Master Bookings Registry with Decision Timestamps Audit"
echo "                 • Interactive Visual Timeline Modal per reservation"
echo "                 • User & Staff Account Management (Full Edit & Delete)"
echo "                 • KYC Guest ID Verification Inspection & Adjudication"
echo "                 • Payment Verification Hub with Receipt Lightbox"
echo "                 • Full Security Audit Logs & AI System Configuration"
echo ""
echo "--------------------------------------------------------------------------------"
echo "📂 Naka-save din ang kumpletong links sa: $HOTEL_DIR/LIVE_URL.txt"
echo "--------------------------------------------------------------------------------"

if [ "$IS_DAEMON" -eq 1 ]; then
    echo "$TUNNEL_PID" > /tmp/hotel_tunnel.pid
    trap - SIGINT SIGTERM
    echo "🚀 [DAEMON MODE NAKA-ENABLE] Tumatakbo ang tunnel sa background!"
    echo "💡 LIGTAS ISARA ANG TERMINAL. Mananatiling buhay ang link hangga't hindi ini-stop."
    echo "🛑 Kapag tapos na ang demo, patayin gamit ang: ./share-capstone.sh --stop"
    echo "================================================================================"
    exit 0
fi

echo "💡 PAALALA PARA SA CAPSTONE DEFENSE / LIVE DEMO:"
echo "• Huwag isasara ang terminal na ito habang nagpe-presenta sa panel."
echo "• O patakbuhin gamit ang: ./share-capstone.sh --daemon para hindi mamatay kahit isara ang terminal."
echo "• Lahat ng pagbabago sa code o database ay LIVE agad sa cloud link!"
echo "• Pindutin ang [CTRL + C] upang ihinto ang live cloud tunnel."
echo "================================================================================"
echo "⏳ Tumatakbo ang live server... (Press Ctrl+C to Stop)"

if [ -n "$TUNNEL_PID" ]; then
    wait "$TUNNEL_PID"
else
    # Keep alive if running purely on LAN
    while true; do sleep 1; done
fi
