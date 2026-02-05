<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_dossier' => $this->faker->numberBetween(1, 5),
            'type_document' => $this->faker->randomElement(['Piece d\'identite', 'Passport', 'Permis de conduire']),
            'carte_grise' => $this->faker->word,
            'certifica_visite_technique' => $this->faker->word,
            'piece' => $this->faker->word,
            'assurance_en_cours_de_validite' => $this->faker->word,
            'declaration_de_perte' => $this->faker->word,
            'certificat_de_residence' => $this->faker->word,
            'piece_identite_en_cours_de_validite' => $this->faker->word,
            'registre_de_commerce' => $this->faker->word,
            'autorisation_de_la_societe_de_credit' => $this->faker->word,
            'extrait_de_carte_grise' => $this->faker->word,
        ];
    }
}
