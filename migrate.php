<?php
// ============================================================
// MARVELOUS HOTEL — Migration Runner (CLI)
// Gamit:
//   php migrate.php            → ilapat ang mga bagong migration
//   php migrate.php --status   → i-list ang applied/pending
//
// Bawat .sql file sa migrations/ ay nilalabasan nang isang beses
// lang (naka-track sa schema_migrations table).
// ============================================================

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die('CLI only: php migrate.php' . "\n");
}

// Ang CLI PHP ay hindi gumagamit ng XAMPP MySQL socket — gamitin ang TCP.
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

dbExec("CREATE TABLE IF NOT EXISTS schema_migrations (
    migration  VARCHAR(191) NOT NULL PRIMARY KEY,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)");

$applied = array_column(dbRows("SELECT migration FROM schema_migrations"), 'migration');
$files = glob(__DIR__ . '/migrations/*.sql') ?: [];
sort($files);

$onlyStatus = in_array('--status', $argv ?? [], true);
$pending = 0;

foreach ($files as $file) {
    $name = basename($file);
    $done = in_array($name, $applied, true);

    if ($onlyStatus) {
        echo ($done ? '[applied]  ' : '[pending]  ') . $name . "\n";
        if (!$done) $pending++;
        continue;
    }

    if ($done) {
        echo "[skip]     $name\n";
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "[error]    Cannot read: $name\n");
        exit(1);
    }

    // Alisin ang mga comment lines, paghiwalayin kada statement.
    $lines = array_filter(explode("\n", $sql), function ($line) {
        return !preg_match('/^\s*--/', $line);
    });
    $statements = array_filter(array_map('trim', explode(';', implode("\n", $lines))), function ($s) {
        return $s !== '';
    });

    foreach ($statements as $statement) {
        try {
            dbExec($statement);
        } catch (Throwable $e) {
            fwrite(STDERR, "[error]    $name: " . $e->getMessage() . "\n");
            exit(1);
        }
    }

    dbExec("INSERT INTO schema_migrations (migration) VALUES (?)", [$name]);
    echo "[applied]  $name\n";
}

if ($onlyStatus) {
    echo $pending === 0 ? "Lahat ay applied na.\n" : "$pending pending migration(s).\n";
} else {
    echo "Migration run tapos na.\n";
}
