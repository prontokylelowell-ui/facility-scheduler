-- =============================================================================
-- DBA Midterm Project - Roles, Privileges & RLS Demonstration
-- =============================================================================
-- Run this script as the PostgreSQL superuser (postgres) so you can SET ROLE.
-- app_admin / app_user / report_viewer are LOGIN roles created by migration 11,
-- each with a different privilege set.
\c facility_scheduler

-- -----------------------------------------------------------------------------
-- 1. DEMO: app_user sees only their own bookings (RLS in action).
-- -----------------------------------------------------------------------------
SET ROLE app_user;
SET app.user_id = '3';    -- simulate Laravel middleware after user login

SELECT count(*) AS bookings_user_3_sees FROM bookings;   -- only user 3's rows

-- Try to INSERT a booking for a DIFFERENT user  -> RLS WITH CHECK blocks it
INSERT INTO bookings (user_id, facility_id, start_time, end_time, purpose, status)
VALUES (99, 1,
        '2026-12-01 10:00:00+08',
        '2026-12-01 12:00:00+08',
        'Malicious insert',
        'pending');
-- Expected ERROR:  new row violates row-level security policy for table "bookings"

RESET ROLE;
RESET app.user_id;

-- -----------------------------------------------------------------------------
-- 2. DEMO: app_admin sees everything and can approve/reject.
-- -----------------------------------------------------------------------------
SET ROLE app_admin;
SET app.user_id = '1';

SELECT count(*) AS bookings_admin_sees FROM bookings;   -- should be the total count

UPDATE bookings
SET status = 'approved', admin_remarks = 'Admin test approval'
WHERE id = (SELECT id FROM bookings WHERE status = 'pending' LIMIT 1);
-- Should succeed (admin RLS policy USING (true) WITH CHECK (true))

RESET ROLE;
RESET app.user_id;

-- -----------------------------------------------------------------------------
-- 3. DEMO: report_viewer is read-only (SELECT on views only) -> writes denied.
-- -----------------------------------------------------------------------------
SET ROLE report_viewer;

SELECT * FROM report_bookings_summary LIMIT 5;     -- succeeds (view allowed)
SELECT * FROM report_user_booking_history LIMIT 5; -- succeeds (view allowed)

INSERT INTO bookings (user_id, facility_id, start_time, end_time, purpose, status)
VALUES (2, 1, '2026-12-02 10:00:00+08', '2026-12-02 12:00:00+08', 'X', 'pending');
-- Expected ERROR:  permission denied for table bookings (report_viewer has SELECT only)

RESET ROLE;

-- -----------------------------------------------------------------------------
-- 5. Defense helper: show actual grants per role
-- -----------------------------------------------------------------------------
SELECT
    grantee,
    table_schema,
    table_name,
    string_agg(privilege_type, ', ' ORDER BY privilege_type) AS privileges
FROM information_schema.role_table_grants
WHERE grantee IN ('app_admin', 'app_user', 'report_viewer')
GROUP BY grantee, table_schema, table_name
ORDER BY grantee, table_name;

-- -----------------------------------------------------------------------------
-- 6. Defense helper: show RLS policies
-- -----------------------------------------------------------------------------
SELECT
    schemaname,
    tablename,
    policyname,
    permissive,
    roles,
    cmd,
    qual,
    with_check
FROM pg_policies
WHERE schemaname = 'public'
ORDER BY tablename, policyname;
