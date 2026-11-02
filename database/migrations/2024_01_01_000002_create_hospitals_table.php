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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 20);
            $table->string('address');
            $table->string('city');
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('hospital_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_password_reset_tokens');
        Schema::dropIfExists('hospitals');
    }
};
