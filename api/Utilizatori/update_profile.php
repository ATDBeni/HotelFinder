<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Neautorizat.']); exit;
}

$fn    = trim($_POST['first_name'] ?? '');
$ln    = trim($_POST['last_name']  ?? '');
$email = trim($_POST['email']      ?? '');
$phone = trim($_POST['phone']      ?? '');

if (!$fn || !$ln || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success'=>false,'message'=>'Date invalide.']); exit;
}

try {
    // Check email uniqueness
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
    $check->execute([$email, $_SESSION['user_id']]);
    if ($check->fetch()) { echo json_encode(['success'=>false,'message'=>'Email-ul este deja folosit de alt cont.']); exit; }

    $pdo->prepare('UPDATE users SET first_name=?, last_name=?, email=?, phone=? WHERE id=?')
        ->execute([$fn, $ln, $email, $phone, $_SESSION['user_id']]);

    $_SESSION['user_name']  = "$fn $ln";
    $_SESSION['user_email'] = $email;

    echo json_encode(['success'=>true, 'name'=>"$fn $ln"]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Eroare server.']);
}