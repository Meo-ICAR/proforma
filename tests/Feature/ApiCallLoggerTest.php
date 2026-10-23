<?php

namespace Tests\Feature;

use App\Models\ApiCall;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApiCallLoggerTest extends TestCase
{
    use RefreshDatabase;

    /** In produzione `api_calls` la crea unicofinance nel database condiviso: nei test si ricrea con lo stesso schema. */
    protected function afterRefreshingDatabase(): void
    {
        if (Schema::hasTable('api_calls')) {
            return;
        }

        Schema::create('api_calls', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30)->index();
            $table->string('operation', 60);
            $table->string('subject_type')->nullable();
            $table->string('subject_id', 36)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status', 20)->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->decimal('cost', 10, 4)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->string('summary')->nullable();
            $table->string('reference')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function test_a_successful_call_is_logged_without_credentials(): void
    {
        Http::fake(['*' => Http::response('a;b', 200)]);

        Http::logged('mediafacile', 'pratiche')->withHeaders(['X-Api-Key' => 'segreto'])->get('https://user:pwd@example.test/api', ['table' => 'pratiche']);

        $call = ApiCall::sole();
        $this->assertSame('mediafacile', $call->provider);
        $this->assertSame('pratiche', $call->operation);
        $this->assertSame('GET', $call->request['method']);
        $this->assertSame(200, $call->http_status);
        $this->assertSame('success', $call->status);
        $this->assertStringNotContainsString('pwd', $call->request['url']);
        $this->assertStringNotContainsString('segreto', json_encode($call->toArray()));
    }

    public function test_an_error_response_is_logged_with_an_excerpt_and_the_body_stays_readable(): void
    {
        Http::fake(['*' => Http::response('boom', 500)]);

        $response = Http::logged('business_central', 'ANCWS_SendExtCoge')->post('https://example.test/bc', ['docs' => 'x']);

        $call = ApiCall::sole();
        $this->assertSame('failed', $call->status);
        $this->assertSame(500, $call->http_status);
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
            $this->assertNull($call->http_status);
            $this->assertSame('failed', $call->status);
            $this->assertStringContainsString('timeout', $call->error);
        }
    }
}
