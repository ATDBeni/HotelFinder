<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(['rezervari'=>[],'stats'=>[]]); exit; }
$uid = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT r.*, h.name AS hotel_name, h.image_url, h.location,
               (SELECT COUNT(*) FROM reviews rv WHERE rv.hotel_id = r.hotel_id AND rv.user_id = r.user_id) AS has_review
        FROM rezervari r
        LEFT JOIN hotels h ON h.id = r.hotel_id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$uid]);
    $rezervari = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fix image_url to first image if JSON array
    foreach ($rezervari as &$r) {
        if ($r['image_url']) {
            $dec = json_decode($r['image_url'], true);
            if (is_array($dec)) $r['image_url'] = $dec[0] ?? null;
        }
    }

    // Stats
    $stats = [
        'total'   => count($rezervari),
        'pending' => count(array_filter($rezervari, fn($r) => in_array($r['status'], ['pending','confirmed']))),
        'nights'  => array_sum(array_column($rezervari, 'nights')),
    ];
    $revStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ?");
    $revStmt->execute([$uid]);
    $stats['reviews'] = $revStmt->fetchColumn();

    echo json_encode(['rezervari' => $rezervari, 'stats' => $stats]);
} catch (PDOException $e) {
    echo json_encode(['rezervari' => [], 'stats' => [], 'error' => $e->getMessage()]);
}