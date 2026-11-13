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
        Schema::create('demande_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();

            // 1 = en attente, 2 = validée, 3 = rejetée, 4 = appliquée
            $table->tinyInteger('statut')->default(1);

            $table->json('champs_vehicule')->nullable();
            $table->json('champs_client')->nullable();
            $table->json('champs_entreprise')->nullable();

            $table->text('message')->nullable();
            $table->text('motif_rejet')->nullable();

            $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('traite_le')->nullable();
            $table->foreignId('applique_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applique_le')->nullable();

            $table->timestamps();

            $table->index(['statut', 'dossier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_modifications');
    }
};
