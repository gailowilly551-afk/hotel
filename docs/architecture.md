# Marvelous Hotel — System Architecture & Directory Structure

Ipinapaliwanag ng dokumentong ito ang malinis at organisadong arkitektura ng codebase ng **Marvelous Hotel**.

---

## 1. Directory Tree Overview

```text
/opt/lampp/htdocs/hotel/
├── index.php                 # Public Landing Page (Hero, Showcase, Booking Bar)
├── about.php                 # Hotel Background & Amenities
├── rooms.php                 # Public Rooms Catalog
├── contact.php               # Guest Contact Form & Inquiries
├── 404.php / 500.php         # Error Handling Pages
├── serve-file.php            # Secure Uploads File Proxy & Access Gate
├── migrate.php               # CLI Database Migration Runner
│
├── admin/                    # 👑 Super Admin Portal (Restricted to 'admin' role)
│   ├── dashboard.php         # Executive Metrics, Occupancy, & Financial Digest
│   ├── bookings.php          # Master Reservations Ledger
│   ├── payments.php          # Official Payment Verification & Audit Lightbox
│   ├── rooms.php             # Room Inventory, Pricing & Floor Assignment
│   ├── users.php             # Guest Accounts & KYC Document Verification
│   ├── staff.php             # Staff Accounts Administration
│   ├── audit-logs.php        # Security Audit Trail (Staff & User Actions)
│   ├── ai-config.php         # AI System Configuration & Blocked Intent Logs
│   ├── settings.php          # Global Policies & Hotel Metadata
│   └── login.php             # Admin Authentication Entry
│
├── staff/                    # 🛎️ Frontdesk Staff Portal (Restricted to 'staff' role)
│   ├── dashboard.php         # Shift Briefing, Pending Bookings, & Pending Payments
│   ├── check-in.php          # Guest Arrivals & Departures (Check-in/Check-out)
│   ├── payments.php          # Frontdesk Delegated Payment Approvals (When Admin is Away)
│   ├── rooms-status.php      # Live Housekeeping & Room Status Board
│   ├── guests.php            # (Locked - Redirects to dashboard; accounts are Admin-only)
│   └── login.php             # Frontdesk Authentication Entry
│
├── user/                     # 👤 Guest Portal (Restricted to 'user' role)
│   ├── dashboard.php         # Guest Dashboard & Quick Stays Overview
│   ├── booking.php           # Room Reservation Funnel (with Availability Lock)
│   ├── booking-detail.php    # Official Booking Voucher & Payment Timestamps
│   ├── my-bookings.php       # Past & Upcoming Reservations
│   ├── payment.php           # QR Payment Settlement (Maya & GCash)
│   ├── payment-history.php   # Detailed Transaction History (Date, Year & Time)
│   ├── profile.php           # KYC Identity & Government ID Submission
│   ├── reviews.php           # Room Reviews & Star Ratings
│   ├── notifications.php     # Real-time System Alerts & Status Notifications
│   └── ai-recommend.php      # AI-Powered Room Matcher
│
├── auth/                     # 🔐 Authentication Module
│   ├── login.php             # Universal User Login
│   ├── register.php          # User Registration
│   ├── register-staff.php    # Restricted Staff Registration
│   ├── setup-admin.php       # First-Time Admin Bootstrap
│   ├── forgot-password.php   # Password Reset Request
│   ├── reset-password.php    # Password Reset Submission
│   ├── verify-email.php      # Email Verification Token Handler
│   ├── resend-verification.php # Resend Activation Token
│   └── logout.php            # Session Destroyer (Role-Aware Return)
│
├── ai/                       # 🤖 AI Engine & Assistants
│   ├── chatbot.php           # Public & Guest Concierge Chatbot Endpoint
│   ├── staff-assistant.php   # Shift Briefing & Frontdesk Co-Pilot Generator
│   ├── admin-insights.php    # Executive Business Intelligence & Revenue Digest
│   ├── nvidia-client.php     # API Integration Client (NVIDIA NIM / Gemini)
│   └── training-data/        # Knowledge Base & Prompts
│
├── includes/                 # ⚙️ Core Libraries & Shared Helpers
│   ├── config.php            # Central Config (Environment, Database, Encryption)
│   ├── db.php                # PDO Database Connection & Query Wrappers
│   ├── session.php           # Session Guards, CSRF Protection, Flash Messaging
│   ├── functions.php         # Shared Helpers (Formatters, Validation, KYC)
│   ├── logger.php            # Structured Audit & Error Logging
│   ├── rate-limiter.php      # Brute-force & Request Throttle
│   ├── email.php             # Mail Dispatcher (SMTP / Native)
│   ├── xml.php               # XML Data Parsing Helper
│   └── components/           # Shared UI Layout Components
│       ├── sidebar.php       # Unified Multi-Portal Sidebar
│       └── ai-chatbot.php    # Floating AI Widget
│
├── assets/                   # 🎨 Static Assets
│   ├── css/                  # Role-Separated CSS (base, public, user, staff, admin)
│   ├── js/                   # Core & Portal-Specific JavaScript
│   ├── images/               # Logos, Backgrounds, Maya QR Codes, Placeholders
│   └── xml/                  # XML Data Catalogs & Navigation Schemas
│
├── config/                   # 🛠️ Server & Deployment Configurations
│   └── apache/
│       ├── vhosts-linux.conf # Multi-Port Setup for Linux XAMPP (/opt/lampp)
│       └── vhosts-windows.conf # Multi-Port Setup for Windows XAMPP (C:\xampp)
│
├── database/                 # 🗄️ Database Schemas
│   └── marvelous_hotel.sql   # Master Database Schema & Initial Data
│
├── migrations/               # 🔄 Incremental Database Migrations
│   ├── 0001_contact_messages.sql
│   ├── ...
│   └── 0009_kyc_verification_lifecycle.sql
│
├── scripts/                  # 💻 Utility Scripts
│   └── share-capstone.sh     # Cloudflare/Ngrok Tunnel & Capstone Presentation Script
│
├── tests/                    # 🧪 Automated Test Suite (PHPUnit)
│   ├── bootstrap.php         # Test Harness Bootstrap
│   └── Unit/
│       └── FunctionsTest.php # Unit Tests for Core Business Logic
│
├── docs/                     # 📚 Documentation & Technical Guides
│   ├── architecture.md       # Directory & Folder Organization Guide
│   └── syntax.md             # Developer Coding Standards & Syntax Rules
│
└── uploads/                  # 📁 File Uploads (Protected by serve-file.php)
    ├── avatars/              # Profile Pictures
    ├── proofs/               # Payment Receipts (Maya / GCash)
    └── ids/                  # KYC Valid Identity Documents
```

