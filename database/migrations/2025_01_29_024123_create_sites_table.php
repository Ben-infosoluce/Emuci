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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('nom_site', 100);
            $table->string('type_site', 50);
            $table->string('region', 50)->nullable();
            $table->time('heures_ouverture')->nullable();
            $table->time('heures_fermeture')->nullable();
            $table->integer('statut')->default(1)->comment('1: activer, 2: desactiver');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
