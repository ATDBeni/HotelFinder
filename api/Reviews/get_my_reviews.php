<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(['reviews'=>[]]); exit; }

try {
    $stmt = $pdo->prepare("
        SELECT rv.*, h.name AS hotel_name, h.image_url
        FROM reviews rv
        JOIN hotels h ON h.id = rv.hotel_id
        WHERE rv.user_id = ?
        ORDER BY rv.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($reviews as &$r) {
        if ($r['image_url']) {
            $dec = json_decode($r['image_url'], true);
            if (is_array($dec)) $r['image_url'] = $dec[0] ?? null;
        }
    }

    echo json_encode(['reviews' => $reviews]);
} catch (PDOException $e) {
    echo json_encode(['reviews' => []]);
}