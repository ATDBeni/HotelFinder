<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Neautorizat.']);
    exit;
}

$id        = intval($_POST['id']          ?? 0);
$name      = trim($_POST['name']          ?? '');
$location  = trim($_POST['location']      ?? '');
$price     = floatval($_POST['price']     ?? 0);
$desc      = trim($_POST['description']   ?? '');
$stars     = intval($_POST['stars']       ?? 4);
$amenities = trim($_POST['amenities']     ?? '');
$images    = trim($_POST['images']        ?? '[]');
$is_new      = intval($_POST['is_new']        ?? 0);
$total_rooms = intval($_POST['total_rooms']    ?? 10);

if (!$id || !$name || !$location || !$price || !$desc) {
    echo json_encode(['success' => false, 'message' => 'Campuri obligatorii lipsa.']);
    exit;
}

try {
    $cols = $pdo->query("SHOW COLUMNS FROM hotels")->fetchAll(PDO::FETCH_COLUMN);

    $sql    = "UPDATE hotels SET name=?, location=?, price=?, description=?, image_url=?";
    $params = [$name, $location, $price, $desc, $images];

    if (in_array('stars', $cols))     { $sql .= ', stars=?';     $params[] = $stars; }
    if (in_array('amenities', $cols)) { $sql .= ', amenities=?'; $params[] = $amenities; }
    if (in_array('is_new', $cols))       { $sql .= ', is_new=?';       $params[] = $is_new; }
    if (in_array('total_rooms', $cols))  { $sql .= ', total_rooms=?';  $params[] = $total_rooms; }

    $sql .= " WHERE id=?";
    $params[] = $id;

    $pdo->prepare($sql)->execute($params);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}