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
            if (Schema::hasColumn('detail_type_services', 'montant_1_plaque') || Schema::hasColumn('detail_type_services', 'montant_2_plaques')) {
                $table->dropColumn(['montant_1_plaque', 'montant_2_plaques']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_type_services', function (Blueprint $table) {
            // recreate the columns in case of rollback
            $table->decimal('montant_2_plaques', 10, 2)->nullable()->after('montant');
            $table->decimal('montant_1_plaque', 10, 2)->nullable()->after('montant_2_plaques');
        });
    }
};
