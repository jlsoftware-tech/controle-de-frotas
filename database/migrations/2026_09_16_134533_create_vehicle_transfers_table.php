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
        Schema::create('vehicle_transfers', function (Blueprint $table) {
            $table->id();
            $table->date('begin_transfer_date');
            $table->date('end_transfer_date');
            $table->string('motivation');
            $table->enum('status', ['PENDING', 'APPROVED', 'CANCELLED', 'FINISHED'])
                ->default('PENDING');
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('approver_id')->constrained('users');
            $table->foreignId('requested_secretariat_id')->constrained('secretariats');
            $table->foreignId('requesting_secretariat_id')->constrained('secretariats');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_vehicles');
    }
};
