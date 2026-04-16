<?php session_start(); header('Content-Type: application/json'); require_once '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }
$id     = intval($_POST['id']     ?? 0);
$status = trim($_POST['status']   ?? '');
$allowed = ['pending','confirmed','completed','cancelled'];
if (!$id || !in_array($status, $allowed)) { echo json_encode(['success'=>false,'message'=>'Date invalide.']); exit; }
$pdo->prepare("UPDATE rezervari SET status=? WHERE id=?")->execute([$status, $id]);
echo json_encode(['success'=>true]);