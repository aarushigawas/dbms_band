
CREATE DATABASE IF NOT EXISTS band CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE band;

-- Users
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(120),
  number VARCHAR(30),
  role ENUM('general','manager','venue_owner','admin') NOT NULL DEFAULT 'general',
  status VARCHAR(40) DEFAULT 'active',
  date_joined DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (email),
  INDEX (role)
) ENGINE=InnoDB;

-- =========================================================
-- Programmability: Functions, Procedures, Triggers
-- Import notes (phpMyAdmin): These blocks use custom delimiters
-- =========================================================

-- Functions
DELIMITER //
CREATE FUNCTION IF NOT EXISTS fn_band_avg_rating(p_band_id INT)
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
  DECLARE v_avg DECIMAL(5,2);
  SELECT IFNULL(AVG(r.rating),0)
    INTO v_avg
  FROM reviews r
  WHERE r.target_type = 'band' AND r.target_id = p_band_id;
  RETURN v_avg;
END //

CREATE FUNCTION IF NOT EXISTS fn_performance_duration_minutes(p_performance_id INT)
RETURNS INT
DETERMINISTIC
BEGIN
  DECLARE v_minutes INT;
  SELECT TIMESTAMPDIFF(MINUTE,
           CONCAT(date,' ', IFNULL(start_time,'00:00:00')),
           CONCAT(date,' ', IFNULL(end_time,   IFNULL(start_time,'00:00:00'))))
    INTO v_minutes
  FROM performances
  WHERE performance_id = p_performance_id;
  RETURN IFNULL(v_minutes, 0);
END //
DELIMITER ;

-- Procedures
DELIMITER //
CREATE PROCEDURE sp_confirm_booking(IN p_booking_id INT, IN p_owner_user_id INT)
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
  END;
  START TRANSACTION;
    -- Ensure this booking belongs to a venue owned by the caller and is pending
    UPDATE bookings bk
    JOIN venues v ON v.venue_id = bk.venue_id
       SET bk.status = 'confirmed'
     WHERE bk.booking_id = p_booking_id
       AND v.owner_user_id = p_owner_user_id
       AND bk.status = 'pending';
  COMMIT;
END //

-- Cancels all pending bookings created by a user (demonstrates cursor)
CREATE PROCEDURE sp_cancel_user_pending_bookings(IN p_user_id INT)
BEGIN
  DECLARE v_booking_id INT;
  DECLARE done INT DEFAULT 0;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
  END;
  DECLARE cur CURSOR FOR
    SELECT booking_id FROM bookings WHERE created_by = p_user_id AND status = 'pending';
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  START TRANSACTION;
    OPEN cur;
      read_loop: LOOP
        FETCH cur INTO v_booking_id;
        IF done = 1 THEN
          LEAVE read_loop;
        END IF;
        UPDATE bookings SET status = 'cancelled' WHERE booking_id = v_booking_id AND status = 'pending';
      END LOOP;
    CLOSE cur;
  COMMIT;
END //

-- Creates a performance from an already confirmed booking
CREATE PROCEDURE sp_create_performance_from_booking(
  IN p_booking_id INT,
  IN p_start_time TIME,
  IN p_end_time TIME,
  OUT p_new_performance_id INT
)
BEGIN
  DECLARE v_b_id INT;
  DECLARE v_venue_id INT;
  DECLARE v_date DATE;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    SET p_new_performance_id = NULL;
  END;

  SELECT b_id, venue_id, booking_date
    INTO v_b_id, v_venue_id, v_date
  FROM bookings
  WHERE booking_id = p_booking_id AND status = 'confirmed'
  LIMIT 1;

  START TRANSACTION;
    INSERT INTO performances (b_id, venue_id, date, start_time, end_time, performance_type, status)
    VALUES (v_b_id, v_venue_id, v_date, p_start_time, p_end_time, 'booking', 'scheduled');
    SET p_new_performance_id = LAST_INSERT_ID();
  COMMIT;
END //
DELIMITER ;

-- Triggers
DELIMITER //
-- Ensure booking date is not in the past
CREATE TRIGGER trg_bookings_before_insert_validate_date
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
  IF NEW.booking_date < CURDATE() THEN
    SET NEW.booking_date = CURDATE();
  END IF;
END //

-- Default end_time to 2 hours after start_time (when provided) on performance insert
CREATE TRIGGER trg_performances_before_insert_default_end
BEFORE INSERT ON performances
FOR EACH ROW
BEGIN
  IF NEW.start_time IS NOT NULL AND (NEW.end_time IS NULL OR NEW.end_time = '00:00:00') THEN
    SET NEW.end_time = ADDTIME(NEW.start_time, '02:00:00');
  END IF;
