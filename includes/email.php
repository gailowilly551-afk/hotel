<?php
// ============================================================
// MARVELOUS HOTEL — Email Utility
// includes/email.php
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Send email using PHP mail() or SMTP via settings table.
 * For production, configure SMTP in settings table.
 */
function sendEmail(string $to, string $subject, string $htmlBody, string $textBody = ''): bool {
    $fromEmail = getSetting('mail_from_email', 'noreply@' . parse_url(SITE_URL, PHP_URL_HOST));
    $fromName  = getSetting('mail_from_name', SITE_NAME);
    $mailer    = getSetting('mail_driver', 'mail'); // 'mail' or 'smtp'

    $headers = [
        "From: {$fromName} <{$fromEmail}>",
        "Reply-To: {$fromEmail}",
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
    ];

    if ($mailer === 'smtp') {
        return sendEmailSMTP($to, $subject, $htmlBody, $textBody, $fromEmail, $fromName);
    }

    // Use PHP mail()
    $headerStr = implode("\r\n", $headers);
    return @mail($to, $subject, $htmlBody, $headerStr);
}

/**
 * Send email via SMTP using settings from database.
 */
function sendEmailSMTP(string $to, string $subject, string $htmlBody, string $textBody, string $fromEmail, string $fromName): bool {
    $host     = getSetting('smtp_host', '');
    $port     = (int) getSetting('smtp_port', 587);
    $username = getSetting('smtp_username', '');
    $password = getSetting('smtp_password', '');
    $encryption = getSetting('smtp_encryption', 'tls'); // tls, ssl, or none

    if (!$host || !$username || !$password) {
        error_log('SMTP settings incomplete; falling back to mail()');
        return sendEmail($to, $subject, $htmlBody, $textBody);
    }

    // Try to use PHPMailer if available, else fallback to mail()
    $phpmailerPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    if (!file_exists($phpmailerPath)) {
        error_log('PHPMailer not installed; falling back to mail()');
        return sendEmail($to, $subject, $htmlBody, $textBody);
    }

    require_once $phpmailerPath;
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->SMTPSecure = $encryption === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $port;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('SMTP Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Generate password reset email HTML.
 */
function emailTemplateResetPassword(string $resetLink, string $userName, int $expiryHours = 1): string {
    $siteName = SITE_NAME;
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Password Reset - {$siteName}</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#f4f4f4;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
    <tr>
      <td style="background:linear-gradient(135deg,#c9a84c,#b8943f);padding:30px;text-align:center;">
        <h1 style="margin:0;color:#0f172a;font-family:Georgia,serif;font-size:24px;font-weight:700;">{$siteName}</h1>
        <p style="margin:8px 0 0;color:#1e293b;font-size:12px;text-transform:uppercase;letter-spacing:1px;">& Dormitory</p>
      </td>
    </tr>
    <tr>
      <td style="padding:30px;">
        <h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Password Reset Request</h2>
        <p style="margin:0 0 16px;color:#475569;line-height:1.6;">Hello <strong>{$userName}</strong>,</p>
        <p style="margin:0 0 16px;color:#475569;line-height:1.6;">We received a request to reset your password. Click the button below to create a new password:</p>
        <div style="text-align:center;margin:24px 0;">
          <a href="{$resetLink}" style="display:inline-block;background:linear-gradient(135deg,#c9a84c,#b8943f);color:#0f172a;padding:14px 28px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;box-shadow:0 4px 12px rgba(201,168,76,0.3);">Reset My Password</a>
        </div>
        <p style="margin:0 0 8px;color:#64748b;font-size:13px;line-height:1.6;">This link expires in <strong>{$expiryHours} hour</strong> and can only be used once.</p>
        <p style="margin:0 0 8px;color:#64748b;font-size:13px;line-height:1.6;">If you didn't request this, please ignore this email or contact support.</p>
        <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">
        <p style="margin:0;color:#94a3b8;font-size:12px;">If the button doesn't work, copy this link:<br><a href="{$resetLink}" style="color:#c9a84c;word-break:break-all;">{$resetLink}</a></p>
      </td>
    </tr>
    <tr>
      <td style="background:#f8fafc;padding:16px 30px;text-align:center;border-top:1px solid #e2e8f0;">
        <p style="margin:0;color:#94a3b8;font-size:11px;">{$siteName} | <a href="{SITE_URL}" style="color:#c9a84c;">{$siteName}</a></p>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}

/**
 * Generate email verification email HTML.
 */
function emailTemplateVerifyEmail(string $verifyLink, string $userName): string {
    $siteName = SITE_NAME;
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Email - {$siteName}</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#f4f4f4;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
    <tr>
      <td style="background:linear-gradient(135deg,#c9a84c,#b8943f);padding:30px;text-align:center;">
        <h1 style="margin:0;color:#0f172a;font-family:Georgia,serif;font-size:24px;font-weight:700;">{$siteName}</h1>
        <p style="margin:8px 0 0;color:#1e293b;font-size:12px;text-transform:uppercase;letter-spacing:1px;">& Dormitory</p>
      </td>
    </tr>
    <tr>
      <td style="padding:30px;">
        <h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Verify Your Email Address</h2>
        <p style="margin:0 0 16px;color:#475569;line-height:1.6;">Welcome, <strong>{$userName}</strong>!</p>
        <p style="margin:0 0 16px;color:#475569;line-height:1.6;">Thank you for registering. Please verify your email address by clicking the button below:</p>
        <div style="text-align:center;margin:24px 0;">
          <a href="{$verifyLink}" style="display:inline-block;background:linear-gradient(135deg,#c9a84c,#b8943f);color:#0f172a;padding:14px 28px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;box-shadow:0 4px 12px rgba(201,168,76,0.3);">Verify Email</a>
        </div>
        <p style="margin:0 0 8px;color:#64748b;font-size:13px;line-height:1.6;">If you didn't create this account, please ignore this email.</p>
        <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">
        <p style="margin:0;color:#94a3b8;font-size:12px;">If the button doesn't work, copy this link:<br><a href="{$verifyLink}" style="color:#c9a84c;word-break:break-all;">{$verifyLink}</a></p>
      </td>
    </tr>
    <tr>
      <td style="background:#f8fafc;padding:16px 30px;text-align:center;border-top:1px solid #e2e8f0;">
        <p style="margin:0;color:#94a3b8;font-size:11px;">{$siteName} | <a href="{SITE_URL}" style="color:#c9a84c;">{$siteName}</a></p>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}

/**
 * Generate generic notification email HTML.
 */
function emailTemplateNotification(string $title, string $message, string $userName, string $actionUrl = '', string $actionText = ''): string {
    $siteName = SITE_NAME;
    $btnHtml = $actionUrl && $actionText ? <<<BTN
    <div style="text-align:center;margin:24px 0;">
      <a href="{$actionUrl}" style="display:inline-block;background:linear-gradient(135deg,#c9a84c,#b8943f);color:#0f172a;padding:14px 28px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;">{$actionText}</a>
    </div>
BTN : '';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$title} - {$siteName}</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#f4f4f4;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
    <tr>
      <td style="background:linear-gradient(135deg,#c9a84c,#b8943f);padding:30px;text-align:center;">
        <h1 style="margin:0;color:#0f172a;font-family:Georgia,serif;font-size:24px;font-weight:700;">{$siteName}</h1>
      </td>
    </tr>
    <tr>
      <td style="padding:30px;">
        <h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">{$title}</h2>
        <p style="margin:0 0 16px;color:#475569;line-height:1.6;">Hello <strong>{$userName}</strong>,</p>
        <p style="margin:0 0 16px;color:#475569;line-height:1.6;">{$message}</p>
        {$btnHtml}
      </td>
    </tr>
    <tr>
      <td style="background:#f8fafc;padding:16px 30px;text-align:center;border-top:1px solid #e2e8f0;">
        <p style="margin:0;color:#94a3b8;font-size:11px;">{$siteName}</p>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}