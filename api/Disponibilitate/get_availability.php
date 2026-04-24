<?php
header('Content-Type: application/json');
require_once '../db.php';

$hotel_id = intval($_GET['hotel_id'] ?? 0);
if (!$hotel_id) { echo json_encode(['blocked' => [], 'free_rooms' => 0, 'total_rooms' => 0]); exit; }

try {
    // Ia total camere
    $hotelStmt = $pdo->prepare("SELECT total_rooms FROM hotels WHERE id = ?");
    $hotelStmt->execute([$hotel_id]);
    $hotel      = $hotelStmt->fetch(PDO::FETCH_ASSOC);
    $totalRooms = intval($hotel['total_rooms'] ?? 1);

    // Zile blocate manual
    $stmt = $pdo->prepare("SELECT blocked_date FROM availability WHERE hotel_id = ? AND blocked_date >= CURDATE()");
    $stmt->execute([$hotel_id]);
    $manualBlocked = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Rezervari active viitoare
    $stmt2 = $pdo->prepare("
        SELECT checkin_date, checkout_date FROM rezervari
        WHERE hotel_id = ?
          AND status IN ('confirmed', 'pending')
          AND checkout_date >= CURDATE()
    ");
    $stmt2->execute([$hotel_id]);
    $reservations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Construieste mapa: data => numar rezervari active
    $dateCount = [];
    foreach ($reservations as $rez) {
        $start = new DateTime($rez['checkin_date']);
        $end   = new DateTime($rez['checkout_date']);
        for ($d = clone $start; $d < $end; $d->modify('+1 day')) {
            $key = $d->format('Y-m-d');
            $dateCount[$key] = ($dateCount[$key] ?? 0) + 1;
        }
    }

    // O data e blocata daca: e in manual blocked SAU toate camerele sunt ocupate
    $fullyBlocked = [];
    foreach ($dateCount as $date => $count) {
        if ($count >= $totalRooms) {
            $fullyBlocked[] = $date;
        }
    }

    $blocked = array_values(array_unique(array_merge($manualBlocked, $fullyBlocked)));

    echo json_encode([
        'blocked'     => $blocked,
        'total_rooms' => $totalRooms,
        'date_counts' => $dateCount  // util pentru frontend sa arate "X camere libere"
    ]);

} catch (PDOException $e) {
    echo json_encode(['blocked' => [], 'total_rooms' => 0, 'error' => $e->getMessage()]);
}