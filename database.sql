-- ============================================================================
-- Healthcare Portal Database Schema
-- Covid Vaccination Management System
-- ============================================================================
-- This SQL file can be imported directly into MySQL/MariaDB
-- Run this before running Laravel migrations if you want to set up manually
-- ============================================================================

-- Create database
CREATE DATABASE IF NOT EXISTS healthcare_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE healthcare_portal;

-- ============================================================================
-- PATIENTS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS patients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    address VARCHAR(500) NOT NULL,
    city VARCHAR(100) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_patients_email (email),
    INDEX idx_patients_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PATIENT PASSWORD RESET TOKENS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS patient_password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- HOSPITALS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS hospitals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(500) NOT NULL,
    city VARCHAR(100) NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_hospitals_email (email),
    INDEX idx_hospitals_city (city),
    INDEX idx_hospitals_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- HOSPITAL PASSWORD RESET TOKENS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS hospital_password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ADMINS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admins_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ADMIN PASSWORD RESET TOKENS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS admin_password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- VACCINES TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS vaccines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    manufacturer VARCHAR(255) NOT NULL,
    doses_required TINYINT UNSIGNED NOT NULL DEFAULT 2,
    available BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vaccines_available (available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- APPOINTMENTS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS appointments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    hospital_id BIGINT UNSIGNED NOT NULL,
    purpose ENUM('Covid Test', 'Vaccination') NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    INDEX idx_appointments_patient_date (patient_id, date),
    INDEX idx_appointments_hospital_date (hospital_id, date),
    INDEX idx_appointments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- COVID TESTS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS covid_tests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    hospital_id BIGINT UNSIGNED NOT NULL,
    result ENUM('Positive', 'Negative', 'Pending') NOT NULL DEFAULT 'Pending',
    test_date DATE NOT NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    INDEX idx_covid_tests_patient (patient_id),
    INDEX idx_covid_tests_hospital (hospital_id),
    INDEX idx_covid_tests_result (result)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- VACCINATIONS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS vaccinations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    hospital_id BIGINT UNSIGNED NOT NULL,
    vaccine_id BIGINT UNSIGNED NOT NULL,
    dose_number TINYINT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('Completed', 'Scheduled', 'Cancelled') NOT NULL DEFAULT 'Scheduled',
    vaccination_date DATE NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    FOREIGN KEY (vaccine_id) REFERENCES vaccines(id) ON DELETE CASCADE,
    INDEX idx_vaccinations_patient (patient_id),
    INDEX idx_vaccinations_hospital (hospital_id),
    INDEX idx_vaccinations_vaccine (vaccine_id),
    INDEX idx_vaccinations_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PERSONAL ACCESS TOKENS TABLE (Laravel Sanctum)
-- ============================================================================
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_personal_access_tokens_tokenable (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- MIGRATIONS TABLE (Laravel)
-- ============================================================================
CREATE TABLE IF NOT EXISTS migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA: ADMIN
-- ============================================================================
-- Password: admin123 (bcrypt hashed)
INSERT INTO admins (username, email, password, created_at, updated_at) VALUES
('admin', 'admin@system.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4oaAjVgjBZ9V.YOu', NOW(), NOW());

-- ============================================================================
-- SEED DATA: VACCINES
-- ============================================================================
INSERT INTO vaccines (name, manufacturer, doses_required, available, created_at, updated_at) VALUES
('Covishield', 'Serum Institute of India', 2, TRUE, NOW(), NOW()),
('Covaxin', 'Bharat Biotech', 2, TRUE, NOW(), NOW()),
('Sputnik V', 'Gamaleya Research Institute', 2, FALSE, NOW(), NOW()),
('Pfizer-BioNTech', 'Pfizer Inc.', 2, TRUE, NOW(), NOW());

-- ============================================================================
-- SEED DATA: HOSPITALS
-- ============================================================================
-- Password: demo123 (bcrypt hashed)
INSERT INTO hospitals (name, email, password, phone, address, city, status, created_at, updated_at) VALUES
('City Medical Center', 'city@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '022-12345678', '100 Healthcare Avenue', 'Mumbai', 'Approved', NOW(), NOW()),
('Apollo Hospital', 'apollo@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '011-23456789', '200 Medical Complex', 'Delhi', 'Approved', NOW(), NOW()),
('Fortis Healthcare', 'fortis@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '080-34567890', '300 Hospital Road', 'Bangalore', 'Approved', NOW(), NOW()),
('Max Hospital', 'max@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '044-45678901', '400 Health Street', 'Chennai', 'Pending', NOW(), NOW()),
('Narayana Health', 'narayana@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '040-56789012', '500 Care Center', 'Hyderabad', 'Pending', NOW(), NOW());

-- ============================================================================
-- SEED DATA: PATIENTS
-- ============================================================================
-- Password: demo123 (bcrypt hashed)
INSERT INTO patients (name, email, password, mobile, dob, gender, address, city, created_at, updated_at) VALUES
('Rahul Sharma', 'rahul@demo.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', '1990-05-15', 'Male', '123 MG Road', 'Mumbai', NOW(), NOW()),
('Priya Patel', 'priya@demo.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543211', '1988-08-22', 'Female', '456 Park Street', 'Delhi', NOW(), NOW()),
('Amit Kumar', 'amit@demo.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543212', '1995-03-10', 'Male', '789 Lake View', 'Bangalore', NOW(), NOW());

-- ============================================================================
-- SEED DATA: APPOINTMENTS
-- ============================================================================
INSERT INTO appointments (patient_id, hospital_id, purpose, date, time, status, created_at, updated_at) VALUES
(1, 1, 'Covid Test', '2024-02-15', '10:00:00', 'Approved', NOW(), NOW()),
(1, 1, 'Vaccination', '2024-02-20', '11:00:00', 'Approved', NOW(), NOW()),
(2, 2, 'Vaccination', '2024-02-18', '14:00:00', 'Pending', NOW(), NOW()),
(3, 3, 'Covid Test', '2024-02-22', '09:00:00', 'Rejected', NOW(), NOW());

-- ============================================================================
-- SEED DATA: COVID TESTS
-- ============================================================================
INSERT INTO covid_tests (appointment_id, patient_id, hospital_id, result, test_date, remarks, created_at, updated_at) VALUES
(1, 1, 1, 'Negative', '2024-02-15', 'RT-PCR test conducted. Results negative.', NOW(), NOW());

-- ============================================================================
-- SEED DATA: VACCINATIONS
-- ============================================================================
INSERT INTO vaccinations (appointment_id, patient_id, hospital_id, vaccine_id, dose_number, status, vaccination_date, created_at, updated_at) VALUES
(2, 1, 1, 1, 1, 'Completed', '2024-02-20', NOW(), NOW());

-- ============================================================================
-- DEMO CREDENTIALS
-- ============================================================================
-- Patient: rahul@demo.com / demo123
-- Hospital: city@hospital.com / demo123
-- Admin: admin / admin123
-- ============================================================================
