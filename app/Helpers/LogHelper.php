<?php

namespace App\Helpers;

use App\Models\ModificationLog;

class LogHelper
{
    /**
     * Enregistre une modification dans la table modification_logs
     *
     * @param string $modelType Type logique (ex: 'client', 'vehicule', 'entreprise')
     * @param \Illuminate\Database\Eloquent\Model $modelInstance L'instance du modèle modifié
     * @param array $newValues Les nouvelles valeurs modifiées
     * @param int|null $userId L'ID de l'utilisateur connecté
     * @param array|null $oldValues Les anciennes valeurs (optionnel si non issues de l'instance)
     */
    public static function enregistrer(string $modelType, $modelInstance, array $newValues, ?int $userId, array $oldValues = null)
    {
        if (empty($newValues)) {
            return; // Rien à enregistrer
        }

        // Si pas d'anciennes valeurs fournies, on prend celles de l'instance
        if (is_null($oldValues)) {
            $oldValues = $modelInstance->getOriginal();
        }

        // On ne conserve que les anciennes valeurs des champs modifiés
        $old = [];
        foreach ($newValues as $key => $value) {
            $old[$key] = $oldValues[$key] ?? null;
        }

        ModificationLog::create([
            'model_type' => $modelType,
            'model_id' => $modelInstance->id,
            'old_values' => json_encode($old, JSON_UNESCAPED_UNICODE),
            'new_values' => json_encode($newValues, JSON_UNESCAPED_UNICODE),
            'user_id' => $userId,
        ]);
    }
}
