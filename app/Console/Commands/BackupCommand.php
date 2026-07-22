<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class BackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup
        {--only-db : Only back up the database}
        {--only-files : Only back up the configured file directories}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Back up the database (mysqldump/sqlite copy, gzipped) and upload directories (zip) into storage/app/backups, then prune old archives';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backup): int
    {
        if (! config('backup.enabled', true)) {
            $this->warn('Backups are disabled (backup.enabled = false); nothing to do.');
            Log::info('Backup skipped: backups are disabled via config.');

            return self::SUCCESS;
        }

        $onlyDb = (bool) $this->option('only-db');
        $onlyFiles = (bool) $this->option('only-files');

        if ($onlyDb && $onlyFiles) {
            $this->error('The --only-db and --only-files options are mutually exclusive.');

            return self::INVALID;
        }

        $runDb = ! $onlyFiles;
        $runFiles = ! $onlyDb;

        $failures = [];

        if ($runDb) {
            $failures = array_merge($failures, $this->runStep('db', fn () => $backup->backupDatabase(), $backup));
        }

        if ($runFiles) {
            $failures = array_merge($failures, $this->runStep('files', fn () => $backup->backupFiles(), $backup));
        }

        if ($failures !== []) {
            Log::error('Backup run finished with failures.', ['failed' => $failures]);
            $this->error('Backup finished with failures: '.implode(', ', $failures));

            return self::FAILURE;
        }

        Log::info('Backup run completed successfully.', [
            'db' => $runDb,
            'files' => $runFiles,
        ]);
        $this->info('Backup completed successfully.');

        return self::SUCCESS;
    }

    /**
     * Run one backup step, then prune that type. Returns the failed step names.
     *
     * @param  callable(): string  $callback
     * @return array<int, string>
     */
    protected function runStep(string $type, callable $callback, BackupService $backup): array
    {
        $label = $type === 'db' ? 'Database' : 'Files';

        try {
            $started = microtime(true);
            $path = $callback();
            $seconds = round(microtime(true) - $started, 2);

            $this->info("{$label} backup created: {$path} ({$seconds}s)");
            Log::info("{$label} backup created.", ['path' => $path, 'seconds' => $seconds]);
        } catch (Throwable $e) {
            $this->error("{$label} backup failed: {$e->getMessage()}");
            Log::error("{$label} backup failed.", ['exception' => $e]);

            return [$type];
        }

        try {
            $deleted = $backup->prune($type);

            if ($deleted !== []) {
                $this->line("Pruned old {$type} backups: ".implode(', ', $deleted));
                Log::info("Pruned old {$type} backups.", ['deleted' => $deleted]);
            }
        } catch (Throwable $e) {
            // Pruning problems should not fail the run; the backup itself succeeded.
            $this->warn("Pruning {$type} backups failed: {$e->getMessage()}");
            Log::warning("Pruning {$type} backups failed.", ['exception' => $e]);
        }

        return [];
    }
}
