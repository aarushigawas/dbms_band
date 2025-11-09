
use band;
-- 1) Update / Recreate sp_confirm_booking with exception handler
DELIMITER //
DROP PROCEDURE IF EXISTS sp_confirm_booking //
CREATE PROCEDURE sp_confirm_booking(
  IN p_booking_id INT,
  IN p_owner_user_id INT
)
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    -- on error rollback and exit
    ROLLBACK;
  END;

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

-- 2) New procedure: create_booking_with_payment
-- Creates a booking and optionally creates a payment record (transactional + exception handling)
DELIMITER //
DROP PROCEDURE IF EXISTS sp_create_booking_with_payment //
CREATE PROCEDURE sp_create_booking_with_payment(
  IN p_b_id INT,
  IN p_venue_id INT,
  IN p_booking_date DATE,
  IN p_created_by INT,
  IN p_total_amount DECIMAL(10,2),
  IN p_create_payment TINYINT -- 1 = create payment record, 0 = no
)
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
  END;

  START TRANSACTION;
    INSERT INTO bookings (b_id, venue_id, booking_date, status, created_by, total_amount, created_at)
    VALUES (p_b_id, p_venue_id, p_booking_date, 'pending', p_created_by, p_total_amount, NOW());

    -- get the last booking id
    SET @new_booking_id = LAST_INSERT_ID();

    IF p_create_payment = 1 THEN
      INSERT INTO payments (booking_id, payer_user_id, payment_amount, payment_date, payment_status)
      VALUES (@new_booking_id, p_created_by, p_total_amount, NOW(), 'pending');
    END IF;
  COMMIT;
END //
DELIMITER ;

-- 3) New procedure that uses a CURSOR:
-- sp_list_band_members_cursor: iterates members of a band and inserts their names & positions into a temp table
DELIMITER //
DROP PROCEDURE IF EXISTS sp_list_band_members_cursor //
CREATE PROCEDURE sp_list_band_members_cursor(
  IN p_b_id INT
)
BEGIN
  -- A simple temp table to collect results (session-only)
  CREATE TEMPORARY TABLE IF NOT EXISTS tmp_band_members (
    seq_no INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    name VARCHAR(120),
    position VARCHAR(100)
  );

  DECLARE done INT DEFAULT 0;
  DECLARE v_member_id INT;
  DECLARE v_name VARCHAR(120);
  DECLARE v_position VARCHAR(100);

  DECLARE cur_members CURSOR FOR
    SELECT member_id, name, position FROM members WHERE b_id = p_b_id;

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  OPEN cur_members;
  read_loop: LOOP
    FETCH cur_members INTO v_member_id, v_name, v_position;
    IF done = 1 THEN
      LEAVE read_loop;
    END IF;
    INSERT INTO tmp_band_members (member_id, name, position) VALUES (v_member_id, v_name, v_position);
  END LOOP;
  CLOSE cur_members;

  -- Return the temp table contents for caller (SELECT will display results in phpMyAdmin)
  SELECT * FROM tmp_band_members ORDER BY seq_no;

  -- optional: drop temp table (it will auto-drop at session end)
  DROP TEMPORARY TABLE IF EXISTS tmp_band_members;
END //
DELIMITER ;

-- 4) Additional helpful function: total_bookings_for_venue
DROP FUNCTION IF EXISTS fn_venue_booking_count;
DELIMITER //
CREATE FUNCTION fn_venue_booking_count(p_venue_id INT)
RETURNS INT
DETERMINISTIC
BEGIN
  DECLARE v_count INT;
  SELECT COUNT(*) INTO v_count FROM bookings WHERE venue_id = p_venue_id;
  RETURN IFNULL(v_count, 0);
END //
DELIMITER ;

-- 5) Another small procedure to demonstrate exception handling and to call the function
DELIMITER //
DROP PROCEDURE IF EXISTS sp_get_venue_stats //
CREATE PROCEDURE sp_get_venue_stats(
  IN p_venue_id INT
)
BEGIN
  DECLARE v_count INT;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    -- Return a simple select to indicate failure
    ROLLBACK;
    SELECT CONCAT('Error retrieving stats for venue_id=', p_venue_id) AS error;
  END;

  START TRANSACTION;
    SET v_count = fn_venue_booking_count(p_venue_id);
  COMMIT;

  SELECT p_venue_id AS venue_id, v_count AS total_bookings;
END //
DELIMITER ;
USE band;

-- =========================================================
-- FUNCTION: fn_venue_avg_rating
-- Calculates the average rating for a given venue ID
-- =========================================================
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



-- =========================================================
-- PROCEDURE 1: sp_cancel_user_pending_bookings
-- Cancels all pending bookings created by a specific user.
-- Uses CURSOR + Exception Handling
-- =========================================================
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



-- =========================================================
-- PROCEDURE 2: sp_expire_past_pending_bookings
-- Cancels all pending bookings that are scheduled for past dates.
-- Uses CURSOR + Exception Handling
-- =========================================================
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



-- =========================================================
-- PROCEDURE 3: sp_recalculate_venue_ratings_snapshot
-- Recalculates average ratings of all venues and stores
-- the results in a TEMPORARY TABLE for analysis.
-- Uses Exception Handling (non-invasive to main tables)
-- =========================================================
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
