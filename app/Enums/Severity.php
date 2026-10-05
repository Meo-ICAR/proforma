<?php

namespace App\Enums;

/**
 * Grado di severity con cui un check riporta il proprio stato a UnicoBPM, dal meno al più grave.
 */
enum Severity: string
{
    case Ok = 'ok';
    case Regular = 'regular';
    case Warning = 'warning';
    case Alert = 'alert';

    /**
     * Severity in base a una quantità (es. record anomali): 0 è ok, poi si sale al
     * raggiungimento di ciascuna soglia.
     */
    public static function fromCount(int $count, int $regularFrom = 1, int $warningFrom = 3, int $alertFrom = 10): self
    {
        return match (true) {
            $count >= $alertFrom => self::Alert,
            $count >= $warningFrom => self::Warning,
            $count >= $regularFrom => self::Regular,
            default => self::Ok,
        };
    }
}
