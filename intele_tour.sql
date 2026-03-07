-- Intele Tour Booking System Database
CREATE DATABASE IF NOT EXISTS intele_tour;
USE intele_tour;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    mobile VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Packages Table
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50),
    image VARCHAR(255),
    available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    booking_date DATE NOT NULL,
    travelers INT DEFAULT 1,
    total_price DECIMAL(10,2),
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- Payments Table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    payment_method ENUM('credit_card','debit_card','upi','net_banking') NOT NULL,
    payment_status ENUM('pending','completed','failed') DEFAULT 'pending',
    transaction_id VARCHAR(100) UNIQUE,
    amount DECIMAL(10,2),
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Admin Table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Sample Packages
INSERT INTO packages (destination, description, price, duration, image) VALUES
('Paris', 'Experience the city of love with guided tours of the Eiffel Tower, Louvre Museum, and Seine River cruise.', 85000.00, '7 Days / 6 Nights', 'images/paris.jpg'),
('Tokyo', 'Explore the vibrant capital of Japan with visits to Shibuya, Mount Fuji, and authentic Japanese culture.', 95000.00, '8 Days / 7 Nights', 'images/tokyo.jpg'),
('Bali', 'Relax on the beautiful island of Bali with beach stays, temple tours, and rice terrace treks.', 65000.00, '6 Days / 5 Nights', 'images/bali.jpg'),
('Dubai', 'Discover the luxury of Dubai with desert safari, Burj Khalifa visit, and Dubai Mall shopping.', 75000.00, '5 Days / 4 Nights', 'images/dubai.jpg');

-- Default Admin (password: admin123)
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
