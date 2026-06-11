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
        Schema::create('rapport_visites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_intervention')
                ->constrained('interventions')
                ->onDelete('cascade');
            $table->foreignId('id_user')
                ->constrained('users')
                ->onDelete('cascade');
            $table->text('observations');
            $table->text('materiel_necessaire');
            $table->decimal('estimation_cout', 10, 2)->nullable();
            $table->enum('faisable', ['oui', 'non']);
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapport_visite');
    }
};
