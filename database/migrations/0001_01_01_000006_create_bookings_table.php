<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('restrict');
            $table->timestampTz('start_time')->notNullable();
            $table->timestampTz('end_time')->notNullable();
            $table->string('purpose', 255)->notNullable();
            $table->string('status', 20)->default('pending')->notNullable();
            $table->text('admin_remarks')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'start_time', 'end_time']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'start_time']);
        });

        DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_end_time_check CHECK (end_time > start_time);');
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status IN ('pending','approved','rejected','cancelled'));");
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
