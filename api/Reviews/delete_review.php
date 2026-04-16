<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Neautorizat.']);
    exit;
}

$id       = intval($_POST['id']       ?? 0);
$hotel_id = intval($_POST['hotel_id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID invalid.']);
    exit;
}

try {
    // Dacă nu s-a trimis hotel_id, îl luăm din DB înainte de ștergere
    if (!$hotel_id) {
        $stmt = $pdo->prepare('SELECT hotel_id FROM reviews WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        $hotel_id = $row ? intval($row['hotel_id']) : 0;
    }

    $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);

    // Recalculează avg_rating și review_count pe hotel
    if ($hotel_id) {
        $pdo->prepare('
            UPDATE hotels SET
                avg_rating   = (SELECT ROUND(AVG(rating), 2) FROM reviews WHERE hotel_id = ?),
                review_count = (SELECT COUNT(*) FROM reviews WHERE hotel_id = ?)
            WHERE id = ?
        ')->execute([$hotel_id, $hotel_id, $hotel_id]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare server: ' . $e->getMessage()]);
}