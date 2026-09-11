<?php

namespace App\Filament\Resources\PrimaNotaConfigs\Schemas;

use App\Models\Fornitore;
use App\Models\Pratica;
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
        Pratica::class => 'Pratica',
        Provvigione::class => 'Provvigione',
        Fornitore::class => 'Fornitore',
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
            ->components([
                TextInput::make('event_label')
                    ->label('Etichetta evento')
                    ->required()
                    ->maxLength(255),

                Select::make('model_type')
                    ->label('Modello di origine')
                    ->options(self::MODEL_OPTIONS)
                    ->required()
                    ->live(),

                Select::make('value_field')
                    ->label('Campo valore')
                    ->options(fn (Get $get) => self::columnOptions($get('model_type'), self::AMOUNT_TYPES))
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('model_type')),

                Select::make('date_field')
                    ->label('Campo data evento')
                    ->helperText('Opzionale: se impostato, la prima nota viene generata solo dopo questa data.')
                    ->options(fn (Get $get) => self::columnOptions($get('model_type'), self::DATE_TYPES))
                    ->disabled(fn (Get $get) => ! $get('model_type')),

                TextInput::make('conto_dare')
                    ->label('Conto Dare')
                    ->required()
                    ->maxLength(255),

                TextInput::make('conto_avere')
                    ->label('Conto Avere')
                    ->required()
                    ->maxLength(255),

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
