<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom_site' => $this->faker->unique()->city,
            'type_site' => $this->faker->randomElement(['Agence', 'Dépôt', 'Bureau']),
            'region' => $this->faker->state,
            'heures_ouverture' => $this->faker->time,
            'heures_fermeture' => $this->faker->time,
            'statut' => $this->faker->randomElement([1, 2]),
        ];
    }
}
