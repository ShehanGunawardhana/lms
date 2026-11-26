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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('file_asset_id')->nullable()->constrained('file_assets')->nullOnDelete();
            $table->text('note')->nullable();
            $table->string('status')->default('submitted'); // submitted, late, returned, graded
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_late')->default(false);
            $table->dateTime('submitted_at');
            $table->dateTime('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('feedback')->nullable();
            $table->unsignedInteger('marks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'assignment_id', 'student_id']);
            $table->index(['assignment_id', 'student_id', 'version']);
            $table->index(['assignment_id', 'status']);
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
