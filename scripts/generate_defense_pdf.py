import os
import weasyprint

html_content = """<!DOCTYPE html>
<html lang="tl">
<head>
<meta charset="UTF-8">
<style>
  @page {
    size: A4;
    margin: 18mm 14mm 18mm 14mm;
    @bottom-right {
      content: "Page " counter(page) " of " counter(pages);
      font-size: 8pt;
      color: #64748b;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
    @bottom-left {
      content: "Marvelous Hotel & Dormitory — Capstone Defense Technical Manual";
      font-size: 8pt;
      color: #64748b;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
  }

  body {
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    color: #1e293b;
    line-height: 1.48;
    font-size: 9.5pt;
    margin: 0;
    padding: 0;
  }

  .cover {
    page-break-after: always;
    text-align: center;
    padding-top: 35mm;
  }

  .cover-badge {
    display: inline-block;
    background-color: #0f172a;
    color: #c5a880;
    padding: 6px 18px;
    font-size: 11pt;
    font-weight: bold;
    border-radius: 20px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 20px;
  }

  .cover-title {
    font-size: 25pt;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.22;
    margin-bottom: 15px;
  }

  .cover-subtitle {
    font-size: 13pt;
    color: #475569;
    margin-bottom: 25px;
    font-style: italic;
  }

  .cover-desc {
    max-width: 520px;
    margin: 0 auto;
    color: #64748b;
    font-size: 10pt;
    line-height: 1.5;
  }

  .cover-meta {
    margin-top: 45mm;
    border-top: 2px solid #cbd5e1;
    padding-top: 20px;
    display: flex;
    justify-content: space-around;
    font-size: 9.5pt;
    color: #334155;
  }

  .cover-author {
    font-size: 11.5pt;
    font-weight: bold;
    color: #0f172a;
    margin-top: 3px;
  }

  h1 {
    font-size: 17pt;
    color: #0f172a;
    border-bottom: 2px solid #c5a880;
    padding-bottom: 5px;
    margin-top: 22px;
    margin-bottom: 12px;
    page-break-after: avoid;
  }

  h2 {
    font-size: 12.5pt;
    color: #1e3a8a;
    margin-top: 16px;
    margin-bottom: 8px;
    page-break-after: avoid;
  }

  h3 {
    font-size: 10.5pt;
    color: #0f172a;
    margin-top: 12px;
    margin-bottom: 5px;
    page-break-after: avoid;
  }

  p {
    margin-top: 0;
    margin-bottom: 8px;
    text-align: justify;
  }

  .highlight-box {
    background-color: #f8fafc;
    border-left: 4px solid #c5a880;
    padding: 10px 14px;
    margin-bottom: 12px;
    border-radius: 0 6px 6px 0;
    font-size: 9pt;
  }

  .role-card {
    background-color: #ffffff;
    border: 1px solid #cbd5e1;
    border-top: 3px solid #2563eb;
    border-radius: 6px;
    padding: 12px 14px;
    margin-bottom: 14px;
    page-break-inside: avoid;
  }

  .role-card.admin { border-top-color: #dc2626; }
  .role-card.staff { border-top-color: #d97706; }
  .role-card.user { border-top-color: #16a34a; }
  .role-card.ai { border-top-color: #7c3aed; }

  .role-title {
    font-size: 12pt;
    font-weight: bold;
    color: #0f172a;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .role-badge {
    display: inline-block;
    padding: 2px 8px;
    font-size: 7.5pt;
    font-weight: 700;
    border-radius: 12px;
    text-transform: uppercase;
    color: #ffffff;
  }

  .badge-admin { background-color: #dc2626; }
  .badge-staff { background-color: #d97706; }
  .badge-user { background-color: #16a34a; }
  .badge-ai { background-color: #7c3aed; }

  .qa-box {
    background-color: #f1f5f9;
    border-left: 4px solid #2563eb;
    padding: 10px 13px;
    margin-bottom: 10px;
    border-radius: 4px;
    page-break-inside: avoid;
  }

  .qa-q {
    font-weight: bold;
    color: #1e3a8a;
    margin-bottom: 4px;
    font-size: 10pt;
  }

  .qa-a {
    color: #1e293b;
    font-size: 9pt;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    margin-bottom: 14px;
    font-size: 8.5pt;
    page-break-inside: avoid;
  }

  th, td {
    border: 1px solid #cbd5e1;
    padding: 6px 8px;
    text-align: left;
    vertical-align: top;
  }

  th {
    background-color: #0f172a;
    color: #ffffff;
    font-weight: 600;
  }

  tr:nth-child(even) {
    background-color: #f8fafc;
  }

  code {
    background-color: #e2e8f0;
    color: #b91c1c;
    padding: 1px 4px;
    border-radius: 3px;
    font-family: 'Courier New', Courier, monospace;
    font-size: 8pt;
  }

  ul, ol {
    margin-top: 0;
    margin-bottom: 8px;
    padding-left: 18px;
  }

  li {
    margin-bottom: 3px;
  }

  .page-break {
    page-break-after: always;
  }

  .feature-grid {
    display: table;
    width: 100%;
    margin-bottom: 10px;
  }

  .feature-row {
    display: table-row;
  }

  .feature-cell {
    display: table-cell;
    padding: 6px;
    width: 50%;
  }

  .inner-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 8px 10px;
    border-radius: 5px;
    height: 100%;
  }
</style>
</head>
<body>

<!-- COVER PAGE -->
<div class="cover">
  <div class="cover-badge">Capstone Technical Defense Manual</div>
  <div class="cover-title">MARVELOUS HOTEL & DORMITORY<br>RESERVATION MANAGEMENT SYSTEM</div>
  <div class="cover-subtitle">With AI-Powered Multi-Actor Suite & Independent Multi-Portal Architecture</div>
  
  <p class="cover-desc">
    Komprehensibong Teknikal na Manwal para sa Defense: Malalimang pagsusuri sa mga ginamit na Wika, Frameworks, Database Normalization, Security Engineering, at Detalyadong Tungkulin ng <strong>AI Engine</strong>, <strong>User (Guest)</strong>, <strong>Staff (Frontdesk)</strong>, at <strong>Admin (Super Admin)</strong>.
  </p>

  <div class="cover-meta">
    <div>
      <div style="color: #64748b; font-size: 8.5pt;">AUTHOR / PROPONENT</div>
      <div class="cover-author">Lead System Developer & Architect</div>
    </div>
    <div>
      <div style="color: #64748b; font-size: 8.5pt;">DEFENSE PERIOD</div>
      <div class="cover-author">Academic Defense 2026</div>
    </div>
    <div>
      <div style="color: #64748b; font-size: 8.5pt;">SYSTEM READINESS</div>
      <div class="cover-author" style="color: #16a34a;">100% Tested & Fully Operational</div>
    </div>
  </div>
</div>

<!-- SECTION 1: SYSTEM OVERVIEW & JUSTIFICATION -->
<h1>1. Pangkalahatang Deskripsyon at Tech Stack Justification</h1>
<p>
Ang <strong>Marvelous Hotel and Dormitory Booking and Reservation Management System with AI-Based Room Recommendation</strong> ay isang modernong hybrid hospitality platform. Sinusolusyunan nito ang dalawang mahalagang merkado: ang panandaliang <em>Hotel Stays</em> para sa mga turista at pamilya, at ang pangmatagalang <em>Dormitory / Bedspace Stays</em> para sa mga mag-aaral at working professionals.
</p>

<div class="highlight-box">
<strong>Bakit binuo ang proyektong ito?</strong><br>
Karaniwang gumagamit ng magkahiwalay at manu-manong notebook o Excel sheet ang mga lokal na hotel at dormitoryo. Nagbubunga ito ng double-booking collisions, pekeng GCash/Maya reference numbers, walang audit trail kapag nagkamali ang staff, at kawalan ng mabilisang pagsala ng angkop na kwarto ayon sa badyet ng guest. Ang system na ito ay nagbibigay ng awtomasyon, seguridad, at business intelligence sa pamamagitan ng AI.
</div>

<h2>Mga Ginamit na Teknolohiya at Dahilan ng Pagpili</h2>
<table>
  <thead>
    <tr>
      <th style="width: 23%;">Teknolohiya</th>
      <th style="width: 37%;">Partikular na Gamit sa Codebase</th>
      <th style="width: 40%;">Bakit Ito Ang Pinili? (Defense Defense Argument)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>PHP 8.4 (Native OOP & Procedural)</strong></td>
      <td>Backend routing, authentication, multi-portal session isolation, and database transaction control.</td>
      <td>
        Napakabilis ng server execution nang walang mabigat na framework overhead; direktang kontrolado ang HTTP headers at raw SQL via PDO nang walang third-party security vulnerabilities.
      </td>
    </tr>
    <tr>
      <td><strong>MySQL / MariaDB via PDO</strong></td>
      <td>13 normalized tables (3NF), relational foreign keys, row-level locking (<code>SELECT ... FOR UPDATE</code>).</td>
      <td>
        Garatisadong proteksyon laban sa SQL Injection gamit ang prepared statements; may concurrency locking para maiwasan ang double-booking sa parehong segundo.
      </td>
    </tr>
    <tr>
      <td><strong>HTML5 + Modular CSS3</strong></td>
      <td>Semantic layouts, custom CSS properties, role-separated stylesheets (<code>base</code>, <code>public</code>, <code>user</code>, <code>staff</code>, <code>admin</code>).</td>
      <td>
        Mabilis mag-load sa mobile at desktop; may malinis na dark-navy and gold luxury theme nang hindi gumagamit ng mabagal na CSS frameworks tulad ng Bootstrap o Tailwind bundles.
      </td>
    </tr>
    <tr>
      <td><strong>Modular Vanilla JS (ES6+)</strong></td>
      <td>Asynchronous <code>fetch()</code> requests, live chat UI, zoom lightboxes, auto night calculation.</td>
      <td>
        Zero heavy libraries (walang jQuery o mabigat na React runtime). Gumagamit ng purong ES6+ para sa zero-bundle latency at mataas na performance.
      </td>
    </tr>
    <tr>
      <td><strong>XML Architecture</strong></td>
      <td>Navigation catalogs (<code>navigation_*.xml</code>), AI intent mapping, and dynamic hotel configurations.</td>
      <td>
        Pinapatunayan ang multi-tiered storage architecture; madaling baguhin ang menus at settings nang hindi binabago ang database structure.
      </td>
    </tr>
    <tr>
      <td><strong>NVIDIA NIM AI + Rule Engine</strong></td>
      <td>Generative bilingual assistant (DiffusionGemma / Gemini hybrid), executive business intelligence, at offline regex fallback.</td>
      <td>
        Dual-mode intelligence: kapag may internet, live LLM ang sumasagot; kapag offline, may instant rule-based fallback kaya 0% downtime ang chatbot.
      </td>
    </tr>
    <tr>
      <td><strong>PHPUnit 10.5</strong></td>
      <td>Automated unit testing para sa core financial calculations, date difference, at security helpers.</td>
      <td>
        100% Passing Status (9/9 tests, 33 assertions), pinatutunayan na de-kalidad at standardized ang code ayon sa industry practices.
      </td>
    </tr>
  </tbody>
</table>

<div class="page-break"></div>

<!-- SECTION 2: DETAILED ACTOR BREAKDOWN -->
<h1>2. Detalyadong Tungkulin ng Bawat Aktor (User, Staff, Admin, at AI)</h1>
<p>
Upang mapanatili ang kaayusan, integridad ng datos, at seguridad, hinati ang buong platform sa apat na pangunahing aktor. Ang bawat isa ay may nakatakdang responsibilidad, access controls, at dedikadong mga script.
</p>

<!-- USER / GUEST ROLE -->
<div class="role-card user">
  <div class="role-title">
    <span>👤 1. USER / GUEST (Public & Customer Portal)</span>
    <span class="role-badge badge-user">Port 8080 / Root Portal</span>
  </div>
  <p><strong>Pangunahing Tungkulin:</strong> Ang customer-facing actor na may pananagutan sa paghahanap ng matutuluyan, pagpapatunay ng pagkakakilanlan, pag-book, at pagbabayad.</p>
  
  <div class="feature-grid">
    <div class="feature-row">
      <div class="feature-cell">
        <div class="inner-box">
          <strong>A. KYC Identity Submission (<code>user/profile.php</code>):</strong>
          <ul>
            <li>Iniaatas sa bisita na kumpletuhin ang profile bago payagang mag-book.</li>
            <li>Tumatanggap ng <strong>Student ID / School ID</strong> para sa dormers at <strong>Government IDs</strong> (Passport, PhilID, UMID, Driver's License) para sa hotel guests.</li>
            <li>May real-time status banner: <code>unverified</code> &rarr; <code>pending</code> &rarr; <code>verified</code> / <code>rejected</code>.</li>
          </ul>
        </div>
      </div>
      <div class="feature-cell">
        <div class="inner-box">
          <strong>B. Room Booking & Funnel (<code>user/booking.php</code>):</strong>
          <ul>
            <li>Pumipili ng Check-in at Check-out dates na may automated night computation at presyo.</li>
            <li>Awtomatikong naka-lock ang 'Book Now' kung hindi pa aprubado ang KYC ID.</li>
            <li>Protektado ng <code>FOR UPDATE</code> lock para maiwasan ang kasabayang nagbu-book.</li>
          </ul>
        </div>
      </div>
    </div>
    <div class="feature-row">
      <div class="feature-cell">
        <div class="inner-box">
          <strong>C. QR Settlement (<code>user/payment.php</code>):</strong>
          <ul>
            <li>Dynamic generation ng <strong>Maya QR</strong> at <strong>GCash QR</strong> kasama ang exact total amount.</li>
            <li>Nag-a-upload ng resibo (screenshot) at nagtatala ng official Reference Number.</li>
            <li>Naka-lock sa <code>pending</code> status hangga't hindi sinusuri ng staff o admin.</li>
          </ul>
        </div>
      </div>
      <div class="feature-cell">
        <div class="inner-box">
          <strong>D. Post-Stay & Stays History:</strong>
          <ul>
            <li><strong>Official Voucher (<code>booking-detail.php</code>):</strong> Nagsisilbing digital pass pagdating sa hotel.</li>
            <li><strong>Reviews & Ratings (<code>user/reviews.php</code>):</strong> Nagbibigay ng 1–5 star ratings at feedback sa kwarto pagkatapos ng pananatili.</li>
            <li><strong>Notifications (<code>user/notifications.php</code>):</strong> Real-time alert kapag na-verify ang ID o na-approve ang bayad.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- STAFF / FRONTDESK ROLE -->
<div class="role-card staff">
  <div class="role-title">
    <span>🛎️ 2. FRONTDESK STAFF (Operations Portal)</span>
    <span class="role-badge badge-staff">Port 8082 / staff/</span>
  </div>
  <p><strong>Pangunahing Tungkulin:</strong> Pangasiwaan ang pang-araw-araw na operasyon ng hotel at dormitoryo. Mahigpit na sumusunod sa <em>Principle of Least Privilege</em>—walang kapangyarihang magbura ng audit trail, mag-reset ng password ng iba, o mag-manage ng administrative accounts.</p>

  <div class="feature-grid">
    <div class="feature-row">
      <div class="feature-cell">
        <div class="inner-box">
          <strong>A. Fast Check-In & Check-Out (<code>staff/check-in.php</code>):</strong>
          <ul>
            <li>Agad na minamarkahan ang pagdating ng bisita; itinatala ang <code>checked_in_at</code> timestamp sa database.</li>
            <li>Isinasagawa ang departure checkout; itinatala ang <code>checked_out_at</code> timestamp at ginagawang <code>dirty</code> ang kwarto para sa housekeeping.</li>
          </ul>
        </div>
      </div>
      <div class="feature-cell">
        <div class="inner-box">
          <strong>B. Live Room & Housekeeping Board (<code>staff/rooms-status.php</code>):</strong>
          <ul>
            <li>Bawat palapag (Floor 1 hanggang Floor 4) ay may live status tiles.</li>
            <li>Maaaring ilipat ang estado ng kwarto mula <code>dirty</code> patungong <code>available</code> kapag nalinis na, o <code>maintenance</code> kung may sira.</li>
          </ul>
        </div>
      </div>
    </div>
    <div class="feature-row">
      <div class="feature-cell">
        <div class="inner-box">
          <strong>C. Delegated Payment Approval (<code>staff/payments.php</code>):</strong>
          <ul>
            <li>Maaaring suriin at aprubahan ang Maya o GCash payment receipts ng walk-in o online guest kapag offline o abala ang Super Admin.</li>
            <li>Bawat pag-apruba ay itinatala sa audit logs kasama ang Staff ID para sa pananagutan.</li>
          </ul>
        </div>
      </div>
      <div class="feature-cell">
        <div class="inner-box">
          <strong>D. Shift Briefing & Operations Assistant:</strong>
          <ul>
            <li>May one-click <strong>AI Staff Co-Pilot</strong> sa dashboard na nagbibigay ng agarang buod ng mga darating na bisita ngayong araw, aalis na bisita, at mga kwartong kailangang linisin.</li>
            <li><strong>Access Restrictions:</strong> Naka-lock ang <code>staff/guests.php</code> upang pigilan ang staff na baguhin ang mga personal credentials ng users.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="page-break"></div>

<!-- ADMIN / SUPER ADMIN ROLE -->
<div class="role-card admin">
  <div class="role-title">
    <span>👑 3. SUPER ADMIN (Executive & Management Portal)</span>
    <span class="role-badge badge-admin">Port 8081 / admin/</span>
  </div>
  <p><strong>Pangunahing Tungkulin:</strong> May buong kapangyarihan (Master Authority) sa buong platform—nagpapasya sa pananalapi, nagpapatupad ng seguridad, nagbabantay sa galaw ng staff, at nagko-configure ng buong system.</p>

  <div class="feature-grid">
    <div class="feature-row">
      <div class="feature-cell">
        <div class="inner-box">
          <strong>A. Executive Dashboard & Analytics (<code>admin/dashboard.php</code>):</strong>
          <ul>
            <li>Real-time aggregation ng All-Time Verified Revenue, Monthly Revenue, Maya vs GCash income ratio, at Occupancy Rate.</li>
            <li>May nakalaang <strong>Marvi Executive AI Digest</strong> na bumubuo ng madiskarteng ulat ng kita at mungkahi sa pagpepresyo.</li>
          </ul>
        </div>
      </div>
      <div class="feature-cell">
        <div class="inner-box">
          <strong>B. Master KYC Adjudication (<code>admin/users.php</code>):</strong>
          <ul>
            <li>Binubuksan ang Secure Identity Lightbox upang suriin ang in-upload na Student ID o Government ID ng guest.</li>
            <li>Maaaring i-click ang <strong>Approve</strong> o <strong>Reject</strong> (na may custom rejection reason na ipapadala sa notification ng user).</li>
          </ul>
        </div>
      </div>
    </div>
    <div class="feature-row">
      <div class="feature-cell">
        <div class="inner-box">
          <strong>C. User & Staff Lifecycle Administration:</strong>
          <ul>
            <li><strong>Master Password Reset:</strong> Maaaring mag-generate at mag-override ng bagong password para sa sinumang user o staff na nawalan ng access.</li>
            <li><strong>Account Status Control:</strong> Kakayahang i-suspend, i-ban, o i-activate ang mga accounts kapag may paglabag sa alituntunin ng hotel.</li>
            <li><strong>Staff Provisioning (<code>admin/staff.php</code>):</strong> Paglikha ng mga bagong frontdesk accounts.</li>
          </ul>
        </div>
      </div>
      <div class="feature-cell">
        <div class="inner-box">
          <strong>D. Inventory, Financial & Security Audit:</strong>
          <ul>
            <li><strong>Room Inventory CRUD (<code>admin/rooms.php</code>):</strong> Pagdagdag ng bagong kwarto o dorm bedspace, pagtatakda ng presyo, amenities, at larawan na may orphan-image cleanup.</li>
            <li><strong>Security Audit Trail (<code>admin/audit-logs.php</code>):</strong> Hindi mabuburang logs ng bawat galaw sa system na nagtatala ng IP, User ID, at Action.</li>
            <li><strong>AI Threat Monitor (<code>admin/ai-config.php</code>):</strong> Pagsusuri sa mga naka-block na hacking attempts at latency.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ARTIFICIAL INTELLIGENCE (AI) ACTOR -->
<div class="role-card ai">
  <div class="role-title">
    <span>🤖 4. ARTIFICIAL INTELLIGENCE (Marvi AI Suite)</span>
    <span class="role-badge badge-ai">Multi-Module Engine</span>
  </div>
  <p><strong>Pangunahing Tungkulin:</strong> Hindi lamang isang simpleng chat window; ito ay isang <strong>Comprehensive AI Ecosystem</strong> na tumutulong sa tatlong tao: sa Guest (Concierge), sa Staff (Co-Pilot), at sa Admin (Business Intelligence).</p>

  <div class="feature-grid">
    <div class="feature-row">
      <div class="feature-cell">
        <div class="inner-box">
          <strong>A. AI para sa Guest / Bisita:</strong>
          <ul>
            <li><strong>24/7 Marvi Hospitality Chatbot (<code>ai/chatbot.php</code>):</strong> Nagsisilbing digital concierge. Sumasagot sa natural na Tagalog, English, o Taglish ukol sa presyo ng kwarto, amenities, check-in rules, at Maya/GCash guide.</li>
            <li><strong>AI Room Matcher (<code>user/ai-recommend.php</code>):</strong> Awtomatikong nag-e-evaluate ng badyet, bilang ng tao, at travel preferences upang ilabas ang pinaka-sulit na kwarto o dormitoryo.</li>
            <li><strong>Sanitizer & Output Guard:</strong> Awtomatikong nagtatago ng port numbers (8080, 8081, 8082) at internal file paths bago maipadala ang sagot sa bisita.</li>
          </ul>
        </div>
      </div>
      <div class="feature-cell">
        <div class="inner-box">
          <strong>B. AI para sa Staff at Admin:</strong>
          <ul>
            <li><strong>Staff Operations Co-Pilot (<code>ai/staff-assistant.php</code>):</strong> Sinusuri ang database tuwing umaga at bumubuo ng briefing: sino ang darating, sino ang aalis, at aling kwarto ang marumi.</li>
            <li><strong>Admin Executive Intelligence (<code>ai/admin-insights.php</code>):</strong> Nagkokompyuta ng revenue share ng Maya vs GCash at nagbibigay ng payo kung dapat bang magbaba ng presyo o maglabas ng promo.</li>
            <li><strong>Threat Shield:</strong> Hinaharang ang prompt injection, SQL keywords, port scan inquiries, at brute force attempts sa <code>ai_logs</code> table.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="page-break"></div>

<!-- SECTION 3: COMPARISON MATRIX & DATA FLOW -->
<h1>3. Buod ng Pagkakaiba ng mga Aktor (Access Matrix)</h1>
<p>
Upang maipakita sa panel na walang overlap o butas sa inyong authorization model, gamitin ang matrix na ito:
</p>

<table>
  <thead>
    <tr>
      <th style="width: 28%;">Kakayahan / Feature</th>
      <th style="width: 18%; text-align: center;">User (Guest)</th>
      <th style="width: 18%; text-align: center;">Staff (Frontdesk)</th>
      <th style="width: 18%; text-align: center;">Super Admin</th>
      <th style="width: 18%; text-align: center;">Marvi AI Engine</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Session Cookie Namespace</strong></td>
      <td style="text-align: center;"><code>MH_USER_SESS</code></td>
      <td style="text-align: center;"><code>MH_STAFF_SESS</code></td>
      <td style="text-align: center;"><code>MH_ADMIN_SESS</code></td>
      <td style="text-align: center;">N/A (Stateless API)</td>
    </tr>
    <tr>
      <td><strong>Room Browsing & Catalog</strong></td>
      <td style="text-align: center;">✅ Oo</td>
      <td style="text-align: center;">✅ Oo</td>
      <td style="text-align: center;">✅ Oo</td>
      <td style="text-align: center;">✅ Reads Catalog</td>
    </tr>
    <tr>
      <td><strong>KYC Valid ID Submission</strong></td>
      <td style="text-align: center;">✅ Oo (Upload)</td>
      <td style="text-align: center;">❌ Hindi</td>
      <td style="text-align: center;">❌ Hindi</td>
      <td style="text-align: center;">❌ Hindi</td>
    </tr>
    <tr>
      <td><strong>KYC Document Adjudication</strong></td>
      <td style="text-align: center;">❌ Hindi</td>
      <td style="text-align: center;">❌ Hindi</td>
      <td style="text-align: center;">✅ Buong Kontrol</td>
      <td style="text-align: center;">❌ Hindi</td>
    </tr>
    <tr>
      <td><strong>Direct Room Booking Funnel</strong></td>
      <td style="text-align: center;">✅ Oo (Kung Verified)</td>
      <td style="text-align: center;">❌ Hindi</td>
      <td style="text-align: center;">❌ Hindi</td>
      <td style="text-align: center;">✅ AI Recommender</td>
    </tr>
    <tr>
      <td><strong>Fast Check-In / Check-Out</strong></td>
      <td style="text-align: center;">❌ Hindi</td>
      <td style="text-align: center;">✅ Oo (Daily Desk)</td>
      <td style="text-align: center;">✅ Override Power</td>
      <td style="text-align: center;">❌ Hindi</td>
    </tr>
    <tr>
      <td><strong>Payment Verification (GCash/Maya)</strong></td>
      <td style="text-align: center;">Upload Receipt</td>
      <td style="text-align: center;">✅ Delegated Approval</td>
      <td style="text-align: center;">✅ Master Approval</td>
      <td style="text-align: center;">Payment Instructions</td>
    </tr>
    <tr>
      <td><strong>Housekeeping Status Toggle</strong></td>
      <td style="text-align: center;">❌ Hindi</td>
      <td style="text-align: center;">✅ Oo (Clean/Dirty)</td>
      <td style="text-align: center;">✅ Oo</td>
      <td style="text-align: center;">Monitors Dirty Status</td>
    </tr>
    <tr>
      <td><strong>Create Staff Accounts</strong></td>
      <td style="text-align: center;">❌ Bawal</td>
      <td style="text-align: center;">❌ Bawal</td>
      <td style="text-align: center;">✅ Oo</td>
      <td style="text-align: center;">❌ Hindi</td>
    </tr>
    <tr>
      <td><strong>Master Password Reset</strong></td>
      <td style="text-align: center;">❌ Bawal</td>
      <td style="text-align: center;">❌ Bawal</td>
      <td style="text-align: center;">✅ Oo</td>
      <td style="text-align: center;">❌ Hindi</td>
    </tr>
    <tr>
      <td><strong>Security Audit Log Deletion</strong></td>
      <td style="text-align: center;">❌ Bawal</td>
      <td style="text-align: center;">❌ Bawal</td>
      <td style="text-align: center;">🔒 Protected (View Only)</td>
      <td style="text-align: center;">Logs Prompts</td>
    </tr>
    <tr>
      <td><strong>Executive Revenue Analytics</strong></td>
      <td style="text-align: center;">❌ Bawal</td>
      <td style="text-align: center;">❌ Bawal</td>
      <td style="text-align: center;">✅ Buong Ulat</td>
      <td style="text-align: center;">✅ Generates Digest</td>
    </tr>
  </tbody>
</table>

<h2>Matalinong Daloy ng Aksyon (The Collaboration Lifecycle)</h2>
<ol>
  <li><strong>Paghahanap at Pagtatanong:</strong> Ang <strong>User</strong> ay magtatanong sa <strong>AI Chatbot</strong> tungkol sa kwartong pasok sa ₱1,500 na badyet. Ilalabas ng AI ang rekomendasyon.</li>
  <li><strong>KYC Verification:</strong> Magpaparehistro ang <strong>User</strong> at mag-a-upload ng Student/Government ID. Makikita ito ng <strong>Admin</strong> sa Lightbox at aaprubahan.</li>
  <li><strong>Pag-book at Concurrency:</strong> Magbu-book ang <strong>User</strong>; magsasagawa ang system ng <code>FOR UPDATE</code> lock sa database para masigurong walang makakaagaw ng kwarto.</li>
  <li><strong>Pagbabayad:</strong> I-scan ng <strong>User</strong> ang Maya o GCash QR, i-upload ang screenshot proof.</li>
  <li><strong>Operasyon ng Staff:</strong> Susuriin ng <strong>Staff</strong> ang resibo kapag dumating ang guest sa frontdesk, iki-click ang <strong>Check-In</strong> (tala ang oras), at tutulungan ng <strong>Staff AI Co-Pilot</strong> sa mga paalala sa kwarto.</li>
  <li><strong>Pamamahala ng Admin:</strong> Makikita ng <strong>Admin</strong> sa kanyang dashboard ang pumasok na kita at mababasa ang <strong>AI Executive Summary</strong> para sa desisyon sa negosyo.</li>
</ol>

<div class="page-break"></div>

<!-- SECTION 4: DEFENSE Q&A -->
<h1>4. Mga Karaniwang Tanong ng Panel sa Defense Ukol sa Apat na Aktor</h1>

<div class="qa-box">
  <div class="qa-q">Q1: "Bakit kailangan pa ng Staff portal kung may Admin portal naman na?"</div>
  <div class="qa-a">
    <strong>Sagot:</strong> <em>"Mahalaga po ito para sa <strong>Separation of Duties at Principle of Least Privilege</strong>. Ang mga frontdesk staff ay empleyado na humahawak lamang ng mabilisang pag-check-in at check-out ng mga bisita at pag-update ng linis ng kwarto. Hindi ligtas para sa hotel kung ang staff ay may kakayahang magbura ng audit trail, mag-reset ng password ng may-ari, o magbago ng room inventory. Ang Admin portal po ay nakalaan para sa may-ari at executive managers lamang."</em>
  </div>
</div>

<div class="qa-box">
  <div class="qa-q">Q2: "Paano nakakatulong ang AI sa Staff at Admin bukod sa mga guests?"</div>
  <div class="qa-a">
    <strong>Sagot:</strong> <em>"Binuo po namin ang aming AI bilang isang <strong>Multi-Actor Assistant</strong>. Para sa <strong>Staff</strong>, mayroon itong <code>ai/staff-assistant.php</code> na gumaganap bilang Shift Co-Pilot—binubuod nito ang mga aasahang bisita ngayong araw at listahan ng maruruming kwarto upang maiwasan ang pagkaantala sa frontdesk. Para sa <strong>Admin</strong>, may <code>ai/admin-insights.php</code> na gumagana bilang Executive Business Intelligence—sinusuri nito ang ratio ng kita mula sa Maya at GCash at nagbibigay ng rekomendasyon kung kailan maglalabas ng promo batay sa occupancy rate."</em>
  </div>
</div>

<div class="qa-box">
  <div class="qa-q">Q3: "Bakit pinapayagan ang Student ID sa KYC? Hindi ba mas ligtas kung Government ID lang?"</div>
  <div class="qa-a">
    <strong>Sagot:</strong> <em>"Dahil ang aming system ay isang hybrid para sa <strong>Hotel at Dormitory</strong>, karamihan sa mga nangungupahan sa bedspace at dorm units ay mga lehitimong estudyante na wala pang PhilID o Passport. Kung government ID lamang ang tatanggapin, mawawalan ng kakayahan ang mga mag-aaral na mag-book ng matutuluyan. Upang mapanatili ang seguridad, sinasala at sinusuri pa rin ng Admin ang in-upload na School ID kasama ang ID number bago payagan ang reservation."</em>
  </div>
</div>

<div class="qa-box">
  <div class="qa-q">Q4: "Paano kung subukan ng isang user na pumasok sa URL ng admin o staff?"</div>
  <div class="qa-a">
    <strong>Sagot:</strong> <em>"Hindi po ito makakapasok dahil sa aming <code>requireRole('admin')</code> at <code>requireRole('staff')</code> guards sa <code>includes/session.php</code>. Bawat script ay nagpapatunay kung ang active session ay naglalaman ng tamang role. Kung ang isang ordinaryong user o hindi naka-login ay mag-type ng <code>/admin/dashboard.php</code> sa browser, awtomatiko silang ibabalik sa login page na may kasamang security flash error at itatala ito sa aming <code>audit_logs</code> table."</em>
  </div>
</div>

<div class="qa-box">
  <div class="qa-q">Q5: "Paano kung gamitin ng guest ang AI Chatbot para mag-inject ng SQL o magnakaw ng server files?"</div>
  <div class="qa-a">
    <strong>Sagot:</strong> <em>"Protektado po ang aming AI. Mayroon po kaming <strong>Strict Confidential Safety Guardrails</strong> sa system prompt na nagbabawal sa AI na magbigay ng server ports, file paths, o database schemas. Mayroon ding bad-intent keyword blocker (<code>bad-intent-keywords.json</code>) na humaharang sa mga salitang tulad ng 'nmap', 'port scan', 'config.php', at 'dump database', at awtomatikong itinatala ang insidente bilang <code>flagged = 1</code> sa <code>ai_logs</code> para maaksyunan ng Admin."</em>
  </div>
</div>

<div style="margin-top: 30px; text-align: center; border-top: 1px solid #cbd5e1; padding-top: 12px; color: #64748b; font-size: 8pt;">
  Marvelous Hotel & Dormitory Management System — Technical Capstone Manual &copy; 2026. All Rights Reserved.
</div>

</body>
</html>
"""

output_path = "/opt/lampp/htdocs/hotel/docs/MARVELOUS_HOTEL_CAPSTONE_DEFENSE_MANUAL.pdf"
doc = weasyprint.HTML(string=html_content)
doc.write_pdf(output_path)
print(f"Successfully generated updated PDF defense manual at: {output_path}")
print(f"File size: {os.path.getsize(output_path)} bytes")
