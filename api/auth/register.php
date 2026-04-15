<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodă invalidă.']);
    exit;
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name']  ?? '');
$email      = trim($_POST['email']      ?? '');
$password   = $_POST['password']        ?? '';
$phone      = trim($_POST['phone']      ?? '');

// Validare
if (!$first_name || !$last_name) {
    echo json_encode(['success' => false, 'message' => 'Numele și prenumele sunt obligatorii.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Adresa de email nu este validă.']);
    exit;
}
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Parola trebuie să aibă cel puțin 8 caractere.']);
    exit;
}

try {
    // Verifică dacă emailul există deja
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Această adresă de email este deja înregistrată.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password, phone, role, created_at) VALUES (?, ?, ?, ?, ?, "client", NOW())');
    $stmt->execute([$first_name, $last_name, $email, $hash, $phone]);

    $userId = $pdo->lastInsertId();
    $_SESSION['user_id']   = $userId;
    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
    $_SESSION['user_email']= $email;
    $_SESSION['role']      = 'client';

    echo json_encode(['success' => true, 'message' => 'Cont creat cu succes.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare de server. Încearcă din nou.']);
}