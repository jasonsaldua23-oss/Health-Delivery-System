-- ============================================================================
-- HEALTH DELIVERY SYSTEM
-- Clean Database Schema
-- ============================================================================
-- Purpose: A readable, maintainable schema with consistent naming.
-- Naming rules:
--   * tables      : plural snake_case
--   * primary key : id
--   * references  : <entity>_id
--   * timestamps  : created_at, updated_at
--   * booleans    : is_<state>
-- ============================================================================

CREATE DATABASE IF NOT EXISTS health_delivery_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE health_delivery_system;

-- ----------------------------------------------------------------------------
-- 1. USER ACCOUNTS
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_accounts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_name      VARCHAR(150) NOT NULL,
    office_name     VARCHAR(255) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_accounts_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS staff_accounts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    station_slug    VARCHAR(100) NOT NULL,
    station_name    VARCHAR(255) NOT NULL,
    staff_name      VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_staff_accounts_station (station_slug),
    UNIQUE KEY uq_staff_accounts_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS patient_accounts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      VARCHAR(32) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    first_name      VARCHAR(100) NOT NULL,
    middle_name     VARCHAR(100) DEFAULT NULL,
    last_name       VARCHAR(100) NOT NULL,
    birth_date      DATE NOT NULL,
    gender          VARCHAR(30) NOT NULL,
    contact_number  VARCHAR(20) NOT NULL,
    complete_address VARCHAR(255) NOT NULL,
    station_slug    VARCHAR(100) DEFAULT NULL,
    station_name    VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_patient_accounts_patient_id (patient_id),
    UNIQUE KEY uq_patient_accounts_email (email),
    KEY idx_patient_accounts_name (last_name, first_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. PATIENT PROFILE AND HISTORY
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS patient_profiles (
    patient_id      VARCHAR(32) PRIMARY KEY,
    first_name      VARCHAR(100) NOT NULL,
    middle_name     VARCHAR(100) DEFAULT NULL,
    last_name       VARCHAR(100) NOT NULL,
    birth_date      DATE NOT NULL,
    gender          VARCHAR(30) NOT NULL,
    contact_number  VARCHAR(20) NOT NULL,
    email           VARCHAR(150) DEFAULT NULL,
    complete_address VARCHAR(255) NOT NULL,
    photo_path      VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_patient_profiles_name (last_name, first_name),
    KEY idx_patient_profiles_contact (contact_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS patient_info_history (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      VARCHAR(32) NOT NULL,
    field_name      VARCHAR(50) NOT NULL,
    old_value       TEXT DEFAULT NULL,
    new_value       TEXT DEFAULT NULL,
    changed_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_patient_info_history_patient (patient_id),
    KEY idx_patient_info_history_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS patient_update_notifications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      VARCHAR(32) NOT NULL,
    patient_name    VARCHAR(255) NOT NULL,
    field_updated   VARCHAR(50) NOT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_patient_update_notifications_patient (patient_id),
    KEY idx_patient_update_notifications_read (is_read),
    KEY idx_patient_update_notifications_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. APPOINTMENTS AND CLINICAL INFORMATION
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference_code  VARCHAR(32) NOT NULL,
    appointment_code VARCHAR(10) DEFAULT NULL,
    patient_id      VARCHAR(32) DEFAULT NULL,

    -- Health station and service
    station_slug    VARCHAR(100) NOT NULL,
    station_name    VARCHAR(255) NOT NULL,
    service_slug    VARCHAR(100) NOT NULL,
    service_name    VARCHAR(255) NOT NULL,

    -- Patient snapshot
    first_name      VARCHAR(100) NOT NULL,
    middle_name     VARCHAR(100) DEFAULT NULL,
    last_name       VARCHAR(100) NOT NULL,
    birth_date      DATE NOT NULL,
    gender          VARCHAR(30) NOT NULL,
    contact_number  VARCHAR(20) NOT NULL,
    email           VARCHAR(150) DEFAULT NULL,
    complete_address VARCHAR(255) NOT NULL,
    immunization_relationship VARCHAR(100) DEFAULT NULL,

    -- Appointment schedule
    preferred_date  DATE NOT NULL,
    preferred_time  VARCHAR(30) NOT NULL,
    notes           TEXT DEFAULT NULL,

    -- Clinical measurements
    body_temperature VARCHAR(30) DEFAULT NULL,
    pulse_rate      VARCHAR(30) DEFAULT NULL,
    respiration_rate VARCHAR(30) DEFAULT NULL,
    blood_pressure  VARCHAR(30) DEFAULT NULL,
    doctor_notes    TEXT DEFAULT NULL,
    photo_path      VARCHAR(255) DEFAULT NULL,

    status          VARCHAR(30) NOT NULL DEFAULT 'Pending',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_appointments_reference_code (reference_code),
    KEY idx_appointments_patient (patient_id),
    KEY idx_appointments_station_date (station_slug, preferred_date),
    KEY idx_appointments_service (service_slug),
    KEY idx_appointments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS appointment_status_notifications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id  INT UNSIGNED NOT NULL,
    reference_code  VARCHAR(32) NOT NULL,
    patient_id      VARCHAR(32) NOT NULL,
    status          VARCHAR(30) NOT NULL,
    message         TEXT NOT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_appointment_notifications_patient (patient_id),
    KEY idx_appointment_notifications_appointment (appointment_id),
    KEY idx_appointment_notifications_read (is_read),
    CONSTRAINT fk_appointment_status_notifications_appointment
        FOREIGN KEY (appointment_id) REFERENCES appointments(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. STATION CONFIGURATION
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS station_service_assignments (
    station_slug    VARCHAR(100) NOT NULL,
    service_slug    VARCHAR(100) NOT NULL,
    sort_order      INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (station_slug, service_slug),
    KEY idx_station_service_assignments_service (service_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS station_open_hours (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    station_slug    VARCHAR(100) NOT NULL,
    day_of_week     TINYINT UNSIGNED NOT NULL COMMENT '1=Monday ... 7=Sunday',
    is_open         TINYINT(1) NOT NULL DEFAULT 1,
    open_time       TIME DEFAULT NULL,
    close_time      TIME DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_station_open_hours_day (station_slug, day_of_week),
    KEY idx_station_open_hours_station (station_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. EVENTS AND AUDIT LOGS
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS upcoming_events (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    station_slug    VARCHAR(100) NOT NULL,
    station_name    VARCHAR(255) NOT NULL,
    title           VARCHAR(255) NOT NULL,
    description     TEXT NOT NULL,
    event_date      DATE NOT NULL,
    time_label      VARCHAR(100) NOT NULL,
    end_time_label  VARCHAR(100) DEFAULT NULL,
    icon            VARCHAR(50) NOT NULL DEFAULT 'calendar',
    accent          VARCHAR(50) NOT NULL DEFAULT 'mint',
    created_by      VARCHAR(150) DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_upcoming_events_station (station_slug),
    KEY idx_upcoming_events_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_log (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_type       VARCHAR(30) NOT NULL,
    user_name       VARCHAR(150) NOT NULL,
    action          VARCHAR(100) NOT NULL,
    description     TEXT DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_activity_log_created_at (created_at),
    KEY idx_activity_log_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
