<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('covid_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('hospital_id')->constrained('hospitals')->onDelete('cascade');
            $table->enum('result', ['Positive', 'Negative', 'Pending'])->default('Pending');
            $table->date('test_date');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('patient_id');
            $table->index('hospital_id');
            $table->index('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('covid_tests');
    }
};
