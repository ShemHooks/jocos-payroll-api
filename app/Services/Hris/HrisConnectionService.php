<?php

namespace App\Services\Hris;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class HrisConnectionService
{
    private const CONNECTION = 'hris';

    public function connection(): Connection
    {
        return DB::connection(self::CONNECTION);
    }

    public function isConnected(): bool
    {
        try {
            $this->connection()->selectOne('SELECT 1');

            return true;
        } catch (Throwable $exception) {
            Log::warning('HRIS database connection failed.', [
                'exception' => get_class($exception),
            ]);

            return false;
        }
    }

    public function getStatus(): array
    {
        $startedAt = microtime(true);

        try {
            $result = $this->connection()->selectOne(
                'SELECT DATABASE() AS database_name,
                        VERSION() AS server_version'
            );

            return [
                'connected' => true,
                'database' => $result->database_name,
                'server_version' => $result->server_version,
                'response_time_ms' => round(
                    (microtime(true) - $startedAt) * 1000,
                    2
                ),
                'checked_at' => now()->toIso8601String(),
                'message' => 'HRIS database is available.',
            ];
        } catch (Throwable $exception) {
            Log::warning('HRIS connection status check failed.', [
                'exception' => get_class($exception),
            ]);

            return [
                'connected' => false,
                'database' => null,
                'server_version' => null,
                'response_time_ms' => round(
                    (microtime(true) - $startedAt) * 1000,
                    2
                ),
                'checked_at' => now()->toIso8601String(),
                'message' => 'HRIS database is unavailable.',
            ];
        }
    }

    public function getAppointmentStatistics(): array
    {
        $result = $this->connection()
            ->table('appointments')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw(
                'COUNT(CASE WHEN is_current = 1 THEN 1 END) AS current_count'
            )
            ->first();

        return [
            'total_appointments' => (int) $result->total,
            'current_appointments' => (int) $result->current_count,
        ];
    }
}