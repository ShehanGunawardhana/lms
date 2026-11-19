<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Weekly Recurring Schedules (§7.1, T-3.3)
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 1 = Monday, 2 = Tuesday, ..., 7 = Sunday
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('room', 100)->nullable();
            $table->string('online_url')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'class_id', 'weekday']);
        });

        // 2. Generated Dated Sessions (§7.1, T-3.3)
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('class_schedules')->nullOnDelete();
            $table->string('title')->nullable();
            $table->date('date');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('room', 100)->nullable();
            $table->string('online_url')->nullable();
            $table->string('status', 30)->default('scheduled'); // scheduled, cancelled, completed
            $table->text('cancel_reason')->nullable();
            $table->unsignedBigInteger('rescheduled_from_id')->nullable();
            $table->timestamps();

            $table->unique(['class_id', 'date', 'starts_at']);
            $table->index(['institution_id', 'date', 'status']);
            $table->foreign('rescheduled_from_id')->references('id')->on('class_sessions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
        Schema::dropIfExists('class_schedules');
    }
};
