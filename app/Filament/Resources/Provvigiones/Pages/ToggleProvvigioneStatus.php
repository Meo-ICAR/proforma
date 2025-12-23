<?php

namespace App\Filament\Resources\Provvigiones\Pages;

use App\Models\Provvigione;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;

class ToggleProvvigioneStatus extends Page
{
    public $record;

    public function mount(Provvigione $record)
    {
        $this->record = $record;

        // Toggle status between 'Inserito' and 'Sospeso'
        $this->record->update([
            'stato' => $this->record->stato === 'Inserito' ? 'Sospeso' : 'Inserito'
        ]);

        // Redirect back to the previous page with a success message
        return redirect()->back()->with('success', 'Stato aggiornato con successo!');
    }

    public function render()
    {
        return ''; // This will never be shown due to the redirect in mount()
    }
}
