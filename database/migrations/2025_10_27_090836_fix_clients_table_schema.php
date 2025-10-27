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
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'numeroCompte')) {
                $table->dropColumn(['numeroCompte', 'type', 'solde', 'devise', 'dateCreation', 'statut']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('numeroCompte')->unique();
            $table->string('type');
            $table->decimal('solde', 15, 2);
            $table->string('devise');
            $table->timestamp('dateCreation');
            $table->string('statut');
        });
    }
};
