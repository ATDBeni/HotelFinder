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
    // Protecție: nu poți șterge alt admin
    $check = $pdo->prepare('SELECT role FROM users WHERE id = ?');
    $check->execute([$id]);
    $user = $check->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Utilizatorul nu există.']);
        exit;
    }
    if ($user['role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Nu poți șterge un cont de administrator.']);
        exit;
    }

    $pdo->prepare('DELETE FROM users WHERE id = ? AND role = "client"')->execute([$id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare server: ' . $e->getMessage()]);
}