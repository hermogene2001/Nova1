-- SQL script to add missing columns to database tables
-- This makes the database schema consistent with application requirements

-- 1. Add missing columns to withdrawals table
ALTER TABLE withdrawals ADD COLUMN agent_id INT(11) NULL AFTER user_id;
ALTER TABLE withdrawals ADD COLUMN fee_percent DECIMAL(5,2) NULL DEFAULT NULL AFTER withdrawal_fee_percent;
ALTER TABLE withdrawals ADD COLUMN fee_amount DECIMAL(10,2) NULL DEFAULT NULL AFTER fee_amount_usd;
ALTER TABLE withdrawals ADD COLUMN client_id INT(11) NULL DEFAULT NULL AFTER id;

-- 2. Create user_banks table
CREATE TABLE IF NOT EXISTS user_banks (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    bank_name VARCHAR(255) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    account_holder VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Add missing columns to recharges table
ALTER TABLE recharges ADD COLUMN client_id INT(11) NULL DEFAULT NULL AFTER id;
ALTER TABLE recharges ADD COLUMN agent_id INT(11) NULL DEFAULT NULL AFTER client_id;

-- 4. Optional: Add triggers to keep columns synchronized
DELIMITER $$

-- Trigger to keep client_id and user_id synchronized in withdrawals
CREATE TRIGGER withdrawals_client_user_sync_insert 
BEFORE INSERT ON withdrawals
FOR EACH ROW
BEGIN
    IF NEW.client_id IS NOT NULL AND NEW.user_id IS NULL THEN
        SET NEW.user_id = NEW.client_id;
    ELSEIF NEW.user_id IS NOT NULL AND NEW.client_id IS NULL THEN
        SET NEW.client_id = NEW.user_id;
    END IF;
    
    IF NEW.fee_amount IS NOT NULL AND NEW.fee_amount_usd IS NULL THEN
        SET NEW.fee_amount_usd = NEW.fee_amount;
    ELSEIF NEW.fee_amount_usd IS NOT NULL AND NEW.fee_amount IS NULL THEN
        SET NEW.fee_amount = NEW.fee_amount_usd;
    END IF;
    
    IF NEW.fee_percent IS NOT NULL AND NEW.withdrawal_fee_percent IS NULL THEN
        SET NEW.withdrawal_fee_percent = NEW.fee_percent;
    ELSEIF NEW.withdrawal_fee_percent IS NOT NULL AND NEW.fee_percent IS NULL THEN
        SET NEW.fee_percent = NEW.withdrawal_fee_percent;
    END IF;
END$$

CREATE TRIGGER withdrawals_client_user_sync_update 
BEFORE UPDATE ON withdrawals
FOR EACH ROW
BEGIN
    IF NEW.client_id != OLD.client_id AND NEW.client_id IS NOT NULL THEN
        SET NEW.user_id = NEW.client_id;
    ELSEIF NEW.user_id != OLD.user_id AND NEW.user_id IS NOT NULL THEN
        SET NEW.client_id = NEW.user_id;
    END IF;
    
    IF NEW.fee_amount != OLD.fee_amount AND NEW.fee_amount IS NOT NULL THEN
        SET NEW.fee_amount_usd = NEW.fee_amount;
    ELSEIF NEW.fee_amount_usd != OLD.fee_amount_usd AND NEW.fee_amount_usd IS NOT NULL THEN
        SET NEW.fee_amount = NEW.fee_amount_usd;
    END IF;
    
    IF NEW.fee_percent != OLD.fee_percent AND NEW.fee_percent IS NOT NULL THEN
        SET NEW.withdrawal_fee_percent = NEW.fee_percent;
    ELSEIF NEW.withdrawal_fee_percent != OLD.withdrawal_fee_percent AND NEW.withdrawal_fee_percent IS NOT NULL THEN
        SET NEW.fee_percent = NEW.withdrawal_fee_percent;
    END IF;
END$$

DELIMITER ;

-- Sample queries for verification:
-- DESCRIBE withdrawals;
-- DESCRIBE user_banks;
-- DESCRIBE recharges;