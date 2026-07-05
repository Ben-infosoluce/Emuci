<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->unsignedBigInteger('caisse_ouverture_id')->nullable()->default(null)->change();
            $table->unsignedBigInteger('caisse_id')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->unsignedBigInteger('caisse_ouverture_id')->nullable(false)->change();
            $table->unsignedBigInteger('caisse_id')->nullable(false)->change();
        });
    }
};
