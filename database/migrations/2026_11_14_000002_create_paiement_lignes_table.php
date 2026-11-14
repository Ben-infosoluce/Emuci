<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lignes réelles d'un paiement (ventilation figée au moment de l'encaissement).
     *
     * Ces lignes sont la source de vérité pour l'envoi vers Sage X3. Les paiements
     * historiques qui n'ont pas encore de lignes continuent d'utiliser l'ancienne
     * logique de description JSON (fallback dans SendPaiementsToX3Job).
     */
    public function up(): void
    {
        Schema::create('paiement_lignes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paiement_id');
            $table->string('article_code', 50);
            $table->string('libelle')->nullable();
            $table->string('fait_generateur')->nullable();
            $table->decimal('montant_ht', 14, 2)->default(0);
            $table->decimal('montant_ttc', 14, 2)->default(0);
            $table->string('compte_x3', 30)->nullable();
            $table->unsignedBigInteger('parent_detail_type_service_id')->nullable();
            $table->timestamps();

            $table->index('paiement_id');
            $table->index('article_code');
            $table->index('parent_detail_type_service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_lignes');
    }
};
