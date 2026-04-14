<?php
header('Content-Type: application/json');
require_once 'db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['error' => 'ID invalid']); exit; }

try {
    $stmt = $pdo->prepare("
        SELECT h.*,
               COALESCE(AVG(r.rating), NULL) AS avg_rating,
               COUNT(r.id) AS review_count
        FROM hotels h
        LEFT JOIN reviews r ON r.hotel_id = h.id
        WHERE h.id = ?
        GROUP BY h.id
    ");
    $stmt->execute([$id]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hotel) { echo json_encode(['error' => 'Hotel negăsit']); exit; }

    // Fetch images (din coloana image_url sau JSON)
    $images = [];
    if (!empty($hotel['image_url'])) {
        $decoded = json_decode($hotel['image_url'], true);
        if (is_array($decoded)) $images = $decoded;
        else $images = [$hotel['image_url']];
    }
    $hotel['images'] = $images;

    echo json_encode($hotel);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Eroare server']);
}