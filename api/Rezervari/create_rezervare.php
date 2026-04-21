<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metoda invalida.']); exit;
}

// Daca nu e logat, refuza rezervarea
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Trebuie sa fii autentificat pentru a face o rezervare.',
        'redirect' => 'login.html'
    ]);
    exit;
}

$hotel_id   = intval($_POST['hotel_id']   ?? 0);
$checkin    = trim($_POST['checkin']      ?? '');
$checkout   = trim($_POST['checkout']     ?? '');
$guests     = intval($_POST['guests']     ?? 1);
$children   = intval($_POST['children']   ?? 0);
$room_type  = trim($_POST['room_type']    ?? 'standard');
$first_name = trim($_POST['first_name']   ?? '');
$last_name  = trim($_POST['last_name']    ?? '');
$email      = trim($_POST['email']        ?? '');
$phone      = trim($_POST['phone']        ?? '');
$requests   = trim($_POST['requests']     ?? '');
$services   = trim($_POST['services']     ?? '');
$total      = floatval($_POST['total_price'] ?? 0);
$nights     = intval($_POST['nights']     ?? 0);
$user_id    = $_SESSION['user_id']; // intotdeauna din sesiune

if (!$hotel_id || !$checkin || !$checkout || !$first_name || !$last_name || !$email) {
    echo json_encode(['success' => false, 'message' => 'Date incomplete.']); exit;
}

if (strtotime($checkout) <= strtotime($checkin)) {
    echo json_encode(['success' => false, 'message' => 'Datele de sejur sunt invalide.']); exit;
}

try {
    // Verifica disponibilitate
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM rezervari
        WHERE hotel_id = ?
          AND status IN ('confirmed','pending')
          AND NOT (checkout_date <= ? OR checkin_date >= ?)
    ");
    $check->execute([$hotel_id, $checkin, $checkout]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Hotelul nu este disponibil in perioada selectata.']); exit;
    }

    $code = 'HF-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    $cols = $pdo->query("SHOW COLUMNS FROM rezervari")->fetchAll(PDO::FETCH_COLUMN);

    $sql    = "INSERT INTO rezervari (hotel_id, user_id, first_name, last_name, email, phone, checkin_date, checkout_date, guests, total_price, booking_code, status, created_at";
    $vals   = "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW()";
    $params = [$hotel_id, $user_id, $first_name, $last_name, $email, $phone, $checkin, $checkout, $guests, $total, $code];

    if (in_array('children', $cols))         { $sql .= ', children';         $vals .= ', ?'; $params[] = $children; }
    if (in_array('room_type', $cols))        { $sql .= ', room_type';        $vals .= ', ?'; $params[] = $room_type; }
    if (in_array('services', $cols))         { $sql .= ', services';         $vals .= ', ?'; $params[] = $services; }
    if (in_array('special_requests', $cols)) { $sql .= ', special_requests'; $vals .= ', ?'; $params[] = $requests; }
    if (in_array('nights', $cols))           { $sql .= ', nights';           $vals .= ', ?'; $params[] = $nights; }

    $pdo->prepare("$sql) $vals)")->execute($params);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'code' => $code]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}