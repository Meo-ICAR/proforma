<?php

namespace App\Console\Commands;

use App\Models\Pratiche;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportPraticheFromApi extends Command
{
    protected $signature = 'pratiche:import-api
                            {--start-date= : Start date (YYYY-MM-DD)}
                            {--end-date= : End date (YYYY-MM-DD)}}';

    protected $description = 'Import pratiche from external API';

    public function handle()
    {
        $endDate = $this->option('end-date') ? Carbon::parse($this->option('end-date')) : now();
        $startDate = $this->option('start-date')
            ? Carbon::parse($this->option('start-date'))
            : $endDate->copy()->subDays(60);

        $this->info("Importing pratiche from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        try {
            $apiUrl = env('MEDIAFACILE_BASE_URL', 'https://races.mediafacile.it/ws/hassisto.php');
            $queryParams = [
                'table' => 'pratiche',
                'data_inizio' => $startDate->format('Y-m-d'),
                'data_fine' => $endDate->format('Y-m-d'),
            ];

            $response = Http::withHeaders([
                'Accept' => 'application/json, */*',
                'User-Agent' => 'ProForma Import/1.0',
                'X-Api-Key' => env('MEDIAFACILE_HEADER_KEY'),
            ])
            ->timeout(60) // 60 seconds timeout
            ->connectTimeout(10) // 10 seconds to establish connection
            ->withOptions([
                'http_errors' => false,
                'verify' => false, // Only if you need to bypass SSL verification
            ])
            ->retry(3, 1000, function ($exception) {
                // Retry on connection timeouts or server errors
                return $exception instanceof \Illuminate\Http\Client\ConnectionException ||
                       ($exception->getCode() >= 500);
            })
            ->get($apiUrl, $queryParams);



            if (!$response->successful()) {
                \Log::error('Pratiche API Error', [
                    'status' => $response->status(),
                    'response' => substr($response->body(), 0, 1000),
                ]);
                $this->error('API request failed with status: ' . $response->status());
                return 1;
            }

            $responseBody = trim($response->body());
            $lines = explode("\n", $responseBody);
            $lines = array_filter($lines, function($line) {
                return trim($line) !== '';
            });
            $lines = array_values($lines);

            if (empty($lines)) {
                $this->error('No data received from API');
                return 1;
            }


                // Get headers from first line
                $headers = $this->parseLine($lines[0]);
                $data = [];

                // Process data lines
                for ($i = 1; $i < count($lines); $i++) {
                    $values = $this->parseLine($lines[$i]);
                    if (count($values) === count($headers)) {
                        $data[] = array_combine($headers, $values);
                    }
                }

                if (empty($data)) {
                    $this->info('No records found in the specified date range');
                    return 0;
                }

                $imported = 0;
                $updated = 0;
                $errors = 0;

                foreach ($data as $item) {
                    try {
                        $praticaData = $this->mapApiToModel($item);

                        if (empty($praticaData['id'])) {
                            $this->warn('Skipping item without id: ' . json_encode($item));
                            $errors++;
                            continue;
                        }

                        $existing = Pratiche::where('id', $praticaData['id'])->first();

                        if ($existing) {
                            $existing->update($praticaData);
                            $updated++;
                          //  $this->info("Updated pratica: {$praticaData['id']}");
                        } else {
                            Pratiche::create($praticaData);
                            $imported++;
                          //  $this->info("Imported new pratica: {$praticaData['id']}");
                        }
                    } catch (\Exception $e) {
                        $this->error("Error processing item: " . $e->getMessage());
                        $errors++;
                    }
                }

                $this->info("Import completed. Imported: {$imported}, Updated: {$updated}, Errors: {$errors}");
                return 0;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $this->error('HTTP Request Error: ' . $e->getMessage());
            \Log::error('Pratiche API Request Exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'response' => $e->response ? [
                    'status' => $e->response->status(),
                    'body' => substr((string)$e->response->body(), 0, 1000)
                ] : null
            ]);
            return 1;
        } catch (\Throwable $e) {
            $this->error('Unexpected Error: ' . $e->getMessage());
            \Log::error('Unexpected Error in Pratiche Import', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    protected function parseLine($line)
    {
        return array_map('trim', explode("\t", $line));
    }

    protected function mapApiToModel(array $apiData): array
    {
        $dataInserimento = null;
        $dataInserimentoValue = $apiData['Data Inserimento Pratica'];

        if (!empty($dataInserimentoValue)) {
            try {
                $dateParts = explode('/', $dataInserimentoValue);
                if (count($dateParts) === 3) {
                    $dataInserimento = Carbon::createFromFormat('d/m/Y', $dataInserimentoValue);
                }
            } catch (\Exception $e) {
                // If parsing fails, leave as null
                $this->warn("Failed to parse date: " . $dataInserimentoValue);
            }
        }
        return [
            'id' => $apiData['ID Pratica'] ?? (string) Str::uuid(),
            'codice_pratica' => $apiData['ID Pratica'] ?? null,
            'nome_cliente' => $apiData['Cognome Cliente'] ?? null,
            'cognome_cliente' => $apiData['Nome Cliente'] ?? null,
            'codice_fiscale' => $apiData['Codice Fiscale'] ?? null,
            'denominazione_agente' => $apiData['Denominazione Agente'] ?? null,
            'partita_iva_agente' => $apiData['Partita IVA Agente'] ?? null,
            'denominazione_banca' => $apiData['Denominazione Banca'] ?? null,
            'tipo_prodotto' => $apiData['Tipo Prodotto'] ?? null,
            'denominazione_prodotto' => $apiData['Descrizione Prodotto'] ?? null,
            'data_inserimento_pratica' => $dataInserimento  ?? now(),
            'stato_pratica' => $apiData['Stato Pratica'] ?? null,
        ];
    }
}