END //

-- When a review targeting a band is added, update the band's rating to the new average
CREATE TRIGGER trg_reviews_after_insert_update_band_rating
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
  IF NEW.target_type = 'band' THEN
    UPDATE bands
       SET rating = fn_band_avg_rating(NEW.target_id)
     WHERE b_id = NEW.target_id;
  END IF;
END //
DELIMITER ;

-- Bands
CREATE TABLE IF NOT EXISTS bands (
  b_id INT AUTO_INCREMENT PRIMARY KEY,
  band_name VARCHAR(150) NOT NULL,
  genre VARCHAR(80),
  no_of_members SMALLINT UNSIGNED DEFAULT 1,
  availability_status ENUM('available','booked','inactive') DEFAULT 'available',
  formation_year YEAR NULL,
  manager_user_id INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bands_manager_user FOREIGN KEY (manager_user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Band members
CREATE TABLE IF NOT EXISTS members (
  member_id INT AUTO_INCREMENT PRIMARY KEY,
  b_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  position VARCHAR(100),
  stage_name VARCHAR(120),
  joined_on DATE,
  CONSTRAINT fk_members_band FOREIGN KEY (b_id) REFERENCES bands(b_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Venues (owned by a user with role venue_owner)
CREATE TABLE IF NOT EXISTS venues (
  venue_id INT AUTO_INCREMENT PRIMARY KEY,
  venue_name VARCHAR(150) NOT NULL,
  location VARCHAR(255),
  venue_type VARCHAR(100),
  capacity INT,
  rent DECIMAL(10,2) DEFAULT 0.00,
  owner_user_id INT NULL,
  contact_number VARCHAR(30),
  email VARCHAR(150),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_venues_owner FOREIGN KEY (owner_user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Performances
CREATE TABLE IF NOT EXISTS performances (
  performance_id INT AUTO_INCREMENT PRIMARY KEY,
  b_id INT NOT NULL,
  venue_id INT NOT NULL,
  date DATE NOT NULL,
  start_time TIME,
  end_time TIME,
  performance_type VARCHAR(100),
  status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (date),
  INDEX (status),
  CONSTRAINT fk_performances_band FOREIGN KEY (b_id) REFERENCES bands(b_id) ON DELETE CASCADE,
  CONSTRAINT fk_performances_venue FOREIGN KEY (venue_id) REFERENCES venues(venue_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bookings (request to book a band at a venue on a date)
CREATE TABLE IF NOT EXISTS bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  b_id INT NOT NULL,
  venue_id INT NOT NULL,
  booking_date DATE NOT NULL,
  status ENUM('pending','confirmed','rejected','cancelled') DEFAULT 'pending',
  created_by INT NULL,
  total_amount DECIMAL(10,2) DEFAULT 0.00,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_band FOREIGN KEY (b_id) REFERENCES bands(b_id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_venue FOREIGN KEY (venue_id) REFERENCES venues(venue_id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_user FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Payments (optional)
CREATE TABLE IF NOT EXISTS payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NULL,
  payer_user_id INT NULL,
  payment_amount DECIMAL(10,2) NOT NULL,
  payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  payment_status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
  payment_method VARCHAR(50),
  transaction_ref VARCHAR(200),
  CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE SET NULL,
  CONSTRAINT fk_payments_user FOREIGN KEY (payer_user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Reviews (target may be band or venue)
CREATE TABLE IF NOT EXISTS reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  author_user_id INT NOT NULL,
  target_type ENUM('band','venue') NOT NULL,
  target_id INT NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT,
  review_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_author FOREIGN KEY (author_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  INDEX (target_type, target_id)
) ENGINE=InnoDB;

-- Tickets for performances (general users purchase)
CREATE TABLE IF NOT EXISTS tickets (
  ticket_id INT AUTO_INCREMENT PRIMARY KEY,
  performance_id INT NOT NULL,
  buyer_user_id INT NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status ENUM('reserved','purchased','refunded') DEFAULT 'purchased',
  purchased_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tickets_performance FOREIGN KEY (performance_id) REFERENCES performances(performance_id) ON DELETE CASCADE,
  CONSTRAINT fk_tickets_buyer FOREIGN KEY (buyer_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  INDEX (buyer_user_id),
  INDEX (performance_id)
) ENGINE=InnoDB;
