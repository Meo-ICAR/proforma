<?php

namespace App\Filament\Resources\InvoiceIns\Pages;

use App\Filament\Resources\Invoiceins\InvoiceinResource;
use App\Imports\InvoiceinsImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;

class ImportInvoiceins extends Page
{
    protected static string $resource = InvoiceinResource::class;
    protected static ?string $title = 'Import Fatture';
    protected string $view = 'filament.resources.invoice-ins.pages.import-invoiceins';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    Step::make('Upload File')
                        ->schema([
                            FileUpload::make('file')
                                ->label('Excel File')
                                ->required()
                                ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                                ->maxSize(10240),  // 10MB
                        ]),
                ])
            ])
            ->statePath('data');
    }

    public function import()
    {
        $data = $this->form->getState();

        try {
            Excel::import(new InvoiceinsImport, $data['file']);

            Notification::make()
                ->title('Import completed successfully')
                ->success()
                ->send();

            return redirect(InvoiceinResource::getUrl('index'));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error during import')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('import')
                ->label('Import')
                ->action('import'),
            Action::make('cancel')
                ->label('Cancel')
                ->url(InvoiceinResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
