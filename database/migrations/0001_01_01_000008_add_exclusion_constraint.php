<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist;');

        DB::statement("
            ALTER TABLE bookings ADD CONSTRAINT no_overlap
            EXCLUDE USING gist (
                facility_id WITH =,
                tstzrange(start_time, end_time) WITH &&
            ) WHERE (status IN ('pending','approved'));
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS no_overlap;');
    }
};
