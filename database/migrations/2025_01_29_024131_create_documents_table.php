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
        Schema::disableForeignKeyConstraints();

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dossier')->nullable()->constrained('dossiers');
            $table->string('type_document', 100)->comment('Piece d\'identite, passport, permis de conduire');
            $table->string('carte_grise', 255);
            $table->string('certifica_visite_technique', 255);
            $table->string('piece', 255)->comment('Piece d\'identite, passport, permis de conduire');
            $table->string('assurance_en_cours_de_validite', 255);
            $table->string('declaration_de_perte', 255)->nullable();
            $table->string('certificat_de_residence', 255)->nullable();
            $table->string('piece_identite_en_cours_de_validite', 255)->nullable()->comment('Piece du directeur de la societe');
            $table->string('registre_de_commerce', 255)->nullable();
            $table->string('autorisation_de_la_societe_de_credit', 255)->nullable();
            $table->string('extrait_de_carte_grise', 255)->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['id_dossier']);
            $table->dropColumn('id_dossier');
        });
        Schema::dropIfExists('documents');
    }
};
