<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_dossier' => $this->faker->numberBetween(1, 10),
            'Montant' => $this->faker->randomNumber(4),
            'date_transaction' => $this->faker->dateTimeThisYear,
            'type_transaction' => $this->faker->randomElement(['Vente', 'Achat', 'Location']),
            'id_utilisateur' => $this->faker->numberBetween(1, 5),
            'statut' => $this->faker->randomElement([1, 2]),
        ];
    }
}
