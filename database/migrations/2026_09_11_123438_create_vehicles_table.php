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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('plate')->nullable();
            $table->string('renavan')->nullable();
            $table->string('chassi')->nullable();
            $table->string('brand');
            $table->string('model');
            $table->string('model_year');
            $table->string('fuel_type')->nullable();
            $table->integer('tank_capacity')->nullable();
            $table->string('status');
            $table->foreignId('secretariat_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
