<?php

namespace Tests\Feature;

use App\Console\Commands\CheckStalePurchaseInvoices;
use App\Console\Commands\StatusCheckCommand;
use App\Enums\Severity;
use Tests\TestCase;

class CheckStalePurchaseInvoicesTest extends TestCase
{
    public function test_severity_follows_the_age_of_the_latest_invoice(): void
    {
        $this->assertSame(Severity::Ok, CheckStalePurchaseInvoices::severityFor(24));
        $this->assertSame(Severity::Regular, CheckStalePurchaseInvoices::severityFor(25));
        $this->assertSame(Severity::Regular, CheckStalePurchaseInvoices::severityFor(30));
        $this->assertSame(Severity::Warning, CheckStalePurchaseInvoices::severityFor(31));
        $this->assertSame(Severity::Warning, CheckStalePurchaseInvoices::severityFor(44));
        $this->assertSame(Severity::Alert, CheckStalePurchaseInvoices::severityFor(45));
    }

    public function test_is_a_status_check_reachable_by_the_bridge(): void
    {
        $this->assertInstanceOf(StatusCheckCommand::class, $this->app->make(CheckStalePurchaseInvoices::class));
    }
}
