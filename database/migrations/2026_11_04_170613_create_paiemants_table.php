<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un paiement = un versement libre d'un apprenant.
     * Le total dû, le reste à payer et la date limite sont calculés
     * (formations de l'apprenant + date_fin de sa cohorte), jamais stockés.
     */
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('id_paiement')->primary();

            $table->foreignUuid('id_apprenant')
                ->constrained('apprenants', 'id_apprenant')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('numero');            // 1 = acompte, 2..4 = versements suivants
            $table->unsignedBigInteger('montant');            // FCFA, entier
            $table->dateTime('date_paiement');
            $table->string('mode_paiement', 30)->default('espece');
            $table->string('reference')->unique();
            $table->string('reference_transaction')->nullable();
            $table->string('statut', 30)->default('valide');

            $table->timestamps();

            $table->index(['id_apprenant', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
