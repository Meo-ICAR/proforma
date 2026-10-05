<?php

namespace App\Filament\Resources\PrimaNotaConfigs\Schemas;

use App\Models\Clienti;
use App\Models\Fornitore;
use App\Models\Pratica;
use App\Models\Proforma;
use App\Models\Provvigione;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class PrimaNotaConfigForm
{
    /**
     * @var array<string, string>
     */
    public const MODEL_OPTIONS = [
        Fornitore::class => 'Fornitore',
        Clienti::class => 'Istituto',
        Pratica::class => 'Pratica',
        Proforma::class => 'Proforma',
        Provvigione::class => 'Provvigione',

    ];

    /**
     * @var array<int, string>
     */
    protected const AMOUNT_TYPES = ['decimal', 'float', 'double'];

    /**
     * @var array<int, string>
     */
    protected const DATE_TYPES = ['date', 'datetime', 'timestamp'];

    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),

                TextInput::make('conto_dare')
                    ->label('Conto Dare')
                    ->required()
                    ->maxLength(255),

                TextInput::make('conto_dare_description')
                    ->label('Descrizione Conto Dare')
                    ->maxLength(255),

                TextInput::make('conto_avere')
                    ->label('Conto Avere')
                    ->required()
                    ->maxLength(255),

                TextInput::make('conto_avere_description')
                    ->label('Descrizione Conto Avere')
                    ->maxLength(255),

                Select::make('model_type')
                    ->label('Tabella origine')
                    ->helperText('Quale tabella usare per generare la prima nota')
                    ->options(self::MODEL_OPTIONS)
                    ->required()
                    ->live(),

                Select::make('value_field')
                    ->label('Campo importo')
                    ->helperText('Quale valore prendere per la prima nota')

                    ->options(fn (Get $get) => self::columnOptions($get('model_type'), self::AMOUNT_TYPES))
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('model_type')),

                Select::make('is_positive')
                    ->label('Segno importo')
                    ->helperText('Filtra le righe da considerare in base al segno dello stesso campo valore. Lasciare vuoto per non filtrare per segno (comunque esclusi gli importi a zero).')
                    ->options([
                        1 => 'Solo importi positivi (> 0)',
                        0 => 'Solo importi negativi (< 0)',
                    ])
                    ->placeholder('Qualsiasi segno'),

                Select::make('date_field')
                    ->label('Campo data evento')
                    ->helperText('Opzionale: la prima nota viene generata solo se valorizzata questa data.')
                    ->options(fn (Get $get) => self::columnOptions($get('model_type'), self::DATE_TYPES))
                    ->disabled(fn (Get $get) => ! $get('model_type')),
                DatePicker::make('effective_from')
                    ->label('Attiva da')
                    ->helperText('Ignora i record aggiornati prima di questa data.'),

                Toggle::make('is_active')
                    ->label('Attiva')
                    ->default(true),

            ]);
    }

    /**
     * @param  array<int, string>  $dataTypes
     * @return array<string, string>
     */
    protected static function columnOptions(?string $modelType, array $dataTypes): array
    {
        if (! $modelType || ! class_exists($modelType)) {
            return [];
        }

        $table = (new $modelType)->getTable();

        $columns = DB::select(
            'select COLUMN_NAME, COLUMN_COMMENT from information_schema.columns
                where TABLE_SCHEMA = DATABASE() and TABLE_NAME = ? and DATA_TYPE in ('.implode(',', array_fill(0, count($dataTypes), '?')).')
                order by ORDINAL_POSITION',
            [$table, ...$dataTypes]
        );

        return collect($columns)
            ->mapWithKeys(fn ($column) => [
                $column->COLUMN_NAME => $column->COLUMN_COMMENT
                    ? "{$column->COLUMN_NAME} — {$column->COLUMN_COMMENT}"
                    : $column->COLUMN_NAME,
            ])
            ->all();
    }
}
