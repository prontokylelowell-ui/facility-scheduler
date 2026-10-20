<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->notNullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('facility_types')->insert([
            ['name' => 'Meeting Room', 'description' => 'Small enclosed rooms for meetings'],
            ['name' => 'Lecture Hall', 'description' => 'Large halls for lectures and events'],
            ['name' => 'Laboratory', 'description' => 'Computer or science laboratories'],
            ['name' => 'Gymnasium', 'description' => 'Sports and physical activity facilities'],
            ['name' => 'Auditorium', 'description' => 'Large venue for performances and ceremonies'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_types');
    }
};
