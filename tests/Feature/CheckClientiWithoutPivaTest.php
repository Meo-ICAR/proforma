<?php

namespace Tests\Feature;

use App\Console\Commands\CheckClientiWithoutPiva;
use App\Console\Commands\StatusCheckCommand;
use Tests\TestCase;

class CheckClientiWithoutPivaTest extends TestCase
{
    /**
     * Il check ora riporta solo valore e severity a UnicoBPM (GET /api/checks/{comando}):
     * non è più un comando di invio email lanciabile da /api/commands. Il calcolo richiede
     * lo schema MySQL (le migration legacy non girano sul database sqlite di test), quindi qui
     * si verifica solo la registrazione; il contratto dell'API è in CheckStatusApiControllerTest.
     */
    public function test_missing_piva_check_is_a_status_check_without_email_options(): void
    {
        $command = $this->app->make(CheckClientiWithoutPiva::class);

        $this->assertInstanceOf(StatusCheckCommand::class, $command);
        $this->assertFalse($command->getDefinition()->hasOption('to'));
    }
}
