-- PURPOSE: Creates the vehicle_rental database tables and inserts the six sample vehicles.
-- Run SQL only in the intended local vehicle_rental database.

CREATE DATABASE IF NOT EXISTS vehicle_rental;
USE vehicle_rental;

CREATE TABLE IF NOT EXISTS vehicles (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(80) NOT NULL,
 category VARCHAR(40) NOT NULL,
 daily_rate DECIMAL(10,2) NOT NULL,
 available BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS bookings (
 id INT AUTO_INCREMENT PRIMARY KEY,
 customer_name VARCHAR(100) NOT NULL,
 phone VARCHAR(20) NOT NULL,
 vehicle_id INT NOT NULL,
 days INT NOT NULL,
 total DECIMAL(10,2) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

CREATE TABLE IF NOT EXISTS enquiries (
 id INT AUTO_INCREMENT PRIMARY KEY,
 customer_name VARCHAR(100) NOT NULL,
 phone VARCHAR(20) NOT NULL,
 message TEXT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO vehicles(name,category,daily_rate,available)
SELECT 'City Hatchback','Hatchback',1800,TRUE
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE name='City Hatchback');

INSERT INTO vehicles(name,category,daily_rate,available)
SELECT 'Family Sedan','Sedan',2500,TRUE
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE name='Family Sedan');

INSERT INTO vehicles(name,category,daily_rate,available)
SELECT 'Adventure SUV','SUV',3500,TRUE
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE name='Adventure SUV');


INSERT INTO vehicles(name,category,daily_rate,available)
SELECT 'Premium Sedan','Premium',3200,TRUE
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE name='Premium Sedan');

INSERT INTO vehicles(name,category,daily_rate,available)
SELECT 'Family MPV','MPV',3000,TRUE
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE name='Family MPV');

INSERT INTO vehicles(name,category,daily_rate,available)
SELECT 'Compact Crossover','Crossover',2700,TRUE
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE name='Compact Crossover');
