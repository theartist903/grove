CREATE DATABASE IF NOT EXISTS thegrove CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE thegrove;

CREATE TABLE IF NOT EXISTS registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    child_name VARCHAR(150) NOT NULL,
    child_dob DATE NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    birth_certificate ENUM('Available', 'Pending', 'N/A') NOT NULL,

    parent1_name VARCHAR(150) NOT NULL,
    parent1_mobile VARCHAR(30) NOT NULL,
    parent1_email VARCHAR(150) NOT NULL,
    parent2_name VARCHAR(150) NULL,
    parent2_mobile VARCHAR(30) NULL,
    parent2_email VARCHAR(150) NULL,

    home_address TEXT NOT NULL,
    child_lives_with ENUM('Both parents', 'Mother', 'Father', 'Other') NOT NULL,

    first_language VARCHAR(100) NULL,
    religion VARCHAR(100) NULL,

    has_allergies ENUM('None', 'Yes') NOT NULL DEFAULT 'None',
    allergies_detail TEXT NULL,
    has_medical_condition ENUM('None', 'Yes') NOT NULL DEFAULT 'None',
    medical_detail TEXT NULL,
    has_additional_needs ENUM('No', 'Yes') NOT NULL DEFAULT 'No',
    additional_needs_detail TEXT NULL,
    has_dietary_requirements ENUM('None', 'Yes') NOT NULL DEFAULT 'None',
    dietary_detail TEXT NULL,

    package ENUM('Full Day 7:30-5:30', '8 Hours', '6 Hours', '4 Hours') NOT NULL,
    days_required VARCHAR(100) NOT NULL COMMENT 'Comma separated: Mon,Tue,Wed,Thu,Fri,Sat',

    collector1_name VARCHAR(150) NULL,
    collector1_relationship VARCHAR(100) NULL,
    collector1_mobile VARCHAR(30) NULL,
    collector1_regular TINYINT(1) NOT NULL DEFAULT 0,

    collector2_name VARCHAR(150) NULL,
    collector2_relationship VARCHAR(100) NULL,
    collector2_mobile VARCHAR(30) NULL,
    collector2_regular TINYINT(1) NOT NULL DEFAULT 0,

    collector3_name VARCHAR(150) NULL,
    collector3_relationship VARCHAR(100) NULL,
    collector3_mobile VARCHAR(30) NULL,
    collector3_regular TINYINT(1) NOT NULL DEFAULT 0,

    emergency_name VARCHAR(150) NOT NULL,
    emergency_relationship VARCHAR(100) NOT NULL,
    emergency_mobile VARCHAR(30) NOT NULL,

    has_important_info ENUM('None', 'Yes') NOT NULL DEFAULT 'None',
    important_info_detail TEXT NULL,

    consent_first_aid TINYINT(1) NOT NULL DEFAULT 0,
    consent_activities TINYINT(1) NOT NULL DEFAULT 0,
    consent_communication TINYINT(1) NOT NULL DEFAULT 0,
    consent_photos TINYINT(1) NOT NULL DEFAULT 0,

    signed_name VARCHAR(150) NOT NULL,
    signed_date DATE NOT NULL,

    status ENUM('Under Review', 'Approved', 'Rejected') NOT NULL DEFAULT 'Under Review',
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
