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
         Schema::create('transactions', function (Blueprint $table) {
             $table->uuid('id')->primary();
             $table->uuid('compteId');
             $table->enum('type', ['depot', 'retrait', 'virement', 'frais']);
             $table->decimal('montant', 15, 2);
             $table->string('devise');
             $table->string('description');
             $table->datetime('dateTransaction');
             $table->enum('statut', ['en_attente', 'validee', 'annulee']);
             $table->timestamps();

             // Foreign key
             $table->foreign('compteId')->references('id')->on('comptes');

             // Indexes
             $table->index('compteId');
             $table->index('type');
             $table->index('statut');
             $table->index('dateTransaction');
         });
     }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
