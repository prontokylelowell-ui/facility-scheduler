<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE FUNCTION log_booking_status_change()
            RETURNS TRIGGER AS \$\$
            DECLARE
                v_changed_by INTEGER;
            BEGIN
                BEGIN
                    v_changed_by := current_setting('app.user_id')::INTEGER;
                EXCEPTION WHEN OTHERS THEN
                    v_changed_by := NULL;
                END;

                IF TG_OP = 'INSERT' THEN
                    INSERT INTO booking_status_log (
                        booking_id,
                        old_status,
                        new_status,
                        changed_by_user_id,
                        remarks,
                        changed_at
                    ) VALUES (
                        NEW.id,
                        NULL,
                        NEW.status,
                        v_changed_by,
                        'Booking created',
                        CURRENT_TIMESTAMP
                    );
                ELSIF TG_OP = 'UPDATE' AND OLD.status IS DISTINCT FROM NEW.status THEN
                    INSERT INTO booking_status_log (
                        booking_id,
                        old_status,
                        new_status,
                        changed_by_user_id,
                        remarks,
                        changed_at
                    ) VALUES (
                        NEW.id,
                        OLD.status,
                        NEW.status,
                        v_changed_by,
                        CONCAT('Status changed from ', OLD.status, ' to ', NEW.status),
                        CURRENT_TIMESTAMP
                    );
                END IF;

                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql SECURITY DEFINER SET search_path = public;
        ");

        DB::statement("
            CREATE TRIGGER trg_booking_status_change
            AFTER INSERT OR UPDATE OF status ON bookings
            FOR EACH ROW
            EXECUTE FUNCTION log_booking_status_change();
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS trg_booking_status_change ON bookings;");
        DB::statement("DROP FUNCTION IF EXISTS log_booking_status_change();");
    }
};
