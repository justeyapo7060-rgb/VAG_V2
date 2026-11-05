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
        Schema::create('cohortes', function (Blueprint $table) {
            $table->uuid('id_cohorte')->primary();
            $table->string('statut', 30)
                ->default('processing');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->string('slug');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cohortes');
    }
};
