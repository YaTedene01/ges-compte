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
         Schema::create('comptes', function (Blueprint $table) {
             $table->uuid('id')->primary();
             $table->string('numeroCompte')->unique();
             $table->string('titulaire');
             $table->enum('type', ['epargne', 'cheque']);
             $table->decimal('solde', 15, 2);
             $table->string('devise');
             $table->date('dateCreation');
             $table->enum('statut', ['actif', 'bloque', 'ferme']);
             $table->string('motifBlocage')->nullable();
             $table->json('metadata');
             $table->uuid('client_id');
             $table->timestamps();
             $table->softDeletes();

             // Foreign key
             $table->foreign('client_id')->references('id')->on('clients');

             // Indexes
             $table->index('numeroCompte');
             $table->index('type');
             $table->index('statut');
             $table->index('client_id');
         });
     }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
