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
        Schema::create('clinics_doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics');
            $table->foreignId('doctor_id')->constrained('clinics');
            $table->json('specialization');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics_doctors');
    }
};
