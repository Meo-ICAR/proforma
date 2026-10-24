<?php

namespace Tests\Unit;

use App\Neuron\ManualAssistantAgent;
use Tests\TestCase;

class ManualAssistantAgentTest extends TestCase
{
    public function test_manual_sources_point_at_the_markdown_files_and_both_html_manuals(): void
    {
        $sources = ManualAssistantAgent::manualSources();

        $this->assertSame([
            base_path('Manual.md'),
            base_path('Istruction.md'),
            resource_path('manuals/manuale-utente.html'),
            resource_path('manuals/manuale-tecnico.html'),
        ], $sources);

        foreach ($sources as $source) {
            $this->assertFileExists($source);
        }
    }
}
