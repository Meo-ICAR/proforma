<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InvoicesImport;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step; // This also moved to Schemas
use Filament\Schemas\Schema;

class ImportInvoices extends Page
{
    protected static string $resource = InvoiceResource::class;
    protected  string $view = 'filament.resources.invoices.pages.import-invoices';

   public ?array $data = [];

    public function mount(): void
    {
      //  parent::mount();
        $this->form->fill();
    }

     public function form(Schema $schema): Schema // Update types here
{
    return $schema
        ->schema([
                Wizard::make([
                    Step::make('Carica File')
                        ->description('Seleziona il file Excel da importare')
                        ->schema([
                            Section::make('Importa Fatture')
                                ->description('Carica un file Excel contenente le fatture da importare')
                                ->schema([
                                    FileUpload::make('file')
                                        ->label('File Excel')
                                        ->acceptedFileTypes([
                                            'application/vnd.ms-excel',
                                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        ])
                                        ->required()
                                        ->preserveFilenames()
                                        ->maxSize(10240) // 10MB
                                        ->directory('imports/invoices')
                                        ->visibility('private'),
                                ])
                                ->columns(1),
                        ]),
                    Step::make('Conferma')
                        ->description('Verifica i dati prima di importare')
                        ->schema([
                            // Preview of the data would go here
                        ]),
                ])
                ->submitAction(
                    Action::make('import')
                        ->label('Importa Fatture')
                        ->action('import')
                        ->color('primary')
                )
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();

        try {
            Excel::import(new InvoicesImport, $data['file']);

            Notification::make()
                ->title('Importazione completata con successo')
                ->success()
                ->send();

            $this->redirect(InvoiceResource::getUrl('index'));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Errore durante l\'importazione')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Torna all\'elenco')
                ->url(InvoiceResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
