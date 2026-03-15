-- Database: job_portal
CREATE DATABASE IF NOT EXISTS job_portal;
USE job_portal;

-- Users table (job seekers, employers, admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('seeker','employer','admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seekers profile table
CREATE TABLE seekers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    headline VARCHAR(255),
    skills TEXT,
    resume_path VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Employers profile table
CREATE TABLE employers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Jobs table
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    salary VARCHAR(50),
    deadline DATE,
    experience INT,
    category VARCHAR(100),        
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE
);

-- Applications table
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    seeker_id INT NOT NULL,
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id) REFERENCES seekers(id) ON DELETE CASCADE
);
