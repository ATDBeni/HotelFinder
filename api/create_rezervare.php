<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodă invalidă.']); exit;
}

$hotel_id   = intval($_POST['hotel_id']   ?? 0);
$checkin    = trim($_POST['checkin']      ?? '');
$checkout   = trim($_POST['checkout']     ?? '');
$guests     = intval($_POST['guests']     ?? 1);
$children   = intval($_POST['children']   ?? 0);
$room_type  = trim($_POST['room_type']    ?? 'standard');
$first_name = trim($_POST['first_name']  ?? '');
$last_name  = trim($_POST['last_name']   ?? '');
$email      = trim($_POST['email']       ?? '');
$phone      = trim($_POST['phone']       ?? '');
$requests   = trim($_POST['requests']    ?? '');
$services   = trim($_POST['services']    ?? '');
$total      = floatval($_POST['total_price'] ?? 0);
$nights     = intval($_POST['nights']    ?? 0);
$user_id    = $_SESSION['user_id'] ?? null;

// Validare
if (!$hotel_id || !$checkin || !$checkout || !$first_name || !$last_name || !$email) {
    echo json_encode(['success' => false, 'message' => 'Date incomplete.']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email invalid.']); exit;
}
if (strtotime($checkout) <= strtotime($checkin)) {
    echo json_encode(['success' => false, 'message' => 'Datele de sejur sunt invalide.']); exit;
}

// Verificare disponibilitate
try {
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM rezervari
        WHERE hotel_id = ?
          AND status IN ('confirmed','pending')
          AND NOT (checkout_date <= ? OR checkin_date >= ?)
    ");
    $check->execute([$hotel_id, $checkin, $checkout]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Hotelul nu este disponibil în perioada selectată.']); exit;
    }

    // Generare cod unic rezervare
    $code = 'HF-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

    $stmt = $pdo->prepare("
        INSERT INTO rezervari
            (hotel_id, user_id, first_name, last_name, email, phone,
             checkin_date, checkout_date, guests, children,
             room_type, services, special_requests,
             total_price, nights, status, booking_code, created_at)
        VALUES
            (?, ?, ?, ?, ?, ?,
             ?, ?, ?, ?,
             ?, ?, ?,
             ?, ?, 'pending', ?, NOW())
    ");
    $stmt->execute([
        $hotel_id, $user_id, $first_name, $last_name, $email, $phone,
        $checkin, $checkout, $guests, $children,
        $room_type, $services, $requests,
        $total, $nights, $code
    ]);

    $id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'id' => $id, 'code' => $code]);

} catch (PDOException $e) {
    // Dacă tabelul nu are toate coloanele, facem fallback
    try {
        $code  = 'HF-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        $stmt2 = $pdo->prepare("
            INSERT INTO rezervari (hotel_id, user_id, first_name, last_name, email, phone, checkin_date, checkout_date, guests, total_price, status, booking_code, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
        ");
        $stmt2->execute([$hotel_id, $user_id, $first_name, $last_name, $email, $phone, $checkin, $checkout, $guests, $total, $code]);
        $id = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'id' => $id, 'code' => $code]);
    } catch (PDOException $e2) {
        echo json_encode(['success' => false, 'message' => 'Eroare server: ' . $e2->getMessage()]);
    }
}