<?php
session_start(); header('Content-Type: application/json'); require_once '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }
$stmt = $pdo->query("SELECT rv.*, h.name AS hotel_name, u.first_name, u.last_name FROM reviews rv JOIN hotels h ON h.id=rv.hotel_id JOIN users u ON u.id=rv.user_id ORDER BY rv.created_at DESC");
echo json_encode(['reviews' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);