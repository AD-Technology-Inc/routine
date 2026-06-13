<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedSmallInteger('total_tasks_scheduled')->default(0);
            $table->unsignedSmallInteger('total_tasks_completed')->default(0);
            $table->unsignedSmallInteger('total_tasks_skipped')->default(0);
            $table->unsignedSmallInteger('total_tasks_missed')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('avg_task_duration_minutes', 6, 2)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_snapshots');
    }
};
