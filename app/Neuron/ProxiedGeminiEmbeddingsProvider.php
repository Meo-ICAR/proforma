<?php

declare(strict_types=1);

namespace App\Neuron;

use NeuronAI\HttpClient\HttpClientInterface;
use NeuronAI\RAG\Embeddings\GeminiEmbeddingsProvider;

/**
 * GeminiEmbeddingsProvider punta sempre a `generativelanguage.googleapis.com`
 * (baseUri è una proprietà interna, non configurabile dal costruttore): qui la
 * sovrascriviamo per passare dal proxy aziendale (GEMINI_BASE_URL), che
 * inoltra le chiamate a Google. Il proxy non inietta una propria chiave:
 * l'header `x-goog-api-key` (impostato da GeminiEmbeddingsProvider con la
 * chiave passata al costruttore) arriva fino a Google, quindi va comunque
 * fornita una chiave valida (GOOGLE_API_KEY) lato app.
 */
class ProxiedGeminiEmbeddingsProvider extends GeminiEmbeddingsProvider
{
    public function __construct(string $baseUri, string $key, string $model, array $config = [], ?HttpClientInterface $httpClient = null)
    {
        $this->baseUri = $baseUri;

        parent::__construct($key, $model, $config, $httpClient);
    }
}
