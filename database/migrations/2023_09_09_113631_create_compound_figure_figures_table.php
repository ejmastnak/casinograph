<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('compound_figure_figures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compound_figure_id')->references('id')->on('compound_figures');
            $table->foreignId('figure_id')->references('id')->on('figures');
            $table->integer('seq_num');
            $table->boolean('is_final');
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compound_figure_figures');
    }
};
