<?php

namespace App\Filament\Resources\PrimaNotaEntries\Schemas;

use App\Models\Fornitore;
use App\Models\Pratica;
use App\Models\PrimaNotaConfig;
use App\Models\Provvigione;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PrimaNotaEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('prima_nota_config_id')
                    ->label('Regola')
                    ->relationship('config', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $config = $state ? PrimaNotaConfig::find($state) : null;

                        $set('record_type', $config?->model_type);
                        $set('record_id', null);
                        $set('importo', null);
                        $set('conto_dare', $config?->conto_dare);
                        $set('conto_avere', $config?->conto_avere);
                    }),

                Hidden::make('record_type'),

                Select::make('record_id')
                    ->label('Record di origine')
                    ->helperText('Seleziona prima una regola per poter cercare il record.')
                    ->searchable()
                    ->live()
                    ->required()
                    ->getSearchResultsUsing(fn (Get $get, string $search) => self::recordOptions($get('record_type'), $search))
                    ->getOptionLabelUsing(function (Get $get, $value) {
                        $modelType = $get('record_type');

                        if (! $value || ! $modelType || ! class_exists($modelType)) {
                            return null;
                        }

                        $record = $modelType::query()->find($value);

                        return $record ? self::recordLabel($modelType, $record) : null;
                    })
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        $modelType = $get('record_type');
                        $config = ($configId = $get('prima_nota_config_id')) ? PrimaNotaConfig::find($configId) : null;

                        if (! $state || ! $modelType || ! class_exists($modelType) || ! $config) {
                            return;
                        }

                        $record = $modelType::query()->find($state);

                        if (! $record) {
                            return;
                        }

                        $set('importo', $record->{$config->value_field});

                        if ($config->date_field && $record->{$config->date_field}) {
                            $set('data', $record->{$config->date_field});
                        }
                    }),

                DatePicker::make('data')
                    ->label('Data registrazione')
                    ->required(),

                TextInput::make('importo')
                    ->label('Importo')
                    ->numeric()
                    ->prefix('€')
                    ->required(),

                TextInput::make('conto_dare')
                    ->label('Conto Dare')
                    ->required()
                    ->maxLength(255),

                TextInput::make('conto_avere')
                    ->label('Conto Avere')
                    ->required()
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Attiva')
                    ->helperText('Disattiva per escludere questa voce dall\'invio a Business Central.')
                    ->default(true),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function recordOptions(?string $modelType, string $search): array
    {
        if (! $modelType || ! class_exists($modelType)) {
            return [];
        }

        $query = $modelType::query();

        self::applySearch($query, $modelType, $search);

        return $query->limit(50)->get()
            ->mapWithKeys(fn (Model $record) => [$record->getKey() => self::recordLabel($modelType, $record)])
            ->all();
    }

    protected static function applySearch(Builder $query, string $modelType, string $search): void
    {
        if ($search === '') {
            return;
        }

        $columns = match ($modelType) {
            Pratica::class => ['id', 'codice_pratica', 'nome_cliente', 'cognome_cliente'],
            Provvigione::class => ['id', 'descrizione', 'id_pratica', 'cognome', 'nome'],
            Fornitore::class => ['id', 'name', 'nome', 'piva'],
            default => ['id'],
        };

        $query->where(function (Builder $query) use ($columns, $search) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    protected static function recordLabel(string $modelType, Model $record): string
    {
        return match ($modelType) {
            Pratica::class => trim("{$record->codice_pratica} — {$record->nome_cliente} {$record->cognome_cliente}"),
            Provvigione::class => trim("{$record->descrizione} (Pratica {$record->id_pratica})"),
            Fornitore::class => trim("{$record->name} ({$record->piva})"),
            default => (string) $record->getKey(),
        };
    }
}
