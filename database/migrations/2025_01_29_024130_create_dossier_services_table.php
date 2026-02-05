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

        Schema::create('dossier_services', function (Blueprint $table) {
            $table->foreignId('id_dossier')->constrained('dossiers');
            $table->foreignId('id_service')->constrained('services');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossier_services', function (Blueprint $table) {
            $table->dropForeign(['id_dossier']);
            $table->dropForeign(['id_service']);
            $table->dropColumn('id_dossier');
            $table->dropColumn('id_service');
        });
        Schema::dropIfExists('dossier_services');
    }
};
