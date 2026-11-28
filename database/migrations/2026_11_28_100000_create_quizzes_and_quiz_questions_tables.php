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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('class_session_id')->nullable()->constrained('class_sessions')->nullOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->string('mode', 32)->default('standard');
            $table->string('status', 32)->default('draft');

            $table->unsignedSmallInteger('time_limit_minutes')->nullable();
            $table->unsignedSmallInteger('max_attempts')->nullable()->default(1);
            $table->decimal('pass_percentage', 5, 2)->default(50.00);
            $table->decimal('total_marks', 8, 2)->default(0.00);

            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('allow_negative_marking')->default(false);
            $table->string('show_answers_policy', 32)->default('after_submission');

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'status']);
            $table->index(['institution_id', 'mode']);
            $table->index('class_id');
            $table->index('class_session_id');
            $table->index('activity_id');
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->unsignedInteger('order_position')->default(1);
            $table->decimal('marks', 5, 2)->nullable();
            $table->decimal('penalty_marks', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'question_id']);
            $table->index(['quiz_id', 'order_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
    }
};
