-- =============================================================================
-- DBA Midterm Project - EXPLAIN ANALYZE Demonstration Script
-- Facility Scheduler - Indexes: Before vs. After
-- =============================================================================
-- Connect as superuser / app_admin before running.

\c facility_scheduler

-- -----------------------------------------------------------------------------
-- 1. Drop composite index temporarily so we can show a Sequential Scan first.
--    (Only if you want to re-demonstrate — production keeps it.)
-- -----------------------------------------------------------------------------
-- DROP INDEX IF EXISTS bookings_facility_id_start_time_end_time_idx;

-- -----------------------------------------------------------------------------
-- 2. Availability lookup (the query our app runs all the time)
-- -----------------------------------------------------------------------------
EXPLAIN ANALYZE
SELECT id, user_id, start_time, end_time, status
FROM bookings
WHERE facility_id = 1
  AND status IN ('pending', 'approved')
  AND start_time < '2026-10-20 18:00:00+08'
  AND end_time   > '2026-10-20 08:00:00+08';

-- EXPECTED (before index)  -> Seq Scan on bookings  (high cost, higher ms)
-- EXPECTED (after  index)  -> Index Scan / Bitmap Heap Scan using the
--                             bookings_facility_id_start_time_end_time_idx
--                             (much lower total cost and execution time)

-- -----------------------------------------------------------------------------
-- 3. Another key query: a user looking at their own bookings (RLS scenario)
-- -----------------------------------------------------------------------------
SET app.user_id = '2';

EXPLAIN ANALYZE
SELECT b.id, f.name AS facility, b.start_time, b.end_time, b.status
FROM bookings b
JOIN facilities f ON f.id = b.facility_id
WHERE b.user_id = current_setting('app.user_id')::int
ORDER BY b.start_time DESC
LIMIT 20;

RESET app.user_id;

-- -----------------------------------------------------------------------------
-- 4. Show which indexes exist for the bookings table
-- -----------------------------------------------------------------------------
SELECT
    indexname,
    indexdef
FROM pg_indexes
WHERE tablename = 'bookings'
ORDER BY indexname;

-- -----------------------------------------------------------------------------
-- 5. Constraint definitions (defense-ready output for CHECK & EXCLUSION)
-- -----------------------------------------------------------------------------
SELECT
    conname,
    contype,
    pg_get_constraintdef(c.oid) AS definition
FROM pg_constraint c
JOIN pg_class t ON t.oid = c.conrelid
WHERE t.relname = 'bookings'
ORDER BY conname;

-- contype legend:
--   c = check constraint
--   f = foreign key
--   p = primary key
--   u = unique
--   x = exclusion constraint (the no_overlap one!)
