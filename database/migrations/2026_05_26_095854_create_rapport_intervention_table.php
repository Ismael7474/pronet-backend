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
        Schema::create('rapport_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_intervention')
                ->constrained('interventions')
                ->onDelete('cascade');
            $table->foreignId('id_user')
                ->constrained('users')
                ->onDelete('cascade');
            $table->text('travail_effectue');
            $table->enum('resultat', ['resolu', 'partiel', 'non_resolu']);
            $table->text('observations')->nullable();
            $table->integer('duree_intervention')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapport_intervention');
    }
};
