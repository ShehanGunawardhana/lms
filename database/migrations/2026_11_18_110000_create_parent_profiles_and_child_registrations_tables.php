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
        Schema::create('parent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship', 50)->default('legal_guardian');
            $table->string('secondary_phone', 30)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->boolean('consent_terms')->default(false);
            $table->boolean('consent_child_safety')->default(false);
            $table->boolean('consent_photo')->default(false);
            $table->string('consent_ip', 45)->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->string('phone_verification_code', 10)->nullable();
            $table->timestamp('phone_verification_expires_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'user_id']);
        });

        Schema::create('child_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('parent_profile_id')->constrained('parent_profiles')->cascadeOnDelete();
            $table->string('first_name', 120);
            $table->string('last_name', 120);
            $table->date('dob');
            $table->string('gender', 20)->nullable();
            $table->string('nursery_group', 50)->default('nursery');
            $table->text('medical_notes')->nullable(); // encrypted at model level
            $table->text('dietary_needs')->nullable();
            $table->text('special_notes')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'parent_profile_id']);
            $table->index('nursery_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_registrations');
        Schema::dropIfExists('parent_profiles');
    }
};
