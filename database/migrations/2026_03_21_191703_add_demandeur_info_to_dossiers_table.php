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
        Schema::table('dossiers', function (Blueprint $table) {
            $table->string('demandeur_nom')->nullable();
            $table->string('demandeur_prenom')->nullable();
            $table->string('demandeur_telephone')->nullable();
            $table->string('demandeur_type_piece')->nullable();
            $table->string('demandeur_numero_piece')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn([
                'demandeur_nom',
                'demandeur_prenom',
                'demandeur_telephone',
                'demandeur_type_piece',
                'demandeur_numero_piece'
            ]);
        });
    }
};
