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
        Schema::create('transportation_requests', function (Blueprint $table) {
            $table->id();
            $table->mediumText('description');
            $table->string('origin');
            $table->string('destination');
            $table->dateTime('expected_departure_datetime');
            $table->dateTime('expected_arrival_datetime');
            $table->dateTime('real_departure_datetime')->nullable();
            $table->dateTime('real_arrival_datetime')->nullable();
            $table->string('status');
            $table->foreignId('requesting_secretariat_id')->constrained('secretariats');
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportation_requests');
    }
};
