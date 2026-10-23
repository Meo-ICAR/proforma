<?php

namespace Tests\Unit;

use App\Filament\Exports\DynamicGroupExport;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class DynamicGroupExportTest extends TestCase
{
    public function test_money_formatter_uses_the_exported_record_as_related_record(): void
    {
        $record = new class extends Model {};
        $column = TextColumn::make('importo')->money('EUR', locale: 'it');
        $formatter = (fn () => $this->formatStateUsing)->call($column);

        $export = new class('export') extends DynamicGroupExport
        {
            protected function getDefaultEvaluationParameters(): array
            {
                return [];
            }
        };

        $formatted = $export->evaluate($formatter, [
            'column' => $column,
            'state' => 1234.5,
            'record' => $record,
        ]);

        $this->assertIsString($formatted);
        $this->assertStringContainsString('1.234,50', $formatted);
    }
}
