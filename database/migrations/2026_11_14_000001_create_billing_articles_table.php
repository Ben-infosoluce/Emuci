<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catalogue de ventilation comptable des services.
     *
     * Un service "parent" (detail_type_services) peut être ventilé en plusieurs
     * articles de facturation (ex. REI01 -> REI01_1, REI01_2, REI01_3).
     * C'est ce catalogue qui est utilisé pour construire les lignes de paiement
     * et les lignes de facture Sage X3.
     */
    public function up(): void
    {
        Schema::create('billing_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('detail_type_service_id');
            $table->string('code', 50);
            $table->string('libelle');
            $table->string('fait_generateur')->nullable();
            $table->decimal('montant_ht', 14, 2);
            $table->decimal('montant_ttc', 14, 2);
            $table->string('compte_x3', 30)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->unique(['detail_type_service_id', 'code'], 'billing_articles_service_code_unique');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_articles');
    }
};
