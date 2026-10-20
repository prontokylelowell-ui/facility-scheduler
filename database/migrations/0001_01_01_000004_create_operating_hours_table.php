<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
            $table->tinyInteger('day_of_week')->unsigned()->notNullable();
            $table->time('open_time')->notNullable();
            $table->time('close_time')->notNullable();
            $table->timestamps();

            $table->unique(['facility_id', 'day_of_week']);
        });

        // Laravel's schema builder has no checkConstraint() method, so the
        // CHECK constraints are added with raw PostgreSQL DDL.
        DB::statement("ALTER TABLE operating_hours ADD CONSTRAINT operating_hours_day_of_week_check CHECK (day_of_week BETWEEN 0 AND 6);");
        DB::statement("ALTER TABLE operating_hours ADD CONSTRAINT operating_hours_time_order_check CHECK (close_time > open_time);");
    }

    public function down(): void
    {
        Schema::dropIfExists('operating_hours');
    }
};
