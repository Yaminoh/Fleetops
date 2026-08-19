CREATE DATABASE IF NOT EXISTS fleet_management;

USE fleet_management;

CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL,
    type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_name VARCHAR(100) NOT NULL,
    vehicle_type VARCHAR(100) NOT NULL,
    reservation_date DATE NOT NULL,
    duration_days INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NOT NULL,
    score DECIMAL(3,1) NOT NULL,
    dispatch_count INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    detail VARCHAR(255) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO vehicles (name, status, type) VALUES
('SUV Cargo 01', 'Active', 'SUV Cargo'),
('Van Shuttle 02', 'Active', 'Van Shuttle'),
('Executive Sedan 03', 'Maintenance', 'Executive Sedan'),
('Cargo Truck 04', 'Active', 'Cargo Truck');

INSERT INTO reservations (driver_name, vehicle_type, reservation_date, duration_days, status) VALUES
('Joanna Reforsado', 'SUV Cargo', '2026-08-03', 4, 'pending'),
('Daniella Agus', 'Van Shuttle', '2026-08-05', 2, 'approved'),
('Erwin Cober', 'Executive Sedan', '2026-08-07', 1, 'pending');

INSERT INTO drivers (name, role, score, dispatch_count) VALUES
('Joanna Reforsado', 'Senior Driver', 9.8, 124),
('Daniella Agus', 'Regional Courier', 9.4, 111),
('Erwin Cober', 'Executive Driver', 9.2, 98);

INSERT INTO alerts (icon, title, detail) VALUES
('🛠️', 'Scheduled Vios Maintenance due on Aug 02', 'Service bay 3 • 09:00'),
('🗺️', 'Route deviation detected on North Loop', 'Driver Harvey Villarin • 11 mins ago'),
('⚠️', 'Accident report logged for Unit #A17', 'Insurance follow-up pending'),
('⛽', 'Fuel spike noted on Cargo Unit #C08', 'Consumption above threshold');

INSERT INTO users (name, email, password, role, status) VALUES
('Reybie Ruelo', 'reybie@fleetops.com', 'password123', 'Admin', 'active'),
('Maria Santos', 'maria@fleetops.com', 'maria123', 'Manager', 'active'),
('Juan dela Cruz', 'juan@fleetops.com', 'juan123', 'Dispatcher', 'active'),
('Ana Garcia', 'ana@fleetops.com', 'ana123', 'Accountant', 'active'),
('Pedro Reyes', 'pedro@fleetops.com', 'pedro123', 'Admin', 'inactive');
