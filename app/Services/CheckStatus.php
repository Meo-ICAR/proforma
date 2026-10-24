<?php

namespace App\Services;

use App\Enums\Severity;

/**
 * Stato di un check restituito a UnicoBPM: il valore misurato, la severity e un dettaglio
 * leggibile opzionale (es. l'elenco dei record anomali). Cosa farne (email, destinatari) lo
 * decide UnicoBPM tramite la RACI. Un allegato opzionale (es. un CSV) viaggia come {filename, content}.
 *
 * @param  array{filename: string, content: string}|null  $attachment
 */
readonly class CheckStatus
{
    public function __construct(
        public int|string $value,
        public Severity $severity,
        public ?string $details = null,
        public ?array $attachment = null,
    ) {}

    /**
     * @return array{value: int|string, severity: string, details: ?string, attachment?: array{filename: string, content: string}}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'severity' => $this->severity->value,
            'details' => $this->details,
        ] + ($this->attachment === null ? [] : ['attachment' => $this->attachment]);
    }
}
