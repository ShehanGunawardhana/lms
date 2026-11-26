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
        // 1. Add health, allergies, and medical columns to students table (§6.5)
        Schema::table('students', function (Blueprint $table) {
            $table->text('medical_notes')->nullable()->after('kind'); // encrypted at model level
            $table->text('allergies')->nullable()->after('medical_notes'); // encrypted at model level
            $table->string('blood_group', 10)->nullable()->after('allergies');
            $table->text('emergency_notes')->nullable()->after('blood_group');
        });

        // 2. Emergency contacts table (§6.5)
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('relationship', 60);
            $table->string('phone_primary', 30);
            $table->string('phone_secondary', 30)->nullable();
            $table->unsignedSmallInteger('priority')->default(1); // 1 = Primary, 2 = Secondary, etc.
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'student_id']);
            $table->index(['student_id', 'priority']);
        });

        // 3. Authorized pickup persons table with optional photo (§6.5)
        Schema::create('authorized_pickups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('relationship', 60);
            $table->string('phone', 30);
            $table->string('national_id', 50)->nullable();
            $table->foreignId('photo_file_id')->nullable()->constrained('file_assets')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'student_id']);
            $table->index(['student_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorized_pickups');
        Schema::dropIfExists('emergency_contacts');

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'medical_notes',
                'allergies',
                'blood_group',
                'emergency_notes',
            ]);
        });
    }
};
