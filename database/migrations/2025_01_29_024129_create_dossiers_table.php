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

        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_vehicule')->nullable()->constrained('vehicules');
            $table->foreignId('id_user')->nullable()->constrained('users');
            $table->foreignId('id_client')->nullable()->constrained('client');
            $table->integer('statut')->default(1)->comment('1: En attente, 2: Valider, 3: Refuser');
            $table->string('date_creation')->nullable()->useCurrent();
            $table->string('date_validation')->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropForeign(['id_vehicule']);
            $table->dropForeign(['id_user']);
            $table->dropForeign(['id_client']);
            $table->dropColumn('id_vehicule');
            $table->dropColumn('id_user');
            $table->dropColumn('id_client');
        });
        Schema::dropIfExists('dossiers');
    }
};
