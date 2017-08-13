<?php
/**
 * This file should be added as a cron job and be set to at least the
 * smallest interval in which an paste can expire.
 * As default, this is 24 hours. If you don't run the script often
 * enough, a paste might exist longer than expected.
 * Preferably, run often (every hour).
 */


/* Should have a better way to set default timezones than constant. If filename changes
this script breaks. Maybe .env file. */
include_once __DIR__ . '/../app/Controllers/PasteController.php';

// Connect to database
try {
    $pdo = new PDO("sqlite:" . __DIR__ . '/../database/database.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    print('Could not connect to database. ');
    exit(1);
}
$stmt = $pdo->prepare('SELECT * from pastebox');
$stmt->execute();

$result = $stmt->fetchAll();

foreach ($result as $paste) {
    if (!isset($paste['expire_date'])) {
        /* Pastes with expire 'never' will have no expire_date */
        continue;
    }
    $dateNow = new DateTime('now', new DateTimeZone(\App\Controllers\PasteController::TIMEZONE));
    $expireDate = new DateTime($paste['expire_date'], new DateTimeZone(\App\Controllers\PasteController::TIMEZONE));
    $diff = $dateNow->diff($expireDate);

    /* The invert property is 1 when the difference is a negative amount
      (the invert property is 0 if $expireDate is after $dateNow) */
    if ($diff->invert === 1) {
        /* Delete both pastes and pastebox */
        try {
            $pdo->exec('DELETE FROM pastes WHERE id = ' . $paste['id']);
            $pdo->exec('DELETE FROM pastebox WHERE id = ' . $paste['id']);
        } catch (PDOException $e) {
            print('Could not delete pastes. ');
            exit(1);
        }
    }
}
