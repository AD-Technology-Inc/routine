<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->enum('frequency', ['daily', 'weekdays', 'weekends', 'weekly', 'custom'])->default('daily');
            $table->json('custom_days')->nullable()->comment('Array of weekday ints 0-6 for custom frequency');
            $table->enum('time_block', ['morning', 'afternoon', 'evening', 'anytime'])->default('anytime');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routines');
    }
};
