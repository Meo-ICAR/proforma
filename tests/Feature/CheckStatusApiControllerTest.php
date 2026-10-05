<?php

namespace Tests\Feature;

use App\Console\Commands\CheckClientiWithoutPiva;
use App\Console\Commands\StatusCheckCommand;
use App\Enums\Severity;
use App\Services\CheckStatus;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CheckStatusApiControllerTest extends TestCase
{
    private const HEADERS = ['X-Api-Key' => 'test-bpm-api-key'];

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.bpm.api_key' => 'test-bpm-api-key']);

        // I check reali richiedono lo schema MySQL (le migration legacy non girano su sqlite):
        // il contratto dell'API si verifica con un check fittizio.
        Artisan::registerCommand(new class extends StatusCheckCommand
        {
            protected $signature = 'test:check-fake';

            protected $description = 'Check fittizio';

            public function checkStatus(): CheckStatus
            {
                return new CheckStatus(4, Severity::Warning, '- uno');
            }
        });
    }

    public function test_returns_value_and_severity_of_a_check(): void
    {
        $this->getJson('/api/checks/test:check-fake', self::HEADERS)
            ->assertOk()
            ->assertExactJson([
                'command' => 'test:check-fake',
                'value' => 4,
                'severity' => 'warning',
                'details' => '- uno',
            ]);
    }

    public function test_lists_only_status_check_commands(): void
    {
        $this->getJson('/api/checks', self::HEADERS)
            ->assertOk()
            ->assertJsonStructure(['checks' => ['test:check-fake', 'clienti:check-missing-piva', 'proformas:check-unpaid']])
            ->assertJsonMissingPath('checks.migrate');
    }

    public function test_rejects_commands_that_are_not_checks(): void
    {
        $this->getJson('/api/checks/migrate', self::HEADERS)->assertNotFound();
    }

    public function test_requires_the_api_key(): void
    {
        $this->getJson('/api/checks/test:check-fake')->assertUnauthorized();
    }

    public function test_missing_piva_check_is_no_longer_dispatchable_as_a_command(): void
    {
        $this->assertInstanceOf(StatusCheckCommand::class, Artisan::all()['clienti:check-missing-piva']);
        $this->assertInstanceOf(CheckClientiWithoutPiva::class, Artisan::all()['clienti:check-missing-piva']);

        $this->postJson('/api/commands/clienti:check-missing-piva', [], self::HEADERS)->assertNotFound();
    }
}
