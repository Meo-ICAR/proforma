<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BusinessCentralService
{
    protected string $tenantId;
    protected string $clientId;
    protected string $clientSecret;
    protected string $scope;

    public function __construct()
    {
        $this->tenantId = env('BC_TENANT_ID');
        $this->clientId = env('BC_CLIENT_ID');
        $this->clientSecret = env('BC_CLIENT_SECRET');
        $this->scope = env('BC_SCOPE');
    }

    /**
     * Recupera il Token OAuth2 (Equivale alla tua GET in Postman)
     */
    public function getToken(): ?string
    {
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";

        $response = Http::asForm()->post($url, [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => $this->scope,
        ]);

        if ($response->failed()) {
            Log::error("Errore recupero Token BC: " . $response->body());
            return null;
        }

        return $response->json('access_token');
    }

    /**
     * Invia i dati a Business Central (Equivale alla tua POST)
     */
    public function inviaPrimaNota(array $documenti)
    {
        $token = $this->getToken();

        if (!$token) return false;

        $env = env('BC_ENVIRONMENT');
        $companyId = env('BC_COMPANY_ID');

        $url = env('COGE_URL_POST');

        if (!$url) {
            $url = "https://api.businesscentral.dynamics.com/v2.0/{$this->tenantId}/{$env}/ODataV4/ANCWS_SendExtCoge";

            $payload = [
                'docs' => json_encode(['docs' => $documenti])
            ];

            Log::debug("Payload inviato (URL costruito):", $payload);

            return Http::withToken($token)
                ->withQueryParameters(['Company' => $companyId])
                ->post($url, $payload);
        }

        // Costruiamo il body esattamente come nel tuo file JSON
        $payload = [
            'docs' => json_encode(['docs' => $documenti])
        ];

        Log::debug("Payload inviato (URL diretto):", $payload);

        // Use the URL as is from .env (already contains parameters)
        return Http::withToken($token)
            ->post($url, $payload);
    }
}
