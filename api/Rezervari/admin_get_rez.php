<?php
session_start(); header('Content-Type: application/json'); require_once '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }
$stmt = $pdo->query("SELECT r.*, h.name AS hotel_name FROM rezervari r LEFT JOIN hotels h ON h.id=r.hotel_id ORDER BY r.created_at DESC");
echo json_encode(['rezervari' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);