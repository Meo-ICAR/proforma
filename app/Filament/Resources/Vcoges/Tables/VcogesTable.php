<?php

namespace App\Filament\Resources\Vcoges\Tables;

use App\Models\Coges as Coge;
use App\Models\Vcoge;  // Make sure this is correctly cased
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class VcogesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mese')
                    ->searchable(),
                TextColumn::make('entrata')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->label('Entrata')
                    ->sortable(),
                TextColumn::make('uscita')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->label('Uscita')
                    ->sortable(),
                TextColumn::make('saldo')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
            ])
            ->defaultSort('mese', 'desc')
            ->filters([
                SelectFilter::make('mese')
                    ->label('Mesi')
                    ->options(fn() => Vcoge::getDistinctMonths())
                    ->multiple()
                // In Filament 4.x, il valore di default viene applicato automaticamente
                // se non diversamente specificato.
            ])
            ->recordActions([
                Action::make('invia')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->label('Invia in Contabilita la primanota ')
                    ->action(function (Vcoge $record) {
                        Log::info("Inizio invio dati in contabilità per il mese: {$record->mese}");

                        try {
                            $coge1 = Coge::where(['fonte' => 'mediafacile', 'entrata_uscita' => 'Entrata'])->first();
                            $coge2 = Coge::where(['fonte' => 'mediafacile', 'entrata_uscita' => 'Uscita'])->first();

                            if (!$coge1) {
                                Log::warning("Configurazione Coge per 'Entrata' (fonte mediafacile) non trovata. Uso i valori di default.");
                            }
                            if (!$coge2) {
                                Log::warning("Configurazione Coge per 'Uscita' (fonte mediafacile) non trovata. Uso i valori di default.");
                            }

                            // 1. Acquire Token
                            $accessToken = self::getAccessToken();

                            if (!$accessToken) {
                                Notification::make()
                                    ->title('Errore Autenticazione')
                                    ->body('Impossibile ottenere il token di accesso. Controlla i log.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Calculate end of month for PostingDate
                            $datacoge = Carbon::createFromFormat('Y-m', $record->mese)->endOfMonth()->toDateString();

                            // 2. Prepare Data
                            $innerDocs = [
                                [
                                    'JournalTemplateName' => 'GENERALE',
                                    'JournalBatchName' => 'COGEWS',
                                    'LineNo' => '1',
                                    'AccountNo' => str_replace('.', '', $coge1->conto_dare ?? '0128002'),
                                    'PostingDate' => $datacoge,
                                    'DocumentNo' => 'DOCNO',
                                    'Description' => $coge1->descrizione_dare ?? 'Clienti c/fatture da emettere',
                                    'Amount' => -$record->entrata
                                ],
                                [
                                    'JournalTemplateName' => 'GENERALE',
                                    'JournalBatchName' => 'COGEWS',
                                    'LineNo' => '2',
                                    'AccountNo' => str_replace('.', '', $coge1->conto_avere ?? '0501001'),
                                    'PostingDate' => $datacoge,
                                    'DocumentNo' => 'DOCNO',
                                    'Description' => $coge1->descrizione_avere ?? 'Ricavi Italia',
                                    'Amount' => $record->entrata
                                ],
                                [
                                    'JournalTemplateName' => 'GENERALE',
                                    'JournalBatchName' => 'COGEWS',
                                    'LineNo' => '1',
                                    'AccountNo' => str_replace('.', '', $coge2->conto_dare ?? '0221002'),
                                    'PostingDate' => $datacoge,
                                    'DocumentNo' => 'DOCNO1',
                                    'Description' => $coge2->descrizione_avere ?? 'Fornitori c/fatture da ricevere',
                                    'Amount' => $record->uscita
                                ],
                                [
                                    'JournalTemplateName' => 'GENERALE',
                                    'JournalBatchName' => 'COGEWS',
                                    'LineNo' => '2',
                                    'AccountNo' => str_replace('.', '', $coge2->conto_avere ?? '0405019'),
                                    'PostingDate' => $datacoge,
                                    'DocumentNo' => 'DOCNO1',
                                    'Description' => $coge2->descrizione_dare ?? 'Consulenze diverse',
                                    'Amount' => -$record->uscita
                                ]
                            ];

                            $innerJson = json_encode(['docs' => $innerDocs]);

                            // The payload wrapped in "docs" property as a string
                            $payload = [
                                'docs' => $innerJson
                            ];

                            Log::debug("Payload preparato per l'invio:", $payload);

                            // Log cURL equivalent
                            $curlCommand = "curl -X POST '" . env('COGE_URL_POST') . "' "
                                . "-H 'Authorization: Bearer " . $accessToken . "' "
                                . "-H 'Content-Type: application/json' "
                                . "-d '" . json_encode($payload) . "'";
                            Log::debug('Comando cURL equivalente: ' . $curlCommand);

                            // 3. Send Data
                            Log::info("Invio dati all'API di contabilità...");
                            $dataResponse = Http::withToken($accessToken)
                                ->post(env('COGE_URL_POST'), $payload);

                            Log::info('API Response Status: ' . $dataResponse->status());
                            Log::debug('API Response Body: ' . $dataResponse->body());

                            if ($dataResponse->successful()) {
                                Notification::make()
                                    ->title('Invio Completato')
                                    ->body('I dati sono stati inviati correttamente.')
                                    ->success()
                                    ->send();
                            } else {
                                Log::error("Errore durante l'invio dati: " . $dataResponse->body());
                                Notification::make()
                                    ->title('Errore Invio Dati')
                                    ->body('Errore API: ' . $dataResponse->body())
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Log::error("Eccezione imprevista durante l'invio in contabilità: " . $e->getMessage(), [
                                'exception' => $e,
                                'mese' => $record->mese,
                                'trace' => $e->getTraceAsString()
                            ]);
                            Notification::make()
                                ->title('Errore Inaspettato')
                                ->body('Si è verificato un errore imprevisto. Controlla i log per i dettagli.')
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->toolbarActions([]);
    }

    private static function getAccessToken(): ?string
    {
        Log::info('Richiesta token di autenticazione (POST asForm)...');

        $url = env('COGE_URL_GET_TOKEN');

        $params = [
            'grant_type' => 'client_credentials',
            'scope' => 'https://api.businesscentral.dynamics.com/.default',
            'client_id' => env('COGE_CLIENT_ID_TOKEN'),
            'client_secret' => env('COGE_CLIENT_SECRET_TOKEN'),
        ];

        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => array(
                    'Cookie: fpc=AibG5DveuGpLlJG24tKl8To-u5M_AQAAADhUDuEOAAAA; stsservicecookie=estsfd; x-ms-gateway-slice=estsfd'
                ),
            ));
            Log::debug('Comando cURL: ' . json_encode([
                'url' => $url,
                'method' => 'GET',
                'params' => $params
            ]));
            $response = curl_exec($curl);

            curl_close($curl);

            Log::debug('Risposta cURL: ' . $response);

            $responseData = json_decode($response, true);
            if (!$responseData) {
                Log::error('Errore parsing JSON risposta: ' . $response);
                return null;
            }

            $token = $responseData['access_token'] ?? null;
            if (!$token) {
                Log::error('Token non trovato nella risposta: ' . $response);
                return null;
            }

            Log::info('Token ottenuto con successo.');
            Log::debug('Access Token (primi 20 char): ' . substr($token, 0, 20) . '...');

            return $token;
        } catch (\Exception $e) {
            Log::error('Eccezione durante il recupero del token: ' . $e->getMessage());
            return null;
        }
    }

    private static function getAccessTokenNew(): ?string
    {
        Log::info('Richiesta token di autenticazione (POST asForm)...');

        $url = env('COGE_URL_GET_TOKEN');

        $params = [
            'grant_type' => 'client_credentials',
            'scope' => 'https://api.businesscentral.dynamics.com/.default',
            'client_id' => env('COGE_CLIENT_ID_TOKEN'),
            'client_secret' => env('COGE_CLIENT_SECRET_TOKEN'),
        ];

        try {
            // Log cURL equivalent for POST form
            $tokenCurl = "curl -X POST '{$url}' "
                . "-H 'Content-Type: application/x-www-form-urlencoded' "
                . "-d '" . http_build_query($params) . "'";
            Log::debug('Comando cURL token: ' . $tokenCurl);

            $response = Http::asForm()->post($url, $params);

            Log::debug('Token Response Status: ' . $response->status());
            Log::debug('Token Response Body: ' . $response->body());

            if ($response->failed()) {
                Log::error('Errore ottenimento token: ' . $response->body());
                return null;
            }

            $token = $response->json('access_token');
            Log::info('Token ottenuto con successo.');
            Log::debug('Access Token: ' . $token);

            return $token;
        } catch (\Exception $e) {
            Log::error('Eccezione durante il recupero del token: ' . $e->getMessage());
            return null;
        }
    }
}
