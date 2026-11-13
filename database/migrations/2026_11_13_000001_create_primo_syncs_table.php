<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historique des actualisations PRIMO-ESPECE.
     *
     * Chaque synchronisation réussie avec l'API externe CIL ajoute une ligne.
     * La ligne la plus récente fournit la date de début (last_sync_at) utilisée
     * pour construire l'URL de la synchronisation suivante.
     */
    public function up(): void
    {
        Schema::create('primo_syncs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('last_sync_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('primo_syncs');
    }
};
