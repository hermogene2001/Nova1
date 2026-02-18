-- SQL script to add status column to users table
-- This adds account status management functionality

-- Check if column exists (optional - for manual verification)
-- SHOW COLUMNS FROM users LIKE 'status';

-- Add status column to users table
ALTER TABLE users 
ADD COLUMN status ENUM('active', 'inactive', 'suspended', 'pending') 
DEFAULT 'active' 
AFTER role;

-- Verify the column was added
-- DESCRIBE users;

-- Sample queries for using the status column:

-- Find all active users
-- SELECT * FROM users WHERE status = 'active';

-- Find all suspended users
-- SELECT * FROM users WHERE status = 'suspended';

-- Update user status
-- UPDATE users SET status = 'suspended' WHERE id = 123;

-- Count users by status
-- SELECT status, COUNT(*) as count FROM users GROUP BY status;