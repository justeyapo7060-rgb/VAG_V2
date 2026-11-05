<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('apprenants', function (Blueprint $table) {
        $table->dropForeign('apprenants_cohorte_id_foreign');
    });

    Schema::table('apprenants', function (Blueprint $table) {
        $table->string('cohorte_id')->nullable()->change();
    });

    Schema::table('apprenants', function (Blueprint $table) {
        $table->foreign('cohorte_id')
            ->references('id_cohorte')
            ->on('cohortes')
            ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('apprenants', function (Blueprint $table) {
        $table->string('cohorte_id')->nullable(false)->change();
    });
}
};
