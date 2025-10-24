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
         Schema::create('clients', function (Blueprint $table) {
             $table->uuid('id')->primary();
             $table->string('numeroCompte')->unique();
             $table->string('titulaire');
             $table->string('type');
             $table->decimal('solde', 15, 2);
             $table->string('devise');
             $table->timestamp('dateCreation');
             $table->string('statut');
             $table->string('nci')->nullable();
             $table->string('email')->nullable();
             $table->string('telephone')->nullable();
             $table->text('adresse')->nullable();
             $table->string('password');
             $table->string('code');
             $table->timestamps();

             // Indexes
             $table->index('numeroCompte');
             $table->index('type');
             $table->index('statut');
         });
     }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
