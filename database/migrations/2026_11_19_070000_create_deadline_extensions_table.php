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
        Schema::create('deadline_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('extendable_type');
            $table->unsignedBigInteger('extendable_id');
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignId('extended_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('original_deadline')->nullable();
            $table->dateTime('extended_deadline');
            $table->text('reason');
            $table->timestamps();

            $table->index(['institution_id', 'extendable_type', 'extendable_id'], 'idx_deadline_ext_lookup');
            $table->index(['extendable_type', 'extendable_id', 'student_id'], 'idx_deadline_ext_student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deadline_extensions');
    }
};
