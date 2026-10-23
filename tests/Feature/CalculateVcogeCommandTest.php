<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Unico\Core\Bridge\Jobs\RunBridgeCommand;

class CalculateVcogeCommandTest extends TestCase
{
    /**
     * The calculation queries are covered by CoreIntegrationTest, so only the API dispatch is covered here.
     */
    public function test_vcoge_calculate_can_be_dispatched_via_the_commands_api(): void
    {
        config(['unico-core.bridge.api_key' => 'test-bpm-api-key']);
        Queue::fake();

        $response = $this->postJson('/api/commands/vcoge:calculate', [], ['X-Api-Key' => 'test-bpm-api-key']);

        $response->assertAccepted()->assertJsonStructure(['run_id', 'status_url']);
        Queue::assertPushed(RunBridgeCommand::class);

        $this->getJson($response->json('status_url'), ['X-Api-Key' => 'test-bpm-api-key'])->assertOk()->assertJsonPath('status', 'queued')->assertJsonPath('command', 'vcoge:calculate');
    }

    public function test_commands_outside_the_whitelist_and_calls_without_the_key_are_refused(): void
    {
        config(['unico-core.bridge.api_key' => 'test-bpm-api-key']);
        Queue::fake();

        $this->postJson('/api/commands/db:wipe', [], ['X-Api-Key' => 'test-bpm-api-key'])->assertNotFound();
        $this->postJson('/api/commands/vcoge:calculate')->assertUnauthorized();
        Queue::assertNothingPushed();
    }

    public function test_venasarco_trimestre_calculate_can_be_dispatched_via_the_commands_api(): void
    {
        config(['unico-core.bridge.api_key' => 'test-bpm-api-key']);
        Queue::fake();

        $response = $this->postJson('/api/commands/venasarco-trimestre:calculate', [], ['X-Api-Key' => 'test-bpm-api-key']);

        $response->assertAccepted();
        Queue::assertPushed(RunBridgeCommand::class);
    }
}
