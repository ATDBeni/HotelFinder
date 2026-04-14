-- ============================================================
-- HotelFinder — Migration Script
-- Rulează asta o singură dată în phpMyAdmin sau MySQL CLI
-- ============================================================

-- 1. USERS
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

-- 2. REVIEWS
CREATE TABLE IF NOT EXISTS reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id   INT  NOT NULL,
    user_id    INT  NOT NULL,
    rating     TINYINT NOT NULL,
    comment    TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY one_review (hotel_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. AVAILABILITY (zile blocate manual)
CREATE TABLE IF NOT EXISTS availability (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id     INT  NOT NULL,
    blocked_date DATE NOT NULL,
    reason       VARCHAR(120) DEFAULT NULL,
    UNIQUE KEY unique_block (hotel_id, blocked_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. COLOANE NOI IN HOTELS
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS stars        TINYINT        DEFAULT 4    AFTER price;
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS is_new       TINYINT(1)     DEFAULT 0    AFTER stars;
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS avg_rating   DECIMAL(3,2)   DEFAULT NULL AFTER is_new;
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS review_count INT            DEFAULT 0    AFTER avg_rating;
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS amenities    VARCHAR(500)   DEFAULT NULL AFTER review_count;

-- 5. COLOANE NOI IN REZERVARI
ALTER TABLE rezervari ADD COLUMN IF NOT EXISTS children        INT          DEFAULT 0    AFTER guests;
ALTER TABLE rezervari ADD COLUMN IF NOT EXISTS room_type       VARCHAR(30)  DEFAULT 'standard' AFTER children;
ALTER TABLE rezervari ADD COLUMN IF NOT EXISTS services        TEXT         DEFAULT NULL AFTER room_type;
ALTER TABLE rezervari ADD COLUMN IF NOT EXISTS special_requests TEXT        DEFAULT NULL AFTER services;
ALTER TABLE rezervari ADD COLUMN IF NOT EXISTS nights          INT          DEFAULT 1    AFTER special_requests;
ALTER TABLE rezervari ADD COLUMN IF NOT EXISTS booking_code    VARCHAR(20)  DEFAULT NULL AFTER nights;
ALTER TABLE rezervari ADD COLUMN IF NOT EXISTS status          ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending' AFTER booking_code;

-- Index pe booking_code pentru lookup rapid
ALTER TABLE rezervari ADD UNIQUE KEY IF NOT EXISTS idx_booking_code (booking_code);

-- 6. ADMIN DEFAULT (schimbă parola după import!)
-- Parola implicită: Admin@2026
INSERT IGNORE INTO users (first_name, last_name, email, password, role, created_at)
VALUES ('Admin', 'HotelFinder', 'admin@hotelfinder.ro',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin', NOW());