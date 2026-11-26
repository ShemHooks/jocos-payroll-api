<?php

namespace App\Console\Commands;

use App\Services\Hris\HrisConnectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class TestHrisConnection extends Command
{
    protected $signature = 'hris:test';

    protected $description =
        'Test the PGNO HRIS database connection';

    public function handle(
        HrisConnectionService $hris
    ): int {
        $this->newLine();

        $this->info('PGNO Payroll - HRIS Integration');

        $this->line(
            'Testing secondary database connection...'
        );

        $this->newLine();

        $status = $hris->getStatus();

        if (!$status['connected']) {
            $this->error($status['message']);

            $this->warn(
                'Check the HRIS database configuration, '
                . 'network connectivity and credentials.'
            );

            return self::FAILURE;
        }

        $this->info('HRIS connection successful.');

        $this->table(
            ['Property', 'Value'],
            [
                ['Connection', 'hris'],
                ['Database', $status['database']],
                ['Server Version', $status['server_version']],
                [
                    'Response Time',
                    $status['response_time_ms'] . ' ms',
                ],
                ['Checked At', $status['checked_at']],
            ]
        );

        try {
            $statistics = $hris->getAppointmentStatistics();

            $this->newLine();

            $this->info('HRIS Appointment Statistics');

            $this->table(
                ['Record', 'Count'],
                [
                    [
                        'Total Appointments',
                        number_format(
                            $statistics['total_appointments']
                        ),
                    ],
                    [
                        'Current Appointments',
                        number_format(
                            $statistics['current_appointments']
                        ),
                    ],
                ]
            );
        } catch (Throwable $exception) {
            Log::warning('HRIS appointment query failed.', [
                'exception' => get_class($exception),
            ]);

            $this->error(
                'Connected to HRIS, but appointment '
                . 'records could not be retrieved.'
            );

            return self::FAILURE;
        }

        $this->newLine();

        $this->info(
            'HRIS integration connection test completed.'
        );

        return self::SUCCESS;
    }
}