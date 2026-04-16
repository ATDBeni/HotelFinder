<?php

session_start();
header('Content-Type: application/json');
require_once '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { http_response_code(403); exit; }

try {
    $hotels = $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn();
    $rez    = $pdo->query("SELECT COUNT(*) FROM rezervari")->fetchColumn();
    $users  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn();
    $today  = $pdo->query("SELECT COUNT(*) FROM rezervari WHERE DATE(created_at)=CURDATE()")->fetchColumn();

    // Monthly last 6 months
    $monthly = $pdo->query("
        SELECT MONTH(created_at) AS month, COUNT(*) AS count
        FROM rezervari
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY MONTH(created_at)
        ORDER BY MONTH(created_at)
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Activity (last 10 actions combined)
    $activity = [];
    $rezAct = $pdo->query("SELECT 'rezervare' as type, CONCAT(first_name,' ',last_name,' a rezervat') as text, created_at FROM rezervari ORDER BY created_at DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
    $usrAct = $pdo->query("SELECT 'user' as type, CONCAT(first_name,' ',last_name,' s-a înregistrat') as text, created_at FROM users WHERE role='client' ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    $revAct = $pdo->query("SELECT 'review' as type, CONCAT(u.first_name,' a lăsat o recenzie (★',r.rating,')') as text, r.created_at FROM reviews r JOIN users u ON u.id=r.user_id ORDER BY r.created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    $activity = array_merge($rezAct, $usrAct, $revAct);
    usort($activity, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));
    $activity = array_slice($activity, 0, 8);

    echo json_encode(compact('hotels','rez','users','today','monthly','activity'));
} catch(PDOException $e) { echo json_encode(['hotels'=>0,'rez'=>0,'users'=>0,'today'=>0,'monthly'=>[],'activity'=>[]]); }