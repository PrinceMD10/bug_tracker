-- Create database
CREATE DATABASE IF NOT EXISTS bug_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bug_tracker;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Tickets table
CREATE TABLE tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    priority TINYINT NOT NULL DEFAULT 1 COMMENT '0=Low, 1=Standard, 2=High',
    status TINYINT NOT NULL DEFAULT 0 COMMENT '0=Open, 1=In Progress, 2=Closed',
    created_by INT NOT NULL,
    assigned_to INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Insert categories
INSERT INTO categories (title) VALUES
('Front-end'),
('Back-end'),
('Infrastructure');

-- Insert default admin user
-- Password: 111111
INSERT INTO users (name, email, password) VALUES
('Admin User', 'prince@bugtracker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');


-- Insert sample tickets (10 tickets with different statuses)
INSERT INTO tickets (title, category_id, priority, status, created_by, assigned_to, created_at, resolved_at) VALUES
('Login button not responding on mobile', 1, 2, 0, 1, NULL, '2025-12-10 09:00:00', NULL),
('API endpoint returns 500 error', 2, 2, 1, 1, 1, '2025-12-11 10:30:00', NULL),
('Server crashes during peak hours', 3, 2, 1, 1, 1, '2025-12-12 14:00:00', NULL),
('Navigation menu misaligned on mobile', 1, 1, 0, 1, NULL, '2025-12-13 11:00:00', NULL),
('Database connection timeout issue', 2, 2, 0, 1, NULL, '2025-12-14 08:00:00', NULL),
('Footer links broken after update', 1, 0, 2, 1, 1, '2025-12-08 16:00:00', '2025-12-09 10:00:00'),
('Slow query performance on reports', 2, 1, 2, 1, 1, '2025-12-07 13:00:00', '2025-12-08 15:00:00'),
('SSL certificate expired warning', 3, 2, 2, 1, 1, '2025-12-06 09:00:00', '2025-12-06 18:00:00'),
('Color contrast accessibility issue', 1, 0, 1, 1, 1, '2025-12-15 10:00:00', NULL),
('Load balancer configuration error', 3, 1, 0, 1, NULL, '2025-12-16 12:00:00', NULL);