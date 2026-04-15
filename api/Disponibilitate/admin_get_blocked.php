<?php // api/admin_get_blocked.php
session_start(); header('Content-Type: application/json'); require_once 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }
$stmt = $pdo->query("SELECT a.*, h.name AS hotel_name FROM availability a JOIN hotels h ON h.id=a.hotel_id ORDER BY a.blocked_date DESC");
echo json_encode(['blocked' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);