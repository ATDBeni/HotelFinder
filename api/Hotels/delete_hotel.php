<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Neautorizat.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID invalid.']);
    exit;
}

try {
    // Șterge hotelul — reviews și availability se șterg automat prin FK CASCADE
    // dacă nu ai CASCADE setat, le ștergem manual
    $pdo->prepare('DELETE FROM reviews      WHERE hotel_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM availability WHERE hotel_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM hotels       WHERE id = ?')->execute([$id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare server: ' . $e->getMessage()]);
}