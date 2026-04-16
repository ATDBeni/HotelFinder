<?php
session_start(); header('Content-Type: application/json'); require_once '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }
$id = intval($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'ID lipsă.']); exit; }
try {
    $pdo->prepare("UPDATE hotels SET name=?, location=?, price=?, description=?, image_url=?, stars=?, amenities=?, is_new=? WHERE id=?")
        ->execute([
            trim($_POST['name']        ?? ''),
            trim($_POST['location']    ?? ''),
            floatval($_POST['price']   ?? 0),
            trim($_POST['description'] ?? ''),
            trim($_POST['images']      ?? '[]'),
            intval($_POST['stars']     ?? 4),
            trim($_POST['amenities']   ?? ''),
            intval($_POST['is_new']    ?? 0),
            $id
        ]);
    echo json_encode(['success'=>true]);
} catch(PDOException $e) { echo json_encode(['success'=>false,'message'=>$e->getMessage()]); }
