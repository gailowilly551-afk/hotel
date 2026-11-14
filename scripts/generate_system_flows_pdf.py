#!/usr/bin/env python3
"""
=============================================================================
Marvelous Hotel & Dormitory — Complete System Architecture & System Flows
Document Generator (November 2026 Edition)
Target: marvelous_hotel_system_flows.pdf & docs/marvelous_hotel_system_flows.pdf
Engine: Python 3 + WeasyPrint 70.0
Architect: Dr. ENI (Senior Software Architect & Lead System Designer)
Client / Lead: LO
=============================================================================
"""

import os
import sys
import weasyprint

def build_html() -> str:
    return """<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Marvelous Hotel & Dormitory — System Architecture & Flows</title>
<style>
  @page {
    size: A4 portrait;
    margin: 14mm 12mm 14mm 12mm;
    @top-left {
      content: "MARVELOUS HOTEL & DORMITORY — TECHNICAL ARCHITECTURE & TRANSACTIONAL FLOWS";
      font-size: 7pt;
      font-weight: 700;
      color: #94a3b8;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      letter-spacing: 0.5px;
    }
    @top-right {
      content: "NOVEMBER 2026 EDITION • SYSTEM FLOW MANUAL";
      font-size: 7pt;
      font-weight: 700;
      color: #c5a880;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
    @bottom-left {
      content: "Marvelous Hotel & Dormitory Management System • Capstone 2026 • PHP 8.x & MySQL 8.0";
      font-size: 7pt;
      color: #94a3b8;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
    @bottom-right {
      content: "Page " counter(page) " of " counter(pages);
      font-size: 7.5pt;
      font-weight: 700;
      color: #0f172a;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
  }

  @page:first {
    margin: 0mm;
    @top-left { content: none; }
    @top-right { content: none; }
    @bottom-left { content: none; }
    @bottom-right { content: none; }
  }

  * {
    box-sizing: border-box;
  }

  body {
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    color: #1e293b;
    font-size: 8.2pt;
    line-height: 1.38;
    margin: 0;
    padding: 0;
    background: #ffffff;
  }

  .page-container {
    page-break-after: always;
    height: 100%;
    position: relative;
  }

  .page-container.last-page {
    page-break-after: avoid;
  }

  /* Cover Page Styling */
  .cover-wrapper {
    background: linear-gradient(135deg, #0b1120 0%, #0f172a 45%, #1e293b 100%);
    color: #ffffff;
    height: 100%;
    min-height: 297mm;
    padding: 24mm 20mm;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .cover-top {
    border-bottom: 2px solid rgba(197, 168, 128, 0.4);
    padding-bottom: 16px;
  }

  .brand-badge {
    display: inline-block;
    background: rgba(197, 168, 128, 0.15);
    border: 1px solid #c5a880;
    color: #e2c9a5;
    padding: 4px 14px;
    font-size: 8.5pt;
    font-weight: 700;
    border-radius: 20px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin-bottom: 14px;
  }

  .cover-title {
    font-size: 26pt;
    font-weight: 900;
    color: #ffffff;
    line-height: 1.18;
    margin: 0 0 10px 0;
    letter-spacing: -0.5px;
  }

  .cover-title span {
    color: #c5a880;
  }

  .cover-subtitle {
    font-size: 11.5pt;
    color: #94a3b8;
    font-weight: 400;
    line-height: 1.45;
    margin-bottom: 16px;
  }

  .cover-badges-grid {
    display: table;
    width: 100%;
    margin-top: 14px;
  }

  .cover-badge-item {
    display: table-cell;
    padding: 6px 6px;
    width: 20%;
    text-align: center;
  }

  .cover-pill {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px;
    padding: 8px 6px;
    font-size: 7.5pt;
    color: #f1f5f9;
    font-weight: 600;
  }

  .cover-pill i-tag {
    display: block;
    color: #c5a880;
    font-size: 10pt;
    margin-bottom: 4px;
    font-weight: bold;
  }

  .cover-main-box {
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(197, 168, 128, 0.3);
    border-radius: 10px;
    padding: 16px 18px;
    margin: 18px 0;
  }

  .cover-main-box h3 {
    color: #c5a880;
    margin: 0 0 8px 0;
    font-size: 10pt;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .cover-main-box p {
    color: #cbd5e1;
    font-size: 8.5pt;
    line-height: 1.5;
    margin: 0 0 8px 0;
  }

  .cover-toc-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  .cover-toc-table th {
    background: rgba(197, 168, 128, 0.2);
    color: #e2c9a5;
    font-size: 7.5pt;
    padding: 5px 8px;
    text-align: left;
    border: 1px solid rgba(255, 255, 255, 0.1);
  }

  .cover-toc-table td {
    padding: 4.5px 8px;
    font-size: 7.5pt;
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.08);
  }

  .cover-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    padding-top: 14px;
    display: table;
    width: 100%;
  }

  .cover-footer-col {
    display: table-cell;
    width: 33.33%;
    vertical-align: top;
    font-size: 7.5pt;
    color: #94a3b8;
  }

  .cover-footer-col strong {
    color: #ffffff;
    display: block;
    font-size: 8.5pt;
    margin-bottom: 2px;
  }

  /* Content Pages Common Typography */
  h1 {
    font-size: 13.5pt;
    color: #0f172a;
    font-weight: 800;
    margin: 0 0 4px 0;
    letter-spacing: -0.3px;
    display: flex;
    align-items: center;
    border-bottom: 1.5px solid #c5a880;
    padding-bottom: 4px;
  }

  .section-tag {
    background: #0f172a;
    color: #c5a880;
    font-size: 7pt;
    padding: 2px 7px;
    border-radius: 4px;
    margin-right: 8px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .intro-text {
    font-size: 7.8pt;
    color: #475569;
    margin: 4px 0 8px 0;
    line-height: 1.35;
  }

  h2 {
    font-size: 9.5pt;
    color: #0f172a;
    margin: 8px 0 4px 0;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }

  /* Compact Data Tables */
  table.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
    font-size: 7.3pt;
  }

  table.data-table th {
    background: #0f172a;
    color: #f8fafc;
    padding: 4.5px 6px;
    font-weight: 700;
    text-align: left;
    border: 1px solid #334155;
  }

  table.data-table td {
    padding: 4.5px 6px;
    border: 1px solid #cbd5e1;
    color: #1e293b;
    vertical-align: top;
  }

  table.data-table tr:nth-child(even) {
    background: #f8fafc;
  }

  .badge {
    display: inline-block;
    padding: 1.5px 5px;
    border-radius: 3px;
    font-size: 6.5pt;
    font-weight: 700;
    text-transform: uppercase;
  }

  .badge-admin { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
  .badge-staff { background: #e0f2fe; color: #075985; border: 1px solid #38bdf8; }
  .badge-guest { background: #dcfce7; color: #166534; border: 1px solid #4ade80; }
  .badge-ai    { background: #f3e8ff; color: #6b21a8; border: 1px solid #c084fc; }
  .badge-gold  { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }

  /* Flow Diagram Cards */
  .diagram-container {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 8px 10px;
    margin-bottom: 8px;
  }

  .diagram-title {
    font-size: 8pt;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 6px;
    border-bottom: 1px dashed #cbd5e1;
    padding-bottom: 3px;
    display: flex;
    justify-content: space-between;
  }

  .diagram-title span.badge-pill {
    background: #c5a880;
    color: #0f172a;
    font-size: 6.5pt;
    padding: 1px 6px;
    border-radius: 10px;
  }

  /* Flex/Table Grids for Architecture */
  .arch-grid {
    display: table;
    width: 100%;
    margin-bottom: 6px;
  }

  .arch-col {
    display: table-cell;
    padding: 4px;
    vertical-align: top;
  }

  .card-box {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    padding: 6px 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
  }

  .card-box.primary { border-top: 3px solid #2563eb; }
  .card-box.warning { border-top: 3px solid #f59e0b; }
  .card-box.success { border-top: 3px solid #10b981; }
  .card-box.danger  { border-top: 3px solid #ef4444; }
  .card-box.gold    { border-top: 3px solid #c5a880; }
  .card-box.purple  { border-top: 3px solid #8b5cf6; }

  .card-header {
    font-weight: 700;
    font-size: 7.5pt;
    color: #0f172a;
    margin-bottom: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .card-body {
    font-size: 7pt;
    color: #334155;
    line-height: 1.35;
  }

  .flow-arrow-down {
    text-align: center;
    color: #64748b;
    font-weight: bold;
    font-size: 9pt;
    line-height: 1;
    margin: 2px 0;
  }

  .flow-arrow-right {
    display: inline-block;
    color: #c5a880;
    font-weight: 800;
    margin: 0 3px;
  }

  /* Step by Step Flow Box */
  .step-node {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-left: 3.5px solid #0f172a;
    border-radius: 4px;
    padding: 5px 8px;
    margin-bottom: 4px;
    font-size: 7.2pt;
  }

  .step-node.green { border-left-color: #10b981; }
  .step-node.blue { border-left-color: #2563eb; }
  .step-node.gold { border-left-color: #c5a880; }
  .step-node.red { border-left-color: #ef4444; }
  .step-node.amber { border-left-color: #f59e0b; }

  .step-node strong {
    color: #0f172a;
    font-weight: 700;
  }

  .step-subtext {
    color: #64748b;
    font-size: 6.8pt;
    margin-top: 1.5px;
  }

  /* Code snippet display */
  code {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    padding: 1px 3.5px;
    border-radius: 3px;
    font-family: 'Courier New', Courier, monospace;
    font-size: 7pt;
    font-weight: 600;
  }

  .callout-box {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-left: 3.5px solid #d97706;
    padding: 5px 8px;
    border-radius: 4px;
    font-size: 7.2pt;
    color: #92400e;
    margin-bottom: 6px;
  }

  .callout-box.info {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-left-color: #2563eb;
    color: #1e40af;
  }

  .callout-box.success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-left-color: #16a34a;
    color: #166534;
  }

  .pipeline-step {
    display: table-cell;
    padding: 4px;
    text-align: center;
    vertical-align: top;
  }

  .pipeline-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 6px 4px;
    height: 100%;
  }

  .pipeline-num {
    display: inline-block;
    width: 16px;
    height: 16px;
    line-height: 16px;
    background: #0f172a;
    color: #c5a880;
    border-radius: 50%;
    font-size: 6.5pt;
    font-weight: bold;
    margin-bottom: 3px;
  }
</style>
</head>
<body>

<!-- ========================================================================= -->
<!-- PAGE 1: EXECUTIVE COVER PAGE                                              -->
<!-- ========================================================================= -->
<div class="page-container">
  <div class="cover-wrapper">
    <div class="cover-top">
      <div class="brand-badge">Official Architecture Specification</div>
      <h1 class="cover-title">MARVELOUS HOTEL &amp; DORMITORY<br><span>COMPLETE SYSTEM FLOWS</span></h1>
      <div class="cover-subtitle">3-Tier Multi-Portal Architecture, Synchronized Dual-Approval Engine, Housekeeping State Machine &amp; Conversational AI Core</div>
      
      <div class="cover-badges-grid">
        <div class="cover-badge-item">
          <div class="cover-pill">
            <i-tag>🔒</i-tag>
            Session Isolation<br>MH_USER / STAFF / ADMIN
          </div>
        </div>
        <div class="cover-badge-item">
          <div class="cover-pill">
            <i-tag>🪪</i-tag>
            KYC Identity Gate<br>Govt ID Proxy Shield
          </div>
        </div>
        <div class="cover-badge-item">
          <div class="cover-pill">
            <i-tag>👑 👤</i-tag>
            Dual-Approval Engine<br>Real-Time Auto-Sync
          </div>
        </div>
        <div class="cover-badge-item">
          <div class="cover-pill">
            <i-tag>🧹</i-tag>
            Cleaning State Machine<br>Automatic Maintenance
          </div>
        </div>
        <div class="cover-badge-item">
          <div class="cover-pill">
            <i-tag>🤖</i-tag>
            Marvi AI Engine<br>Tri-Role LLM Pipeline
          </div>
        </div>
      </div>
    </div>

    <div class="cover-main-box">
      <h3>Executive Summary &amp; Scope</h3>
      <p>This technical architectural manual documents the design, authentication boundaries, reservation lifecycle, housekeeping operations, financial clearances, and AI integration for the <strong>Marvelous Hotel &amp; Dormitory Management System</strong>.</p>
      <p>Engineered for high reliability and multi-tenant security, the system eliminates cross-contamination across three independent portals, guarantees financial integrity via a synchronized dual-approval engine with atomic concurrency locks, automates room hygiene workflows upon check-out, and autonomously releases stale inventory via a 24-hour housekeeping daemon.</p>

      <table class="cover-toc-table">
        <thead>
          <tr>
            <th style="width: 10%;">Page</th>
            <th style="width: 45%;">Module &amp; Focus Area</th>
            <th style="width: 45%;">Key Technical Upgrades &amp; Artifacts</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="font-weight:bold; color:#c5a880;">Page 1</td>
            <td>Title Cover, Architecture Badges &amp; Stack Metadata</td>
            <td>Executive overview, full 8-page blueprint, and capstone authorship.</td>
          </tr>
          <tr>
            <td style="font-weight:bold; color:#c5a880;">Page 2</td>
            <td>Executive 3-Tier Multi-Portal Architecture &amp; Sessions</td>
            <td>Isolated cookies, dedicated ports, MySQL single source of truth, file proxy gate.</td>
          </tr>
          <tr>
            <td style="font-weight:bold; color:#c5a880;">Page 3</td>
            <td>Authentication &amp; Centralized Navigation Architecture</td>
            <td>Bcrypt cost 12, rate limiters, session regeneration, unified slide-out drawer.</td>
          </tr>
          <tr>
            <td style="font-weight:bold; color:#c5a880;">Page 4</td>
            <td>Guest Lifecycle, Reservation State &amp; Auto-Expiry Daemon</td>
            <td>End-to-end guest flow, KYC verification gate, 24-hour auto-cancellation engine.</td>
          </tr>
          <tr>
            <td style="font-weight:bold; color:#c5a880;">Page 5</td>
            <td>Staff Operations &amp; Housekeeping State Machine</td>
            <td>Frontdesk least-privilege matrix, automated check-out maintenance shift.</td>
          </tr>
          <tr>
            <td style="font-weight:bold; color:#c5a880;">Page 6</td>
            <td>Super Admin Master Governance &amp; Audit Matrix</td>
            <td>System configuration, closed staff provisioning, audit trails, and AI controls.</td>
          </tr>
          <tr>
            <td style="font-weight:bold; color:#c5a880;">Page 7</td>
            <td>Marvi AI Engine Architecture &amp; NLP Budget Matcher</td>
            <td>4-stage AI pipeline, Tagalog/English NLP, prompt shield, and local fallback.</td>
          </tr>
          <tr>
            <td style="font-weight:bold; color:#c5a880;">Page 8</td>
            <td>Payment Settlement &amp; Synchronized Dual-Approval</td>
            <td>Maya/GCash manual clearing, role badges, and atomic concurrency locking.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="cover-footer">
      <div class="cover-footer-col">
        <strong>PROJECT &amp; STACK</strong>
        Marvelous Hotel &amp; Dormitory<br>
        PHP 8.2 • MySQL 8.0 • XML • WeasyPrint 70.0
      </div>
      <div class="cover-footer-col">
        <strong>TECHNICAL ARCHITECT</strong>
        Dr. ENI, Lead System Designer<br>
        Pair Programmed &amp; Verified for Production
      </div>
      <div class="cover-footer-col" style="text-align: right;">
        <strong>CLIENT &amp; LEAD</strong>
        LO, Executive Director<br>
        Edition: November 09, 2026 • Final Defense
      </div>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- PAGE 2: EXECUTIVE 3-TIER MULTI-PORTAL ARCHITECTURE                        -->
<!-- ========================================================================= -->
<div class="page-container">
  <h1><span class="section-tag">Section 1</span> Executive 3-Tier Multi-Portal Architecture &amp; Isolated Sessions</h1>
  <div class="intro-text">
    The Marvelous Hotel &amp; Dormitory Management System utilizes a strictly partitioned 3-tier portal architecture. To prevent privilege escalation, session fixation, and cross-actor state pollution, each portal runs on an independent session namespace, rate limiter, and authorization gate while reading from a shared, ACID-compliant MySQL database.
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 18%;">Actor &amp; Portal</th>
        <th style="width: 14%;">Host / Route</th>
        <th style="width: 18%;">Session Namespace</th>
        <th style="width: 25%;">Access Gates &amp; Security Shield</th>
        <th style="width: 25%;">Domain Scope &amp; Capabilities</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>👤 Guest / Public</strong></td>
        <td><code>:8080</code><br><code>/hotel/user/</code></td>
        <td><code>MH_USER_SESS</code></td>
        <td><code>applyAuthRateLimit('login')</code><br>Public self-registration; CSRF token validation.</td>
        <td>Room browsing, AI concierge, reservation checkout, KYC ID upload, e-wallet proof payment.</td>
      </tr>
      <tr>
        <td><strong>👨‍💼 Frontdesk Staff</strong></td>
        <td><code>:8082</code><br><code>/hotel/staff/</code></td>
        <td><code>MH_STAFF_SESS</code></td>
        <td><code>requireStaff()</code> Gate<br>Closed to public; admin-provisioned only.</td>
        <td>Guest check-in/out, live room cleaning matrix, delegated payment verification with concurrency lock.</td>
      </tr>
      <tr>
        <td><strong>👑 Super Admin</strong></td>
        <td><code>:8081</code><br><code>/hotel/admin/</code></td>
        <td><code>MH_ADMIN_SESS</code></td>
        <td><code>requireAdmin()</code> Gate<br>Dedicated master auth guard; audit logged.</td>
        <td>Master pricing, room management, staff provisioning, master payments ledger, audit logs, AI configs.</td>
      </tr>
      <tr>
        <td><strong>🤖 Marvi AI Engine</strong></td>
        <td>Service Layer<br><code>/hotel/ai/</code></td>
        <td>Stateless REST<br>Session-Aware Context</td>
        <td><code>applyRateLimit('ai_chat', 20)</code><br>Prompt sanitization; SQL injection filters.</td>
        <td>NLP room recommendations, staff shift handover summaries, executive revenue digests.</td>
      </tr>
    </tbody>
  </table>

  <div class="diagram-container">
    <div class="diagram-title">
      <span>Architectural Perimeter &amp; Data Boundary Diagram</span>
      <span class="badge-pill">Single Source of Truth (SSOT)</span>
    </div>

    <div class="arch-grid">
      <div class="arch-col" style="width: 33.3%;">
        <div class="card-box primary">
          <div class="card-header">
            <span>👤 USER PORTAL</span>
            <span class="badge badge-guest">:8080</span>
          </div>
          <div class="card-body">
            <strong>Endpoint:</strong> <code>user/dashboard.php</code><br>
            <strong>Session:</strong> <code>MH_USER_SESS</code><br>
            • Public Catalog &amp; Direct Booking<br>
            • KYC Verification Submission Gate<br>
            • E-Wallet Manual Payment Upload<br>
            • Interactive Marvi AI Concierge
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 33.3%;">
        <div class="card-box warning">
          <div class="card-header">
            <span>👨‍💼 STAFF PORTAL</span>
            <span class="badge badge-staff">:8082</span>
          </div>
          <div class="card-body">
            <strong>Endpoint:</strong> <code>staff/dashboard.php</code><br>
            <strong>Session:</strong> <code>MH_STAFF_SESS</code><br>
            • Guest Arrivals / Check-In Desk<br>
            • Auto-Maintenance Check-Out<br>
            • Delegated Receipt Audit Lightbox<br>
            • Shift Handover Co-pilot Briefing
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 33.3%;">
        <div class="card-box danger">
          <div class="card-header">
            <span>👑 ADMIN PORTAL</span>
            <span class="badge badge-admin">:8081</span>
          </div>
          <div class="card-body">
            <strong>Endpoint:</strong> <code>admin/dashboard.php</code><br>
            <strong>Session:</strong> <code>MH_ADMIN_SESS</code><br>
            • Master Room Inventory &amp; Rates<br>
            • Closed Staff Account Provisioning<br>
            • Master Dual-Approval Payment Desk<br>
            • Security Audit Logs &amp; AI Tuning
          </div>
        </div>
      </div>
    </div>

    <div class="flow-arrow-down">▼ &nbsp; Independent Session Boundaries &amp; CSRF Anti-Replay Tokens &nbsp; ▼</div>

    <div class="arch-grid" style="margin-top: 4px;">
      <div class="arch-col" style="width: 50%;">
        <div class="card-box gold">
          <div class="card-header">
            <span>🛡️ SECURITY GATEWAY &amp; FILE PROXY</span>
            <span class="badge badge-gold">serve-file.php</span>
          </div>
          <div class="card-body">
            • <strong>Path Traversal Defense:</strong> <code>basename()</code> &amp; MIME-type whitelist (JPG, PNG, PDF)<br>
            • <strong>Protected Directories:</strong> <code>uploads/ids/</code> (KYC) &amp; <code>uploads/proofs/</code> (Receipts)<br>
            • <strong>Access Gate:</strong> Strict role checks; guests access only their own files; staff/admin audited.
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 50%;">
        <div class="card-box purple">
          <div class="card-header">
            <span>🤖 MARVI AI SERVICE LAYER</span>
            <span class="badge badge-ai">ai/chatbot.php</span>
          </div>
          <div class="card-body">
            • <strong>Tri-Role Prompts:</strong> Dynamic persona selection (Guest, Staff, Admin)<br>
            • <strong>Cloud LLM:</strong> NVIDIA NIM (Meta-Llama 3.1) / Gemini API streaming<br>
            • <strong>Deterministic Fallback:</strong> Local SQL NLP pattern matcher when offline.
          </div>
        </div>
      </div>
    </div>

    <div class="flow-arrow-down">▼ &nbsp; Persistent Data Operations (ACID Transactions &amp; Row-Level Locks) &nbsp; ▼</div>

    <div class="card-box success" style="margin-top: 2px;">
      <div class="card-header">
        <span>💾 SHARED MYSQL 8.0 DATABASE &amp; CONCURRENCY CONTROL ENGINE</span>
        <span class="badge badge-gold">InnoDB Storage Engine</span>
      </div>
      <div class="card-body" style="display: table; width: 100%;">
        <div style="display: table-cell; width: 25%;">
          <strong>users</strong><br>
          Roles: user, staff, admin<br>
          KYC status &amp; password hashes
        </div>
        <div style="display: table-cell; width: 25%;">
          <strong>rooms &amp; categories</strong><br>
          Status: available, occupied, maintenance<br>
          Capacity, amenities &amp; pricing
        </div>
        <div style="display: table-cell; width: 25%;">
          <strong>bookings &amp; payments</strong><br>
          Dual verifier columns (accepted_by)<br>
          Pending, confirmed, cancelled
        </div>
        <div style="display: table-cell; width: 25%;">
          <strong>audit_logs &amp; notifications</strong><br>
          Full immutable audit trail<br>
          In-app alerts &amp; push events
        </div>
      </div>
    </div>
  </div>

  <div class="callout-box info">
    <strong>Key Architectural Isolation Rule:</strong> Even when running on identical hostnames under local development (e.g., <code>localhost/hotel</code>), cookies are partitioned with distinct names (<code>MH_USER_SESS</code>, <code>MH_STAFF_SESS</code>, <code>MH_ADMIN_SESS</code>). A frontdesk officer logging into <code>staff/login.php</code> cannot hijack an admin route or overwrite guest shopping sessions.
  </div>
</div>

<!-- ========================================================================= -->
<!-- PAGE 3: AUTHENTICATION & CENTRALIZED NAVIGATION ARCHITECTURE              -->
<!-- ========================================================================= -->
<div class="page-container">
  <h1><span class="section-tag">Section 2</span> Authentication &amp; Centralized Navigation Architecture</h1>
  <div class="intro-text">
    Marvelous Hotel combines hardened multi-portal security with modern UX simplicity. The authentication engine enforces cryptographic protection (Bcrypt cost 12), session fixation defense, and brute-force lockouts. Simultaneously, the navigation system unifies all public and private routes into an intuitive, slide-out drawer triggered by the interactive user profile badge.
  </div>

  <div class="diagram-container">
    <div class="diagram-title">
      <span>Multi-Portal Authentication &amp; Verification Decision Tree</span>
      <span class="badge-pill">Zero-Trust Auth Guard</span>
    </div>

    <div class="arch-grid">
      <div class="arch-col" style="width: 50%;">
        <div class="step-node blue">
          <strong>1. Submission &amp; Rate Limiter Gate</strong>
          <div class="step-subtext">POST to <code>auth/login.php</code>, <code>staff/login.php</code>, or <code>admin/login.php</code>. Evaluates <code>applyAuthRateLimit($scope)</code>. If > 5 failures within 15 minutes, IP is blocked with a <code>429 Too Many Requests</code> flash alert.</div>
        </div>
        <div class="step-node gold">
          <strong>2. Credential Hash Verification</strong>
          <div class="step-subtext">Queries record by unique email. Validates password via <code>password_verify($password, $user['password_hash'])</code> using Bcrypt cost 12. If invalid, logs failure to <code>audit_logs</code> with client IP and user agent.</div>
        </div>
        <div class="step-node red">
          <strong>3. Role &amp; Portal Alignment Check</strong>
          <div class="step-subtext">Enforces strict boundary checks: Staff cannot log into <code>admin/login.php</code>; Guests cannot log into <code>staff/login.php</code>. Rejects unauthorized roles and redirects with error.</div>
        </div>
      </div>
      <div class="arch-col" style="width: 50%;">
        <div class="step-node amber">
          <strong>4. Account Status &amp; KYC Verification</strong>
          <div class="step-subtext">Checks <code>status === 'active'</code> (banned users are immediately locked out). Evaluates email verification and retrieves government ID status (<code>unverified</code>, <code>pending</code>, <code>verified</code>).</div>
        </div>
        <div class="step-node green">
          <strong>5. Session Fixation Defense &amp; Regeneration</strong>
          <div class="step-subtext">Executes <code>session_regenerate_id(true)</code> to discard previous anonymous session tokens. Stores user ID, role, and permissions into the portal-specific cookie namespace.</div>
        </div>
        <div class="step-node green">
          <strong>6. Sanitized Redirection</strong>
          <div class="step-subtext">Validates <code>$_GET['redirect']</code> via <code>sanitizeRedirect()</code> to defeat Open-Redirect phishing attacks. Dispatches actor to authorized portal dashboard.</div>
        </div>
      </div>
    </div>
  </div>

  <h2>Centralized Navigation Architecture: Interactive Profile Badge &amp; Drawer</h2>
  <div class="intro-text">
    To eliminate visual clutter, duplicate 3-dot buttons, and conflicting mobile menus, authenticated navigation is centralized into a single sliding drawer (<code>#centralNav</code>). The header displays an interactive user profile badge (<code>.nav-profile-badge</code>) that acts as the primary controller.
  </div>

  <div class="diagram-container">
    <div class="diagram-title">
      <span>Unified Slide-Out Drawer Navigation Flow (#centralNav)</span>
      <span class="badge-pill">Mobile &amp; Desktop Unified</span>
    </div>

    <div class="arch-grid">
      <div class="arch-col" style="width: 34%;">
        <div class="card-box gold">
          <div class="card-header">
            <span>TRIGGER COMPONENT</span>
            <span class="badge badge-gold">Header</span>
          </div>
          <div class="card-body">
            <strong>Interactive Badge:</strong><br>
            <code>[Avatar] Ian ⌵</code> (Profile Badge)<br>
            • Embedded in desktop &amp; mobile nav.<br>
            • Replaces clunky separate dropdowns.<br>
            • On Click: calls <code>toggleCentralNav()</code>.<br>
            • Shows live verification checkmark.
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 33%;">
        <div class="card-box primary">
          <div class="card-header">
            <span>DRAWER OVERLAY &amp; PANEL</span>
            <span class="badge badge-guest">CSS3 Slide-Out</span>
          </div>
          <div class="card-body">
            <strong>Centralized Drawer (<code>#centralNav</code>):</strong><br>
            • Fixed right-hand slide (320px width).<br>
            • Dimmed backdrop <code>#centralNavOverlay</code>.<br>
            • Brand header with quick close button.<br>
            • Dynamic User Summary Card at top.
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 33%;">
        <div class="card-box success">
          <div class="card-header">
            <span>ROLE-AWARE ROUTE SECTIONS</span>
            <span class="badge badge-ai">includes/components/</span>
          </div>
          <div class="card-body">
            <strong>Intelligent Route Partitioning:</strong><br>
            • <strong>Guest:</strong> My Bookings, Profile, AI Concierge.<br>
            • <strong>Staff:</strong> Check-in Desk, Rooms Status.<br>
            • <strong>Admin:</strong> Master Settings, Audits, Users.<br>
            • Safe Sign-Out with CSRF protection.
          </div>
        </div>
      </div>
    </div>

    <div class="step-node blue" style="margin-top: 4px;">
      <strong>UX Optimization:</strong> The centralized navigation model provides consistent access across all form factors. On desktop, it frees up horizontal header space for brand aesthetics. On mobile, it acts as the primary navigation drawer, eliminating the redundant hamburger menu for authenticated guests.
    </div>
  </div>

  <div class="callout-box success">
    <strong>Closed Staff Registration Protocol:</strong> Unlike guest accounts, staff registration is strictly prohibited through public endpoints. <code>auth/register-staff.php</code> is locked and automatically redirects to the homepage. Only authenticated Super Admins inside <code>admin/staff.php</code> can provision new staff credentials.
  </div>
</div>

<!-- ========================================================================= -->
<!-- PAGE 4: GUEST LIFECYCLE, RESERVATION STATE & AUTO-EXPIRY DAEMON           -->
<!-- ========================================================================= -->
<div class="page-container">
  <h1><span class="section-tag">Section 3</span> Guest Lifecycle, Reservation State Machine &amp; Auto-Expiry Daemon</h1>
  <div class="intro-text">
    The guest journey transitions through catalog discovery, AI matching, date/rate booking calculations, mandatory KYC identity verification, and payment proof settlement. Unpaid reservations are actively monitored and cleared by the 24-hour housekeeping daemon.
  </div>

  <div class="diagram-container">
    <div class="diagram-title">
      <span>End-to-End Guest Reservation State Machine</span>
      <span class="badge-pill">State Transition Integrity</span>
    </div>

    <div class="arch-grid">
      <div class="arch-col" style="width: 20%;">
        <div class="step-node blue" style="text-align: center;">
          <strong>1. DISCOVERY</strong><br>
          <span class="badge badge-guest">Catalog / AI</span>
          <div class="step-subtext">Browse rooms, filter by budget &amp; capacity, or consult Marvi AI.</div>
        </div>
      </div>
      <div class="arch-col" style="width: 20%;">
        <div class="step-node gold" style="text-align: center;">
          <strong>2. BOOKING FORM</strong><br>
          <span class="badge badge-gold">Pending State</span>
          <div class="step-subtext">Select dates &amp; guests. Generates unique voucher <code>MH-YYYY-XXXX</code>.</div>
        </div>
      </div>
      <div class="arch-col" style="width: 20%;">
        <div class="step-node red" style="text-align: center;">
          <strong>3. KYC GATE</strong><br>
          <span class="badge badge-admin">ID Mandatory</span>
          <div class="step-subtext">Must have verified government ID before payment submission is unlocked.</div>
        </div>
      </div>
      <div class="arch-col" style="width: 20%;">
        <div class="step-node amber" style="text-align: center;">
          <strong>4. SETTLEMENT</strong><br>
          <span class="badge badge-staff">QR Upload</span>
          <div class="step-subtext">Scan Maya/GCash QR, input 13-digit Ref #, and upload screenshot.</div>
        </div>
      </div>
      <div class="arch-col" style="width: 20%;">
        <div class="step-node green" style="text-align: center;">
          <strong>5. CONFIRMED</strong><br>
          <span class="badge badge-ai">Dual-Approved</span>
          <div class="step-subtext">Audited by Admin or Staff. Room reserved; voucher printable.</div>
        </div>
      </div>
    </div>
  </div>

  <h2>Stale Booking Auto-Cancellation Daemon: <code>autoExpireStaleBookings(24)</code></h2>
  <div class="intro-text">
    A common hotel vulnerability is "inventory starvation", where guests create pending bookings without settling payments, locking up valuable rooms. Marvelous Hotel solves this via an automated housekeeping daemon embedded in system headers.
  </div>

  <div class="diagram-container">
    <div class="diagram-title">
      <span>Autonomous Stale Booking Expiry Workflow (24-Hour Daemon)</span>
      <span class="badge-pill">Automatic Inventory Reclaim</span>
    </div>

    <div class="arch-grid">
      <div class="arch-col" style="width: 32%;">
        <div class="card-box warning">
          <div class="card-header">
            <span>1. DAEMON INVOCATION</span>
            <span class="badge badge-gold">Auto-Triggered</span>
          </div>
          <div class="card-body">
            • <strong>Trigger:</strong> Executes on dashboard initialization in <code>admin/dashboard.php</code> &amp; <code>staff/dashboard.php</code>.<br>
            • <strong>Interval:</strong> Configured to 24-hour threshold (<code>INTERVAL 24 HOUR</code>).<br>
            • <strong>Target Query:</strong> Scans for bookings in <code>status = 'pending'</code> created &gt; 24h ago with zero payment records.
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 36%;">
        <div class="card-box danger">
          <div class="card-header">
            <span>2. ATOMIC CANCELLATION &amp; AUDIT</span>
            <span class="badge badge-admin">MySQL PDO</span>
          </div>
          <div class="card-body">
            • <strong>Status Transition:</strong> Sets <code>status = 'cancelled'</code>.<br>
            • <strong>Cancellation Reason:</strong> "Auto-cancelled: Payment was not settled within 24 hours of booking."<br>
            • <strong>Timestamp:</strong> Records <code>cancelled_at = NOW()</code>.<br>
            • <strong>Audit Log:</strong> Logs event to <code>audit_logs</code> as 'Booking Auto-Expired' under actor 'System Daemon'.
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 32%;">
        <div class="card-box success">
          <div class="card-header">
            <span>3. GUEST ALERT &amp; INVENTORY RELEASE</span>
            <span class="badge badge-guest">Live Re-Stock</span>
          </div>
          <div class="card-body">
            • <strong>Guest Notification:</strong> Dispatches in-app notification via <code>createNotification()</code>.<br>
            • <strong>Inventory Reclaim:</strong> Immediately unlocks room dates for new prospective guests.<br>
            • <strong>Financial Protection:</strong> Eliminates manual staff tracking of abandoned reservations.
          </div>
        </div>
      </div>
    </div>

    <div class="step-node green" style="margin-top: 4px;">
      <strong>Deterministic SQL Query:</strong><br>
      <code>SELECT b.id, b.booking_code, b.user_id, b.room_id FROM bookings b WHERE b.status = 'pending' AND b.created_at &lt; DATE_SUB(NOW(), INTERVAL 24 HOUR) AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.booking_id = b.id AND p.status IN ('verified', 'pending'))</code>
    </div>
  </div>

  <div class="callout-box warning">
    <strong>KYC Prerequisite Rule:</strong> The payment portal blocks receipt submission until <code>users.profile_verified = 1</code> or valid ID proof is reviewed. Guests uploading invalid or fake IDs are flagged in the admin console before any financial settlement occurs.
  </div>
</div>

<!-- ========================================================================= -->
<!-- PAGE 5: STAFF FRONTDESK OPERATIONS & HOUSEKEEPING CLEANING STATE          -->
<!-- ========================================================================= -->
<div class="page-container">
  <h1><span class="section-tag">Section 4</span> Staff Frontdesk Operations &amp; Housekeeping Cleaning State Machine</h1>
  <div class="intro-text">
    Frontdesk officers serve as the operational backbone of Marvelous Hotel, handling arrivals, departures, physical room sanitation inspections, and delegated payment clearances. Operating under a strict <strong>least-privilege model</strong>, staff cannot alter pricing, delete user accounts, or modify system configurations.
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 25%;">Operational Module</th>
        <th style="width: 25%;">Endpoint &amp; Script</th>
        <th style="width: 25%;">Authorized Actions</th>
        <th style="width: 25%;">Restricted / Blocked Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Check-In &amp; Check-Out Desk</strong></td>
        <td><code>staff/check-in.php</code></td>
        <td>Scan vouchers, confirm guest identity, execute check-in (occupied) and check-out (maintenance).</td>
        <td>Cannot override booking totals, modify stay dates, or bypass confirmed status check.</td>
      </tr>
      <tr>
        <td><strong>Live Housekeeping Matrix</strong></td>
        <td><code>staff/rooms-status.php</code></td>
        <td>Inspect room cleanliness, toggle rooms from <code>maintenance</code> to <code>available</code>.</td>
        <td>Cannot delete room assets, change room categories, or manipulate nightly pricing.</td>
      </tr>
      <tr>
        <td><strong>Delegated Payments Desk</strong></td>
        <td><code>staff/payments.php</code></td>
        <td>Audit Maya/GCash proof screenshots in lightbox, approve/reject pending transactions.</td>
        <td>Cannot approve without verified proof; verifier ID is permanently stamped to audit log.</td>
      </tr>
      <tr>
        <td><strong>Shift Handover Co-pilot</strong></td>
        <td><code>staff/dashboard.php</code></td>
        <td>Generate AI-driven shift handover summaries (arrivals, pending tasks, cleaning backlog).</td>
        <td>Cannot alter prompt engineering guidelines or switch AI backends.</td>
      </tr>
    </tbody>
  </table>

  <h2>Automated Housekeeping &amp; Sanitation State Machine</h2>
  <div class="intro-text">
    To eliminate human error and prevent guests from being assigned uncleaned rooms, check-out triggers an automatic shift to <code>maintenance</code> (Housekeeping/Cleaning inspection).
  </div>

  <div class="diagram-container">
    <div class="diagram-title">
      <span>Room Sanitation &amp; Turnover Lifecycle Flowchart</span>
      <span class="badge-pill">Hygiene Assurance Guarantee</span>
    </div>

    <div class="arch-grid">
      <div class="arch-col" style="width: 25%;">
        <div class="card-box success">
          <div class="card-header">
            <span>STATE: AVAILABLE</span>
            <span class="badge badge-guest">Vacant</span>
          </div>
          <div class="card-body">
            • Clean, sanitized, and inspected.<br>
            • Visible in public catalog.<br>
            • Ready for guest check-in.<br>
            • <code>rooms.status = 'available'</code>
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 25%;">
        <div class="card-box primary">
          <div class="card-header">
            <span>STATE: OCCUPIED</span>
            <span class="badge badge-staff">In-Stay</span>
          </div>
          <div class="card-body">
            • Guest arrives with voucher.<br>
            • Staff clicks 'Check-In'.<br>
            • <code>bookings.status = 'checked_in'</code><br>
            • <code>rooms.status = 'occupied'</code>
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 25%;">
        <div class="card-box danger">
          <div class="card-header">
            <span>STATE: MAINTENANCE</span>
            <span class="badge badge-admin">Auto-Shift</span>
          </div>
          <div class="card-body">
            • Guest departs with staff.<br>
            • Staff clicks 'Check-Out'.<br>
            • <code>rooms.status = 'maintenance'</code><br>
            • <strong>Room locked from booking!</strong>
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 25%;">
        <div class="card-box gold">
          <div class="card-header">
            <span>SANITATION RE-SET</span>
            <span class="badge badge-gold">Staff Inspection</span>
          </div>
          <div class="card-body">
            • Cleaning team sanitizes room.<br>
            • Staff inspects towels &amp; linens.<br>
            • Staff toggles to 'Available' on <code>staff/rooms-status.php</code>.<br>
            • Re-enters public inventory.
          </div>
        </div>
      </div>
    </div>

    <div class="step-node amber" style="margin-top: 4px;">
      <strong>Atomic Database Logic in <code>staff/check-in.php</code>:</strong><br>
      <code>dbExec("UPDATE bookings SET status = 'checked_out', checked_out_at = NOW() WHERE id = ?", [$bookingId]);</code><br>
      <code>dbExec("UPDATE rooms SET status = 'maintenance' WHERE id = ?", [$bk['room_id']]);</code><br>
      <code>auditLog('Customer Checked Out', 'Staff Module', "Booking #{$bk['booking_code']} marked checked out. Room set to Cleaning / Maintenance.");</code>
    </div>
  </div>

  <div class="callout-box success">
    <strong>Zero-Dirty-Room Guarantee:</strong> Because <code>staff/check-in.php</code> automatically sets <code>rooms.status = 'maintenance'</code> upon check-out, frontdesk staff cannot accidentally assign a room to an incoming guest until a cleaning team has physically serviced the room and staff has updated its status on <code>staff/rooms-status.php</code>.
  </div>
</div>

<!-- ========================================================================= -->
<!-- PAGE 6: SUPER ADMIN MASTER GOVERNANCE & AUDIT MATRIX                      -->
<!-- ========================================================================= -->
<div class="page-container">
  <h1><span class="section-tag">Section 5</span> Super Admin Master Governance, Audit Trail &amp; AI Security Controls</h1>
  <div class="intro-text">
    The Super Admin maintains executive sovereignty over the Marvelous Hotel platform. Accessible solely through <code>admin/dashboard.php</code> with dedicated <code>MH_ADMIN_SESS</code> cookies, the admin console controls room pricing, inventory expansion, staff account provisioning, KYC approvals, payment verifications, and AI API configurations.
  </div>

  <div class="diagram-container">
    <div class="diagram-title">
      <span>Super Admin Master Governance &amp; Functional Hierarchy</span>
      <span class="badge-pill">Executive Authority Matrix</span>
    </div>

    <div class="arch-grid">
      <div class="arch-col" style="width: 33.3%;">
        <div class="card-box danger">
          <div class="card-header">
            <span>STAFF &amp; USER GOVERNANCE</span>
            <span class="badge badge-admin">admin/staff.php</span>
          </div>
          <div class="card-body">
            • <strong>Sole Account Provisioner:</strong> Creates staff accounts with unique employee IDs.<br>
            • <strong>User KYC Verification:</strong> Audits government IDs on <code>admin/users.php</code>.<br>
            • <strong>Security Lockout:</strong> Bans suspicious users; revokes rogue staff access.
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 33.3%;">
        <div class="card-box gold">
          <div class="card-header">
            <span>INVENTORY &amp; PRICING</span>
            <span class="badge badge-gold">admin/rooms.php</span>
          </div>
          <div class="card-body">
            • <strong>Room &amp; Dorm Setup:</strong> Configures single, double, family, and dormitory units.<br>
            • <strong>Dynamic Rates:</strong> Sets weekday/weekend rates and extra-bed surcharges.<br>
            • <strong>Media Management:</strong> Uploads high-res photography and amenities.
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 33.3%;">
        <div class="card-box primary">
          <div class="card-header">
            <span>FINANCIAL CLEARANCE</span>
            <span class="badge badge-guest">admin/payments.php</span>
          </div>
          <div class="card-body">
            • <strong>Master Payments Desk:</strong> Audits high-value Maya &amp; GCash receipts.<br>
            • <strong>Synchronized Auto-Sync:</strong> Stamps <code>accepted_by = admin_id</code>.<br>
            • <strong>Voucher Release:</strong> Triggers official guest reservation PDF voucher.
          </div>
        </div>
      </div>
    </div>

    <div class="arch-grid" style="margin-top: 4px;">
      <div class="arch-col" style="width: 50%;">
        <div class="card-box purple">
          <div class="card-header">
            <span>SECURITY AUDIT TRAIL ENGINE</span>
            <span class="badge badge-ai">admin/audit-logs.php</span>
          </div>
          <div class="card-body">
            • <strong>Immutable Logging:</strong> Every critical event (login failure, status change, payment review, check-in) is recorded in <code>audit_logs</code>.<br>
            • <strong>Metadata Captured:</strong> Timestamp, Actor ID, Role, Client IP Address, Action Summary, and Before/After State Diffs.<br>
            • <strong>Security Filter:</strong> Filter logs by actor type, date range, or suspicious keyword.
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 50%;">
        <div class="card-box warning">
          <div class="card-header">
            <span>AI CONFIGURATION &amp; PROMPT SHIELD</span>
            <span class="badge badge-staff">admin/ai-config.php</span>
          </div>
          <div class="card-body">
            • <strong>API Key Management:</strong> Secure storage of NVIDIA NIM and Gemini API credentials.<br>
            • <strong>Prompt Defense:</strong> Sets jailbreak rules, system prompt persona, and blocked terms.<br>
            • <strong>Fallback Toggles:</strong> Enables/disables local SQL deterministic matcher when offline.
          </div>
        </div>
      </div>
    </div>
  </div>

  <h2>Security Matrix: Role-Based Access Control (RBAC) Comparison</h2>
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 25%;">Capability / Privilege</th>
        <th style="width: 25%; text-align: center;">👑 Super Admin</th>
        <th style="width: 25%; text-align: center;">👨‍💼 Frontdesk Staff</th>
        <th style="width: 25%; text-align: center;">👤 Guest / User</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Self-Registration</td>
        <td style="text-align: center; color: #991b1b; font-weight: bold;">❌ Locked (One-Time Bootstrap)</td>
        <td style="text-align: center; color: #991b1b; font-weight: bold;">❌ Blocked (Admin Only)</td>
        <td style="text-align: center; color: #166534; font-weight: bold;">✅ Public Self-Registration</td>
      </tr>
      <tr>
        <td>Room &amp; Nightly Price Editing</td>
        <td style="text-align: center; color: #166534; font-weight: bold;">✅ Full Sovereign Control</td>
        <td style="text-align: center; color: #991b1b; font-weight: bold;">❌ Read-Only Access</td>
        <td style="text-align: center; color: #991b1b; font-weight: bold;">❌ Read-Only Catalog</td>
      </tr>
      <tr>
        <td>Staff Account Provisioning &amp; Deletion</td>
        <td style="text-align: center; color: #166534; font-weight: bold;">✅ Full Authority</td>
        <td style="text-align: center; color: #991b1b; font-weight: bold;">❌ Forbidden</td>
        <td style="text-align: center; color: #991b1b; font-weight: bold;">❌ Forbidden</td>
      </tr>
      <tr>
        <td>Payment Verification (GCash / Maya)</td>
        <td style="text-align: center; color: #166534; font-weight: bold;">✅ Master Approval Badge</td>
        <td style="text-align: center; color: #166534; font-weight: bold;">✅ Delegated Staff Badge</td>
        <td style="text-align: center; color: #991b1b; font-weight: bold;">❌ Proof Upload Only</td>
      </tr>
      <tr>
        <td>Housekeeping &amp; Cleaning Inspection</td>
        <td style="text-align: center; color: #166534; font-weight: bold;">✅ View / Override</td>
        <td style="text-align: center; color: #166534; font-weight: bold;">✅ Daily Operational Update</td>
        <td style="text-align: center; color: #991b1b; font-weight: bold;">❌ Forbidden</td>
      </tr>
      <tr>
        <td>Audit Trail &amp; AI Engine Configuration</td>
        <td style="text-align: center; color: #166534; font-weight: bold;">✅ Full Access</td>
        <td style="text-align: center; color: #991b1b; font-weight: bold;">❌ Forbidden</td>
        <td style="text-align: center; color: #991b1b; font-weight: bold;">❌ Forbidden</td>
      </tr>
    </tbody>
  </table>

  <div class="callout-box info">
    <strong>Immutable Audit Guarantee:</strong> Audit log entries cannot be modified or truncated through the admin user interface. Any administrative action performed by the Super Admin is stamped with their user ID, IP address, and timestamp.
  </div>
</div>

<!-- ========================================================================= -->
<!-- PAGE 7: MARVI AI ENGINE ARCHITECTURE & NLP BUDGET MATCHER                 -->
<!-- ========================================================================= -->
<div class="page-container">
  <h1><span class="section-tag">Section 6</span> Marvi AI Engine Architecture, Tri-Role Prompts &amp; NLP Budget Matcher</h1>
  <div class="intro-text">
    The Marvelous Hotel AI subsystem, <strong>Marvi AI</strong>, is engineered as a multi-role conversational assistant. It serves guests with NLP-driven budget matching, assists frontdesk staff with shift handover summaries, and provides executives with operational revenue digests. It incorporates prompt shields and a deterministic local fallback.
  </div>

  <div class="diagram-container">
    <div class="diagram-title">
      <span>4-Stage Conversational Processing Pipeline (ai/chatbot.php)</span>
      <span class="badge-pill">NVIDIA NIM / Gemini Flash</span>
    </div>

    <div style="display: table; width: 100%;">
      <div class="pipeline-step" style="width: 25%;">
        <div class="pipeline-card">
          <div class="pipeline-num">1</div>
          <strong style="display:block; font-size:7.2pt; color:#0f172a;">Intent &amp; Language</strong>
          <div style="font-size:6.7pt; color:#64748b; margin-top:2px;">
            Detects English or Tagalog/Taglish. Identifies intent: Room Search, Amenities, Policy, or Shift Briefing.
          </div>
        </div>
      </div>
      <div class="pipeline-step" style="width: 25%;">
        <div class="pipeline-card">
          <div class="pipeline-num">2</div>
          <strong style="display:block; font-size:7.2pt; color:#0f172a;">Budget &amp; Guests</strong>
          <div style="font-size:6.7pt; color:#64748b; margin-top:2px;">
            Regex &amp; NLP extraction: parses <code>under 1.5k</code> (1500 PHP), <code>for 2 pax</code>, and dates from natural language.
          </div>
        </div>
      </div>
      <div class="pipeline-step" style="width: 25%;">
        <div class="pipeline-card">
          <div class="pipeline-num">3</div>
          <strong style="display:block; font-size:7.2pt; color:#0f172a;">Prompt Shield</strong>
          <div style="font-size:6.7pt; color:#64748b; margin-top:2px;">
            Filters jailbreaks, prompt injection, and unauthorized database access attempts. Rate limited: 20 req/min.
          </div>
        </div>
      </div>
      <div class="pipeline-step" style="width: 25%;">
        <div class="pipeline-card">
          <div class="pipeline-num">4</div>
          <strong style="display:block; font-size:7.2pt; color:#0f172a;">Dual-Engine Output</strong>
          <div style="font-size:6.7pt; color:#64748b; margin-top:2px;">
            Queries Cloud LLM or Local Deterministic Engine. Returns structured JSON with UI room cards and booking CTA.
          </div>
        </div>
      </div>
    </div>
  </div>

  <h2>Tri-Role Context Adaptation &amp; Dual Engine Execution</h2>
  <div class="arch-grid">
    <div class="arch-col" style="width: 33.3%;">
      <div class="card-box primary">
        <div class="card-header">
          <span>👤 GUEST AI CONCIERGE</span>
          <span class="badge badge-guest">Public</span>
        </div>
        <div class="card-body">
          • <strong>Persona:</strong> Warm, hospitable Filipino concierge ("Marvi").<br>
          • <strong>NLP Matching:</strong> Maps queries like <em>"May room ba for 3 people under 2k?"</em> directly to available inventory.<br>
          • <strong>UI Cards:</strong> Returns photo, price, amenities, and direct 1-click 'Book Now' button.
        </div>
      </div>
    </div>
    <div class="arch-col" style="width: 33.3%;">
      <div class="card-box warning">
        <div class="card-header">
          <span>👨‍💼 STAFF SHIFT COPILOT</span>
          <span class="badge badge-staff">staff/</span>
        </div>
        <div class="card-body">
          • <strong>Persona:</strong> Efficient operations assistant.<br>
          • <strong>Shift Briefings:</strong> Analyzes today's expected arrivals, departures, and pending housekeeping cleaning backlog.<br>
          • <strong>Delegated Alerts:</strong> Highlights unverified GCash/Maya receipts requiring attention.
        </div>
      </div>
    </div>
    <div class="arch-col" style="width: 33.3%;">
      <div class="card-box danger">
        <div class="card-header">
          <span>👑 ADMIN REVENUE DIGEST</span>
          <span class="badge badge-admin">admin/</span>
        </div>
        <div class="card-body">
          • <strong>Persona:</strong> Executive business intelligence advisor.<br>
          • <strong>Occupancy Metrics:</strong> Aggregates monthly occupancy rates, peak dorm demand, and revenue.<br>
          • <strong>Fraud Alerts:</strong> Detects repetitive failed logins or rejected receipts.
        </div>
      </div>
    </div>
  </div>

  <div class="diagram-container" style="margin-top: 4px;">
    <div class="diagram-title">
      <span>Deterministic Engine Fallback Architecture (Zero-Downtime Guarantee)</span>
      <span class="badge-pill">High Availability</span>
    </div>

    <div class="arch-grid">
      <div class="arch-col" style="width: 50%;">
        <div class="card-box purple">
          <div class="card-header">
            <span>CLOUD LLM (PRIMARY ENGINE)</span>
            <span class="badge badge-ai">Cloud API</span>
          </div>
          <div class="card-body">
            • <strong>Model:</strong> Meta-Llama 3.1 70B via NVIDIA NIM or Gemini 1.5 Flash.<br>
            • <strong>Features:</strong> Full conversational nuance, bilingual Taglish comprehension, multi-turn memory.<br>
            • <strong>Requirement:</strong> Valid API key and outbound internet access.
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 50%;">
        <div class="card-box gold">
          <div class="card-header">
            <span>LOCAL SQL RECOMMENDER (FALLBACK)</span>
            <span class="badge badge-gold">Local PHP/SQL</span>
          </div>
          <div class="card-body">
            • <strong>Trigger:</strong> Cloud API timeout (> 4000ms), 5xx error, or offline environment.<br>
            • <strong>Mechanism:</strong> Pure regex tokenization + parameterized SQL queries.<br>
            • <strong>Output:</strong> Returns exact matching rooms without any external dependencies.
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="callout-box info">
    <strong>Security Guardrail (Prompt Shield):</strong> All incoming AI messages are passed through a sanitation layer that strips SQL meta-characters, HTML scripts, and system prompt override attempts (e.g., "Ignore previous instructions"). The system prompt strictly limits Marvi's scope to Marvelous Hotel hospitality topics.
  </div>
</div>

<!-- ========================================================================= -->
<!-- PAGE 8: PAYMENT SETTLEMENT & SYNCHRONIZED DUAL-APPROVAL ENGINE            -->
<!-- ========================================================================= -->
<div class="page-container last-page">
  <h1><span class="section-tag">Section 7</span> GCash/Maya Payment Settlement &amp; Synchronized Dual-Approval Engine</h1>
  <div class="intro-text">
    Payment clearances for guest reservations adhere to a verified manual e-wallet clearing model (Maya &amp; GCash). To maintain absolute data consistency between the Frontdesk Staff and Super Admin, Marvelous Hotel features a <strong>Synchronized Dual-Approval Engine</strong> with atomic concurrency locking.
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 12%;">Stage</th>
        <th style="width: 15%;">Actor &amp; Route</th>
        <th style="width: 35%;">Action &amp; Technical Execution</th>
        <th style="width: 38%;">Database Mutation &amp; Security Validation</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>1. KYC Gate</strong></td>
        <td>Guest<br><code>user/profile.php</code></td>
        <td>Upload government ID (Passport, UMID, Driver's License) to <code>uploads/ids/</code> via file proxy.</td>
        <td>Checks <code>profile_verified = 1</code>. Blocks payment portal until ID is submitted.</td>
      </tr>
      <tr>
        <td><strong>2. Channel Pick</strong></td>
        <td>Guest<br><code>user/payment.php</code></td>
        <td>Selects Maya (<code>@willygailojr</code>) or GCash. Dynamic QR card and instructions render.</td>
        <td>Retrieves reservation total from <code>bookings</code>; computes exact PHP amount.</td>
      </tr>
      <tr>
        <td><strong>3. Proof Upload</strong></td>
        <td>Guest<br><code>user/payment.php</code></td>
        <td>Enters 13-digit Reference #, Amount Paid, and uploads JPG/PNG proof screenshot.</td>
        <td>Stores screenshot to <code>uploads/proofs/</code>. Inserts into <code>payments</code> (<code>status = 'pending'</code>).</td>
      </tr>
      <tr>
        <td><strong>4. Dual Audit</strong></td>
        <td>Admin or Staff<br><code>admin/payments.php</code><br><code>staff/payments.php</code></td>
        <td>Auditor examines receipt image in lightbox, cross-checks reference number with official wallet ledger.</td>
        <td><strong>Atomic Concurrency Lock:</strong> Halts duplicate clicks if already verified by another actor.</td>
      </tr>
      <tr>
        <td><strong>5. Approval</strong></td>
        <td>System Engine<br>Database Execution</td>
        <td>Updates <code>payments.status = 'verified'</code>, sets <code>bookings.status = 'confirmed'</code>, stamps <code>accepted_by</code>.</td>
        <td>Dispatches confirmation notification to guest; generates official reservation PDF voucher.</td>
      </tr>
    </tbody>
  </table>

  <div class="diagram-container">
    <div class="diagram-title">
      <span>Synchronized Dual-Approval Engine Flowchart</span>
      <span class="badge-pill">Real-Time Concurrency Guard</span>
    </div>

    <div class="arch-grid">
      <div class="arch-col" style="width: 48%;">
        <div class="card-box danger">
          <div class="card-header">
            <span>BRANCH A: SUPER ADMIN ON-SITE</span>
            <span class="badge badge-admin">admin/payments.php</span>
          </div>
          <div class="card-body">
            1. Super Admin clicks <strong>'Approve Payment'</strong>.<br>
            2. System stamps <code>accepted_by = admin_id</code>.<br>
            3. Sets <code>payments.status = 'verified'</code> and <code>bookings.status = 'confirmed'</code>.<br>
            4. <strong>Live Auto-Sync:</strong> Staff portal immediately displays gold badge:<br>
            <div style="margin-top:3px;"><span class="badge badge-gold" style="font-size:7pt;">👑 Super Admin: {Admin Name}</span></div>
          </div>
        </div>
      </div>
      <div class="arch-col" style="width: 4%;">
        <div style="text-align:center; padding-top:25px; font-weight:bold; color:#64748b; font-size:9pt;">OR</div>
      </div>
      <div class="arch-col" style="width: 48%;">
        <div class="card-box warning">
          <div class="card-header">
            <span>BRANCH B: FRONTDESK STAFF (DELEGATED)</span>
            <span class="badge badge-staff">staff/payments.php</span>
          </div>
          <div class="card-body">
            1. Frontdesk Staff clicks <strong>'Approve Payment'</strong>.<br>
            2. System stamps <code>accepted_by = staff_id</code>.<br>
            3. Sets <code>payments.status = 'verified'</code> and <code>bookings.status = 'confirmed'</code>.<br>
            4. <strong>Live Auto-Sync:</strong> Admin portal immediately displays blue badge:<br>
            <div style="margin-top:3px;"><span class="badge badge-staff" style="font-size:7pt;">👤 Frontdesk Staff: {Staff Name}</span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="step-node gold" style="margin-top: 5px;">
      <strong>Atomic Concurrency Lock (Race-Condition Shield):</strong><br>
      Both <code>admin/payments.php</code> and <code>staff/payments.php</code> execute an atomic verification check prior to executing database updates. If an admin and staff click approve simultaneously, the first committed transaction succeeds, while the second gracefully detects <code>status === 'verified'</code> and flashes an informational message: <em>"Payment was already verified by [Actor Role: Name]."</em>
    </div>

    <div class="step-node green" style="margin-top: 4px;">
      <strong>Arrivals Desk &amp; Guest Voucher Verification:</strong><br>
      When the guest arrives at the hotel, frontdesk officers open <code>staff/check-in.php</code>. The system displays the exact verifier badge and timestamp (e.g., <code>👑 Super Admin: Ian</code> or <code>👤 Frontdesk Staff: Willy</code>). This transparent audit trail eliminates disputed payments and provides instant proof of financial clearance.
    </div>
  </div>

  <div class="callout-box success">
    <strong>Complete Architectural Integrity:</strong> From initial guest inquiry to check-out sanitation, the Marvelous Hotel system enforces absolute data consistency, role isolation, and financial compliance.
  </div>
</div>

</body>
</html>
"""

def generate_pdf():
    print("Building HTML content for Marvelous Hotel System Flows...")
    html_str = build_html()
    
    target_paths = [
        "/opt/lampp/htdocs/hotel/marvelous_hotel_system_flows.pdf",
        "/opt/lampp/htdocs/hotel/docs/marvelous_hotel_system_flows.pdf"
    ]
    
    print(f"Compiling PDF via WeasyPrint {weasyprint.__version__}...")
    doc = weasyprint.HTML(string=html_str)
    
    for path in target_paths:
        os.makedirs(os.path.dirname(path), exist_ok=True)
        print(f"Writing output to: {path}")
        doc.write_pdf(path)
        print(f"Successfully generated {path} ({os.path.getsize(path):,} bytes)")

if __name__ == "__main__":
    generate_pdf()

