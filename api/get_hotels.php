<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $stmt = $pdo->query("
        SELECT h.*,
               COALESCE(AVG(r.rating), NULL) AS avg_rating,
               COUNT(r.id) AS review_count
        FROM hotels h
        LEFT JOIN reviews r ON r.hotel_id = h.id
        GROUP BY h.id
    ");

    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($hotels);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Eroare server']);
}