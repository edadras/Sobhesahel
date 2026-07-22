<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Dependency-free backup service (WP-14).
 *
 * - Database: mysqldump (mysql/mariadb) piped through gzip, or a gzipped
 *   copy of the sqlite database file.
 * - Files: ZipArchive of the configured upload directories.
 * - Retention: keeps the newest N archives per type and prunes the rest.
 */
class BackupService
{
    /**
     * Dump the configured database connection to a gzipped file.
     *
     * @return string absolute path of the created archive
     */
    public function backupDatabase(): string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (! is_array($config)) {
            throw new RuntimeException("Unknown database connection [{$connection}].");
        }

        $directory = $this->path('db');
        $this->ensureDirectory($directory);

        $driver = $config['driver'] ?? '';

        return match ($driver) {
            'mysql', 'mariadb' => $this->dumpMysql($config, $directory),
            'sqlite' => $this->copySqlite($config, $directory),
            default => throw new RuntimeException("Unsupported database driver [{$driver}] for backups."),
        };
    }

    /**
     * Zip the configured directories into a single archive.
     *
     * @return string absolute path of the created archive
     */
    public function backupFiles(): string
    {
        $directory = $this->path('files');
        $this->ensureDirectory($directory);

        $target = $directory.DIRECTORY_SEPARATOR.'backup-'.$this->timestamp().'.zip';

        $zip = new ZipArchive();

        if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Unable to create zip archive at [{$target}].");
        }

        $backupRoot = $this->normalize($this->path());
        $excluded = array_filter(array_map(
            fn ($path) => $this->normalize($path),
            (array) config('backup.files.exclude', [])
        ));
        $excluded[] = $backupRoot;

        $added = 0;

        foreach ((array) config('backup.files.include', []) as $includeDir) {
            $includeDir = $this->normalize($includeDir);

            if ($includeDir === '' || ! is_dir($includeDir)) {
                continue;
            }

            $prefix = $this->archivePrefix($includeDir);

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($includeDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->isLink()) {
                    continue;
                }

                $realPath = $this->normalize($file->getPathname());

                if ($this->isExcluded($realPath, $excluded)) {
                    continue;
                }

                $relative = ltrim(substr($realPath, strlen($includeDir)), '/\\');

                if ($zip->addFile($file->getPathname(), $prefix.'/'.str_replace('\\', '/', $relative))) {
                    $added++;
                }
            }
        }

        if (! $zip->close()) {
            @unlink($target);

            throw new RuntimeException("Failed to finalize zip archive at [{$target}].");
        }

        if ($added === 0) {
            @unlink($target);

            throw new RuntimeException('Files backup skipped: none of the configured directories contained files.');
        }

        return $target;
    }

    /**
     * Remove archives beyond the configured retention count for a type.
     *
     * @param  string  $type  "db" or "files"
     * @return array<int, string> names of the deleted files
     */
    public function prune(string $type): array
    {
        $keep = max(1, (int) config("backup.retention.{$type}", 7));
        $files = $this->archives($type);

        $deleted = [];

        foreach (array_slice($files, $keep) as $file) {
            if (@unlink($file['path'])) {
                $deleted[] = $file['name'];
            }
        }

        return $deleted;
    }

    /**
     * List existing backup archives (both types), newest first.
     *
     * @return array<int, array{type: string, name: string, path: string, size: int, size_for_humans: string, modified: Carbon}>
     */
    public function listBackups(): array
    {
        $all = array_merge($this->archives('db'), $this->archives('files'));

        usort($all, fn ($a, $b) => $b['modified']->getTimestamp() <=> $a['modified']->getTimestamp());

        return $all;
    }

    /**
     * Absolute path of the backup root or one of its type sub-directories.
     */
    public function path(?string $type = null): string
    {
        $root = rtrim((string) config('backup.path', storage_path('app/backups')), '/\\');

        return $type ? $root.DIRECTORY_SEPARATOR.$type : $root;
    }

    /**
     * Archives of one type, newest first.
     *
     * @return array<int, array{type: string, name: string, path: string, size: int, size_for_humans: string, modified: Carbon}>
     */
    protected function archives(string $type): array
    {
        $directory = $this->path($type);

        if (! is_dir($directory)) {
            return [];
        }

        $files = [];

        foreach (glob($directory.DIRECTORY_SEPARATOR.'backup-*') ?: [] as $path) {
            if (! is_file($path)) {
                continue;
            }

            $size = (int) filesize($path);

            $files[] = [
                'type' => $type,
                'name' => basename($path),
                'path' => $path,
                'size' => $size,
                'size_for_humans' => $this->humanSize($size),
                'modified' => Carbon::createFromTimestamp((int) filemtime($path)),
            ];
        }

        usort($files, fn ($a, $b) => $b['modified']->getTimestamp() <=> $a['modified']->getTimestamp());

        return $files;
    }

    /**
     * Dump a MySQL/MariaDB database via mysqldump, gzipping the output.
     *
     * @param  array<string, mixed>  $config
     */
    protected function dumpMysql(array $config, string $directory): string
    {
        $target = $directory.DIRECTORY_SEPARATOR.'backup-'.$this->timestamp().'.sql.gz';

        $command = [
            (string) config('backup.mysqldump.binary_path', 'mysqldump'),
            '--user='.($config['username'] ?? 'root'),
        ];

        if (! empty($config['unix_socket'])) {
            $command[] = '--socket='.$config['unix_socket'];
        } else {
            $command[] = '--host='.($config['host'] ?? '127.0.0.1');
            $command[] = '--port='.($config['port'] ?? '3306');
        }

        foreach ((array) config('backup.mysqldump.extra_args', []) as $arg) {
            $command[] = (string) $arg;
        }

        $command[] = (string) ($config['database'] ?? '');

        $gz = gzopen($target, 'wb9');

        if ($gz === false) {
            throw new RuntimeException("Unable to open [{$target}] for writing.");
        }

        // Pass the password via the environment so it never shows up in `ps`.
        $process = new Process($command, base_path(), [
            'MYSQL_PWD' => (string) ($config['password'] ?? ''),
        ]);
        $process->setTimeout((int) config('backup.mysqldump.timeout', 600));

        $stderr = '';

        try {
            $process->run(function (string $type, string $buffer) use ($gz, &$stderr): void {
                if ($type === Process::OUT) {
                    gzwrite($gz, $buffer);
                } else {
                    $stderr .= $buffer;
                }
            });
        } finally {
            gzclose($gz);
        }

        if (! $process->isSuccessful()) {
            @unlink($target);

            throw new RuntimeException('mysqldump failed: '.(trim($stderr) !== '' ? trim($stderr) : 'exit code '.$process->getExitCode()));
        }

        return $target;
    }

    /**
     * Copy the sqlite database file into a gzipped archive.
     *
     * @param  array<string, mixed>  $config
     */
    protected function copySqlite(array $config, string $directory): string
    {
        $source = (string) ($config['database'] ?? '');

        if ($source === '' || ! is_file($source)) {
            throw new RuntimeException("SQLite database file not found at [{$source}].");
        }

        $target = $directory.DIRECTORY_SEPARATOR.'backup-'.$this->timestamp().'.sqlite.gz';

        $in = fopen($source, 'rb');
        $gz = gzopen($target, 'wb9');

        if ($in === false || $gz === false) {
            if (is_resource($in)) {
                fclose($in);
            }

            throw new RuntimeException("Unable to copy sqlite database to [{$target}].");
        }

        while (! feof($in)) {
            $chunk = fread($in, 1024 * 1024);

            if ($chunk === false) {
                break;
            }

            gzwrite($gz, $chunk);
        }

        fclose($in);
        gzclose($gz);

        return $target;
    }

    protected function isExcluded(string $path, array $excluded): bool
    {
        foreach ($excluded as $prefix) {
            if ($prefix !== '' && str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Local name prefix inside the zip for an included directory.
     */
    protected function archivePrefix(string $directory): string
    {
        $base = $this->normalize(base_path());

        if (str_starts_with($directory, $base)) {
            $relative = trim(str_replace('\\', '/', substr($directory, strlen($base))), '/');

            if ($relative !== '') {
                return $relative;
            }
        }

        return basename($directory);
    }

    protected function normalize(string $path): string
    {
        $real = realpath($path);

        return rtrim($real !== false ? $real : $path, '/\\');
    }

    protected function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create backup directory [{$directory}].");
        }
    }

    protected function timestamp(): string
    {
        return now()->format('Y-m-d-His');
    }

    protected function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;
        $value = (float) $bytes;

        while ($value >= 1024 && $index < count($units) - 1) {
            $value /= 1024;
            $index++;
        }

        return sprintf($index === 0 ? '%d %s' : '%.1f %s', $value, $units[$index]);
    }
}
