<?php

namespace App\Http\Controllers;

use App\Models\ReplicaPrimo;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UploadPrimoController extends Controller
{
    /**
     * Afficher la page d'upload
     */
    public function __invoke()
    {
        return view('upload-primo');
    }

    /**Traiter le fichier uploadé
     */
    public function upload(Request $request)
    {
        // Validation du fichier
        $request->validate([
            'file' => 'required|file|mimes:csv,xls,xlsx|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            // Lire les données du fichier
            $data = $this->readFile($file, $extension);

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le fichier ne contient pas de données valides.'
                ], 422);
            }

            // Importer les données
            $stats = $this->importData($data);

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    'Importation terminée. %d enregistrement(s) importé(s), %d erreur(s).',
                    $stats['success'],
                    $stats['errors']
                ),
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur upload primo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du traitement du fichier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**Lire le fichier CSV ou Excel et retourner les données
     */
    private function readFile($file, $extension): array
    {
        $data = [];

        if ($extension === 'csv') {
            $data = $this->readCsv($file);
        } else {
            $data = $this->readExcel($file);
        }

        return $data;
    }

    /**Lire un fichier CSV
     */
    private function readCsv($file): array
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw new \Exception('Impossible de lire le fichier CSV.');
        }

        // Lire l'en-tête
        $header = fgetcsv($handle, 0, ';');

        if (!$header) {
            // Essayer avec la virgule comme séparateur
            rewind($handle);
            $header = fgetcsv($handle, 0, ',');
        }

        if (!$header) {
            fclose($handle);
            throw new \Exception('Impossible de lire l\'en-tête du fichier CSV.');
        }

        // Normaliser les en-têtes
        $header = array_map(function ($col) {
            return trim(strtoupper($col));
        }, $header);

        // Trouver les indices des colonnes CHRONO et MT_TOTAL CIL
        $chronoIndex = $this->findColumnIndex($header, ['CHRONO', 'CHRONO', 'NUMERO_CHRONO']);
        $mtTotalCilIndex = $this->findColumnIndex($header, ['MT_TOTAL CIL', 'MT_TOTAL_CIL', 'MONTANT_TOTAL_CIL', 'MONTANT_CIL']);

        if ($chronoIndex === false || $mtTotalCilIndex === false) {
            fclose($handle);
            throw new \Exception(
                'Colonnes requises non trouvées. Assurez-vous que le fichier contient les colonnes CHRONO et MT_TOTAL_CIL. ' .
                    'Colonnes trouvées: ' . implode(', ', $header)
            );
        }

        // Lire les données
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // Essayer avec la virgule si la ligne est vide
            if (count($row) <= 1) {
                $row = fgetcsv($handle, 0, ',');
                if ($row === false) break;
            }

            $chrono = isset($row[$chronoIndex]) ? trim($row[$chronoIndex]) : null;
            $mtTotalCil = isset($row[$mtTotalCilIndex]) ? trim($row[$mtTotalCilIndex]) : null;

            if (!empty($chrono) && !empty($mtTotalCil)) {
                $data[] = [
                    'chrono' => $chrono,
                    'mt_total_cil' => $this->parseAmount($mtTotalCil),
                ];
            }
        }

        fclose($handle);
        return $data;
    }

    /*Lire un fichier Excel (XLS/XLSX)
     */
    private function readExcel($file): array
    {
        $data = [];

        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            throw new \Exception('Le fichier Excel est vide.');
        }

        // Lire l'en-tête (première ligne)
        $header = array_map(function ($col) {
            return trim(strtoupper($col));
        }, $rows[0]);

        // Trouver les indices des colonnes CHRONO et MT_TOTAL CIL
        $chronoIndex = $this->findColumnIndex($header, ['CHRONO', 'CHRONO', 'NUMERO_CHRONO']);
        $mtTotalCilIndex = $this->findColumnIndex($header, ['MT_TOTAL CIL', 'MT_TOTAL_CIL', 'MONTANT_TOTAL_CIL', 'MONTANT_CIL']);

        if ($chronoIndex === false || $mtTotalCilIndex === false) {
            throw new \Exception(
                'Colonnes requises non trouvées. Assurez-vous que le fichier contient les colonnes CHRONO et MT_TOTAL_CIL. ' .
                    'Colonnes trouvées: ' . implode(', ', $header)
            );
        }

        // Lire les données (à partir de la deuxième ligne)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $chrono = isset($row[$chronoIndex]) ? trim($row[$chronoIndex]) : null;
            $mtTotalCil = isset($row[$mtTotalCilIndex]) ? trim($row[$mtTotalCilIndex]) : null;

            if (!empty($chrono) && !empty($mtTotalCil)) {
                $data[] = [
                    'chrono' => $chrono,
                    'mt_total_cil' => $this->parseAmount($mtTotalCil),
                ];
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $data;
    }

    /* Trouver l'index d'une colonne par son nom (avec variantes)
     */
    private function findColumnIndex(array $header, array $possibleNames): int|false
    {
        foreach ($possibleNames as $name) {
            $index = array_search($name, $header);
            if ($index !== false) {
                return $index;
            }
        }
        return false;
    }

    /** Parser un montant : supprimer les séparateurs (virgules, espaces)*/
    private function parseAmount($amount): string
    {
        $amount = trim($amount);
        // Supprimer les virgules et espaces (séparateurs de milliers/décimaux)
        $amount = str_replace([',', ' '], '', $amount);
        return $amount;
    }

    /**Importer les données dans la base de données
     */
    private function importData(array $data): array
    {
        $stats = [
            'total' => count($data),
            'success' => 0,
            'errors' => 0,
            'duplicates' => 0,
        ];

        DB::beginTransaction();

        try {
            foreach ($data as $row) {
                try {
                    // Vérifier si le chrono existe déjà
                    $existing = ReplicaPrimo::firstWhere('chrono', $row['chrono']);

                    if ($existing) {
                        // Mettre à jour l'enregistrement existant
                        $existing->update([
                            'mt_total_cil' => $row['mt_total_cil'],
                        ]);
                        $stats['duplicates']++;
                    } else {
                        // Créer un nouvel enregistrement
                        ReplicaPrimo::create([
                            'chrono' => $row['chrono'],
                            'mt_total_cil' => $row['mt_total_cil'],
                        ]);
                    }

                    $stats['success']++;
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::warning('Erreur import ligne: ' . json_encode($row) . ' - ' . $e->getMessage());
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $stats;
    }
}
