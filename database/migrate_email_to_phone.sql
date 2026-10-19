-- PURPOSE: One-time database migration that adds phone columns and allows older email columns to be empty.
-- Run SQL only in the intended local vehicle_rental database.

-- Run this ONCE in phpMyAdmin > vehicle_rental > SQL
-- This preserves existing booking/enquiry rows and adds phone fields.
ALTER TABLE bookings ADD COLUMN phone VARCHAR(20) NULL AFTER customer_name;
ALTER TABLE bookings MODIFY COLUMN email VARCHAR(120) NULL;

ALTER TABLE enquiries ADD COLUMN phone VARCHAR(20) NULL AFTER customer_name;
ALTER TABLE enquiries MODIFY COLUMN email VARCHAR(120) NULL;
