<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Code métier stable d'un service (REI01, OPS01, PRIM1, ...).
     *
     * Il permet d'identifier un service indépendamment de son ID auto-incrémenté,
     * notamment pour rattacher le catalogue de ventilation (billing_articles).
     */
    public function up(): void
    {
        Schema::table('detail_type_services', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->unique()->after('element_facturation');
        });
    }

    public function down(): void
    {
        Schema::table('detail_type_services', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
