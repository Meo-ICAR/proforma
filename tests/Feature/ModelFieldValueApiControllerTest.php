<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModelFieldValueApiControllerTest extends TestCase
{
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
        ]);

        $response->assertNotFound();
    }
}
