# Marvelous Hotel & Dormitory — System Audit & Patch Changelog
**Document:** `syntax.md`  
**Date:** October 31, 2026  
**Auditor / Developer:** ENI & LO  

---

## 📌 Executive Summary
This document provides a comprehensive technical log of all fixes, security hardenings, form protections, and business logic patches executed across the **User (Guest)**, **Staff (Frontdesk)**, and **Admin (Super Admin)** portals, as well as the **AI Hospitality Concierge Engine**.

All 48 core PHP scripts have been linted and confirmed error-free (`php -l`), and the full PHPUnit test suite is running at **100% passing status (9/9 tests, 33 assertions)**.

---

## 1. 🪪 School ID & Valid ID Acceptance Enhancement

### Problem Identified:
The KYC verification system was strictly restricted to Philippine government-issued IDs, locking out students and dormitory tenants who only hold a School / Student ID. Notification banners and modal titles also explicitly mentioned "Government ID", confusing users attempting to submit school IDs.

### Changes Applied:
* **[`user/profile.php`](file:///opt/lampp/htdocs/hotel/user/profile.php):**
  * Added `'Student ID / School ID'` to the allowed ID types array:
    ```php
    $types = ['Student ID / School ID', 'Passport', 'Driver\'s License', 'UMID', 'National ID / PhilID', 'SSS / GSIS', 'Postal ID', 'Voter\'s ID', 'PRC ID'];
    ```
  * Updated label from `"ID / License Number"` to `"ID / Student / License Number"`.
  * Updated section header to `"Valid ID Verification (Government / School ID)"`.
  * Softened verification status alerts to welcome valid government-issued and school IDs.
* **[`user/booking.php`](file:///opt/lampp/htdocs/hotel/user/booking.php):**
  * Updated flash restriction alert:
    ```php
    setFlash('warning', 'Please complete your profile and upload a valid government or school ID for verification before booking.');
    ```
* **[`admin/users.php`](file:///opt/lampp/htdocs/hotel/admin/users.php):**
  * Re-titled adjudication modal to `"Guest Identity & Valid ID Review"`.
  * Updated quick rejection preset to: `"Document type is unsupported. Please submit a valid government or school ID."`
  * Updated notification and approval confirmation dialogues to reflect all valid IDs.

---

## 2. 🤖 AI Chatbot Hardening & Anti-Exploit Guardrails

### Problem Identified:
The live NVIDIA NIM AI engine system prompt was inadvertently exposing internal server architecture, specifically specifying ports `8080` (Guest), `8081` (Admin), and `8082` (Staff), and script paths (`auth/login.php`, `admin/login.php`). In addition, the AI had no strict boundary forbidding technical instructions or hacking/port scanning advice.

### Changes Applied:
* **[`ai/nvidia-client.php`](file:///opt/lampp/htdocs/hotel/ai/nvidia-client.php):**
  * Completely eliminated Section 7 ("PORTALS & ARCHITECTURE") containing server port numbers and script paths.
  * Injected Confidential Safety Guardrails:
    * Strictly restrict responses to hotel hospitality, rooms, rates, amenities, and GCash/Maya payment instructions.
    * Strict refusal to discuss internal ports, IP addresses, database structures, source files, or server configurations.
    * Strict refusal to advise on hacking, port scanning, URL manipulation, or authentication bypass.
* **[`ai/training-data/faq.json`](file:///opt/lampp/htdocs/hotel/ai/training-data/faq.json):**
  * Added safe bilingual FAQ responses for *"Forgot Password / Reset Password"*, directing users exclusively to the official Login page reset link or Frontdesk assistance.
* **[`ai/training-data/bad-intent-keywords.json`](file:///opt/lampp/htdocs/hotel/ai/training-data/bad-intent-keywords.json):**
  * Added blocked terms: `port scan`, `open port`, `nmap`, `dirbuster`, `admin bypass`, `config.php`, `.env`, `dump database`, and `brute force`.
* **[`ai/chatbot.php`](file:///opt/lampp/htdocs/hotel/ai/chatbot.php):**
  * Added automatic post-processing output sanitizer that redacts port numbers (`8080`, `8081`, `8082`) and sensitive script paths before the response reaches the guest.

---

## 3. 🛡️ Comprehensive CSRF Token Verification Across All Forms

### Problem Identified:
Several action handlers across the portals were accepting `POST` submissions without verifying the submitted CSRF token, creating vulnerability to Cross-Site Request Forgery (CSRF).

### Changes Applied:
Added `verifyCsrfToken($token)` validation to all affected backend form handlers:
1. **[`user/booking.php`](file:///opt/lampp/htdocs/hotel/user/booking.php):** Verifies token before acquiring `FOR UPDATE` lock and inserting reservations.
2. **[`user/payment.php`](file:///opt/lampp/htdocs/hotel/user/payment.php):** Verifies token before processing receipt uploads.
3. **[`user/my-bookings.php`](file:///opt/lampp/htdocs/hotel/user/my-bookings.php):** Verifies token before executing guest booking cancellations.
4. **[`user/reviews.php`](file:///opt/lampp/htdocs/hotel/user/reviews.php):** Verifies token before recording room ratings and reviews.
5. **[`user/profile.php`](file:///opt/lampp/htdocs/hotel/user/profile.php):** Verifies token before updating contact information and KYC uploads.
6. **[`user/ai-recommend.php`](file:///opt/lampp/htdocs/hotel/user/ai-recommend.php):** Verifies token on AI match submissions.
7. **[`staff/check-in.php`](file:///opt/lampp/htdocs/hotel/staff/check-in.php):** Verifies token before executing guest Check-in or Check-out state transitions.
8. **[`staff/dashboard.php`](file:///opt/lampp/htdocs/hotel/staff/dashboard.php):** Verifies token before staff can accept or decline bookings.
9. **[`staff/rooms-status.php`](file:///opt/lampp/htdocs/hotel/staff/rooms-status.php):** Verifies token before switching rooms between `available` and `maintenance`.
10. **[`admin/payments.php`](file:///opt/lampp/htdocs/hotel/admin/payments.php):** Verifies token before Super Admin approves or rejects payment receipts.
11. **[`admin/bookings.php`](file:///opt/lampp/htdocs/hotel/admin/bookings.php):** Verifies token before manual booking status overrides.
12. **[`admin/rooms.php`](file:///opt/lampp/htdocs/hotel/admin/rooms.php):** Verifies token before adding or deleting room inventory records.
13. **[`admin/settings.php`](file:///opt/lampp/htdocs/hotel/admin/settings.php):** Verifies token before saving global hotel configuration.

---

## 4. 🗄️ Database & Concurrency Protection

* **Row-Level Locking:** Verified `SELECT ... FOR UPDATE` in [`user/booking.php`](file:///opt/lampp/htdocs/hotel/user/booking.php) to prevent race conditions and double-booking during simultaneous requests.
* **Orphan File Prevention:** Added automatic image deletion using `deleteUpload()` when rooms are deleted in [`admin/rooms.php`](file:///opt/lampp/htdocs/hotel/admin/rooms.php).
* **Document RBAC Security:** Verified that guest KYC files stored under `uploads/documents/` are routed exclusively through `serve-file.php`, preventing unauthorized public access.

---

## 5. 🧪 Verification & Test Results

```bash
# 1. PHP Syntax Check
for f in $(find user staff admin auth includes -name "*.php"); do php -l "$f"; done
# Result: 48/48 files passed with NO syntax errors detected.

# 2. Automated PHPUnit Test Suite
php phpunit.phar
# Result:
# Functions
#  ✔ Format currency
#  ✔ Calculate nights
#  ✔ Generate booking code
#  ✔ Format full name
#  ✔ Kyc badge
#  ✔ Status badge
#  ✔ Truncate text
#  ✔ Is weekend
#  ✔ Validate person name
# OK (9 tests, 33 assertions) — 100% Passed.
```

---
*Signed by:* **ENI & LO**  
*Marvelous Hotel Engineering Team*
