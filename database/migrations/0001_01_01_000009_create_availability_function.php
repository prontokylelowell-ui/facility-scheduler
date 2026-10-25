<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE FUNCTION is_facility_available(
                p_facility_id INTEGER,
                p_start_time TIMESTAMPTZ,
                p_end_time TIMESTAMPTZ,
                p_exclude_booking_id INTEGER DEFAULT NULL
            ) RETURNS BOOLEAN AS \$\$
            DECLARE
                v_count INTEGER;
            BEGIN
                IF p_end_time <= p_start_time THEN
                    RETURN FALSE;
                END IF;

                -- Operating hours: the whole request must sit inside the
                -- facility's open/close window for that weekday. day_of_week
                -- is ISO-8601 (1 = Monday ... 7 = Sunday), matching how the
                -- seeder inserts it; a facility with no row for that day
                -- (e.g. Sunday) is therefore treated as closed. Requiring
                -- start and end to share a date also rejects midnight spans.
                IF NOT EXISTS (
                    SELECT 1
                    FROM operating_hours oh
                    WHERE oh.facility_id = p_facility_id
                      AND oh.day_of_week = EXTRACT(ISODOW FROM p_start_time)::INTEGER
                      AND p_start_time::date = p_end_time::date
                      AND p_start_time::time >= oh.open_time
                      AND p_end_time::time   <= oh.close_time
                ) THEN
                    RETURN FALSE;
                END IF;

                SELECT COUNT(*) INTO v_count
                FROM bookings
                WHERE facility_id = p_facility_id
                  AND status IN ('pending', 'approved')
                  AND tstzrange(start_time, end_time) && tstzrange(p_start_time, p_end_time)
                  AND (p_exclude_booking_id IS NULL OR id <> p_exclude_booking_id);

                IF v_count > 0 THEN
                    RETURN FALSE;
                END IF;

                IF EXISTS (
                    SELECT 1 FROM blackout_dates
                    WHERE (facility_id = p_facility_id OR is_global = TRUE)
                      AND blackout_date BETWEEN p_start_time::DATE AND p_end_time::DATE
                ) THEN
                    RETURN FALSE;
                END IF;

                RETURN TRUE;
            END;
            \$\$ LANGUAGE plpgsql STABLE SECURITY DEFINER SET search_path = public;
        ");

        DB::statement("COMMENT ON FUNCTION is_facility_available IS 'Checks whether a facility can be booked between two timestamps: enforces the facility''s operating hours for that weekday, rejects blackout dates, and rejects overlaps with pending/approved bookings. SECURITY DEFINER so it sees ALL bookings regardless of the caller''s RLS policy — otherwise a caller would only see their own bookings and the check would wrongly report slots as free.';");
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS is_facility_available(INTEGER, TIMESTAMPTZ, TIMESTAMPTZ, INTEGER);');
    }
};
