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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->string('affects', 30)->default('all'); // all, nursery, institute
            $table->boolean('is_recurring')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'date']);
            $table->unique(['institution_id', 'date', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
