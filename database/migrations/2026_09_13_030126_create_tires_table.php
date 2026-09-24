<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tires', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number');
            $table->string('brand');
            $table->integer('service_life_km');
            $table->enum('position', ['FRONT_RIGHT', 'FRONT_LEFT', 'BACK_RIGHT', 'BACK_LEFT'])
                ->nullable();
            $table->enum('status', ['IN_USE', 'STORED', 'DISCARDED'])
                ->default('IN_USE');
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tires');
    }
};
