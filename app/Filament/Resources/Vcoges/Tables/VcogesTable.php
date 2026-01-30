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
use App\Services\BusinessCentralService;

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

                         

                            // Calculate end of month for PostingDate
                            $datacoge = Carbon::createFromFormat('Y-m', $record->mese)->endOfMonth()->toDateString();
                            $docnoEntrata = 'ENTRATA-' . $record->mese;
                            $docnoUscita = 'USCITA-' . $record->mese;
                            $entrata = $record->entrata;
                            $uscita = $record->uscita;
                            // 2. Prepare Data
                            $innerDocs = [
                                [
                                    'JournalTemplateName' => 'GENERALE',
                                    'JournalBatchName' => 'COGEWS',
                                    'LineNo' => '1',
                                    'AccountNo' => str_replace('.', '', $coge1->conto_dare ?? '0128002'),
                                    'PostingDate' => $datacoge,
                                    'DocumentNo' =>  $docnoEntrata,
                                    'Description' => $coge1->descrizione_dare ?? 'Clienti c/fatture da emettere',
                                    'Amount' => -$entrata
                                ],
                                [
                                    'JournalTemplateName' => 'GENERALE',
                                    'JournalBatchName' => 'COGEWS',
                                    'LineNo' => '2',
                                    'AccountNo' => str_replace('.', '', $coge1->conto_avere ?? '0501001'),
                                    'PostingDate' => $datacoge,
                                    'DocumentNo' =>  $docnoEntrata,
                                    'Description' => $coge1->descrizione_avere ?? 'Ricavi Italia',
                                    'Amount' =>  $entrata
                                ],
                                [
                                    'JournalTemplateName' => 'GENERALE',
                                    'JournalBatchName' => 'COGEWS',
                                    'LineNo' => '1',
                                    'AccountNo' => str_replace('.', '', $coge2->conto_dare ?? '0221002'),
                                    'PostingDate' => $datacoge,
                                    'DocumentNo' => $docnoUscita,
                                    'Description' => $coge2->descrizione_avere ?? 'Fornitori c/fatture da ricevere',
                                    'Amount' => $uscita
                                ],
                                [
                                    'JournalTemplateName' => 'GENERALE',
                                    'JournalBatchName' => 'COGEWS',
                                    'LineNo' => '2',
                                    'AccountNo' => str_replace('.', '', $coge2->conto_avere ?? '0405019'),
                                    'PostingDate' => $datacoge,
                                    'DocumentNo' => $docnoUscita,
                                    'Description' => $coge2->descrizione_dare ?? 'Consulenze diverse',
                                    'Amount' => -$uscita
                                ]
                            ];
                            Log::debug("Payload preparato per l'invio:",  $innerDocs);
                               /*
                            $innerJson = json_encode(['docs' => $innerDocs]);

                            // The payload wrapped in "docs" property as a string
                            $payload = [
                                'docs' => $innerJson
                            ];

                            Log::debug("Payload preparato per l'invio:",  $innerDocs);
                         
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
                            */
/*
                            $accessToken="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6IlBjWDk4R1g0MjBUMVg2c0JEa3poUW1xZ3dNVSIsImtpZCI6IlBjWDk4R1g0MjBUMVg2c0JEa3poUW1xZ3dNVSJ9.eyJhdWQiOiJodHRwczovL2FwaS5idXNpbmVzc2NlbnRyYWwuZHluYW1pY3MuY29tIiwiaXNzIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvODVhMjVlM2ItOTQ1OS00NWViLWI5YzktMWRjMjZjYWYyZWRmLyIsImlhdCI6MTc2OTc3MTkzOSwibmJmIjoxNzY5NzcxOTM5LCJleHAiOjE3Njk3NzU4MzksImFpbyI6ImsyWmdZR0NxdXF2Mk5KZjc3clc5LzZ6Zml2OWRDUUE9IiwiYXBwaWQiOiIwMjgyMDA1ZS00YTc1LTQ1OTQtOWYyYy1lY2EwYTg0OTYxNzIiLCJhcHBpZGFjciI6IjEiLCJpZHAiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC84NWEyNWUzYi05NDU5LTQ1ZWItYjljOS0xZGMyNmNhZjJlZGYvIiwiaWR0eXAiOiJhcHAiLCJvaWQiOiJjOTc1Mzk4ZS01YmI1LTRmNjYtOWVkNi02ZmFmZDU0MTQ2YjgiLCJyaCI6IjEuQVNBQU8xNmloVm1VNjBXNXlSM0NiSzh1M3ozdmJabHNzMU5CaGdlbV9Ud0J1SjhBQUFBZ0FBLiIsInJvbGVzIjpbIkF1dG9tYXRpb24uUmVhZFdyaXRlLkFsbCIsImFwcF9hY2Nlc3MiLCJBZG1pbkNlbnRlci5SZWFkV3JpdGUuQWxsIiwiQVBJLlJlYWRXcml0ZS5BbGwiXSwic3ViIjoiYzk3NTM5OGUtNWJiNS00ZjY2LTllZDYtNmZhZmQ1NDE0NmI4IiwidGlkIjoiODVhMjVlM2ItOTQ1OS00NWViLWI5YzktMWRjMjZjYWYyZWRmIiwidXRpIjoiRmJJd0pyQXVXMGEtWERuTjZEUUtBQSIsInZlciI6IjEuMCIsInhtc19hY3RfZmN0IjoiMyA5IiwieG1zX2Z0ZCI6ImJhQlFMcGtpNzR1V0RlZ1p2YTJBb3FvTTQ4VXBTQ2p6R2ZUcVJNRGU0R0FCWlhWeWIzQmxibTl5ZEdndFpITnRjdyIsInhtc19pZHJlbCI6IjEwIDciLCJ4bXNfcmQiOiIwLjQyTGxZQkppVkJBUzRXQVhFckJkUDNFZTU3UHZEdk43eEkyN0FsdWZBa1U1aFFRTXJMcG55OWZlOEdtT1hkX3ZJMmJnRGhUbEFLcDluenN6WjNPd1l4djdzcjgyakR2bUEwVzVoUVNhaWhlNkw3aTZyT1BTTXRZN3M0cW55d01BIiwieG1zX3N1Yl9mY3QiOiIzIDkifQ.XkK6WxFGo1KFjtIci3UukFLauS9FcYgK8MivvxZndT6yUzhdXiLLBrgVI7Isdpt2S1eLGJkl6lSwSiI6DNMuAzIBpLctNckQy2Qk_fe6W5JJ5XovcjHMsy4pHYtSGJiaq11uj0GqcRJJMGTkkZUM2Ymr7mjfqO3HJXocWhODRNKiipVd12hZG5DGWCPofYa2umSMAVBTr3ONe4biygP2PJK-LeJn5_rPe6C4X4bUDtGEsyDsR6nBrU2IXq6qp2WeFrAg7qmGqYzIRwg5FrVNMyTQhKzrZUXi-h3m5w7AZiFelL-MvccklLCNfpO61GyWuEULBFgcokDf-Hs-upkdHw";
                            $dataResponse = self::postData($accessToken, $payload);
                            Log::info('API Response Status: ' . $dataResponse->status());
                            Log::debug('API Response Body: ' . $dataResponse->body());
                            */
                            $businessCentralService = new BusinessCentralService();
                            $dataResponse  = $businessCentralService->inviaPrimaNota( $innerDocs);
                           // 
                          //  $dataResponse = $businessCentralService->postData($payload);
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

    }    