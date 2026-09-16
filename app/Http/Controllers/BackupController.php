<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function run(Request $request)
    {
        $token = $request->query('token') ?: $request->header('X-Backup-Token');
        $validToken = env('BACKUP_TOKEN');

        if ($validToken && $token !== $validToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('Automated database backup triggered via API.');

        $exitCode = Artisan::call('db:backup');

        if ($exitCode === 0) {
            Log::info('Automated database backup completed successfully.');
            return response()->json([
                'status' => 'success',
                'message' => 'Backup completed and emailed.',
                'output' => Artisan::output(),
            ]);
        }

        Log::error('Automated database backup failed.', ['exit_code' => $exitCode]);
        return response()->json([
            'status' => 'error',
            'message' => 'Backup failed.',
            'output' => Artisan::output(),
        ], 500);
    }
}
