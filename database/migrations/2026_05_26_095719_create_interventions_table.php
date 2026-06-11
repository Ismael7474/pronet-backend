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
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 200);
            $table->text('description')->nullable();
            $table->enum('type', [
                'panne',
                'installation_wifi',
                'installation_camera',
                'maintenance'
            ]);
            $table->enum('priorite', ['basse', 'normale', 'urgente'])
            ->default('normale');
            $table->enum('statut', [
                'en_attente',
                'visite_prevue',
                'visite_faite',
                'valide',
                'en_cours',
                'termine',
                'annule',
                'archive'
            ])->default('en_attente');
            $table->enum('visite_requise', ['oui', 'non'])
                ->default('non');
            $table->foreignId('id_client')
                ->constrained('clients')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
