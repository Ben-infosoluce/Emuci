<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Garantit la présence du rôle Trésorier dans toutes les bases.
     * (Le rôle partage l'interface du RAF, en lecture seule.)
     */
    public function up(): void
    {
        $exists = DB::table('roles')->where('nom_role', 'Trésorier')->exists();

        if (!$exists) {
            DB::table('roles')->insert([
                'nom_role' => 'Trésorier',
            ]);
        }
    }

    /**
     * Le rôle peut être rattaché à des utilisateurs : on ne le supprime pas.
     */
    public function down(): void
    {
        //
    }
};
