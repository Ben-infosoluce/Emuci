<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DossierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_vehicule' => $this->faker->numberBetween(1, 10),
            'id_utilisateur' => $this->faker->numberBetween(1, 10),
            'id_client' => $this->faker->numberBetween(1, 10),
            'statut' => $this->faker->randomElement([1, 2, 3]),
            'date_creation' => $this->faker->dateTimeThisYear,
            'date_validation' => $this->faker->dateTimeThisYear,
        ];
    }
}
