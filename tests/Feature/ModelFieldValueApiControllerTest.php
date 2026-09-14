<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModelFieldValueApiControllerTest extends TestCase
{
    /**
     * These routes sit behind VerifyBpmApiKey (X-Api-Key header checked
     * against services.bpm.api_key); tests authenticate with a fixed key.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.bpm.api_key' => 'test-bpm-api-key']);
    }

    /**
     * @return array<string, string>
     */
    protected function apiKeyHeader(): array
    {
        return ['X-Api-Key' => 'test-bpm-api-key'];
    }

    /**
     * Fornitore/Clienti/Pratica live on this app's own default connection,
     * which the test suite points at sqlite (no information_schema), so
     * paths needing a real columns() lookup can't run here without hitting
     * the live MySQL database — only the model-resolution guard (checked
     * before any DB read) is covered.
     */
    public function test_returns_404_for_an_unknown_model(): void
    {
        $response = $this->patchJson('/api/models/unknown-model/'.fake()->uuid(), [
            'field' => 'name',
            'value' => 'x',
        ], $this->apiKeyHeader());

        $response->assertNotFound();
    }

    public function test_show_returns_404_for_an_unknown_model(): void
    {
        $response = $this->getJson('/api/models/unknown-model/'.fake()->uuid(), $this->apiKeyHeader());

        $response->assertNotFound();
    }

    public function test_requests_without_the_api_key_are_rejected(): void
    {
        $response = $this->getJson('/api/models/unknown-model/'.fake()->uuid());

        $response->assertUnauthorized();
    }
}
