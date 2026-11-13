<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clé d'unicité applicative pour les dossiers RELICA-PRIMO.
     *
     * La colonne est volontairement nullable : les dossiers déjà présents en
     * base restent à NULL et ne bloquent donc pas la migration, même s'il
     * existe des doublons historiques. MySQL autorise plusieurs NULL dans un
     * index unique. Les nouveaux dossiers, eux, renseignent la clé et sont
     * protégés contre les doublons (y compris les appels concurrents).
     */
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->string('dedup_key', 255)->nullable()->after('num_chrono');
            $table->unique('dedup_key', 'dossiers_dedup_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropUnique('dossiers_dedup_key_unique');
            $table->dropColumn('dedup_key');
        });
    }
};
