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

        Schema::create('vehicules', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->nullable();
            $table->string('vin', 50)->unique();
            $table->string('marque', 50)->nullable();
            $table->string('modele', 50)->nullable();
            $table->string('couleur', 50)->nullable();
            $table->string('source_energie', 50)->nullable();
            $table->string('genre_vehicule', 50)->nullable();
            $table->string('poids_total_charge')->nullable();
            $table->string('poids_utile')->nullable();
            $table->string('poids_vide')->nullable();
            $table->string('puissance_administrative')->nullable();
            $table->integer('places_assises')->nullable();
            $table->integer('nombre_essieux')->nullable();
            $table->foreignId('id_site')->nullable()->constrained('sites');
            $table->foreignId('id_client')->nullable()->constrained('client');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->dropForeign(['id_client']);
            $table->dropForeign(['id_site']);
            $table->dropColumn('id_client');
            $table->dropColumn('id_site');
        });
        Schema::dropIfExists('vehicules');
    }
};
