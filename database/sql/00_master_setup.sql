-- =============================================================================
-- FACILITY SCHEDULER — STANDALONE DB SETUP SCRIPT
-- Use this if you want to demo the database WITHOUT running `php artisan migrate`.
-- Run as postgres superuser in psql:   \i database/sql/00_master_setup.sql
-- =============================================================================

-- 0. Fresh database
DROP DATABASE IF EXISTS facility_scheduler;
CREATE DATABASE facility_scheduler;
\c facility_scheduler

CREATE EXTENSION IF NOT EXISTS btree_gist;
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- =============================================================================
-- 1. TABLES
-- =============================================================================
CREATE TABLE roles (
    id           SERIAL PRIMARY KEY,
    name         VARCHAR(50) UNIQUE NOT NULL,
    description  TEXT,
    created_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE facility_types (
    id           SERIAL PRIMARY KEY,
    name         VARCHAR(100) UNIQUE NOT NULL,
    description  TEXT,
    created_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id           SERIAL PRIMARY KEY,
    first_name   VARCHAR(50)  NOT NULL,
    last_name    VARCHAR(50)  NOT NULL,
    email        VARCHAR(100) UNIQUE NOT NULL,
    password     VARCHAR(255) NOT NULL,
    role_id      INTEGER NOT NULL REFERENCES roles(id) ON DELETE RESTRICT,
    created_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE facilities (
    id               SERIAL PRIMARY KEY,
    name             VARCHAR(150) UNIQUE NOT NULL,
    facility_type_id INTEGER NOT NULL REFERENCES facility_types(id) ON DELETE RESTRICT,
    location         VARCHAR(200) NOT NULL,
    capacity         INTEGER NOT NULL CHECK (capacity > 0),
    notes            TEXT,
    is_active        BOOLEAN DEFAULT TRUE,
    created_at       TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE operating_hours (
    id           SERIAL PRIMARY KEY,
    facility_id  INTEGER NOT NULL REFERENCES facilities(id) ON DELETE CASCADE,
    day_of_week  SMALLINT NOT NULL CHECK (day_of_week BETWEEN 0 AND 6),
    open_time    TIME NOT NULL,
    close_time   TIME NOT NULL CHECK (close_time > open_time),
    created_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (facility_id, day_of_week)
);

CREATE TABLE blackout_dates (
    id             SERIAL PRIMARY KEY,
    facility_id    INTEGER REFERENCES facilities(id) ON DELETE CASCADE,
    blackout_date  DATE NOT NULL,
    reason         VARCHAR(200),
    is_global      BOOLEAN DEFAULT FALSE,
    created_at     TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (facility_id, blackout_date)
);

CREATE TABLE bookings (
    id             BIGSERIAL PRIMARY KEY,
    user_id        INTEGER NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    facility_id    INTEGER NOT NULL REFERENCES facilities(id) ON DELETE RESTRICT,
    start_time     TIMESTAMPTZ NOT NULL,
    end_time       TIMESTAMPTZ NOT NULL,
    purpose        VARCHAR(255) NOT NULL,
    status         VARCHAR(20) NOT NULL DEFAULT 'pending',
    admin_remarks  TEXT,
    created_at     TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT bookings_end_time_check CHECK (end_time > start_time),
    CONSTRAINT bookings_status_check   CHECK (status IN ('pending','approved','rejected','cancelled'))
);

CREATE INDEX bookings_facility_start_end_idx
    ON bookings (facility_id, start_time, end_time);
CREATE INDEX bookings_user_status_idx   ON bookings (user_id, status);
CREATE INDEX bookings_status_start_idx  ON bookings (status, start_time);

ALTER TABLE bookings ADD CONSTRAINT no_overlap
    EXCLUDE USING gist (
        facility_id WITH =,
        tstzrange(start_time, end_time) WITH &&
    ) WHERE (status IN ('pending','approved'));

CREATE TABLE booking_status_log (
    id                 BIGSERIAL PRIMARY KEY,
    booking_id         BIGINT NOT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    old_status         VARCHAR(20),
    new_status         VARCHAR(20) NOT NULL,
    changed_by_user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    remarks            TEXT,
    changed_at         TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX booking_status_log_booking_time_idx ON booking_status_log (booking_id, changed_at);
CREATE INDEX booking_status_log_status_time_idx  ON booking_status_log (new_status, changed_at);

-- =============================================================================
-- 2. PL/pgSQL FUNCTION + TRIGGER
-- =============================================================================
CREATE OR REPLACE FUNCTION is_facility_available(
    p_facility_id INTEGER,
    p_start_time  TIMESTAMPTZ,
    p_end_time    TIMESTAMPTZ,
    p_exclude_booking_id INTEGER DEFAULT NULL
) RETURNS BOOLEAN AS $$
DECLARE
    v_count INTEGER;
BEGIN
    IF p_end_time <= p_start_time THEN RETURN FALSE; END IF;

    SELECT COUNT(*) INTO v_count
    FROM bookings
    WHERE facility_id = p_facility_id
      AND status IN ('pending','approved')
      AND tstzrange(start_time, end_time) && tstzrange(p_start_time, p_end_time)
      AND (p_exclude_booking_id IS NULL OR id <> p_exclude_booking_id);

    IF v_count > 0 THEN RETURN FALSE; END IF;

    IF EXISTS (
        SELECT 1 FROM blackout_dates
        WHERE (facility_id = p_facility_id OR is_global = TRUE)
          AND blackout_date BETWEEN p_start_time::DATE AND p_end_time::DATE
    ) THEN RETURN FALSE; END IF;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql STABLE SECURITY DEFINER SET search_path = public;

CREATE OR REPLACE FUNCTION log_booking_status_change()
RETURNS TRIGGER AS $$
DECLARE
    v_changed_by INTEGER;
BEGIN
    BEGIN
        v_changed_by := current_setting('app.user_id')::INTEGER;
    EXCEPTION WHEN OTHERS THEN
        v_changed_by := NULL;
    END;

    IF TG_OP = 'INSERT' THEN
        INSERT INTO booking_status_log
            (booking_id, old_status, new_status, changed_by_user_id, remarks)
        VALUES (NEW.id, NULL, NEW.status, v_changed_by, 'Booking created');
    ELSIF TG_OP = 'UPDATE' AND OLD.status IS DISTINCT FROM NEW.status THEN
        INSERT INTO booking_status_log
            (booking_id, old_status, new_status, changed_by_user_id, remarks)
        VALUES (NEW.id, OLD.status, NEW.status, v_changed_by,
                'Status changed from ' || OLD.status || ' to ' || NEW.status);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER SET search_path = public;

CREATE TRIGGER trg_booking_status_change
AFTER INSERT OR UPDATE OF status ON bookings
FOR EACH ROW EXECUTE FUNCTION log_booking_status_change();

-- =============================================================================
-- 3. ROLES, PRIVILEGES, RLS POLICIES
-- =============================================================================
DO $$ BEGIN
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'app_admin') THEN
        CREATE ROLE app_admin LOGIN PASSWORD 'app_admin_pass_2026';
    END IF;
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'app_user') THEN
        CREATE ROLE app_user LOGIN PASSWORD 'app_user_pass_2026';
    END IF;
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'report_viewer') THEN
        CREATE ROLE report_viewer LOGIN PASSWORD 'report_viewer_pass_2026';
    END IF;
END $$;

GRANT CONNECT ON DATABASE facility_scheduler TO app_admin, app_user, report_viewer;
GRANT USAGE   ON SCHEMA public           TO app_admin, app_user, report_viewer;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public            TO app_admin;
GRANT SELECT, USAGE ON ALL SEQUENCES IN SCHEMA public TO app_user;

GRANT ALL       ON roles, users, facility_types, facilities, operating_hours,
                  blackout_dates, bookings, booking_status_log               TO app_admin;
GRANT SELECT    ON facility_types, facilities, operating_hours, blackout_dates,
                  roles, users                                               TO app_user;
GRANT SELECT, INSERT, UPDATE ON bookings                                    TO app_user;
GRANT INSERT    ON users                                                    TO app_user;  -- registration
GRANT SELECT    ON booking_status_log                                       TO app_user;
GRANT SELECT    ON ALL TABLES IN SCHEMA public                              TO report_viewer;

-- RLS is enabled only on bookings / booking_status_log.
-- It is NOT enabled on `users`, because authentication must read the user row
-- by email *before* app.user_id is known (chicken-and-egg on login).
ALTER TABLE bookings           ENABLE ROW LEVEL SECURITY;
ALTER TABLE booking_status_log ENABLE ROW LEVEL SECURITY;

CREATE POLICY own_bookings_select ON bookings FOR SELECT
  USING (current_user = 'app_admin' OR user_id = current_setting('app.user_id', true)::INTEGER);

CREATE POLICY own_bookings_insert ON bookings FOR INSERT
  WITH CHECK (current_user = 'app_admin' OR user_id = current_setting('app.user_id', true)::INTEGER);

CREATE POLICY own_bookings_update ON bookings FOR UPDATE
  USING (current_user = 'app_admin' OR user_id = current_setting('app.user_id', true)::INTEGER)
  WITH CHECK (current_user = 'app_admin' OR user_id = current_setting('app.user_id', true)::INTEGER);

CREATE POLICY admin_all_tables_bookings ON bookings TO app_admin USING (true) WITH CHECK (true);

CREATE POLICY own_status_log ON booking_status_log FOR SELECT
  USING (
    current_user = 'app_admin'
    OR EXISTS (SELECT 1 FROM bookings b WHERE b.id = booking_status_log.booking_id
               AND b.user_id = current_setting('app.user_id', true)::INTEGER)
  );
CREATE POLICY admin_all_tables_log ON booking_status_log TO app_admin USING (true);

-- =============================================================================
-- 4. REPORT VIEWS
-- =============================================================================
CREATE OR REPLACE VIEW report_bookings_summary AS
  SELECT f.id facility_id, f.name facility_name, ft.name facility_type,
         DATE_TRUNC('month', b.start_time) booking_month,
         b.status, COUNT(*) total_bookings,
         SUM(EXTRACT(EPOCH FROM (b.end_time - b.start_time))/3600) total_hours_booked
  FROM bookings b JOIN facilities f  ON f.id = b.facility_id
                  JOIN facility_types ft ON ft.id = f.facility_type_id
  GROUP BY f.id, f.name, ft.name, DATE_TRUNC('month', b.start_time), b.status
  ORDER BY booking_month DESC, facility_name;

CREATE OR REPLACE VIEW report_user_booking_history AS
  SELECT u.id user_id, u.first_name||' '||u.last_name user_name,
         r.name user_role, f.name facility_name,
         b.start_time, b.end_time,
         EXTRACT(EPOCH FROM (b.end_time - b.start_time))/3600 duration_hours,
         b.status, b.purpose
  FROM bookings b JOIN users  u ON u.id = b.user_id
                  JOIN roles  r ON r.id = u.role_id
                  JOIN facilities f ON f.id = b.facility_id
  ORDER BY b.start_time DESC;

GRANT SELECT ON report_bookings_summary, report_user_booking_history TO report_viewer;

-- =============================================================================
-- 5. SEED DATA (small — just enough for defense)
-- =============================================================================
INSERT INTO roles (name, description) VALUES
  ('app_admin',       'Full DML on all tables'),
  ('app_user',        'Standard user'),
  ('report_viewer',   'Read-only reporting');

INSERT INTO facility_types (name) VALUES
  ('Meeting Room'),('Lecture Hall'),('Laboratory'),('Gymnasium'),('Auditorium');

-- Passwords are 'password123' (Laravel hash, using bcrypt cost 10)
INSERT INTO users (first_name, last_name, email, password, role_id) VALUES
  ('Admin',   'User',     'admin@pup.edu.ph',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
  ('Report',  'Viewer',   'report@pup.edu.ph',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
  ('Juan',    'Dela Cruz','juan.delacruz@pup.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
  ('Maria',   'Santos',   'maria.santos@pup.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
  ('Ana',     'Reyes',    'ana.reyes@pup.edu.ph',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2);

INSERT INTO facilities (name, facility_type_id, location, capacity) VALUES
  ('Meeting Room A101',  1, 'Admin Bldg 1st Flr', 12),
  ('Lecture Hall 301',   2, 'Acad Bldg 3rd Flr', 150),
  ('Computer Lab 1',     3, 'IT Bldg 1st Flr',    40),
  ('Main Gymnasium',     4, 'PE Complex',         500),
  ('University Auditorium', 5, 'Cultural Bldg',   800);

INSERT INTO operating_hours (facility_id, day_of_week, open_time, close_time)
SELECT f.id, d, '07:00'::time, '20:00'::time
FROM facilities f CROSS JOIN generate_series(1,5) d;

INSERT INTO bookings (user_id, facility_id, start_time, end_time, purpose, status) VALUES
  (3, 1, '2026-10-15 09:00:00+08','2026-10-15 11:00:00+08','Research defense',        'approved'),
  (4, 2, '2026-10-16 13:00:00+08','2026-10-16 16:00:00+08','Guest lecture',           'pending'),
  (5, 3, '2026-10-17 08:00:00+08','2026-10-17 12:00:00+08','Database lab session',    'approved'),
  (3, 4, '2026-10-20 14:00:00+08','2026-10-20 20:00:00+08','Intramurals',             'pending'),
  (4, 5, '2026-10-25 18:00:00+08','2026-10-25 22:00:00+08','University anniversary',  'approved');

INSERT INTO blackout_dates (facility_id, blackout_date, reason, is_global) VALUES
  (NULL, CURRENT_DATE + 30, 'University Holiday', TRUE),
  (4,    CURRENT_DATE + 7,  'Gym Maintenance',   FALSE);

\x
SELECT 'DB setup complete!' AS status,
       (SELECT COUNT(*) FROM users)               AS users,
       (SELECT COUNT(*) FROM facilities)          AS facilities,
       (SELECT COUNT(*) FROM bookings)            AS bookings,
       (SELECT COUNT(*) FROM booking_status_log)  AS status_logs;
\x
