<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;
use Throwable;

/**
 * Ogni risorsa del pannello deve aprire la pagina elenco senza errori, con Laravel 13, Filament 5 e le tabelle del pacchetto.
 */
class AdminResourcesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_resource_list_page_opens(): void
    {
        config(['app.env' => 'local']);
        $this->seed();
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $failures = [];
        $opened = 0;

        foreach (Filament::getDefaultPanel()->getResources() as $resource) {
            // Le risorse non consultabili (es. FatturaResource, disattivata) non hanno una pagina da aprire.
            if (! array_key_exists('index', $resource::getPages()) || ! $resource::canViewAny()) {
                continue;
            }

            try {
                $status = $this->get($resource::getUrl('index'))->getStatusCode();
                $status === 200 ? $opened++ : $failures[$resource] = "HTTP {$status}";
            } catch (UrlGenerationException) {
                // risorsa annidata: la sua pagina elenco richiede il record padre nell'URL
            } catch (Throwable $e) {
                $failures[$resource] = $e instanceof HttpExceptionInterface
                    ? 'HTTP '.$e->getStatusCode()
                    : class_basename($e).': '.mb_substr($e->getMessage(), 0, 200);
            }
        }

        $this->assertSame([], $failures, print_r($failures, true));
        $this->assertGreaterThan(10, $opened);
    }
}
