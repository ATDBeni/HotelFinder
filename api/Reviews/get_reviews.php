<?php
header('Content-Type: application/json');
require_once '../db.php';

$hotel_id = intval($_GET['hotel_id'] ?? 0);
if (!$hotel_id) { echo json_encode(['reviews' => [], 'stats' => []]); exit; }

try {
    // Reviews cu user info
    $stmt = $pdo->prepare("
        SELECT r.*, u.first_name, u.last_name
        FROM reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.hotel_id = ?
        ORDER BY r.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$hotel_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $stmt2 = $pdo->prepare("
        SELECT
            COUNT(*)                                  AS total,
            ROUND(AVG(rating), 2)                     AS avg,
            SUM(rating = 5)                           AS star_5,
            SUM(rating = 4)                           AS star_4,
            SUM(rating = 3)                           AS star_3,
            SUM(rating = 2)                           AS star_2,
            SUM(rating = 1)                           AS star_1
        FROM reviews WHERE hotel_id = ?
    ");
    $stmt2->execute([$hotel_id]);
    $stats = $stmt2->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['reviews' => $reviews, 'stats' => $stats]);
} catch (PDOException $e) {
    echo json_encode(['reviews' => [], 'stats' => [], 'error' => 'Eroare server']);
}