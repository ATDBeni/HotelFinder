<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Neautorizat.']); exit;
}

$current = $_POST['current'] ?? '';
$new     = $_POST['new']     ?? '';

if (!$current || strlen($new) < 8) {
    echo json_encode(['success'=>false,'message'=>'Date invalide.']); exit;
}

try {
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password'])) {
        echo json_encode(['success'=>false,'message'=>'Parola actuală este incorectă.']); exit;
    }

    $pdo->prepare('UPDATE users SET password=? WHERE id=?')
        ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['user_id']]);

    echo json_encode(['success'=>true]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Eroare server.']);
}