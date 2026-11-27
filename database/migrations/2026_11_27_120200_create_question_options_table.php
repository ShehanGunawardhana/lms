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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->text('option_text')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->text('match_text')->nullable();
            $table->string('match_image_path')->nullable();
            $table->integer('order_position')->default(0);
            $table->json('acceptable_answers')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->index(['question_id', 'order_position']);
            $table->index(['question_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
