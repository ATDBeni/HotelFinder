<?php
header('Content-Type: application/json');
require_once '../db.php';

$hotel_id = intval($_GET['hotel_id'] ?? 0);
if (!$hotel_id) { echo json_encode(['blocked' => []]); exit; }

try {
    // Blocked from availability table
    $stmt = $pdo->prepare('SELECT blocked_date FROM availability WHERE hotel_id = ? AND blocked_date >= CURDATE()');
    $stmt->execute([$hotel_id]);
    $blocked = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Also block dates from confirmed reservations
    $stmt2 = $pdo->prepare("
        SELECT checkin_date, checkout_date FROM rezervari
        WHERE hotel_id = ? AND status IN ('confirmed','pending') AND checkout_date >= CURDATE()
    ");
    $stmt2->execute([$hotel_id]);
    $reservations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($reservations as $rez) {
        $start = new DateTime($rez['checkin_date']);
        $end   = new DateTime($rez['checkout_date']);
        for ($d = clone $start; $d < $end; $d->modify('+1 day')) {
            $blocked[] = $d->format('Y-m-d');
        }
    }

    echo json_encode(['blocked' => array_unique(array_values($blocked))]);
} catch (PDOException $e) {
    echo json_encode(['blocked' => []]);
}