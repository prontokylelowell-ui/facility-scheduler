-- =============================================================================
-- DBA Midterm Project - Backup / Restore Proof Script
-- Logical backup  vs  Physical backup  (defense talking points at the bottom)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- STEP 1 — Snapshot of "before" row counts (source DB = facility_scheduler)
-- -----------------------------------------------------------------------------
\c facility_scheduler
CREATE TEMP TABLE pre_restore_counts AS
SELECT 'users'               AS tbl, COUNT(*)::bigint AS n FROM users
UNION ALL SELECT 'roles',               COUNT(*) FROM roles
UNION ALL SELECT 'facility_types',      COUNT(*) FROM facility_types
UNION ALL SELECT 'facilities',          COUNT(*) FROM facilities
UNION ALL SELECT 'operating_hours',     COUNT(*) FROM operating_hours
UNION ALL SELECT 'blackout_dates',      COUNT(*) FROM blackout_dates
UNION ALL SELECT 'bookings',            COUNT(*) FROM bookings
UNION ALL SELECT 'booking_status_log',  COUNT(*) FROM booking_status_log;

SELECT 'BEFORE RESTORE (source)' AS when_snapshot, tbl, n FROM pre_restore_counts ORDER BY tbl;

-- -----------------------------------------------------------------------------
-- STEP 2 — Run the backup script OUTSIDE psql (in a shell):
--
--   Bash:
--     chmod +x backup.sh
--     DB_USER=postgres DB_NAME=facility_scheduler ./backup.sh
--
--   Windows:
--     set DB_USER=postgres
--     set DB_NAME=facility_scheduler
--     backup.bat
--
--   Or manual pg_dump (example):
--     pg_dump -h 127.0.0.1 -p 5432 -U postgres -Fc -Z9 -v \
--         -f backups/facility_scheduler_manual.dump facility_scheduler
-- -----------------------------------------------------------------------------

-- -----------------------------------------------------------------------------
-- STEP 3 — Create a restore target DB and restore into it
--
--   (Run these outside this script, in psql as superuser, or via pgAdmin.)
--
--   createdb -h 127.0.0.1 -p 5432 -U postgres test_restore
--   pg_restore -h 127.0.0.1 -p 5432 -U postgres -d test_restore -v \
--       backups/facility_scheduler_<timestamp>.dump
-- -----------------------------------------------------------------------------

-- -----------------------------------------------------------------------------
-- STEP 4 — Connect to the RESTORED DB and compare row counts
-- -----------------------------------------------------------------------------
-- \c test_restore
--
-- CREATE TEMP TABLE post_restore_counts AS
-- SELECT 'users'               AS tbl, COUNT(*)::bigint AS n FROM users
-- UNION ALL SELECT 'roles',               COUNT(*) FROM roles
-- UNION ALL SELECT 'facility_types',      COUNT(*) FROM facility_types
-- UNION ALL SELECT 'facilities',          COUNT(*) FROM facilities
-- UNION ALL SELECT 'operating_hours',     COUNT(*) FROM operating_hours
-- UNION ALL SELECT 'blackout_dates',      COUNT(*) FROM blackout_dates
-- UNION ALL SELECT 'bookings',            COUNT(*) FROM bookings
-- UNION ALL SELECT 'booking_status_log',  COUNT(*) FROM booking_status_log;
--
-- SELECT 'AFTER RESTORE (test_restore)' AS when_snapshot, tbl, n FROM post_restore_counts ORDER BY tbl;
--
-- EXPECTED: every `n` matches pre_restore_counts exactly.

-- -----------------------------------------------------------------------------
-- STEP 5 — (Optional) Cleanup the restore DB after the demo
--
--   DROP DATABASE test_restore WITH (FORCE);
-- -----------------------------------------------------------------------------

/* =============================================================================
   DEFENSE TALKING POINTS — Logical vs. Physical backups
   =============================================================================

   Logical backups (what backup.sh does — pg_dump / pg_restore):
     • Produces a portable, DB-version-friendly file (works between PG versions
       and different operating systems).
     • Lets you restore individual objects (just one table, schema, or row).
     • Slower for multi-TB databases.
     • Great for the midterm because the demo is small and easy to re-prove
       by showing matching row counts in a brand-new DB.

   Physical backups (pg_basebackup, WAL archiving / PITR):
     • Byte-for-byte copy of the data directory.
     • MUCH faster to restore for big DBs — just replace the directory.
     • Same OS and PG major version required; can't extract a single table.
     • Can do Point-In-Time Recovery (PITR) by replaying WAL files.
     • Good for production disaster recovery, not for a class demo.

   Why we chose pg_dump -Fc (custom format) specifically:
     • Compressed on disk (smaller file).
     • pg_restore supports parallel restores (-j 4).
     • You can list contents (pg_restore -l) and pick objects.
     • Default pg_dump (SQL text) doesn't do that as neatly.
*/
