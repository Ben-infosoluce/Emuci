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

        Schema::create('recus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_transaction')->nullable()->constrained('transactions');
            $table->dateTime('date_emission')->nullable()->useCurrent();
            $table->string('Format', 50);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recus', function (Blueprint $table) {
            $table->dropForeign(['id_transaction']);
            $table->dropColumn('id_transaction');
        });
        Schema::dropIfExists('recus');
    }
};
