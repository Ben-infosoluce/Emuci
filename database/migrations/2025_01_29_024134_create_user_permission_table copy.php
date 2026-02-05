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
        Schema::create('user_permission', function (Blueprint $table) {
            $table->foreignId('id_user')->constrained('users');
            $table->foreignId('id_Permission')->constrained('permissions');
        });
        // activation de la clef etrangere
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_permission', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
            $table->dropForeign(['id_Permission']);
            $table->dropColumn('id_user');
            $table->dropColumn('id_Permission');
        });
        Schema::dropIfExists('user_permission');
    }
};
