<?php

use App\Enums\ModePaiement;
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
    //  Schema::create('paiements', function (Blueprint $table) {
    //     $table->uuid('id_paiement')->primary();

    //     $table->foreignId('inscription_id')
    //         ->constrained('inscriptions', 'id')
    //         ->cascadeOnDelete();

    //     $table->decimal('montant_total', 12, 2)->default(0);

    //     $table->decimal('versement', 12, 2);
        
    //     $table->decimal('reste_a_paye', 12, 2);

    //     $table->integer('nombre_versement');


    //     $table->dateTime('date_paiement');

    //     $table->string('mode_paiement')->default('espece');

    //     $table->string('reference')->unique();

    //     $table->string('reference_transaction')->nullable();

    //     $table->string('statut')->default('VALIDE');

    //     $table->foreignId('inscription_id')
    //         ->constrained('inscriptions', 'id')
    //         ->cascadeOnDelete();

    //     $table->timestamps();
    // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiemants');
    }
};
