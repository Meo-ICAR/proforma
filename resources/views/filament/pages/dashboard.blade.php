<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6">
        <x-filament::section icon="heroicon-o-information-circle" icon-color="primary">
            <x-slot name="heading">
                Benvenuto in Proforma
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <p>Questo manuale ti aiuterà a navigare tra le funzionalità principali:</p>

                <h3>1. Gestione FIRR</h3>
                <p>Per calcolare il contributo, accedi alla sezione <strong>Enasarco</strong> e clicca sul pulsante "Sincronizza".</p>

                <h3>2. Filtri Avanzati</h3>
                <p>Puoi filtrare i record per Anno e Mese utilizzando la barra laterale destra nella tabella.</p>
            </div>
        </x-filament::section>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-filament::card>
                <h4 class="font-bold">Supporto Tecnico</h4>
                <p class="text-sm text-gray-500">Contatta l'assistenza a: info@hassisto.com</p>
            </x-filament::card>

            <x-filament::card>
                <h4 class="font-bold">Ultimo Aggiornamento</h4>
                <p class="text-sm text-gray-500">Versione 4.3 - Dicembre 2025</p>
            </x-filament::card>
        </div>
    </div>
</x-filament-panels::page>
