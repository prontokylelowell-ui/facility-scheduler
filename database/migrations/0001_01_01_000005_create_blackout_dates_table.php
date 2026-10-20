<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blackout_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->nullable()->constrained('facilities')->onDelete('cascade');
            $table->date('blackout_date')->notNullable();
            $table->string('reason', 200)->nullable();
            $table->boolean('is_global')->default(false);
            $table->timestamps();

            $table->unique(['facility_id', 'blackout_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blackout_dates');
    }
};
