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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('cpf');
            $table->string('cnh');
            $table->enum('cnh_category', ['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE']);
            $table->date('cnh_expiration_date');
            $table->string('phone_number');
            $table->enum('status', ['VALID', 'EXPIRED'])->default('VALID');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
