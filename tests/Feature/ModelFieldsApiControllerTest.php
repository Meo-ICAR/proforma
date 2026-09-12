<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModelFieldsApiControllerTest extends TestCase
{
    /**
     * Fornitore/Clienti/Pratica live on this app's own default connection,
     * which the test suite points at sqlite (no information_schema), so a
     * real columns() listing can't run here without hitting the live MySQL
     * database. Verified manually against real data instead (GET
     * /api/models/pratica/fields correctly listed 27 columns and the
     * agente/stato lookups); this test covers the one path that doesn't
     * need a DB read.
     */
    public function test_returns_404_for_an_unknown_model(): void
    {
        $response = $this->getJson('/api/models/unknown-model/fields');

        $response->assertNotFound();
    }
}
