-- PostgreSQL Database Schema for Logistics 2 Fleet Management System

CREATE TABLE IF NOT EXISTS vehicles (
    id SERIAL PRIMARY KEY,
    vehicle_code VARCHAR(50) UNIQUE NOT NULL,
    plate_number VARCHAR(20) UNIQUE NOT NULL,
    type VARCHAR(100) NOT NULL DEFAULT 'Cargo Truck',
    status VARCHAR(50) NOT NULL DEFAULT 'Active', -- Active, Idle, Maintenance
    fuel_level NUMERIC(5,2) DEFAULT 100.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS drivers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    employee_id VARCHAR(50) UNIQUE NOT NULL,
    role VARCHAR(100) DEFAULT 'Senior Driver',
    score NUMERIC(3,1) DEFAULT 9.5,
    dispatch_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vehicle_locations (
    id SERIAL PRIMARY KEY,
    vehicle_id INT NOT NULL REFERENCES vehicles(id) ON DELETE CASCADE,
    latitude NUMERIC(10,7) NOT NULL,
    longitude NUMERIC(10,7) NOT NULL,
    speed NUMERIC(5,2) DEFAULT 0.0,
    heading NUMERIC(5,2) DEFAULT 0.0,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trips (
    id SERIAL PRIMARY KEY,
    vehicle_id INT NOT NULL REFERENCES vehicles(id) ON DELETE CASCADE,
    driver_id INT NOT NULL REFERENCES drivers(id) ON DELETE CASCADE,
    origin VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    origin_lat NUMERIC(10,7),
    origin_lng NUMERIC(10,7),
    dest_lat NUMERIC(10,7),
    dest_lng NUMERIC(10,7),
    departure_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estimated_arrival TIMESTAMP,
    actual_arrival TIMESTAMP,
    total_distance NUMERIC(8,2) DEFAULT 0.0, -- KM
    total_duration INT DEFAULT 0, -- Minutes
    fuel_consumption NUMERIC(8,2) DEFAULT 0.0, -- Liters
    status VARCHAR(50) NOT NULL DEFAULT 'Active', -- Active, Completed, Delayed, Critical Delay
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    trip_id INT REFERENCES trips(id) ON DELETE SET NULL,
    vehicle_id INT REFERENCES vehicles(id) ON DELETE SET NULL,
    type VARCHAR(100) NOT NULL, -- Vehicle arrived, Route deviation, Driver offline, Excessive idle time, Traffic delay
    message VARCHAR(255) NOT NULL,
    severity VARCHAR(20) DEFAULT 'info', -- info, warning, danger
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reservations (
    id SERIAL PRIMARY KEY,
    driver_name VARCHAR(100) NOT NULL,
    vehicle_type VARCHAR(100) NOT NULL,
    reservation_date DATE NOT NULL,
    duration_days INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS alerts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    detail VARCHAR(255) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Dispatcher', -- Driver, Dispatcher, Logistics Officer, Admin
    status VARCHAR(20) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
