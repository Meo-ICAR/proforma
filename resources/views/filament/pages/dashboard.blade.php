<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6">
        <x-filament::section icon="heroicon-o-information-circle" icon-color="primary">
            <x-slot name="heading">
                Benvenuto in Proforma
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <p>Questo manuale ti aiuterà a navigare tra le funzionalità principali:</p>

                <h3>1. Proforma</h3>
                <p>Per calcolare il contributo, accedi alla sezione <strong>Enasarco</strong> e clicca sul pulsante "Sincronizza".</p>

                <h3>2. Filtri Avanzati</h3>
                <p>Puoi filtrare i record per Anno e Mese utilizzando la barra laterale destra nella tabella.</p>
            </div>
        </x-filament::section>

        <!-- Image Gallery Section -->
        <x-filament::section>
            <x-slot name="heading">
                Istruzioni e Guide
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="border rounded-lg overflow-hidden shadow-sm">
                    <img src="{{ asset('cerca.png') }}" alt="Cerca" class="w-full h-auto">
                    <div class="p-4">
                        <h4 class="font-medium">Ricerca</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Guida alla ricerca avanzata</p>
                    </div>
                </div>

                <div class="border rounded-lg overflow-hidden shadow-sm">
                    <img src="{{ asset('cercacompleto.png') }}" alt="Ricerca Completa" class="w-full h-auto">
                    <div class="p-4">
                        <h4 class="font-medium">Ricerca Completa</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tutte le opzioni di ricerca</p>
                    </div>
                </div>

                <div class="border rounded-lg overflow-hidden shadow-sm">
                    <img src="{{ asset('colonne.png') }}" alt="Gestione Colonne" class="w-full h-auto">
                    <div class="p-4">
                        <h4 class="font-medium">Colonne</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Personalizza le colonne visualizzate</p>
                    </div>
                </div>

                <div class="border rounded-lg overflow-hidden shadow-sm">
                    <img src="{{ asset('proforma.png') }}" alt="Gestione Proforma" class="w-full h-auto">
                    <div class="p-4">
                        <h4 class="font-medium">Proforma</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Gestione delle proforme</p>
                    </div>
                </div>

                <div class="border rounded-lg overflow-hidden shadow-sm">
                    <img src="{{ asset('raggruppa.png') }}" alt="Raggruppamento" class="w-full h-auto">
                    <div class="p-4">
                        <h4 class="font-medium">Raggruppamento</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Come raggruppare i dati</p>
                    </div>
                </div>

                <div class="border rounded-lg overflow-hidden shadow-sm">
                    <img src="{{ asset('seleziona.png') }}" alt="Selezione" class="w-full h-auto">
                    <div class="p-4">
                        <h4 class="font-medium">Selezione</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Come selezionare più elementi</p>
                    </div>
                </div>

                <div class="border rounded-lg overflow-hidden shadow-sm">
                    <img src="{{ asset('tastiheader.png') }}" alt="Tasti di Testata" class="w-full h-auto">
                    <div class="p-4">
                        <h4 class="font-medium">Barra Strumenti</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Funzioni della barra superiore</p>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-filament::card>
                <h4 class="font-bold">Supporto Tecnico</h4>
                <p class="text-sm text-gray-500">Contatta l'assistenza a: info@hassisto.com</p>
            </x-filament::card>
        </div>
    </div>
</x-filament-panels::page>
