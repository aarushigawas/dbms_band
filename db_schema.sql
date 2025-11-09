-- USERS TABLE
CREATE TABLE users (
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

-- BANDS TABLE
CREATE TABLE bands (
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

-- MEMBERS TABLE
CREATE TABLE members (
  member_id INT AUTO_INCREMENT PRIMARY KEY,
  b_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  position VARCHAR(100),
  stage_name VARCHAR(120),
  joined_on DATE,
  CONSTRAINT fk_members_band FOREIGN KEY (b_id) REFERENCES bands(b_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- VENUES TABLE
CREATE TABLE venues (
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

-- PERFORMANCES TABLE
CREATE TABLE performances (
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

-- BOOKINGS TABLE
CREATE TABLE bookings (
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

-- PAYMENTS TABLE
CREATE TABLE payments (
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

-- REVIEWS TABLE
CREATE TABLE reviews (
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

-- TICKETS TABLE
CREATE TABLE tickets (
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

-- MANAGER TABLE
CREATE TABLE manager (
  manager_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  name VARCHAR(120),
  email VARCHAR(150),
  number VARCHAR(30),
  agency_name VARCHAR(150),
  experience_years INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_manager_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- VENUE OWNER TABLE
CREATE TABLE venue_owner (
  owner_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  name VARCHAR(120),
  email VARCHAR(150),
  number VARCHAR(30),
  company_name VARCHAR(150),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_owner_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- GENERAL USER TABLE
CREATE TABLE gen_user (
  general_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  name VARCHAR(120),
  email VARCHAR(150),
  number VARCHAR(30),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_general_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- FUNCTION: Calculate average band rating
DELIMITER //
CREATE FUNCTION fn_band_avg_rating(p_band_id INT)
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
  DECLARE v_avg DECIMAL(5,2);
  SELECT IFNULL(AVG(rating),0)
    INTO v_avg
  FROM reviews
  WHERE target_type = 'band'
    AND target_id = p_band_id;
  RETURN v_avg;
END //
DELIMITER ;

-- TRIGGER: Automatically update band rating after a new review
DELIMITER //
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

-- TRIGGER: Validate booking date (not before today)
DELIMITER //
CREATE TRIGGER trg_bookings_before_insert_validate_date
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
  IF NEW.booking_date < CURDATE() THEN
    SET NEW.booking_date = CURDATE();
  END IF;
END //
DELIMITER ;

-- TRIGGER: Set default end time for performance if missing
DELIMITER //
CREATE TRIGGER trg_performances_before_insert_default_end
BEFORE INSERT ON performances
FOR EACH ROW
BEGIN
  IF NEW.start_time IS NOT NULL AND (NEW.end_time IS NULL OR NEW.end_time = '00:00:00') THEN
    SET NEW.end_time = ADDTIME(NEW.start_time, '02:00:00');
  END IF;
END //
DELIMITER ;

-- STORED PROCEDURE: Confirm a booking
DELIMITER //
CREATE PROCEDURE sp_confirm_booking(IN p_booking_id INT, IN p_owner_user_id INT)
BEGIN
  START TRANSACTION;
    UPDATE bookings bk
    JOIN venues v ON v.venue_id = bk.venue_id
       SET bk.status = 'confirmed'
     WHERE bk.booking_id = p_booking_id
       AND v.owner_user_id = p_owner_user_id
       AND bk.status = 'pending';
  COMMIT;
END //
DELIMITER ;

USE band;


-- FUNCTION: fn_venue_avg_rating
DROP FUNCTION IF EXISTS fn_venue_avg_rating;
DELIMITER //
CREATE FUNCTION fn_venue_avg_rating(p_venue_id INT)
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
  DECLARE v_avg DECIMAL(5,2);
  SELECT IFNULL(AVG(rating), 0)
    INTO v_avg
  FROM reviews
  WHERE target_type = 'venue'
    AND target_id = p_venue_id;
  RETURN v_avg;
END //
DELIMITER ;




-- PROCEDURE 1: sp_cancel_user_pending_bookings

DROP PROCEDURE IF EXISTS sp_cancel_user_pending_bookings;
DELIMITER //
CREATE PROCEDURE sp_cancel_user_pending_bookings(IN p_user_id INT)
BEGIN
  DECLARE v_booking_id INT;
  DECLARE done INT DEFAULT 0;

  DECLARE cur CURSOR FOR
    SELECT booking_id
    FROM bookings
    WHERE created_by = p_user_id
      AND status = 'pending';

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
  END;

  START TRANSACTION;
    OPEN cur;
      read_loop: LOOP
        FETCH cur INTO v_booking_id;
        IF done = 1 THEN
          LEAVE read_loop;
        END IF;
        UPDATE bookings
           SET status = 'cancelled'
         WHERE booking_id = v_booking_id
           AND status = 'pending';
      END LOOP;
    CLOSE cur;
  COMMIT;
END //
DELIMITER ;

-- PROCEDURE 2: sp_expire_past_pending_bookings

DROP PROCEDURE IF EXISTS sp_expire_past_pending_bookings;
DELIMITER //
CREATE PROCEDURE sp_expire_past_pending_bookings()
BEGIN
  DECLARE v_booking_id INT;
  DECLARE done INT DEFAULT 0;

  DECLARE cur CURSOR FOR
    SELECT booking_id
    FROM bookings
    WHERE status = 'pending'
      AND booking_date < CURDATE();

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
  END;

  START TRANSACTION;
    OPEN cur;
      loop_rows: LOOP
        FETCH cur INTO v_booking_id;
        IF done = 1 THEN
          LEAVE loop_rows;
        END IF;
        UPDATE bookings
           SET status = 'cancelled'
         WHERE booking_id = v_booking_id
           AND status = 'pending';
      END LOOP;
    CLOSE cur;
  COMMIT;
END //
DELIMITER ;

-- PROCEDURE 3: sp_recalculate_venue_ratings_snapshot

DROP PROCEDURE IF EXISTS sp_recalculate_venue_ratings_snapshot;
DELIMITER //
CREATE PROCEDURE sp_recalculate_venue_ratings_snapshot()
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
  END;

  START TRANSACTION;
    DROP TEMPORARY TABLE IF EXISTS tmp_venue_ratings;
    CREATE TEMPORARY TABLE tmp_venue_ratings (
      venue_id INT PRIMARY KEY,
      avg_rating DECIMAL(5,2)
    ) ENGINE=Memory;

    INSERT INTO tmp_venue_ratings (venue_id, avg_rating)
    SELECT v.venue_id, fn_venue_avg_rating(v.venue_id)
    FROM venues v;
  COMMIT;
END //
DELIMITER ;
