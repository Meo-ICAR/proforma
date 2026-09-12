<?php

declare(strict_types=1);

namespace App\Neuron;

use NeuronAI\HttpClient\HttpClientInterface;
use NeuronAI\RAG\Embeddings\GeminiEmbeddingsProvider;

/**
 * GeminiEmbeddingsProvider punta sempre a `generativelanguage.googleapis.com`
 * (baseUri è una proprietà interna, non configurabile dal costruttore): qui la
 * sovrascriviamo per passare dal proxy aziendale (GEMINI_BASE_URL), che
 * inoltra le chiamate a Gemini gestendo l'autenticazione lato server — per
 * questo la chiave può restare vuota lato app.
 */
class ProxiedGeminiEmbeddingsProvider extends GeminiEmbeddingsProvider
{
    public function __construct(string $baseUri, string $key, string $model, array $config = [], ?HttpClientInterface $httpClient = null)
    {
        $this->baseUri = $baseUri;

        parent::__construct($key, $model, $config, $httpClient);
    }
}