---

## 2. Mga Prinsipyo ng Arkitektura

1. **3-Actor Portal Design (Guest & Public, Staff, Super Admin):**
   * **Guest & Public Portal:** Ang public website (`index.php`, `rooms.php`, `about.php`, `contact.php`) at ang guest reservation hub (`user/*`) ay iisang customer experience. Ang mga bisita at rehistradong guests ay nagba-browse, nagbu-book, nag-a-upload ng KYC ID, at nagbabayad sa parehong domain.
   * **Frontdesk Staff Portal (`staff/*`):** Nakatuon lamang sa daily operations (check-in, check-out, room status, delegated payment verification).
   * **Super Admin Portal (`admin/*`):** May buong kontrol sa executive decision making, user/staff management, master ledger audits, at system configuration.
2. **Security & Zero Direct Exposure:**
   * Ang `uploads/` directory ay may sariling `.htaccess` na pumipigil sa direktang pag-download ng files; lahat ay dumadaan sa [`serve-file.php`](file:///opt/lampp/htdocs/hotel/serve-file.php) na nagve-verify ng user role bago ipakita ang government IDs o financial receipts.
3. **Clean Root Directory:**
   * Ang mga pansuportang scripts tulad ng Apache vhosts, tunnels, at dev docs ay maayos na nakalagay sa `config/`, `scripts/`, at `docs/`.

