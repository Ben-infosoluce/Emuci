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
            ->acceptJson()
            ->contentType('application/json')
            ->timeout(30);
    }

    public function createEntete(array $data)
    {
        $url = $this->baseUrl . '/YPREFIMMAT?representation=YPREFIMMAT.$create';

        $payload = array_merge([
            '$representation' => 'YPREFIMMAT.$create'
        ], $data);


        Log::info('X3 ENTETE REQUEST', [
            'url' => $url,
            'payload' => $payload
        ]);

        $response = $this->client()->post($url, [
            'json' => $payload
        ]);

        if (!$response->successful()) {
            Log::error('X3 ENTETE ERROR', [
                'url' => $url,
                'payload' => $payload,
                'response' => $response->body(),
                'status' => $response->status()
            ]);
            throw new Exception('Erreur création entête X3: ' . $response->body());
        }

        Log::info('X3 ENTETE SUCCESS', [
            'response' => $response->body()
        ]);

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

        $response = $this->client()->post($url, [
            'json' => $payload
        ]);

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
