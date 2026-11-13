<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute le numéro de recette (NUMREC) lié à la caisse sur les paiements.
     *
     * Toutes les transactions d'une même caisse pour une même journée partagent
     * la même valeur. Elle est transmise à Sage X3 dans l'en-tête de facture.
     */
    public function up(): void
    {
        if (!Schema::hasTable('paiements')) {
            return;
        }

        if (!Schema::hasColumn('paiements', 'numrec')) {
            Schema::table('paiements', function (Blueprint $table) {
                $table->string('numrec', 100)->nullable()->after('reference');
                $table->index('numrec');
            });
        }

        // Renseigne les paiements existants afin que l'historique soit exploitable
        // lors d'un prochain envoi vers Sage X3.
        DB::table('paiements')
            ->select(['id', 'caisse_id', 'created_at'])
            ->whereNull('numrec')
            ->whereNotNull('caisse_id')
            ->orderBy('id')
            ->chunkById(500, function ($paiements) {
                foreach ($paiements as $paiement) {
                    DB::table('paiements')
                        ->where('id', $paiement->id)
                        ->update([
                            'numrec' => generateNumrecCaisse(
                                (int) $paiement->caisse_id,
                                $paiement->created_at
                            ),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('paiements') || !Schema::hasColumn('paiements', 'numrec')) {
            return;
        }

        Schema::table('paiements', function (Blueprint $table) {
            $table->dropIndex(['numrec']);
            $table->dropColumn('numrec');
        });
    }
};
