<?php
// header('Content-Type: application/json');

// require_once(__DIR__ . '/db_config.php');

// if (!isset($_POST['id'])) {
//     echo json_encode(['status' => 'error', 'message' => 'ID hotel lipsă']);
//     exit;
// }

// $hotelId = intval($_POST['id']);

// $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// if ($conn->connect_error) {
//     echo json_encode(['status' => 'error', 'message' => 'Eroare la conectarea la baza de date']);
//     exit;
// }

// $sql = "DELETE FROM hotels WHERE id = ?";
// $stmt = $conn->prepare($sql);
// $stmt->bind_param("i", $hotelId);

// if ($stmt->execute()) {
//     echo json_encode(['status' => 'success']);
// } else {
//     echo json_encode(['status' => 'error', 'message' => 'Nu s-a putut șterge hotelul']);
// }

// $stmt->close();
// $conn->close();