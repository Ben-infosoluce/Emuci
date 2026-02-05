<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UtilisateurFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'email' => $this->faker->unique()->safeEmail,
            'mot_de_passe' => Hash::make('password'),
            'id_role' => $this->faker->numberBetween(1, 3),
            'id_site' => $this->faker->numberBetween(1),
            'statut' => $this->faker->randomElement([1, 2]),
        ];
    }
}
