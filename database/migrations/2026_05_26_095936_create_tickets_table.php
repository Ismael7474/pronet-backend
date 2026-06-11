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
    Schema::create('tickets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('id_client')
              ->constrained('clients')
              ->onDelete('cascade');
        $table->enum('type_wifi', ['wifi_box', 'starlink']);
        $table->unsignedInteger('nombre_ticket');
        $table->decimal('prix_unitaire', 10, 2);
        $table->decimal('mon_revenu', 10, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
