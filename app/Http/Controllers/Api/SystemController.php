<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SystemController extends Controller
{
    public function stats()
    {
        // Database Size (Approximate for MySQL)
        $dbSize = 0;
        try {
            $dbName = DB::connection()->getDatabaseName();
            $result = DB::select("SELECT table_schema, SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.tables WHERE table_schema = ? GROUP BY table_schema", [$dbName]);
            if (!empty($result)) {
                $dbSize = round($result[0]->size, 2);
            }
        } catch (\Exception $e) {
            $dbSize = 'Unknown';
        }

        // Log Files
        $logPath = storage_path('logs');
        $logFiles = File::glob($logPath . '/*.log');
        $logCount = count($logFiles);
        $totalLogSize = 0;
        foreach ($logFiles as $file) {
            $totalLogSize += File::size($file);
        }
        $totalLogSizeMB = round($totalLogSize / 1024 / 1024, 2);

        return response()->json([
            'success' => true,
            'data' => [
                'database' => [
                    'status' => 'Connected',
                    'size' => $dbSize . ' MB',
                    'driver' => DB::connection()->getDriverName()
                ],
                'logs' => [
                    'count' => $logCount,
                    'size' => $totalLogSizeMB . ' MB',
                ],
                'server' => [
                    'php_version' => phpversion(),
                    'laravel_version' => app()->version(),
                ]
            ]
        ]);
    }

    public function optimize()
    {
        try {
            Artisan::call('optimize:clear');
            return response()->json([
                'success' => true,
                'message' => 'System optimized successfully (Cache cleared).'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Optimization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearLogs()
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            if (File::exists($logPath)) {
                file_put_contents($logPath, '');
            }
            return response()->json([
                'success' => true,
                'message' => 'System logs cleared successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear logs: ' . $e->getMessage()
            ], 500);
        }
    }
    public function clearAllData()
    {
        try {
            \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

            \App\Models\Appointment::truncate();
            \App\Models\CovidTest::truncate();
            \App\Models\Vaccination::truncate();
            \App\Models\Patient::truncate();
            \App\Models\Hospital::truncate();

            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

            return response()->json([
                'success' => true,
                'message' => 'All system data has been cleared successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear data: ' . $e->getMessage()
            ], 500);
        }
    }
}
