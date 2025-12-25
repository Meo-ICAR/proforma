<x-filament-panels::page>
    <div class="space-y-6">
        <div class="max-w-3xl mx-auto">
            <div class="p-6 bg-white rounded-lg shadow">
                <h2 class="text-2xl font-bold mb-6">
                    {{ __('Importa Fatture') }}
                </h2>

                <div class="space-y-6">
                    {{ $this->form }}

                    <div class="text-sm text-gray-500">
                        <p class="font-medium">Formato file richiesto:</p>
                        <ul class="list-disc pl-5 mt-2 space-y-1">
                            <li>Il file deve essere in formato Excel (.xlsx o .xls)</li>
                            <li>La prima riga deve contenere le intestazioni delle colonne</li>
                            <li>Colonne obbligatorie: P.IVA Fornitore, Ragione Sociale, Numero Fattura, Data Fattura, Importo Totale</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
