-- ==============================================================================
-- Health Delivery System - Supabase / PostgreSQL Schema Migration
-- Table: immunized_infants & appointments columns
-- ==============================================================================

-- 1. Ensure new columns exist on the appointments table
ALTER TABLE IF EXISTS appointments 
    ADD COLUMN IF NOT EXISTS immunization_relationship VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS recipient_first_name VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS recipient_middle_name VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS recipient_last_name VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS recipient_birth_date DATE DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS vaccine_type VARCHAR(150) DEFAULT NULL;

-- 2. Create the immunized_infants table
CREATE TABLE IF NOT EXISTS immunized_infants (
    id BIGSERIAL PRIMARY KEY,
    appointment_id BIGINT DEFAULT NULL,
    appointment_code VARCHAR(20) DEFAULT NULL,
    patient_id VARCHAR(32) DEFAULT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender VARCHAR(30) DEFAULT NULL,
    relationship VARCHAR(50) NOT NULL DEFAULT 'Child',
    station_slug VARCHAR(100) DEFAULT NULL,
    vaccine_type VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. Create performance indexes
CREATE INDEX IF NOT EXISTS idx_immunized_infants_appointment ON immunized_infants (appointment_id);
CREATE INDEX IF NOT EXISTS idx_immunized_infants_code ON immunized_infants (appointment_code);
CREATE INDEX IF NOT EXISTS idx_immunized_infants_patient ON immunized_infants (patient_id);
CREATE INDEX IF NOT EXISTS idx_immunized_infants_station ON immunized_infants (station_slug);
CREATE INDEX IF NOT EXISTS idx_immunized_infants_recipient ON immunized_infants (last_name, first_name);

-- 4. Optional: Enable Row Level Security (RLS) if Supabase client is accessed directly
ALTER TABLE immunized_infants ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Allow public read-access or authenticated access" 
ON immunized_infants 
FOR ALL 
USING (true) 
WITH CHECK (true);
