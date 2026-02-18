-- Add support phone number column to social_links table
ALTER TABLE social_links ADD COLUMN support_phone VARCHAR(20) DEFAULT NULL COMMENT 'Support phone number for client assistance';

-- Insert default support phone if table is empty
INSERT IGNORE INTO social_links (id, support_phone) VALUES (1, NULL);