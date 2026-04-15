<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Neautorizat.']); exit;
}

$id = intval($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'ID invalid.']); exit; }

try {
    // Verify ownership and cancellable status
    $stmt = $pdo->prepare("SELECT id, status FROM rezervari WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $rez = $stmt->fetch();

    if (!$rez) { echo json_encode(['success'=>false,'message'=>'Rezervarea nu a fost găsită.']); exit; }
    if (!in_array($rez['status'], ['pending','confirmed'])) {
        echo json_encode(['success'=>false,'message'=>'Această rezervare nu poate fi anulată.']); exit;
    }

    $pdo->prepare("UPDATE rezervari SET status='cancelled' WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Eroare server.']);
}