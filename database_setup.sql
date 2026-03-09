-- Database Setup Script for Nexus Auth Internship Task
-- You can run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS intern_auth;
USE intern_auth;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- MongoDB note: collection 'users' in db 'intern_profiles' will be auto-created on first insert.
-- Redis note: no predefined schema needed.
