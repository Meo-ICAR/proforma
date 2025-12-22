<?php

namespace App\Console\Commands;

use App\Models\Provvigione;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportProvvigioniFromApi extends Command
{
    protected $signature = 'provvigioni:import-api
                            {--start-date= : Start date (YYYY-MM-DD)}
                            {--end-date= : End date (YYYY-MM-DD)}}';

    protected $description = 'Import provvigioni from external API';

    public function handle()
    {
        /*
        ID Compenso	Data Inserimento	Descrizione	Tipo	Importo	Importo Effettivo	Stato	Data Pagamento	N. Fattura	Data Fattura	Data Status	Status Compenso	Denominazione Riferimento	Entrata Uscita	ID Pratica	Agente	Istituto finanziario	Partita IVA Agente	Codice Fiscale Agente

        */
        $endDate = $this->option('end-date') ? Carbon::parse($this->option('end-date')) : now();
        $startDate = $this->option('start-date')
            ? Carbon::parse($this->option('start-date'))
            : $endDate->copy()->subDays(7);

        $this->info("Importing provvigioni from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        try {
            $apiUrl = env('MEDIAFACILE_BASE_URL', 'https://races.mediafacile.it/ws/hassisto.php');
            $queryParams = [
                'table' => 'compensi',
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

            // Log the request and response for debugging
            \Log::info('Provvigioni API Request', [
                'url' => $apiUrl,
                'params' => $queryParams,
                'status' => $response->status(),
                'response_size' => strlen($response->body()),
            ]);

            if (!$response->successful()) {
                \Log::error('Provvigioni API Error', [
                    'status' => $response->status(),
                    'response' => substr($response->body(), 0, 1000),
                ]);
                $this->error('API request failed with status: ' . $response->status());
                return 1;
            }

            $responseBody = trim($response->body());

            if (empty($responseBody)) {
                $this->error('Empty response body from API');
                return 1;
            }



            // Process lines
            $lines = explode("\n", $responseBody);
            $lines = array_filter($lines, function($line) {
                return trim($line) !== '';
            });
            $lines = array_values($lines);

            // Remove empty lines (already done above)
            $lines = array_values($lines); // Reindex array

            if (count($lines) < 2) { // Need at least header row + 1 data row
                $this->info('No data rows found in API response');
                return 0;
            }

            // Get headers from first line and clean them
            $headers = $this->parseLine($lines[0]);
            $data = [];
            $headerCount = count($headers);

            // Define required headers in the exact expected order
            $requiredHeaders = [
                'ID Compenso',
                'Data Inserimento',
                'Descrizione',
                'Tipo',
                'Importo',
                'Importo Effettivo',
                'Stato',
                'Data Pagamento',
                'N. Fattura',
                'Data Fattura',
                'Data Status',
                'Status Compenso',
                'Denominazione Riferimento',
                'Entrata Uscita',
                'ID Pratica',
                'Agente',
                'Istituto finanziario',
                'Partita IVA Agente',
                'Codice Fiscale Agente',
                'ANNULLATA'
               ];

            // Check if all required headers are present in the response
            $missingHeaders = array_diff($requiredHeaders, $headers);
            if (!empty($missingHeaders)) {
                $this->error('Missing required headers in API response:');
                foreach ($missingHeaders as $missing) {
                    $this->error(" - $missing");
                }
                $this->error('Actual headers received: ' . implode(', ', $headers));
                return 1;
            }

            // Verify the order of headers matches exactly
            if ($headers !== $requiredHeaders) {
                $this->warn('Warning: Headers are not in the expected order.');
                $this->warn('Expected order: ' . implode('\t', $requiredHeaders));
                $this->warn('Actual order:   ' . implode('\t', $headers));

                // Reorder the headers to match the expected order
                $reorderedHeaders = [];
                $headerMap = array_flip($headers);
                foreach ($requiredHeaders as $required) {
                    if (isset($headerMap[$required])) {
                        $reorderedHeaders[] = $required;
                    }
                }
                $headers = $reorderedHeaders;
                $headerCount = count($headers);
                $this->info('Headers have been reordered to match expected format.');
            }

            // Debug: Log the headers
            \Log::debug('Headers:', ['headers' => $headers, 'count' => $headerCount]);

            // Process data lines
            for ($i = 1; $i < count($lines); $i++) {
                $values = $this->parseLine($lines[$i]);

                // Skip empty lines
                if (empty($values)) {
                    continue;
                }

                // If we have more values than headers, truncate the extra values
                if (count($values) > $headerCount) {
                    $values = array_slice($values, 0, $headerCount);
                }
                // If we have fewer values than headers, pad with nulls
                elseif (count($values) < $headerCount) {
                    $values = array_pad($values, $headerCount, null);
                }

                try {
                    $data[] = array_combine($headers, $values);
                } catch (\Exception $e) {
                    $this->warn(sprintf(
                        'Error combining row %d: %s',
                        $i + 1,
                        $e->getMessage()
                    ));
                    \Log::error('Error combining row data', [
                        'headers' => $headers,
                        'values' => $values,
                        'error' => $e->getMessage()
                    ]);
                }
            }


            if (empty($data)) {
                $this->info('No records found in the specified date range');
                $this->info('API Response Status: ' . $response->status());
                $this->info('API Response Preview: ' . substr($response->body(), 0, 200));
                return 0;
            }

            // Verify response headers match expected format
            if (!empty($data)) {
                $firstItem = $data[0];
                $expectedHeaders = [
                'ID Compenso',
                'Data Inserimento',
                'Descrizione',
                'Tipo',
                'Importo',
                'Importo Effettivo',
                'Stato',
                'Data Pagamento',
                'N. Fattura',
                'Data Fattura',
                'Data Status',
                'Status Compenso',
                'Denominazione Riferimento',
                'Entrata Uscita',
                'ID Pratica',
                'Agente',
                'Istituto finanziario',
                'Partita IVA Agente',
                'Codice Fiscale Agente',
                   'ANNULLATA'
                ];

                $actualHeaders = array_keys($firstItem);

                // Check if headers are in the correct order
                $matchingHeaders = array_intersect($expectedHeaders, $actualHeaders);
                if ($matchingHeaders !== $expectedHeaders) {
                    $this->warn('Warning: Headers are not in the expected order.');
                    $this->warn('Expected order: ' . implode('\t', $expectedHeaders));
                    $this->warn('Actual order:   ' . implode('\t', $matchingHeaders));
                }
            }

            $imported = 0;
            $updated = 0;
            $errors = 0;
            $skipped = 0;
            $updatedCount = 0;

            foreach ($data as $item) {
                try {
                    $provvigioneData = $this->mapApiToModel($item);

                    // Use 'Codice Pratica' as the identifier since that's what's in the API response
                    if (empty($item['ID Compenso'])) {
                        $this->warn('Skipping item without ID Compenso: ' . json_encode($item));
                        $errors++;
                        continue;
                    }

                    // Ensure we have the ID Compenso in our data
          //          $provvigioneData['id'] = $item['ID Compenso'];

                    $existing = Provvigione::where('id', $provvigioneData['id'])->first();
                    if ($existing) {
                        // Check if any of the timestamp fields are already set
                        if (empty($existing->sended_at) && empty($existing->received_at) && empty($existing->paided_at)) {
                            $existing->update($provvigioneData);
                            $updated++;
                           // $this->info("Updated provvigione: {$provvigioneData['id']}");
                        } else {

                            $skipped++;
                        }
                    } else {
                      //  $provvigioneData->stato='Inserito';
                        Provvigione::create($provvigioneData);
                        $imported++;

                       // $this->info("Imported new provvigione: {$provvigioneData['id']}");
                    }
                } catch (\Exception $e) {
                    $this->error("Error processing item: " . $e->getMessage());
                    $errors++;
                }
            }
            // Update customer names from pratiche if we have imported any records

            if ($updated > 0) {


                $updatedCount = \DB::update(
                    "UPDATE provvigioni p
                    INNER JOIN pratiches k ON k.id = p.id_pratica
                    SET  p.status_pratica = k.stato_pratica
                    WHERE p.cognome IS NOT NULL"
                );

                $this->info("Updated {$updatedCount} records with status from pratiche.");
            }
              // Update customer names from pratiche if we have imported any records
              if ($imported + $updatedCount> 0) {
                $this->info("Updating customer names from pratiche...");

                $updatedCount = \DB::update(
                    "UPDATE provvigioni p
                    INNER JOIN pratiches k ON k.id = p.id_pratica
                    SET p.cognome = k.cognome_cliente, p.nome = k.nome_cliente , p.status_pratica = k.stato_pratica
                    WHERE p.cognome IS NULL"
                );

                $this->info("Updated {$updatedCount} records with customer names from pratiche.");
            }
                $updatedCount = \DB::update(
                    "update provvigioni set status_pratica = 'PERFEZIONATA'  WHERE status_pratica <'A' and status_compenso  = 'Pratica perfezionata'"
                );
                $this->info("Updated {$updatedCount} records with status_pratica PERFEZIONATA");
               $updatedCount = \DB::update(
                    "UPDATE provvigioni p

                    SET  p.stato = 'Inserito', p.deleted_at = NULL
                    WHERE p.stato IS  NULL and (p.status_pratica = 'PERFEZIONATA' or p.status_pratica = 'IN AMMORTAMENTO')"
                );
                $this->info("Updated {$updatedCount} records with stato from pratiche.");
                $updatedCount = \DB::update(
                    "UPDATE provvigioni p
                    INNER JOIN vwprovvcoordinamento  v on v.id_pratica = p.id_pratica and p.importo = v.minimo
                    set p.stato = 'Coordinamento'
                    where p.stato = 'Inserito'"
                );
                $this->info("Updated {$updatedCount} records with provv. stato = coordinamento");

            $insertedFornitoriCount = \DB::insert(
                    "insert into fornitoris (id,name,nome, piva, cf ) select uuid(),denominazione_riferimento,denominazione_riferimento, piva, cf from vwfornitorinew"
            );
            $this->info("Inserted {$insertedFornitoriCount} records into produttori.");

            $insertedClientiCount = \DB::insert(
                "insert into clientis (id,name,nome) select uuid(),denominazione_riferimento,denominazione_riferimento from vwclientinew"
            );
            $this->info("Inserted {$insertedClientiCount} records into mandatarie.");

            $insertedFornitoriCount = \DB::update("update provvigioni p inner join fornitoris f on f.name = p.denominazione_riferimento set p.fornitori_id = f.id");
            $this->info("Updated {$insertedFornitoriCount} records with fornitori_id from fornitori.");

            $insertedClientiCount = \DB::update("update provvigioni p inner join clientis c on c.name = p.denominazione_riferimento set p.clienti_id = c.id");
            $this->info("Updated {$insertedClientiCount} records with clientis_id from clientis.");

/*
 $insertedClientiCount = \DB::update("UPDATE provvigioni p inner join vwprovvdoppie v on v.minimo = p.id set  stato = 'Annullato', sended_at = null, paided_at= null, received_at = null, annullato = true
where v.minimo < v.maximo");
 $this->info("Deleted {$insertedClientiCount} records with duplicated provvigioni.");
*/
            $this->info("Import completed. Imported: {$imported}, Updated: {$updated}, Errors: {$errors}");
            return 0;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $this->error('HTTP Request Error: ' . $e->getMessage());
            \Log::error('Provvigioni API Request Exception', [
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
            \Log::error('Unexpected Error in Provvigioni Import', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    protected function parseLine($line)
    {
        // First try to split by tab
        $parts = explode("\t", trim($line));

        // If we only got one part, try splitting by multiple spaces
        if (count($parts) <= 1) {
            $parts = preg_split('/\s{2,}/', trim($line));
        }

        // Clean up each part
        return array_map(function($part) {
            return trim($part, " \t\n\r\0\x0B\"'`");
        }, $parts);
    }

    protected function mapApiToModel(array $apiData): array
    {
        // Helper function to parse dates from API
        $parseDate = function($dateValue) {
            if (empty($dateValue)) return null;

            try {
                // Handle DD/MM/YYYY format
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateValue)) {
                    return Carbon::createFromFormat('d/m/Y', $dateValue)->startOfDay();
                }
                // Handle other date formats
                if (strtotime($dateValue)) {
                    return Carbon::parse($dateValue);
                }
            } catch (\Exception $e) {
                $this->warn("Failed to parse date '" . $dateValue . "': " . $e->getMessage());
            }
            return null;
        };



        $requiredFields = [
                'ID Compenso',
                'Data Inserimento',
                'Descrizione',
                'Tipo',
                'Importo',
                'Importo Effettivo',
                'Stato',
                'Data Pagamento',
                'N. Fattura',
                'Data Fattura',
                'Data Status',
                'Status Compenso',
                'Denominazione Riferimento',
                'Entrata Uscita',
                'ID Pratica',
                'Agente',
                'Istituto finanziario',
                'Partita IVA Agente',
                'Codice Fiscale Agente',
                'ANNULLATA'
              ];


        // Parse all date fields
        $dataInserimento = $parseDate($apiData['Data Inserimento'] ?? null);
        $dataPagamento = $parseDate($apiData['Data Pagamento'] ?? null);
        $dataFattura = $parseDate($apiData['Data Fattura'] ?? null);
        $dataStatus = $parseDate($apiData['Data Status'] ?? null);


        return [
            'id' => $apiData['ID Compenso'] ?? null,
            'data_inserimento_compenso' => $dataInserimento ? $dataInserimento->toDateTimeString() : null,
            'descrizione' => $apiData['Descrizione'] ?? null,
            'tipo' => $apiData['Tipo'] ?? 'provvigione',
            'importo' => is_numeric($apiData['Importo']) ? $apiData['Importo'] : (is_string($apiData['Importo']) ? (float) str_replace(',', '.', $apiData['Importo']) : 0),
            'importo_effettivo' => is_numeric($apiData['Importo Effettivo'] ?? null) ? $apiData['Importo Effettivo'] : (is_string($apiData['Importo Effettivo'] ?? null) ? (float) str_replace(',', '.', $apiData['Importo Effettivo']) : null),
            'status_pagamento' => $apiData['Stato'] ?? '',
            'data_pagamento' => $dataPagamento ? $dataPagamento->toDateTimeString() : null,
            'n_fattura' => $apiData['N. Fattura'] ?? null,
            'data_fattura' => $dataFattura ? $dataFattura->toDateTimeString() : null,
            'data_status' => $dataStatus ? $dataStatus->toDateTimeString() : null,
            'status_compenso' => $apiData['Status Compenso'] ?? null,
            'denominazione_riferimento' => $apiData['Denominazione Riferimento'] ?? null,
            'entrata_uscita' => $apiData['Entrata Uscita'] ?? null,
            'id_pratica' => $apiData['ID Pratica'] ?? null,
            'segnalatore' => $apiData['Agente'] ?? null,
            'istituto_finanziario' => $apiData['Istituto finanziario'] ?? null,
            'piva' => $apiData['Partita IVA Agente'] ?? null,
            'cf' => $apiData['Codice Fiscale Agente'] ?? null,
               'annullato' => $apiData['ANNULLATA'] ?? null,
            'fonte' => 'api',
        ];
    }
}
