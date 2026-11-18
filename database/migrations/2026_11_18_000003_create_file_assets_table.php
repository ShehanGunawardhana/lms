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
        Schema::create('file_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('disk')->default('private');
            $table->string('path');
            $table->string('original_name');
            $table->string('extension', 10);
            $table->string('mime_verified', 100);
            $table->unsignedBigInteger('size');
            $table->string('hash_sha256', 64)->index();
            $table->string('kind', 20); // video, document, image, archive
            $table->string('scan_status', 20)->default('clean'); // pending, clean, infected, error
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('purpose', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'scan_status']);
            $table->index(['institution_id', 'kind']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_assets');
    }
};
