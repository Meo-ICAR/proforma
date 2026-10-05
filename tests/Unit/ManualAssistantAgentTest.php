<?php

namespace Tests\Unit;

use App\Neuron\ManualAssistantAgent;
use Tests\TestCase;

class ManualAssistantAgentTest extends TestCase
{
    public function test_manual_sources_point_at_the_real_manual_files(): void
    {
        $sources = ManualAssistantAgent::manualSources();

        $this->assertSame([
            base_path('Manual.md'),
            base_path('Istruction.md'),
        ], $sources);

        foreach ($sources as $source) {
            $this->assertFileExists($source);
        }
    }
}
