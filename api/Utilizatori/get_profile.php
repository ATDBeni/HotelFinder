<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

$stmt = $pdo->prepare('SELECT first_name, last_name, email, phone FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: []);