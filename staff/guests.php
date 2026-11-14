<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('staff');

// User account management is strictly restricted to Super Admin.
setFlash('warning', 'User account management is restricted to Super Admin. Use Check-in & Check-out for operational arrivals.');
header("Location: dashboard.php");
exit;
