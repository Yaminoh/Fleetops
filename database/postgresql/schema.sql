-- FleetOps: consolidated PostgreSQL schema
-- Supports dashboard analytics, reservations, dispatches, live tracking, costs, and alerts.

CREATE TABLE IF NOT EXISTS users (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Staff', status VARCHAR(20) NOT NULL DEFAULT 'active',
    two_factor_code VARCHAR(255), two_factor_expires_at TIMESTAMP, preferences JSONB,
    remember_token VARCHAR(100), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Laravel infrastructure tables. These are required because the application
-- stores sessions, cache entries, and queued jobs in PostgreSQL.
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY, user_id BIGINT, ip_address VARCHAR(45),
    user_agent TEXT, payload TEXT NOT NULL, last_activity INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY, value TEXT NOT NULL, expiration BIGINT NOT NULL
);

CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) PRIMARY KEY, owner VARCHAR(255) NOT NULL, expiration BIGINT NOT NULL
);

CREATE TABLE IF NOT EXISTS jobs (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL, attempts SMALLINT NOT NULL, reserved_at INTEGER,
    available_at INTEGER NOT NULL, created_at INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS job_batches (
    id VARCHAR(255) PRIMARY KEY, name VARCHAR(255) NOT NULL, total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL, failed_jobs INTEGER NOT NULL, failed_job_ids TEXT NOT NULL,
    options TEXT, cancelled_at INTEGER, created_at INTEGER NOT NULL, finished_at INTEGER
);

CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL, queue TEXT NOT NULL, payload TEXT NOT NULL, exception TEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vehicles (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, vehicle_code VARCHAR(50) NOT NULL UNIQUE,
    plate_number VARCHAR(20) NOT NULL UNIQUE, name VARCHAR(100),
    type VARCHAR(100) NOT NULL DEFAULT 'Cargo Truck', status VARCHAR(50) NOT NULL DEFAULT 'Active',
    fuel_level NUMERIC(5,2) NOT NULL DEFAULT 100.00 CHECK (fuel_level BETWEEN 0 AND 100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS drivers (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    name VARCHAR(100), employee_id VARCHAR(50) NOT NULL UNIQUE, role VARCHAR(100) NOT NULL DEFAULT 'Driver',
    score NUMERIC(5,2) NOT NULL DEFAULT 0, dispatch_count INTEGER NOT NULL DEFAULT 0,
    license_number VARCHAR(100), license_expiry DATE, status VARCHAR(50) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reservations (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, reservation_no VARCHAR(50) NOT NULL UNIQUE,
    employee_id VARCHAR(50) NOT NULL, destination VARCHAR(255), purpose VARCHAR(255), requested_date DATE NOT NULL,
    requested_time TIME, vehicle_type VARCHAR(100), passenger_count INTEGER NOT NULL DEFAULT 1 CHECK (passenger_count > 0),
    remarks TEXT, status VARCHAR(50) NOT NULL DEFAULT 'Pending', approved_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    approved_at TIMESTAMP, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dispatches (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, dispatch_no VARCHAR(50) NOT NULL UNIQUE,
    reservation_id BIGINT REFERENCES reservations(id) ON DELETE SET NULL,
    vehicle_id BIGINT NOT NULL REFERENCES vehicles(id) ON DELETE RESTRICT,
    driver_id BIGINT NOT NULL REFERENCES drivers(id) ON DELETE RESTRICT,
    origin VARCHAR(255), destination VARCHAR(255), origin_lat NUMERIC(10,7), origin_lng NUMERIC(10,7),
    dest_lat NUMERIC(10,7), dest_lng NUMERIC(10,7), priority VARCHAR(20) NOT NULL DEFAULT 'Normal',
    status VARCHAR(50) NOT NULL DEFAULT 'Scheduled', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trip_records (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, dispatch_id BIGINT REFERENCES dispatches(id) ON DELETE CASCADE,
    vehicle_id BIGINT NOT NULL REFERENCES vehicles(id) ON DELETE RESTRICT,
    driver_id BIGINT NOT NULL REFERENCES drivers(id) ON DELETE RESTRICT,
    origin VARCHAR(255), destination VARCHAR(255), origin_lat NUMERIC(10,7), origin_lng NUMERIC(10,7),
    dest_lat NUMERIC(10,7), dest_lng NUMERIC(10,7), departure_time TIMESTAMP, estimated_arrival TIMESTAMP,
    actual_arrival TIMESTAMP, total_distance NUMERIC(8,2), total_duration INTEGER, fuel_consumption NUMERIC(8,2),
    status VARCHAR(50) NOT NULL DEFAULT 'Active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS location_logs (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, vehicle_id BIGINT NOT NULL REFERENCES vehicles(id) ON DELETE CASCADE,
    latitude NUMERIC(10,7) NOT NULL, longitude NUMERIC(10,7) NOT NULL, speed NUMERIC(5,2), fuel_level NUMERIC(5,2),
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS fuel_logs (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, vehicle_id BIGINT NOT NULL REFERENCES vehicles(id) ON DELETE CASCADE,
    dispatch_id BIGINT REFERENCES dispatches(id) ON DELETE SET NULL, liters NUMERIC(8,2) NOT NULL CHECK (liters > 0),
    cost NUMERIC(10,2) NOT NULL DEFAULT 0 CHECK (cost >= 0), logged_at DATE NOT NULL, receipt_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS maintenance_records (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, vehicle_id BIGINT NOT NULL REFERENCES vehicles(id) ON DELETE CASCADE,
    description VARCHAR(150) NOT NULL, cost NUMERIC(10,2) NOT NULL DEFAULT 0 CHECK (cost >= 0), serviced_at DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS alerts (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, vehicle_id BIGINT REFERENCES vehicles(id) ON DELETE SET NULL,
    trip_record_id BIGINT REFERENCES trip_records(id) ON DELETE SET NULL, icon VARCHAR(50) NOT NULL DEFAULT 'ℹ️',
    title VARCHAR(255), detail TEXT, type VARCHAR(100), message TEXT, severity VARCHAR(20) NOT NULL DEFAULT 'info',
    read_at TIMESTAMP, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dispatch_logs (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, dispatch_id BIGINT NOT NULL REFERENCES dispatches(id) ON DELETE CASCADE,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL, action VARCHAR(255) NOT NULL, remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS vehicles_status_index ON vehicles(status);
CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions(user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON sessions(last_activity);
CREATE INDEX IF NOT EXISTS jobs_queue_index ON jobs(queue);
CREATE INDEX IF NOT EXISTS drivers_status_index ON drivers(status);
CREATE INDEX IF NOT EXISTS reservations_status_date_index ON reservations(status, requested_date);
CREATE INDEX IF NOT EXISTS dispatches_status_index ON dispatches(status);
CREATE INDEX IF NOT EXISTS trip_records_status_index ON trip_records(status);
CREATE INDEX IF NOT EXISTS location_logs_vehicle_timestamp_index ON location_logs(vehicle_id, timestamp DESC);
CREATE INDEX IF NOT EXISTS fuel_logs_vehicle_logged_at_index ON fuel_logs(vehicle_id, logged_at DESC);
CREATE INDEX IF NOT EXISTS maintenance_records_vehicle_serviced_at_index ON maintenance_records(vehicle_id, serviced_at DESC);
CREATE INDEX IF NOT EXISTS alerts_read_at_index ON alerts(read_at);
