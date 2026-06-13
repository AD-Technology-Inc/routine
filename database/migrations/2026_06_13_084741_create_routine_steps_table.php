<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('estimated_minutes')->default(15);
            $table->enum('energy_level', ['low', 'medium', 'high'])->default('medium');
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->timestamps();

            $table->index(['routine_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_steps');
    }
};
