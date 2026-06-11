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
        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('id_intervention')
                ->nullable()
                ->constrained('interventions')
                ->onDelete('set null');
            $table->foreignId('id_client')
                ->nullable()
                ->constrained('clients')
                ->onDelete('set null');
            $table->string('action', 255);
            $table->string('module', 50);
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
