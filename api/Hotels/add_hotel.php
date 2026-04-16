<?php
session_start(); header('Content-Type: application/json'); require_once '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }

$name     = trim($_POST['name']         ?? '');
$location = trim($_POST['location']     ?? '');
$price    = floatval($_POST['price']    ?? 0);
$desc     = trim($_POST['description']  ?? '');
$stars    = intval($_POST['stars']      ?? 4);
$amenities= trim($_POST['amenities']    ?? '');
$images   = trim($_POST['images']       ?? '[]');
$is_new   = intval($_POST['is_new']     ?? 0);
$pets     = trim($_POST['pets']         ?? 'request');
$checkin  = trim($_POST['checkin_time'] ?? '14:00');
$checkout = trim($_POST['checkout_time']?? '12:00');
$phone    = trim($_POST['contact_phone']?? '');
$website  = trim($_POST['website']      ?? '');

if (!$name || !$location || !$price || !$desc) { echo json_encode(['success'=>false,'message'=>'Câmpuri obligatorii lipsesc.']); exit; }

try {
    $cols = $pdo->query("SHOW COLUMNS FROM hotels")->fetchAll(PDO::FETCH_COLUMN);
    $sql  = "INSERT INTO hotels (name, location, price, description, image_url";
    $vals = "VALUES (?, ?, ?, ?, ?";
    $params = [$name, $location, $price, $desc, $images];

    if (in_array('stars', $cols))        { $sql .= ', stars';        $vals .= ', ?'; $params[] = $stars; }
    if (in_array('amenities', $cols))    { $sql .= ', amenities';    $vals .= ', ?'; $params[] = $amenities; }
    if (in_array('is_new', $cols))       { $sql .= ', is_new';       $vals .= ', ?'; $params[] = $is_new; }
    if (in_array('pets', $cols))         { $sql .= ', pets';         $vals .= ', ?'; $params[] = $pets; }
    if (in_array('checkin_time', $cols)) { $sql .= ', checkin_time'; $vals .= ', ?'; $params[] = $checkin; }
    if (in_array('checkout_time',$cols)) { $sql .= ', checkout_time';$vals .= ', ?'; $params[] = $checkout; }
    if (in_array('contact_phone',$cols)) { $sql .= ', contact_phone';$vals .= ', ?'; $params[] = $phone; }
    if (in_array('website', $cols))      { $sql .= ', website';      $vals .= ', ?'; $params[] = $website; }

    $pdo->prepare("$sql) $vals)")->execute($params);
    echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
} catch(PDOException $e) { echo json_encode(['success'=>false,'message'=>$e->getMessage()]); }