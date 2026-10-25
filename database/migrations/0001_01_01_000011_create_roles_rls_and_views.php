<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Roles, GRANTs, RLS policies and reporting views.
 *
 * IMPORTANT — run migrations as a superuser/owner (e.g. postgres):
 *     $env:DB_USERNAME='postgres'; php artisan migrate:fresh --seed
 * The application itself connects as `app_user` (see .env), which is
 * deliberately NOT a superuser — that is the "auth-aligned" integration.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------
        // 1. Roles.  These are LOGIN roles so the app / pgAdmin can use them.
        //    (Use ALTER ROLE ... PASSWORD to change credentials.)
        // ---------------------------------------------------------------
        DB::statement("DO \$\$ BEGIN
            IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'app_admin') THEN
                CREATE ROLE app_admin LOGIN PASSWORD 'app_admin_pass_2026';
            END IF;
            IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'app_user') THEN
                CREATE ROLE app_user LOGIN PASSWORD 'app_user_pass_2026';
            END IF;
            IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'report_viewer') THEN
                CREATE ROLE report_viewer LOGIN PASSWORD 'report_viewer_pass_2026';
            END IF;
        END \$\$;");

        // The application always connects as `app_user` (.env). For an
        // admin-authenticated request it elevates with `SET ROLE app_admin`.
        // NOINHERIT keeps the principle of least privilege: app_user does NOT
        // automatically gain app_admin's privileges just by being a member —
        // the elevation must be explicit.
        DB::statement('ALTER ROLE app_admin NOINHERIT;');
        DB::statement('GRANT app_admin TO app_user;');

        DB::statement('GRANT CONNECT ON DATABASE facility_scheduler TO app_admin, app_user, report_viewer;');
        DB::statement('GRANT USAGE ON SCHEMA public TO app_admin, app_user, report_viewer;');

        // ---------------------------------------------------------------
        // 2. Privileges (the "two roles with different privileges" requirement)
        // ---------------------------------------------------------------
        DB::statement('GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO app_admin;');
        DB::statement('GRANT SELECT, USAGE ON ALL SEQUENCES IN SCHEMA public TO app_user;');

        $allTables = ['roles', 'users', 'facility_types', 'facilities', 'operating_hours', 'blackout_dates', 'bookings', 'booking_status_log'];

        foreach ($allTables as $table) {
            DB::statement("GRANT ALL PRIVILEGES ON TABLE {$table} TO app_admin;");
        }

        // app_user: read the catalogue, write only its own bookings.
        DB::statement('GRANT SELECT ON TABLE roles, users, facility_types, facilities, operating_hours, blackout_dates TO app_user;');
        DB::statement('GRANT SELECT, INSERT, UPDATE ON TABLE bookings TO app_user;');

        // Registration must be able to INSERT a user row (role is forced to
        // app_user by the application; see LoginController::register).
        DB::statement('GRANT INSERT ON TABLE users TO app_user;');
        DB::statement('GRANT SELECT ON TABLE booking_status_log TO app_user;');

        // ---------------------------------------------------------------
        // 3. Row Level Security
        //
        // NOTE: RLS is intentionally NOT enabled on `users`. Authentication
        // must read the user row by email *before* `app.user_id` is known,
        // so enabling RLS there would create a chicken-and-egg deadlock on
        // login. Column-level safety for users is handled by only exposing
        // first_name/last_name/email/role to the app.
        // ---------------------------------------------------------------
        DB::statement('ALTER TABLE bookings           ENABLE ROW LEVEL SECURITY;');
        DB::statement('ALTER TABLE booking_status_log ENABLE ROW LEVEL SECURITY;');

        foreach ([
            'own_bookings_select', 'own_bookings_insert', 'own_bookings_update',
            'admin_all_tables_bookings', 'own_status_log', 'admin_all_tables_log',
        ] as $policy) {
            DB::statement("DROP POLICY IF EXISTS {$policy} ON bookings;");
        }
        DB::statement('DROP POLICY IF EXISTS own_status_log ON booking_status_log;');
        DB::statement('DROP POLICY IF EXISTS admin_all_tables_log ON booking_status_log;');

        // --- bookings: SELECT own rows -------------------------------------
        DB::statement("
            CREATE POLICY own_bookings_select ON bookings FOR SELECT
            USING (
                current_user = 'app_admin'
                OR user_id = current_setting('app.user_id', true)::INTEGER
            );
        ");

        // --- bookings: INSERT only for yourself ----------------------------
        DB::statement("
            CREATE POLICY own_bookings_insert ON bookings FOR INSERT
            WITH CHECK (
                current_user = 'app_admin'
                OR user_id = current_setting('app.user_id', true)::INTEGER
            );
        ");

        // --- bookings: UPDATE own rows -------------------------------------
        // Legal status transitions are enforced in the app layer
        // (Booking::approve/reject/cancel) and admin-only routes.
        DB::statement("
            CREATE POLICY own_bookings_update ON bookings FOR UPDATE
            USING (
                current_user = 'app_admin'
                OR user_id = current_setting('app.user_id', true)::INTEGER
            )
            WITH CHECK (
                current_user = 'app_admin'
                OR user_id = current_setting('app.user_id', true)::INTEGER
            );
        ");

        // --- bookings: admin sees/does everything --------------------------
        DB::statement('
            CREATE POLICY admin_all_tables_bookings ON bookings
            TO app_admin
            USING (true)
            WITH CHECK (true);
        ');

        // --- booking_status_log: read logs of your own bookings ------------
        DB::statement("
            CREATE POLICY own_status_log ON booking_status_log FOR SELECT
            USING (
                current_user = 'app_admin'
                OR EXISTS (
                    SELECT 1 FROM bookings b
                    WHERE b.id = booking_status_log.booking_id
                      AND b.user_id = current_setting('app.user_id', true)::INTEGER
                )
            );
        ");

        DB::statement('
            CREATE POLICY admin_all_tables_log ON booking_status_log
            TO app_admin
            USING (true);
        ');

        // ---------------------------------------------------------------
        // 4. Reporting views (read-only surface for report_viewer)
        // ---------------------------------------------------------------
        DB::statement("
            CREATE OR REPLACE VIEW report_bookings_summary AS
            SELECT
                f.id AS facility_id,
                f.name AS facility_name,
                ft.name AS facility_type,
                DATE_TRUNC('month', b.start_time) AS booking_month,
                b.status,
                COUNT(*) AS total_bookings,
                SUM(EXTRACT(EPOCH FROM (b.end_time - b.start_time)) / 3600) AS total_hours_booked
            FROM bookings b
            JOIN facilities f ON f.id = b.facility_id
            JOIN facility_types ft ON ft.id = f.facility_type_id
            GROUP BY f.id, f.name, ft.name, DATE_TRUNC('month', b.start_time), b.status
            ORDER BY booking_month DESC, facility_name;
        ");

        DB::statement("
            CREATE OR REPLACE VIEW report_user_booking_history AS
            SELECT
                u.id AS user_id,
                u.first_name || ' ' || u.last_name AS user_name,
                r.name AS user_role,
                f.name AS facility_name,
                b.start_time,
                b.end_time,
                EXTRACT(EPOCH FROM (b.end_time - b.start_time)) / 3600 AS duration_hours,
                b.status,
                b.purpose
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            JOIN roles r ON r.id = u.role_id
            JOIN facilities f ON f.id = b.facility_id
            ORDER BY b.start_time DESC;
        ");

        DB::statement('GRANT SELECT ON report_bookings_summary, report_user_booking_history TO report_viewer;');
        DB::statement('GRANT SELECT ON ALL TABLES IN SCHEMA public TO report_viewer;');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS report_user_booking_history;');
        DB::statement('DROP VIEW IF EXISTS report_bookings_summary;');

        DB::statement('ALTER TABLE booking_status_log DISABLE ROW LEVEL SECURITY;');
        DB::statement('ALTER TABLE bookings DISABLE ROW LEVEL SECURITY;');

        DB::statement('REVOKE ALL ON ALL TABLES IN SCHEMA public FROM app_admin, app_user, report_viewer;');
        DB::statement('REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM app_admin, app_user;');
        DB::statement('REVOKE USAGE ON SCHEMA public FROM app_admin, app_user, report_viewer;');
        DB::statement('REVOKE ALL ON DATABASE facility_scheduler FROM app_admin, app_user, report_viewer;');

        DB::statement('DROP ROLE IF EXISTS report_viewer;');
        DB::statement('DROP ROLE IF EXISTS app_user;');
        DB::statement('DROP ROLE IF EXISTS app_admin;');
    }
};
