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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions');
            $table->string('age_group')->default('all');
            $table->string('status')->default('draft'); // draft, published, closed
            $table->date('publish_date')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->boolean('allow_late')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'status']);
            $table->index(['institution_id', 'class_id']);
            $table->index('publish_date');
            $table->index('deadline');
        });

        Schema::create('activity_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->string('youtube_url');
            $table->string('video_id');
            $table->string('title')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_healthy')->default(true);
            $table->dateTime('last_checked_at')->nullable();
            $table->timestamps();

            $table->index(['activity_id', 'sort_order']);
        });

        Schema::create('activity_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->foreignId('file_asset_id')->nullable()->constrained('file_assets')->nullOnDelete();
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('type')->default('file'); // file, link
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['activity_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_materials');
        Schema::dropIfExists('activity_videos');
        Schema::dropIfExists('activities');
    }
};
