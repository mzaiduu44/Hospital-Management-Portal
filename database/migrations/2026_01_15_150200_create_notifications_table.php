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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('user_type', ['patient', 'hospital', 'admin']);
            $table->string('title');
            $table->text('message');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->enum('sender_type', ['patient', 'hospital', 'admin'])->nullable();
            $table->boolean('read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'user_type']);
            $table->index('read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
