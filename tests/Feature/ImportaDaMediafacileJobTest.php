<?php

namespace Tests\Feature;

use App\Jobs\ImportaDaMediafacile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportaDaMediafacileJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_job_notifies_only_the_configured_users_with_the_result(): void
    {
        $destinatario = User::factory()->create(['email' => 'import@example.test']);
        $altro = User::factory()->create();
        config([
            'services.mediafacile.base_url' => 'https://mediafacile.test/ws',
            'services.mediafacile.header_key' => 'chiave',
            'services.mediafacile.notify_emails' => ['import@example.test'],
        ]);
        Http::fake(fn (Request $request) => Http::response('', 500));

        (new ImportaDaMediafacile)->handle();

        $this->assertCount(1, $destinatario->notifications);
        $this->assertCount(0, $altro->notifications);
        $this->assertSame('Importazione da MediaFacile completata con errori', $destinatario->notifications->first()->data['title']);
        $this->assertStringContainsString('Pratiche: importazione non riuscita', $destinatario->notifications->first()->data['body']);
    }

    public function test_the_import_is_scheduled_nightly_and_no_longer_a_page_action(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('ImportaDaMediafacile')->assertSuccessful();
    }
}
