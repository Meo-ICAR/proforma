<?php

namespace Tests\Feature;

use App\Models\ApiCall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiCallLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_successful_call_is_logged_without_credentials(): void
    {
        Http::fake(['*' => Http::response('a;b', 200)]);

        Http::logged('mediafacile', 'pratiche')->withHeaders(['X-Api-Key' => 'segreto'])->get('https://user:pwd@example.test/api', ['table' => 'pratiche']);

        $call = ApiCall::sole();
        $this->assertSame('mediafacile', $call->service);
        $this->assertSame('pratiche', $call->operation);
        $this->assertSame('GET', $call->method);
        $this->assertSame(200, $call->status);
        $this->assertTrue($call->successful);
        $this->assertStringNotContainsString('pwd', $call->url);
        $this->assertStringNotContainsString('segreto', json_encode($call->toArray()));
    }

    public function test_an_error_response_is_logged_with_an_excerpt_and_the_body_stays_readable(): void
    {
        Http::fake(['*' => Http::response('boom', 500)]);

        $response = Http::logged('business_central', 'ANCWS_SendExtCoge')->post('https://example.test/bc', ['docs' => 'x']);

        $call = ApiCall::sole();
        $this->assertFalse($call->successful);
        $this->assertSame(500, $call->status);
        $this->assertSame('HTTP 500: boom', $call->error);
        $this->assertSame('boom', $response->body());
    }

    public function test_a_connection_failure_is_logged_and_still_thrown(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        try {
            Http::logged('mediafacile')->get('https://example.test/api');
            $this->fail('La ConnectionException doveva propagarsi.');
        } catch (ConnectionException) {
            $call = ApiCall::sole();
            $this->assertNull($call->status);
            $this->assertFalse($call->successful);
            $this->assertStringContainsString('timeout', $call->error);
        }
    }
}
