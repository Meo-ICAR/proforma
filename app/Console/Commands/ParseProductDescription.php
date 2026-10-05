<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pratica;
use Illuminate\Support\Facades\DB;

class ParseProductDescription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:product-description';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse denominazione_prodotto to extract tipo_prodotto, denominazione_banca, rata, nrate';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Inizio parsing del campo denominazione_prodotto...');
        
        $pratiche = Pratica::whereNotNull('denominazione_prodotto')->get();
        
        $updated = 0;
        $errors = 0;
        
        foreach ($pratiche as $pratica) {
            try {
                $parsed = $this->parseDescription($pratica->denominazione_prodotto);
                
                if ($parsed) {
                    $pratica->update([
                        'tipo_prodotto' => $parsed['tipo_prodotto'],
                        'denominazione_banca' => $parsed['denominazione_banca'],
                        'rata' => $parsed['rata'],
                        'nrate' => $parsed['nrate'],
                    ]);
                    
                    $updated++;
                    $this->line("Aggiornata pratica {$pratica->codice_pratica}: {$parsed['tipo_prodotto']} - {$parsed['denominazione_banca']} - {$parsed['rata']} x {$parsed['nrate']}");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("Errore elaborazione pratica {$pratica->codice_pratica}: " . $e->getMessage());
            }
        }
        
        $this->info("Parsing completato. {$updated} pratiche aggiornate, {$errors} errori.");
        
        return Command::SUCCESS;
    }
    
    private function parseDescription($description)
    {
        if (empty($description)) {
            return null;
        }
        
        // Pattern: "TipoProdotto - Banca - Rata x NRate (Codice)"
        // Esempi: 
        // "Cessione - CAPITALFIN - 158 x 120 (QT06447)"
        // "Prestito - AGOS SPA - 155,36 x 60 (QT06446)"
        // "Delega -  - 100 x 84 (QT06438)"
        // "Mutuo - ING BANK - PRS - 599 x  (QT06440)"
        
        // Rimuovi il codice finale tra parentesi
        $description = preg_replace('/\s*\([^)]*\)\s*$/', '', $description);
        
        // Dividi per " - "
        $parts = explode(' - ', $description);
        
        $tipo_prodotto = trim($parts[0] ?? '');
        $denominazione_banca = '';
        $rata = null;
        $nrate = null;
        
        // Cerca la banca e i dati finanziari
        $remaining_parts = array_slice($parts, 1);
        
        foreach ($remaining_parts as $part) {
            $part = trim($part);
            
            // Se contiene "x" probabilmente è la parte finanziaria
            if (preg_match('/(\d+(?:,\d+)?)\s*x\s*(\d*)/', $part, $matches)) {
                $rata = str_replace(',', '.', $matches[1]);
                $nrate = !empty($matches[2]) ? (int)$matches[2] : null;
            } elseif (!empty($part) && !preg_match('/\d+\s*x/', $part)) {
                // È probabilmente il nome della banca
                $denominazione_banca = $part;
            }
        }
        
        // Se non abbiamo trovato la banca, prova a estrarla dal pattern completo
        if (empty($denominazione_banca) && preg_match('/^[^-]+-\s*([^-]+)-/', $description, $matches)) {
            $denominazione_banca = trim($matches[1]);
        }
        
        return [
            'tipo_prodotto' => $tipo_prodotto,
            'denominazione_banca' => $denominazione_banca,
            'rata' => $rata,
            'nrate' => $nrate,
        ];
    }
}
