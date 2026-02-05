<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VehiculeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'vin' => $this->faker->unique()->regexify('[A-Z0-9]{17}'),
            'marque' => $this->faker->word,
            'modele' => $this->faker->word,
            'couleur' => $this->faker->colorName,
            'source_energie' => $this->faker->randomElement(['Essence', 'Diesel', 'Électrique']),
            'genre_vehicule' => $this->faker->randomElement(['Voiture', 'Camion', 'Moto']),
            'poids_total_charge' => $this->faker->randomNumber(4),
            'poids_utile' => $this->faker->randomNumber(4),
            'poids_vide' => $this->faker->randomNumber(4),
            'puissance_administrative' => $this->faker->randomNumber(3),
            'places_assises' => $this->faker->numberBetween(2, 8),
            'nombre_essieux' => $this->faker->numberBetween(2, 6),
            'id_site' => $this->faker->numberBetween(1, 10),
            'id_client' => $this->faker->numberBetween(1, 10),
        ];
    }
}
