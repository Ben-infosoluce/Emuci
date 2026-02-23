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
        Schema::table('detail_type_services', function (Blueprint $table) {
            // Ajouter montant_2_plaques et montant_1_plaque après le champ montant
            $table->decimal('montant_2_plaques', 10, 2)->nullable()->after('montant');
            $table->decimal('montant_1_plaque', 10, 2)->nullable()->after('montant_2_plaques');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_type_services', function (Blueprint $table) {
            $table->dropColumn(['montant_2_plaques', 'montant_1_plaque']);
        });
    }
};
