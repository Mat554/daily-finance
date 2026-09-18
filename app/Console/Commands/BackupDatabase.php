<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--email=}';
    protected $description = 'Export all database tables to CSV and email the backup';

    protected array $tables = [
        'users',
        'transactions',
        'expenses',
        'expense_payments',
        'monthly_reports',
        'account_balances',
    ];

    public function handle(): int
    {
        $timestamp = now()->format('Y-m-d_His');
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $this->info("Starting database backup at {$timestamp}...");

        $manifest = ["Daily Finance Database Backup", "Generated: {$timestamp}", "", "Table row counts:"];
        $csvPaths = [];

        foreach ($this->tables as $table) {
            $this->info("Exporting table: {$table}...");
            $result = $this->exportTableToCsv($table, $backupDir, $timestamp);

            if ($result === null) {
                $this->warn("Table {$table} not found or empty, skipping.");
                $manifest[] = "  {$table}: N/A";
                continue;
            }

            [$path, $count] = $result;
            $csvPaths[] = $path;
            $manifest[] = "  {$table}: {$count} rows";
            $this->info("  Exported {$count} rows from {$table}.");
        }

        // Write manifest
        $manifestPath = "{$backupDir}/backup_{$timestamp}_manifest.txt";
        file_put_contents($manifestPath, implode("\n", $manifest));
        $csvPaths[] = $manifestPath;

        // Email
        $email = $this->option('email') ?: env('BACKUP_EMAIL');
        if ($email) {
            $this->info("Sending backup to {$email}...");
            try {
                Mail::to($email)->send(new \App\Mail\DatabaseBackup($csvPaths, $timestamp));
                $this->info("Backup email sent successfully.");
            } catch (\Exception $e) {
                $this->error("Failed to send email: {$e->getMessage()}");
                Log::error("Backup email failed: {$e->getMessage()}");
                return self::FAILURE;
            }
        } else {
            $this->warn("No BACKUP_EMAIL set. Backup files saved locally only.");
        }

        $this->cleanupOldBackups($backupDir, 7);
        $this->info("Backup complete! Files: {$backupDir}");
        return self::SUCCESS;
    }

    protected function exportTableToCsv(string $table, string $backupDir, string $timestamp): ?array
    {
        try {
            $rows = DB::table($table)->get();
        } catch (\Exception $e) {
            return null;
        }

        if ($rows->isEmpty()) {
            return null;
        }

        $csvPath = "{$backupDir}/backup_{$timestamp}_{$table}.csv";
        $output = fopen($csvPath, 'w');

        $columns = array_keys((array) $rows->first());
        fputcsv($output, $columns);

        foreach ($rows as $row) {
            fputcsv($output, (array) $row);
        }

        fclose($output);
        return [$csvPath, $rows->count()];
    }

    protected function cleanupOldBackups(string $directory, int $keep): void
    {
        $prefix = glob("{$directory}/backup_");
        $allBackups = glob("{$directory}/backup_*.csv");
        $manifests = glob("{$directory}/backup_*_manifest.txt");

        $files = array_merge($allBackups, $manifests);

        if (count($files) <= $keep) {
            return;
        }

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
        foreach (array_slice($files, $keep) as $file) {
            unlink($file);
            $this->info("Removed old backup: " . basename($file));
        }
    }
}
