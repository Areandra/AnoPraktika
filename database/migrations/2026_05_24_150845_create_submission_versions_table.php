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
        Schema::create('submission_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->onDelete('cascade');
            $table->integer('version_number')->default(1);

            $table->string('word_file_path')->nullable();
            $table->string('pdf_file_path')->nullable();
            $table->json('attachment')->nullable();

            $table->boolean('is_format_valid')->default(true);
            $table->json('system_validation_logs')->nullable();
            $table->json('annotation_coordinates')->nullable();
            $table->string('assistant_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_versions');
    }
};
