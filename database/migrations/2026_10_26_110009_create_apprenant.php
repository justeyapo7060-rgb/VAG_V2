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
        Schema::create('apprenants', function (Blueprint $table) {
            $table->uuid('id_apprenant')->primary();

            $table->string('nom', 255);
            $table->string('prenoms', 255);

            $table->string('sexe', 20);

            $table->string('email')->unique();
            $table->string('telephone', 255);

            $table->string('matricule')->unique();

            $table->date('date_naissance');

            $table->string('statut', 30)
                ->default('pending');

            $table->string('nationalite', 100);

             $table->foreignUuid('cohorte_id')
              ->constrained('cohortes', 'id_cohorte')
              ->onDelete('cascade');
              
             $table->foreignUuid('vacation_id')
              ->constrained('vacations', 'id_vacation')
              ->onDelete('cascade');
              
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apprenants');
    }
};
