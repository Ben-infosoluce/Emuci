<?php

namespace App\Http\Controllers;

use App\Models\RelicaPrimo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelicaPrimoController extends Controller
{
    /**
     * Insertion en masse (milliers de lignes envoyées par paquets)
     */
    public function storeBulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:2000',
            'records.*.chrono' => 'required|string|max:255',
            'records.*.mt_total_cil' => 'required|integer|min:0',
        ]);

        // Dédoublonnage داخل même chunk (clé = chrono)
        $rows = [];
        foreach ($validated['records'] as $r) {
            $rows[$r['chrono']] = [
                'chrono' => $r['chrono'],
                'mt_total_cil' => (int) $r['mt_total_cil'],
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ];
        }

        // Exclure les chrono qui existent déjà en base
        $existing = RelicaPrimo::whereIn('chrono', array_keys($rows))
            ->pluck('chrono')
            ->all();

        $rows = array_diff_key($rows, array_flip($existing));

        if (empty($rows)) {
            return response()->json([
                'message' => 'Rien à insérer (doublons ignorés).',
                'inserted' => 0,
                'skipped' => count($existing),
            ], 200);
        }

        // Insertion brute par chunks de 1000 → très rapide
        DB::transaction(function () use ($rows) {
            foreach (array_chunk(array_values($rows), 1000) as $chunk) {
                RelicaPrimo::insert($chunk);
            }
        });

        return response()->json([
            'message' => 'Import OK.',
            'inserted' => count($rows),
            'skipped' => count($existing),
        ], 201);
    }


    public function migrateToRelica(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chronos' => 'required|array|min:1|max:5000',
            'chronos.*' => 'string|max:255',
        ]);

        $updated = DB::table('dossiers')
            ->where('type', 'PRIMO-ESPECE')
            ->whereJsonContains('detail', '47')          // ✅ insensitive au formatage du JSON
            ->where('id_type_service', 19)
            ->where('id_service', 6)
            ->whereIn('num_chrono', $validated['chronos'])   // 👈 vrai nom de ta colonne
            ->update([
                'type' => 'RELICA-PRIMO',
                // ✅ on réécrit dans le MÊME style formaté que tes données actuelles :
                'detail' => json_encode(['46'], JSON_PRETTY_PRINT),
                'id_type_service' => 18,
                'id_service' => 5,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Migration terminée.',
            'updated' => $updated,
        ], 200);
    }
}