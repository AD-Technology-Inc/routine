<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('type', ['learning', 'practice', 'execution', 'review', 'planning'])->default('execution');
            $table->enum('flexibility', ['fixed', 'flexible', 'optional'])->default('flexible');
            $table->enum('reschedule_policy', ['strict', 'soft', 'skip_allowed'])->default('soft');
            $table->enum('energy_level', ['low', 'medium', 'high'])->default('medium');
            $table->string('grouping_key')->nullable()->comment('e.g. interview_prep, fitness, frontend');
            $table->boolean('can_merge')->default(false);
            $table->boolean('can_split')->default(false);
            $table->timestamps();

            $table->index('grouping_key');
            $table->index(['priority', 'energy_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_attributes');
    }
};
