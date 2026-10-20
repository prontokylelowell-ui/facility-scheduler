-- =============================================================================
-- DBA Midterm Project - Constraint, Trigger & Function Demonstrations
-- =============================================================================
\c facility_scheduler

-- -----------------------------------------------------------------------------
-- 1. CHECK constraint: end_time > start_time
-- -----------------------------------------------------------------------------
INSERT INTO bookings (user_id, facility_id, start_time, end_time, purpose, status)
VALUES (2, 1,
        '2026-10-15 14:00:00+08',
        '2026-10-15 12:00:00+08',  -- end BEFORE start -> should fail
        'Test invalid booking',
        'pending');
-- Expected ERROR:  new row for relation "bookings" violates check constraint "bookings_end_time_check"

-- -----------------------------------------------------------------------------
-- 2. CHECK constraint: valid status list
-- -----------------------------------------------------------------------------
INSERT INTO bookings (user_id, facility_id, start_time, end_time, purpose, status)
VALUES (2, 1,
        '2026-10-15 14:00:00+08',
        '2026-10-15 16:00:00+08',
        'Test invalid status',
        'confirmed');             -- not in our list -> should fail
-- Expected ERROR:  new row for relation "bookings" violates check constraint "bookings_status_check"

-- -----------------------------------------------------------------------------
-- 3. EXCLUSION constraint: prevent double-booking at the DB level
-- -----------------------------------------------------------------------------
-- First, make a valid booking:
INSERT INTO bookings (user_id, facility_id, start_time, end_time, purpose, status)
VALUES (2, 1,
        '2026-10-20 09:00:00+08',
        '2026-10-20 12:00:00+08',
        'Original booking',
        'approved');

-- Now try to double-book OVERLAPPING time on the same facility:
INSERT INTO bookings (user_id, facility_id, start_time, end_time, purpose, status)
VALUES (3, 1,
        '2026-10-20 11:00:00+08',  -- overlaps 11:00-12:00
        '2026-10-20 14:00:00+08',
        'Conflict booking',
        'pending');
-- Expected ERROR:  conflicting key value violates exclusion constraint "no_overlap"

-- Cancelled/rejected bookings DON'T cause conflicts:
UPDATE bookings SET status = 'cancelled' WHERE purpose = 'Original booking';

-- Now the overlapping insert should succeed:
INSERT INTO bookings (user_id, facility_id, start_time, end_time, purpose, status)
VALUES (3, 1,
        '2026-10-20 11:00:00+08',
        '2026-10-20 14:00:00+08',
        'Works now because original is cancelled',
        'pending');

-- Cleanup the demo rows
DELETE FROM bookings WHERE purpose IN (
    'Original booking',
    'Conflict booking',
    'Works now because original is cancelled'
);

-- -----------------------------------------------------------------------------
-- 4. PL/pgSQL function: is_facility_available (same logic as exclusion + blackouts)
-- -----------------------------------------------------------------------------
-- A) Available slot
SELECT is_facility_available(
    1,
    '2026-10-25 08:00:00+08'::timestamptz,
    '2026-10-25 10:00:00+08'::timestamptz
) AS available_slot_1;          -- should be t

-- B) Pass end < start
SELECT is_facility_available(
    1,
    '2026-10-25 14:00:00+08'::timestamptz,
    '2026-10-25 10:00:00+08'::timestamptz
) AS backwards_range;            -- should be f

-- -----------------------------------------------------------------------------
-- 5. Trigger: log_booking_status_change  ->  booking_status_log
-- -----------------------------------------------------------------------------
INSERT INTO bookings (user_id, facility_id, start_time, end_time, purpose, status)
VALUES (2, 2,
        '2026-11-02 10:00:00+08',
        '2026-11-02 13:00:00+08',
        'Trigger demo booking',
        'pending');

-- Status changed automatically: show the log rows
SELECT
    l.booking_id,
    l.old_status,
    l.new_status,
    l.remarks,
    l.changed_at
FROM booking_status_log l
JOIN bookings b ON b.id = l.booking_id
WHERE b.purpose = 'Trigger demo booking'
ORDER BY l.changed_at;

-- Now update status -> log is appended
SET app.user_id = '1';
UPDATE bookings SET status = 'approved', admin_remarks = 'OK by admin'
WHERE purpose = 'Trigger demo booking';
RESET app.user_id;

SELECT
    l.booking_id,
    l.old_status,
    l.new_status,
    l.changed_by_user_id,
    l.remarks
FROM booking_status_log l
JOIN bookings b ON b.id = l.booking_id
WHERE b.purpose = 'Trigger demo booking'
ORDER BY l.changed_at;

-- Cleanup
DELETE FROM bookings WHERE purpose = 'Trigger demo booking';
