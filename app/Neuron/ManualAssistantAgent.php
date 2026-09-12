<?php

declare(strict_types=1);

namespace App\Neuron;

use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\RAG;
use NeuronAI\RAG\VectorStore\FileVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use RuntimeException;

/**
 * Assistente AI che risponde a domande sull'uso dell'applicazione, al posto
 * del manuale operativo statico (Manual.md, Istruction.md): i due documenti
 * sono indicizzati nel vector store da `php artisan manual:sync` e recuperati
 * per similarità a ogni domanda.
 */
class ManualAssistantAgent extends RAG
{
    public static function manualSources(): array
    {
        return [
            base_path('Manual.md'),
            base_path('Istruction.md'),
        ];
    }

    protected function provider(): AIProviderInterface
    {
        $key = env('ANTHROPIC_API_KEY');

        if (blank($key)) {
            throw new RuntimeException('Chiave API Anthropic mancante: imposta ANTHROPIC_API_KEY nel file .env');
        }

        return new Anthropic(
            key: (string) $key,
            model: (string) env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
            max_tokens: 4096,
        );
    }

    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'Sei l\'assistente utente di PROFORMA, applicazione di gestione contabile delle provvigioni per mediatori creditizi.',
                'Rispondi SOLO usando le informazioni recuperate dal manuale operativo (documenti allegati al contesto).',
            ],
            steps: [
                'Se il manuale non contiene la risposta, dillo esplicitamente invece di inventare procedure.',
                'Quando possibile, indica in quale sezione/schermata dell\'applicazione si trova la funzione descritta.',
            ],
            output: [
                'Rispondi in italiano, in modo diretto e operativo (passi numerati quando descrivi una procedura).',
            ],
        );
    }

    protected function embeddings(): EmbeddingsProviderInterface
    {
        $baseUri = env('GEMINI_BASE_URL');

        if (blank($baseUri)) {
            throw new RuntimeException('URL del proxy Gemini mancante: imposta GEMINI_BASE_URL nel file .env');
        }

        return new ProxiedGeminiEmbeddingsProvider(
            baseUri: rtrim((string) $baseUri, '/').'/models/',
            key: (string) env('GEMINI_API_KEY', ''),
            model: (string) env('GEMINI_EMBEDDINGS_MODEL', 'gemini-embedding-001'),
        );
    }

    protected function vectorStore(): VectorStoreInterface
    {
        return new FileVectorStore(
            directory: storage_path('app/neuron/manual'),
            name: 'manual',
        );
    }
}
