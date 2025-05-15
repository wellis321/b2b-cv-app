-- Make degree column nullable as a transitional step
ALTER TABLE education ALTER COLUMN degree DROP NOT NULL;