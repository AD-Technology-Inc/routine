<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('grouping_key')->nullable();
            $table->date('date');
            $table->enum('time_block', ['morning', 'afternoon', 'evening', 'anytime'])->default('anytime');
            $table->unsignedSmallInteger('allocated_minutes');
            $table->unsignedSmallInteger('slot_index')->default(0);
            $table->boolean('is_merged')->default(false);
            $table->json('merged_task_ids')->nullable()->comment('Task IDs merged into this slot');
            $table->enum('status', ['pending', 'completed', 'skipped'])->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'date', 'status']);
            $table->index(['user_id', 'date', 'slot_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_slots');
    }
};
