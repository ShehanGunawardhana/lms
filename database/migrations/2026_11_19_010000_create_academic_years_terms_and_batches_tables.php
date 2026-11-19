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
        // 1. Academic Years (§7.1)
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_current')->default(false);
            $table->string('status', 30)->default('active'); // active, archived, upcoming
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'is_current']);
            $table->index(['institution_id', 'status']);
        });

        // 2. Terms (§7.1)
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index(['academic_year_id', 'is_current']);
            $table->index(['institution_id', 'academic_year_id']);
        });

        // 3. Batches & Groups (§7.1)
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->string('kind', 30)->default('institute_batch'); // institute_batch, nursery_group
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active'); // active, archived
            $table->timestamps();

            $table->index(['institution_id', 'kind', 'status']);
            $table->index(['institution_id', 'academic_year_id']);
        });

        // 4. Link school_classes with batch_id and foreign key for academic_year_id
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('academic_year_id')->constrained('batches')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('batch_id');
        });

        Schema::dropIfExists('batches');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('academic_years');
    }
};
