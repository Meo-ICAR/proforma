<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportDailyData extends Command
{
    protected $signature = 'import:daily
                            {--start-date= : The start date (YYYY-MM-DD)}
                            {--end-date= : The end date (YYYY-MM-DD)}';

    protected $description = 'Run all daily import commands with optional date range';

    public function handle()
    {
        $endDate = $this->option('end-date')
            ? Carbon::parse($this->option('end-date'))->format('Y-m-d')
            : Carbon::yesterday()->format('Y-m-d');

        $startDate = $this->option('start-date')
            ? Carbon::parse($this->option('start-date'))->format('Y-m-d')
            : $endDate;  // Default to same as end date if not specified

        $commands = [
            [
                'command' => 'pratiche:import-api',
                'params' => [
                    '--start-date' => $startDate,
                    '--end-date' => $endDate,
                ]
            ],
            [
                'command' => 'provvigioni:import-api',
                'params' => [
                    '--start-date' => $startDate,
                    '--end-date' => $endDate,
                ]
            ],

            /*
             * [
             *     'command' => 'sidial:import-leads',
             *     'params' => [
             *         '--fromDay' => Carbon::parse($startDate)->format('d/m/Y'),
             *         '--today' => Carbon::parse($endDate)->format('d/m/Y'),
             *     ]
             * ],
             * [
             *     'command' => 'sidial:import-esiti',
             *     'params' => [
             *         '--from' => Carbon::parse($startDate)->format('d/m/Y'),
             *         '--to' => Carbon::parse($endDate)->format('d/m/Y'),
             *         '--table' => 'calls',
             *     ]
             * ],
             */
        ];

        $results = [];
        $hasFailures = false;

        foreach ($commands as $cmd) {
            $this->info("Running: {$cmd['command']}...");

            try {
                $result = $this->call($cmd['command'], $cmd['params']);
                $success = $result === 0;
                $message = $success ? '✅ Success' : '❌ Failed';
                $results[] = [
                    'command' => $cmd['command'],
                    'status' => $success ? 'success' : 'failed',
                    'message' => $message,
                    'exit_code' => $result
                ];

                if (!$success) {
                    $hasFailures = true;
                    $this->error("Command failed: {$cmd['command']} (Exit Code: $result)");
                } else {
                    $this->info("Completed: {$cmd['command']}");
                }
            } catch (\Exception $e) {
                $hasFailures = true;
                $errorMsg = $e->getMessage();
                $results[] = [
                    'command' => $cmd['command'],
                    'status' => 'error',
                    'message' => '❌ Error: ' . $errorMsg,
                    'exit_code' => 1
                ];
                $this->error("Error in command {$cmd['command']}: $errorMsg");
            }

            $this->newLine();
        }

        // Display summary
        $this->info('=== Import Summary ===');
        $headers = ['Command', 'Status', 'Exit Code'];
        $rows = [];

        foreach ($results as $result) {
            $rows[] = [
                $result['command'],
                $result['message'],
                $result['exit_code']
            ];
        }

        $this->table($headers, $rows);

        if ($hasFailures) {
            $this->warn('Daily imports completed with some failures.');
            return 1;
        }

        $this->info('✅ All daily imports completed successfully!');
        return 0;
    }
}
