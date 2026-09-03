<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a database backup and store it in storage/app/backups';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Kitonga database backup...');

        $connection = config('database.default');
        $backupDir = storage_path('app/backups');

        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $fileName = "kitonga_backup_{$timestamp}";

        if ($connection === 'mysql') {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');

            $backupPath = "{$backupDir}/{$fileName}.sql";
            
            // Construct mysqldump command
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($backupPath)
            );

            // Execute shell command
            exec($command, $output, $resultCode);

            if ($resultCode === 0) {
                $this->info("MySQL database backup successfully saved to: {$backupPath}");
            } else {
                $this->error('Failed to dump MySQL database. Please verify connection credentials or mysqldump presence in PATH.');
            }
        } elseif ($connection === 'sqlite') {
            $database = config('database.connections.sqlite.database');
            $backupPath = "{$backupDir}/{$fileName}.sqlite";

            if (file_exists($database)) {
                copy($database, $backupPath);
                $this->info("SQLite database backup successfully saved to: {$backupPath}");
            } else {
                $this->error('SQLite database file does not exist.');
            }
        } else {
            $this->error("Backup for driver '{$connection}' is not currently supported.");
        }
    }
}
