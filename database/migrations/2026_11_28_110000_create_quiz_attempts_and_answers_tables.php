<?php

declare(strict_types=1);

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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->string('status', 32)->default('in_progress');

            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();

            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('passed')->nullable();

            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->json('question_order')->nullable();
            $table->json('options_order')->nullable();

            $table->timestamps();

            $table->index(['quiz_id', 'user_id']);
            $table->index(['institution_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();

            $table->foreignId('selected_option_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->json('selected_option_ids')->nullable();
            $table->text('text_answer')->nullable();
            $table->json('ordering_answer')->nullable();
            $table->json('matching_answer')->nullable();

            $table->decimal('marks_awarded', 5, 2)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->text('feedback')->nullable();
            $table->boolean('is_flagged')->default(false);

            $table->timestamps();

            $table->unique(['quiz_attempt_id', 'question_id']);
            $table->index(['quiz_attempt_id', 'is_flagged']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
        Schema::dropIfExists('quiz_attempts');
    }
};
