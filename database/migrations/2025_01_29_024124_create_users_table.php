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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('prenom', 255);
            $table->string('email')->unique();
            $table->string('password', 255);
            $table->foreignId('id_role')->nullable()->constrained('role');
            $table->foreignId('id_site')->nullable()->constrained('sites');
            $table->integer('statut')->default(1)->comment('1: activer, 2: desactiver');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_role']);
            $table->dropForeign(['id_site']);
            $table->dropColumn('id_role');
            $table->dropColumn('id_site');
        });
        Schema::dropIfExists('users');
    }
};
