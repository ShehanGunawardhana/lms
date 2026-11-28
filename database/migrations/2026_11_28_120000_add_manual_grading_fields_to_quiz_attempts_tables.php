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
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->boolean('is_fully_graded')->default(true)->after('passed');
            $table->dateTime('graded_at')->nullable()->after('is_fully_graded');
            $table->foreignId('graded_by')->nullable()->after('graded_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->dateTime('graded_at')->nullable()->after('feedback');
            $table->foreignId('graded_by')->nullable()->after('graded_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->dropForeign(['graded_by']);
            $table->dropColumn(['graded_by', 'graded_at']);
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropForeign(['graded_by']);
            $table->dropColumn(['graded_by', 'graded_at', 'is_fully_graded']);
        });
    }
};
