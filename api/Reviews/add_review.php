<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodă invalidă.']); exit;
}
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Trebuie să fii autentificat.']); exit;
}
if (($_SESSION['role'] ?? '') !== 'client') {
    echo json_encode(['success' => false, 'message' => 'Doar clienții pot lăsa recenzii.']); exit;
}

$hotel_id = intval($_POST['hotel_id'] ?? 0);
$rating   = intval($_POST['rating']   ?? 0);
$comment  = trim($_POST['comment']    ?? '');
$user_id  = $_SESSION['user_id'];

if (!$hotel_id || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Date invalide.']); exit;
}

try {
    // Check if user already reviewed this hotel
    $check = $pdo->prepare('SELECT id FROM reviews WHERE hotel_id = ? AND user_id = ?');
    $check->execute([$hotel_id, $user_id]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ai lăsat deja o recenzie pentru acest hotel.']); exit;
    }

    $stmt = $pdo->prepare('INSERT INTO reviews (hotel_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$hotel_id, $user_id, $rating, $comment ?: null]);

    // Update hotel avg_rating and review_count cache
    $upd = $pdo->prepare('
        UPDATE hotels SET
            avg_rating   = (SELECT ROUND(AVG(rating),2) FROM reviews WHERE hotel_id = ?),
            review_count = (SELECT COUNT(*) FROM reviews WHERE hotel_id = ?)
        WHERE id = ?
    ');
    $upd->execute([$hotel_id, $hotel_id, $hotel_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare de server.']);
}