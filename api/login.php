<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodă invalidă.']);
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';
$role     = $_POST['role']          ?? 'client';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Completează toate câmpurile.']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Email sau parolă incorectă.']);
        exit;
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email']= $user['email'];
    $_SESSION['role']      = $user['role'];

    // Actualizare last_login
    $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

    echo json_encode([
        'success' => true,
        'role'    => $user['role'],
        'name'    => $user['first_name']
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare de server.']);
}