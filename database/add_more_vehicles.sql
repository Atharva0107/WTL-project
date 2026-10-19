-- PURPOSE: Adds sample vehicles only when they are not already present.
-- Run SQL only in the intended local vehicle_rental database.

USE vehicle_rental;

-- Safe to run on an existing database. It adds only missing sample vehicles.
INSERT INTO vehicles(name,category,daily_rate,available)
SELECT 'Premium Sedan','Premium',3200,TRUE
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE name='Premium Sedan');

INSERT INTO vehicles(name,category,daily_rate,available)
SELECT 'Family MPV','MPV',3000,TRUE
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE name='Family MPV');

INSERT INTO vehicles(name,category,daily_rate,available)
SELECT 'Compact Crossover','Crossover',2700,TRUE
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE name='Compact Crossover');
