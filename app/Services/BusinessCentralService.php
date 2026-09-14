<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BusinessCentralService
{
    protected string $tenantId;

    protected string $clientId;

    protected string $clientSecret;

    protected string $scope;

    public function __construct()
    {
        $this->tenantId = config('services.business_central.tenant_id');
        $this->clientId = config('services.business_central.client_id');
        $this->clientSecret = config('services.business_central.client_secret');
        $this->scope = config('services.business_central.scope');
    }

    /**
     * Recupera il Token OAuth2 (Equivale alla tua GET in Postman)
     */
    public function getToken(): ?string
    {
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";

        $response = Http::asForm()->post($url, [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => $this->scope,
        ]);

        if ($response->failed()) {
            Log::error('Errore recupero Token BC: '.$response->body());

            return null;
        }

        return $response->json('access_token');
    }

    /**
     * Invia i dati a Business Central (Equivale alla tua POST)
     *
     * Restituisce sempre una Response (mai false/null): il chiamante può
     * controllare uniformemente ->successful()/->status()/->body() senza
     * doversi ricordare di guardia extra sul valore di ritorno — prima, un
     * fallimento nel recupero del token restituiva `false`, che mandava in
     * errore fatale i chiamanti che invocavano ->status() direttamente
     * (es. coge:sync-monthly) senza controllarlo prima.
     */
    public function inviaPrimaNota(array $documenti): Response
    {
        $token = $this->getToken();

        if (! $token) {
            throw new RuntimeException('Impossibile recuperare il token OAuth2 di Business Central: verificare le credenziali in services.business_central.');
        }

        $env = config('services.business_central.environment');
        $companyId = config('services.business_central.company_id');
        $url = null;
        // $url = env('COGE_URL_POST');
        // https://api.businesscentral.dynamics.com/v2.0/85a25e3b-9459-45eb-b9c9-1dc26caf2edf/Production /ODataV4/ANCWS_SendExtCoge?Company=be36b58a-e198-ed11-bff5-000d3ab8edc9
        if (! $url) {
            $url = "https://api.businesscentral.dynamics.com/v2.0/{$this->tenantId}/{$env}/ODataV4/ANCWS_SendExtCoge";

            $payload = [
                'docs' => json_encode(['docs' => $documenti]),
            ];
            Log::debug('URL costruito:', ['url' => $url]);
            Log::debug('Payload inviato (URL costruito):', $payload);

            return Http::withToken($token)
                ->withQueryParameters(['Company' => $companyId])
                ->post($url, $payload);
        }

        // Costruiamo il body esattamente come nel tuo file JSON
        $payload = [
            'docs' => json_encode(['docs' => $documenti]),
        ];

        Log::debug('Payload inviato (URL diretto):', $payload);

        // Use the URL as is from .env (already contains parameters)
        return Http::withToken($token)
            ->post($url, $payload);
    }
}
