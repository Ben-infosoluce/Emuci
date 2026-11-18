<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Annulation d'un paiement avec traçabilité.
     *
     * statut : 1 = actif, 2 = annulé.
     * Les paiements annulés restent en base (aucune suppression) mais ne sont
     * plus transmis à Sage X3 ni comptés dans les totaux de caisse.
     */
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->unsignedTinyInteger('statut')
                ->default(1)
                ->after('mode_paiement')
                ->comment('1 = actif, 2 = annulé');
            $table->timestamp('annule_at')->nullable()->after('statut');
            $table->unsignedBigInteger('annule_par')->nullable()->after('annule_at');
            $table->string('motif_annulation')->nullable()->after('annule_par');

            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropIndex(['statut']);
            $table->dropColumn(['statut', 'annule_at', 'annule_par', 'motif_annulation']);
        });
    }
};
