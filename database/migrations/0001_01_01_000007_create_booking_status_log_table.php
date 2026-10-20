<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_status_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20)->notNullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->timestampTz('changed_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index(['booking_id', 'changed_at']);
            $table->index(['new_status', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_status_log');
    }
};
