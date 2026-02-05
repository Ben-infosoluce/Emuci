<?php

namespace Database\Seeders;

use App\Models\Dossier;
use Illuminate\Database\Seeder;

class DossierSeeder extends Seeder
{
    public function run(): void
    {
        Dossier::factory()->count(10)->create();
    }
}
