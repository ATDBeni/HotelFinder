<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Neautorizat.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodă invalidă.']);
    exit;
}

$hotel_id = intval(trim($_POST['hotel_id'] ?? 0));
$date     = trim($_POST['date']            ?? '');
$reason   = trim($_POST['reason']          ?? '');

if (!$hotel_id || !$date) {
    echo json_encode(['success' => false, 'message' => 'Selectează hotelul și data.']);
    exit;
}

// Validare format dată
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Format dată invalid.']);
    exit;
}

try {
    $pdo->prepare('
        INSERT IGNORE INTO availability (hotel_id, blocked_date, reason)
        VALUES (?, ?, ?)
    ')->execute([$hotel_id, $date, $reason ?: null]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare server: ' . $e->getMessage()]);
}