<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_capacity_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('daily_available_minutes')->default(240);
            $table->json('preferred_time_blocks')->nullable()->comment('Array of: morning, afternoon, evening');
            $table->unsignedSmallInteger('monday_minutes')->nullable();
            $table->unsignedSmallInteger('tuesday_minutes')->nullable();
            $table->unsignedSmallInteger('wednesday_minutes')->nullable();
            $table->unsignedSmallInteger('thursday_minutes')->nullable();
            $table->unsignedSmallInteger('friday_minutes')->nullable();
            $table->unsignedSmallInteger('saturday_minutes')->nullable();
            $table->unsignedSmallInteger('sunday_minutes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_capacity_profiles');
    }
};
