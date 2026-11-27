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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('title');
            $table->text('question_text');
            $table->text('explanation')->nullable();
            $table->text('hint')->nullable();
            $table->string('difficulty', 16)->default('medium');
            $table->decimal('default_marks', 6, 2)->default(1.00);
            $table->decimal('default_penalty_marks', 6, 2)->default(0.00);
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'type']);
            $table->index(['institution_id', 'difficulty']);
            $table->index(['institution_id', 'created_by']);
            $table->index(['institution_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
