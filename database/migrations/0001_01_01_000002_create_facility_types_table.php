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
            ['name' => 'Swimming Pool', 'description' => 'Pools for swimming and water activities'],
            ['name' => 'Function Hall', 'description' => 'Indoor halls for events and gatherings'],
            ['name' => 'Cottage', 'description' => 'Overnight cottages and cabanas'],
            ['name' => 'Sports Court', 'description' => 'Tennis, basketball and other courts'],
            ['name' => 'Conference Room', 'description' => 'Rooms for meetings and seminars'],
            ['name' => 'Pavilion', 'description' => 'Open-air pavilions for outdoor events'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_types');
    }
};
