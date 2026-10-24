<?php

namespace App\Console\Commands;

use App\Neuron\ManualAssistantAgent;
use App\Neuron\PlainHtmlReader;
use Illuminate\Console\Command;
use NeuronAI\RAG\DataLoader\FileDataLoader;

/**
 * Indicizza i manuali (Manual.md, Istruction.md, manuale utente e manuale tecnico in HTML) nel vector store
 * usato da ManualAssistantAgent. Da rilanciare ogni volta che i documenti cambiano.
 */
class SyncManualAssistant extends Command
{
    protected $signature = 'manual:sync';

    protected $description = 'Indicizza i manuali (utente e tecnico) nel vector store dell\'assistente AI';

    public function handle(): int
    {
        $storeFile = storage_path('app/neuron/manual/manual.store');

        if (file_exists($storeFile)) {
            unlink($storeFile);
        }

        $agent = ManualAssistantAgent::make();
        $totalChunks = 0;

        foreach (ManualAssistantAgent::manualSources() as $path) {
            if (! file_exists($path)) {
                $this->warn("File non trovato, saltato: {$path}");

                continue;
            }

            // I manuali .html si indicizzano come testo: senza un reader dedicato entrerebbe l'HTML grezzo, tag compresi.
            $documents = FileDataLoader::for($path)
                ->addReader('html', new PlainHtmlReader)
                ->getDocuments();
            $agent->addDocuments($documents);
            $totalChunks += count($documents);

            $this->info(basename($path).': '.count($documents).' blocchi indicizzati.');
        }

        $this->info("Totale: {$totalChunks} blocchi indicizzati.");

        return self::SUCCESS;
    }
}
