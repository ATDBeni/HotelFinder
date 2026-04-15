<?php
session_start(); header('Content-Type: application/json'); require_once 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }
$stmt = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM rezervari r WHERE r.user_id=u.id) AS rez_count FROM users u WHERE u.role='client' ORDER BY u.created_at DESC");
echo json_encode(['users' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);