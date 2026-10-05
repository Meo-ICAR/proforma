<?php

namespace Tests\Unit;

use App\Enums\Severity;
use PHPUnit\Framework\TestCase;

class SeverityTest extends TestCase
{
    public function test_from_count_rises_at_each_threshold(): void
    {
        $this->assertSame(Severity::Ok, Severity::fromCount(0));
        $this->assertSame(Severity::Regular, Severity::fromCount(1));
        $this->assertSame(Severity::Regular, Severity::fromCount(2));
        $this->assertSame(Severity::Warning, Severity::fromCount(3));
        $this->assertSame(Severity::Warning, Severity::fromCount(9));
        $this->assertSame(Severity::Alert, Severity::fromCount(10));
    }

    public function test_from_count_honours_custom_thresholds(): void
    {
        $this->assertSame(Severity::Ok, Severity::fromCount(14, regularFrom: 15, warningFrom: 30, alertFrom: 45));
        $this->assertSame(Severity::Alert, Severity::fromCount(45, regularFrom: 15, warningFrom: 30, alertFrom: 45));
    }
}
