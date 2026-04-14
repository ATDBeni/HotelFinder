<?php
// ── CONFIGURARE ──────────────────────────────────────────
$host   = 'localhost';
$dbname = 'hotel_finder_v3';// schimbă dacă e altfel
$user   = 'root';           // schimbă cu userul tău
$pass   = '';               // schimbă cu parola ta
// ─────────────────────────────────────────────────────────

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user, $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Conexiune DB eșuată: ' . $e->getMessage()]);
    exit;
}

// ── CREARE TABELE NOI (dacă nu există) ──────────────────
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    first_name   VARCHAR(80)  NOT NULL,
    last_name    VARCHAR(80)  NOT NULL,
    email        VARCHAR(180) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    phone        VARCHAR(30)  DEFAULT NULL,
    role         ENUM('client','admin') NOT NULL DEFAULT 'client',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login   DATETIME     DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id   INT  NOT NULL,
    user_id    INT  NOT NULL,
    rating     TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    UNIQUE KEY one_review (hotel_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS availability (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id     INT  NOT NULL,
    blocked_date DATE NOT NULL,
    reason       VARCHAR(120) DEFAULT NULL,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_block (hotel_id, blocked_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// ── ADAUGĂ COLOANE NOI LA HOTELS (dacă lipsesc) ─────────
$cols = $pdo->query("SHOW COLUMNS FROM hotels")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('stars', $cols)) {
    $pdo->exec("ALTER TABLE hotels ADD COLUMN stars TINYINT DEFAULT 4 AFTER price");
}
if (!in_array('is_new', $cols)) {
    $pdo->exec("ALTER TABLE hotels ADD COLUMN is_new TINYINT(1) DEFAULT 0 AFTER stars");
}
if (!in_array('avg_rating', $cols)) {
    $pdo->exec("ALTER TABLE hotels ADD COLUMN avg_rating DECIMAL(3,2) DEFAULT NULL AFTER is_new");
}
if (!in_array('review_count', $cols)) {
    $pdo->exec("ALTER TABLE hotels ADD COLUMN review_count INT DEFAULT 0 AFTER avg_rating");
}