<?php

namespace App\Services\X3;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class X3FactureService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;

    public function __construct()
    {
        $this->baseUrl = config('services.x3.url');
        $this->username = config('services.x3.username');
        $this->password = config('services.x3.password');

        if (!$this->baseUrl) {
            throw new \Exception('X3_URL non configurée');
        }
    }

    protected function client()
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->withoutVerifying()
            ->acceptJson()
            ->contentType('application/json')
            ->connectTimeout(30) // Temps max pour établir la connexion
            ->timeout(60)        // Temps max pour la réponse totale
            ->retry(3, 2000);
    }

    /**
     * Crée un en-tête de facture dans Sage X3
     * 
     * Cette méthode envoie une requête à l'API Sage X3 pour créer un en-tête de facture
     * avec les données fournies. Elle gère également la journalisation des requêtes et réponses.
     * 
     * @param array $data Les données de l'en-tête de facture à créer
     * @return array La réponse JSON de l'API Sage X3
     * @throws Exception Si la requête échoue
     */
    public function createEntete(array $data)
    {
        // Construction de l'URL pour l'API Sage X3
        $url = $this->baseUrl . '/YPREFIMMAT?representation=YPREFIMMAT.$create';

        // Préparation du payload avec la représentation requise
        $payload = array_merge([
            '$representation' => 'YPREFIMMAT.$create'
        ], $data);

        // Journalisation de la requête
        Log::info('X3 ENTETE REQUEST', [
            'url' => $url,
            'payload' => $payload
        ]);

        // Envoi de la requête POST à l'API Sage X3
        $response = $this->client()->post($url, $payload);


        // Gestion de la réponse
        if (!$response->successful()) {
            // Journalisation de l'erreur en cas d'échec
            Log::error('X3 ENTETE ERROR', [
                'url' => $url,
                'payload' => $payload,
                'response' => $response->body(),
                'status' => $response->status()
            ]);
            throw new Exception('Erreur création entête X3: ' . $response->body());
        }

        // Journalisation du succès
        Log::info('X3 ENTETE SUCCESS', [
            'response' => $response->body()
        ]);

        // Retour de la réponse JSON
        return $response->json();
    }


    public function createLigne(array $data)
    {
        $url = $this->baseUrl . '/YPREFIMMATD?representation=YPREFIMMATD.$create';

        $payload = array_merge([
            '$representation' => 'YPREFIMMATD.$create'
        ], $data);

        Log::info('X3 LIGNE REQUEST', [
            'url' => $url,
            'payload' => $payload
        ]);
        $response = $this->client()->post($url, $payload);

        if (!$response->successful()) {
            Log::error('X3 LIGNE ERROR', [
                'url' => $url,
                'payload' => $payload,
                'response' => $response->body(),
                'status' => $response->status()
            ]);
            throw new Exception('Erreur création ligne X3: ' . $response->body());
        }

        Log::info('X3 LIGNE SUCCESS', [
            'response' => $response->body()
        ]);

        return $response->json();
    }
}
