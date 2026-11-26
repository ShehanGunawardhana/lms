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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->string('status')->default('draft');
            $table->json('allowed_file_types');
            $table->unsignedInteger('max_file_size_mb')->default(50);
            $table->dateTime('deadline');
            $table->boolean('allow_late')->default(false);
            $table->unsignedInteger('resubmission_limit')->default(3);
            $table->unsignedInteger('total_marks')->default(100);
            $table->unsignedInteger('pass_marks')->nullable()->default(50);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'class_id', 'status']);
            $table->index(['class_id', 'deadline']);
        });

        Schema::create('assignment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('file_asset_id')->nullable()->constrained('file_assets')->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('file'); // 'file' or 'link'
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['assignment_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_attachments');
        Schema::dropIfExists('assignments');
    }
};
