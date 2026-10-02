<?php

namespace Tests\Feature;

use App\Jobs\RunArtisanCommandJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckClientiWithoutPivaTest extends TestCase
{
    /**
     * The check itself needs the MySQL schema (the legacy migrations don't run
     * on the sqlite test database), so only the API dispatch is covered.
     */
    public function test_check_missing_piva_can_be_dispatched_via_the_commands_api(): void
    {
        config(['services.bpm.api_key' => 'test-bpm-api-key']);
        Queue::fake();

        $this->postJson('/api/commands/clienti:check-missing-piva', ['options' => ['to' => 'segreteria@races.it']], ['X-Api-Key' => 'test-bpm-api-key'])
            ->assertAccepted();

        Queue::assertPushed(RunArtisanCommandJob::class);
    }
}
