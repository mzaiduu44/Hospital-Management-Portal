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
        Schema::create('vaccine_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->onDelete('cascade');
            $table->foreignId('vaccine_id')->constrained('vaccines')->onDelete('cascade');
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('min_threshold')->default(10);
            $table->timestamps();

            // Unique constraint to prevent duplicate entries
            $table->unique(['hospital_id', 'vaccine_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccine_inventory');
    }
};
