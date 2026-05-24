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
        Schema::create('practicums', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_year');

            $table->float('required_margin_top_cm')->default(4.0);
            $table->float('required_margin_bottom_cm')->default(3.0);
            $table->float('required_margin_left_cm')->default(4.0);
            $table->float('required_margin_right_cm')->default(3.0);
            $table->float('required_line_spacing')->default(1.5);
            $table->string('required_font_name')->default('Times New Roman');
            $table->integer('required_font_size')->default(12);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practicums');
    }
};
