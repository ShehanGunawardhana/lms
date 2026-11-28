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
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('image_file_id')->nullable()->after('settings')->constrained('file_assets')->nullOnDelete();
            $table->foreignId('audio_file_id')->nullable()->after('image_file_id')->constrained('file_assets')->nullOnDelete();
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->foreignId('image_file_id')->nullable()->after('image_path')->constrained('file_assets')->nullOnDelete();
            $table->foreignId('audio_file_id')->nullable()->after('image_file_id')->constrained('file_assets')->nullOnDelete();
            $table->foreignId('match_image_file_id')->nullable()->after('match_image_path')->constrained('file_assets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropForeign(['image_file_id']);
            $table->dropForeign(['audio_file_id']);
            $table->dropForeign(['match_image_file_id']);
            $table->dropColumn(['image_file_id', 'audio_file_id', 'match_image_file_id']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['image_file_id']);
            $table->dropForeign(['audio_file_id']);
            $table->dropColumn(['image_file_id', 'audio_file_id']);
        });
    }
};
