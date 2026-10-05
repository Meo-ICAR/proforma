<?php

namespace Tests\Feature;

use App\Jobs\RunArtisanCommandJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CalculateVcogeCommandTest extends TestCase
{
    /**
     * The calculation itself reads MySQL views (vwcoge/vwcogestorno) that the
     * sqlite test database doesn't have, so only the API dispatch is covered.
     */
    public function test_vcoge_calculate_can_be_dispatched_via_the_commands_api(): void
    {
        config(['services.bpm.api_key' => 'test-bpm-api-key']);
        Queue::fake();

        $response = $this->postJson('/api/commands/vcoge:calculate', [], ['X-Api-Key' => 'test-bpm-api-key']);

        $response->assertAccepted();
        Queue::assertPushed(RunArtisanCommandJob::class);
    }

    public function test_venasarco_trimestre_calculate_can_be_dispatched_via_the_commands_api(): void
    {
        config(['services.bpm.api_key' => 'test-bpm-api-key']);
        Queue::fake();

        $response = $this->postJson('/api/commands/venasarco-trimestre:calculate', [], ['X-Api-Key' => 'test-bpm-api-key']);

        $response->assertAccepted();
        Queue::assertPushed(RunArtisanCommandJob::class);
    }
}
