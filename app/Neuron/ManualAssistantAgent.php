<?php

declare(strict_types=1);

namespace App\Neuron;

use Illuminate\Support\Facades\DB;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\RAG;
use NeuronAI\RAG\VectorStore\FileVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use NeuronAI\Tools\Toolkits\Calculator\CalculatorToolkit;
use RuntimeException;

/**
 * Assistente AI che risponde a domande sull'uso dell'applicazione (dal manuale
 * operativo, Manual.md e Istruction.md, indicizzati da `php artisan manual:sync`)
 * e a domande sui dati operativi (es. "ultime fatture inserite") tramite sola
 * lettura del database, limitata alle tabelle di dominio in QUERYABLE_TABLES.
 */
class ManualAssistantAgent extends RAG
{
    /**
     * Tabelle interrogabili dall'assistente via SQL: solo dati di dominio
     * (pratiche, provvigioni, fatture, prima nota, anagrafiche, ecc.).
     * Escluse deliberatamente le tabelle di autenticazione/infrastruttura
     * (users, password_reset_tokens, sessions, socialite_users, cache,
     * jobs, migrations, tmpfornitoriemail) per evitare che l'assistente
     * possa leggere credenziali, token o dati non pertinenti.
     *
     * @var array<int, string>
     */
    protected const QUERYABLE_TABLES = [
        'address_types', 'addresses',
        'blacklist_clienti_employees', 'blacklist_clienti_fornitori',
        'client_mandates', 'client_pratiches', 'client_relations', 'client_types',
        'clientis', 'clients',
        'coges', 'companies', 'compensos', 'enasarcos',
        'fatturas', 'firrs', 'fornitoriroles', 'fornitoris',
        'invoiceins', 'invoices', 'onorabilita',
        'pratica_document_requests', 'pratica_status_history', 'pratiches', 'pratiches_statos',
        'prima_nota_configs', 'prima_nota_entries',
        'proformas', 'provvigioni', 'provvigioni_statos',
        'purchase_invoices', 'sales_invoices',
        'tipoprodotto', 'tipoprodotto_sub', 'tipoprodotto_sub_constraints',
        'vcoge', 'venasarcotot', 'venasarcotrimestre',
    ];

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
                'Rispondi alle domande procedurali ("come si fa...") SOLO usando le informazioni recuperate dal manuale operativo (documenti allegati al contesto).',
                'Rispondi alle domande sui dati operativi (es. importi, date, elenchi, conteggi di record) interrogando il database con gli strumenti SQL disponibili, in sola lettura.',
            ],
            steps: [
                'Per domande procedurali: se il manuale non contiene la risposta, dillo esplicitamente invece di inventare procedure. Quando possibile, indica in quale sezione/schermata dell\'applicazione si trova la funzione descritta.',
                'Per domande sui dati: usa prima lo strumento di analisi schema per capire tabelle e colonne disponibili, poi esegui una query SELECT mirata. Se lo strumento SQL rifiuta la query (tabella non consentita o query di scrittura), dillo esplicitamente all\'utente invece di riprovare all\'infinito.',
                'Non rivelare mai contenuti di colonne che sembrano credenziali, password, token o segreti, anche se una query li restituisse per errore.',
            ],
            output: [
                'Rispondi in italiano, in modo diretto e operativo (passi numerati quando descrivi una procedura, tabelle o elenchi puntati quando presenti dati).',
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
            key: (string) env('GOOGLE_API_KEY', ''),
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

    protected function tools(): array
    {
        $pdo = DB::connection()->getPdo();

        return [
            CalculatorToolkit::make(),
            ScopedMySQLSchemaTool::make($pdo, self::QUERYABLE_TABLES),

            ScopedMySQLSelectTool::make($pdo, self::QUERYABLE_TABLES),
        ];
    }
}
