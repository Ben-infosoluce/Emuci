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

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dossier')->nullable()->constrained('dossiers');
            $table->string('Montant');
            $table->dateTime('date_transaction')->nullable()->useCurrent();
            $table->string('type_transaction', 50);
            $table->foreignId('id_user')->nullable()->constrained('users');
            $table->integer('statut')->default(1)->comment('1: Valider, 2: En attente');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
            $table->dropColumn('id_user');
        });
        Schema::dropIfExists('transactions');
    }
};
