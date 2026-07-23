<?php

namespace App\Services;

use FilesystemIterator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Morilog\Jalali\Jalalian;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

/**
 * Central file/media manager backend.
 *
 * Every public operation receives a disk name plus a *relative* path and is
 * strictly confined to the configured local disk roots: relative paths are
 * sanitized segment by segment (no "..", no null bytes) and the final
 * absolute path is canonicalized with realpath() and checked against the
 * canonical root before any filesystem call. This is security-critical —
 * do not bypass resolve()/sanitizeRelativePath() when adding operations.
 */
class MediaManagerService
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'bmp', 'ico'];

    private const AUDIO_EXTENSIONS = ['mp3', 'ogg', 'wav', 'm4a', 'aac'];

    private const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'mkv', 'mpg', 'mpeg'];

    private const MIME_TO_EXTENSION = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'image/avif' => 'avif',
        'image/bmp' => 'bmp',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
        'audio/mpeg' => 'mp3',
        'audio/mp3' => 'mp3',
        'audio/ogg' => 'ogg',
        'audio/wav' => 'wav',
        'audio/x-wav' => 'wav',
        'audio/mp4' => 'm4a',
        'audio/x-m4a' => 'm4a',
        'audio/aac' => 'aac',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
        'video/x-matroska' => 'mkv',
        'video/mpeg' => 'mpg',
        'application/pdf' => 'pdf',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'text/plain' => 'txt',
        'text/csv' => 'csv',
    ];

    /**
     * Browsable disk names (local driver only).
     *
     * @return array<int, string>
     */
    public function disks(): array
    {
        $disks = array_keys((array) config('media-manager.disks', []));

        return array_values(array_filter(
            $disks,
            fn ($disk) => is_string($disk) && config("filesystems.disks.{$disk}.driver") === 'local'
        ));
    }

    /**
     * @return array<string, string> disk name => Persian label
     */
    public function diskLabels(): array
    {
        $labels = [];

        foreach ($this->disks() as $disk) {
            $labels[$disk] = (string) (config("media-manager.disks.{$disk}") ?: $disk);
        }

        return $labels;
    }

    public function assertDisk(string $disk): void
    {
        if (! in_array($disk, $this->disks(), true)) {
            throw new RuntimeException('دیسک انتخاب‌شده مجاز نیست.');
        }
    }

    /**
     * Canonical (realpath) root of an allowed disk.
     */
    public function root(string $disk): string
    {
        $this->assertDisk($disk);

        $root = config("filesystems.disks.{$disk}.root");

        if (! is_string($root) || $root === '') {
            throw new RuntimeException('ریشه دیسک نامعتبر است.');
        }

        if (! is_dir($root)) {
            @mkdir($root, 0755, true);
        }

        $real = realpath($root);

        if ($real === false || ! is_dir($real)) {
            throw new RuntimeException('ریشه دیسک در دسترس نیست.');
        }

        return $real;
    }

    /**
     * Normalize a user-supplied relative path. Rejects null bytes and any
     * "." / ".." segments; returns a clean "a/b/c" style path ('' = root).
     */
    public function sanitizeRelativePath(string $path): string
    {
        if (str_contains($path, "\0")) {
            throw new RuntimeException('مسیر نامعتبر است.');
        }

        $path = str_replace('\\', '/', $path);
        $segments = [];

        foreach (explode('/', $path) as $segment) {
            $segment = trim($segment);

            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                throw new RuntimeException('مسیر نامعتبر است.');
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }

    /**
     * Resolve a relative path to a canonical absolute path inside the disk
     * root, rejecting anything (including symlink tricks) that escapes it.
     * With $mustExist = false the parent directory must already exist and
     * resolve inside the root; the returned path may then be created.
     */
    public function resolve(string $disk, string $path, bool $mustExist = true): string
    {
        $root = $this->root($disk);
        $relative = $this->sanitizeRelativePath($path);

        $absolute = $relative === ''
            ? $root
            : $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

        if (file_exists($absolute)) {
            $real = realpath($absolute);

            if ($real === false || ! $this->isInsideRoot($real, $root)) {
                throw new RuntimeException('دسترسی به مسیر خارج از محدوده مجاز نیست.');
            }

            return $real;
        }

        if ($mustExist) {
            throw new RuntimeException('مسیر مورد نظر یافت نشد.');
        }

        $parent = realpath(dirname($absolute));

        if ($parent === false || ! is_dir($parent) || ! $this->isInsideRoot($parent, $root)) {
            throw new RuntimeException('دسترسی به مسیر خارج از محدوده مجاز نیست.');
        }

        return $parent.DIRECTORY_SEPARATOR.basename($absolute);
    }

    private function isInsideRoot(string $path, string $root): bool
    {
        return $path === $root || str_starts_with($path, $root.DIRECTORY_SEPARATOR);
    }

    /**
     * List one directory: all sub-folders plus a single page of files.
     * Only the file names of the current page are stat()ed, so very large
     * folders stay cheap.
     *
     * @return array{folders: array<int, array{name: string, path: string}>, files: array<int, array<string, mixed>>, total: int, page: int, pages: int}
     */
    public function listDirectory(string $disk, string $path, int $page = 1, ?int $perPage = null): array
    {
        $dir = $this->resolve($disk, $path);

        if (! is_dir($dir)) {
            throw new RuntimeException('مسیر انتخاب‌شده یک پوشه نیست.');
        }

        $relativeBase = $this->sanitizeRelativePath($path);
        $perPage = max(1, $perPage ?? (int) config('media-manager.per_page', 48));

        $folders = [];
        $fileNames = [];

        foreach ((scandir($dir) ?: []) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (is_dir($dir.DIRECTORY_SEPARATOR.$entry)) {
                $folders[] = $entry;
            } else {
                $fileNames[] = $entry;
            }
        }

        sort($folders, SORT_NATURAL | SORT_FLAG_CASE);
        sort($fileNames, SORT_NATURAL | SORT_FLAG_CASE);

        $total = count($fileNames);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);

        $files = [];

        foreach (array_slice($fileNames, ($page - 1) * $perPage, $perPage) as $name) {
            $relative = ltrim($relativeBase.'/'.$name, '/');
            $files[] = $this->fileInfo($disk, $relative, $dir.DIRECTORY_SEPARATOR.$name);
        }

        return [
            'folders' => array_map(fn ($name) => [
                'name' => $name,
                'path' => ltrim($relativeBase.'/'.$name, '/'),
            ], $folders),
            'files' => $files,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    /**
     * Recursive filename search under the given directory (capped).
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $disk, string $path, string $query, ?int $limit = null): array
    {
        $base = $this->resolve($disk, $path);

        if (! is_dir($base)) {
            throw new RuntimeException('مسیر انتخاب‌شده یک پوشه نیست.');
        }

        $root = $this->root($disk);
        $limit = max(1, $limit ?? (int) config('media-manager.search_limit', 300));
        $query = trim($query);
        $results = [];

        if ($query === '') {
            return $results;
        }

        try {
            // SKIP_DOTS and no FOLLOW_SYMLINKS: symlinked directories are not
            // recursed into, so the search cannot walk out of the root.
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY,
                RecursiveIteratorIterator::CATCH_GET_CHILD
            );
        } catch (Throwable) {
            return $results;
        }

        foreach ($iterator as $file) {
            if (! $file instanceof \SplFileInfo || ! $file->isFile()) {
                continue;
            }

            if (mb_stripos($file->getFilename(), $query) === false) {
                continue;
            }

            $relative = str_replace(DIRECTORY_SEPARATOR, '/', ltrim(substr($file->getPathname(), strlen($root)), DIRECTORY_SEPARATOR));
            $results[] = $this->fileInfo($disk, $relative, $file->getPathname());

            if (count($results) >= $limit) {
                break;
            }
        }

        usort($results, fn ($a, $b) => strnatcasecmp($a['name'], $b['name']));

        return $results;
    }

    /**
     * Metadata for a single existing file (validated against the root).
     *
     * @return array<string, mixed>
     */
    public function info(string $disk, string $path): array
    {
        $absolute = $this->resolve($disk, $path);

        if (! is_file($absolute)) {
            throw new RuntimeException('فایل مورد نظر یافت نشد.');
        }

        return $this->fileInfo($disk, $this->sanitizeRelativePath($path), $absolute);
    }

    /**
     * @return array<string, mixed>
     */
    private function fileInfo(string $disk, string $relative, string $absolute): array
    {
        $name = basename($relative);
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $size = is_file($absolute) ? (int) @filesize($absolute) : 0;
        $modified = @filemtime($absolute) ?: time();

        $type = match (true) {
            in_array($extension, self::IMAGE_EXTENSIONS, true) => 'image',
            in_array($extension, self::AUDIO_EXTENSIONS, true) => 'audio',
            in_array($extension, self::VIDEO_EXTENSIONS, true) => 'video',
            default => 'other',
        };

        return [
            'name' => $name,
            'path' => $relative,
            'directory' => str_contains($relative, '/') ? dirname($relative) : '',
            'extension' => $extension,
            'type' => $type,
            'size' => $size,
            'size_human' => $this->humanSize($size),
            'modified_jalali' => Jalalian::fromCarbon(Carbon::createFromTimestamp($modified))->format('Y/m/d H:i'),
            'url' => $this->url($disk, $relative),
        ];
    }

    public function url(string $disk, string $path): string
    {
        $relative = $this->sanitizeRelativePath($path);
        $base = rtrim((string) config("filesystems.disks.{$disk}.url", ''), '/');
        $encoded = implode('/', array_map('rawurlencode', explode('/', $relative)));

        return $base !== '' ? $base.'/'.$encoded : '/'.$encoded;
    }

    public function humanSize(int $bytes): string
    {
        $units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
        $i = 0;
        $value = (float) $bytes;

        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        return ($i === 0 ? number_format($value) : number_format($value, 1)).' '.$units[$i];
    }

    /**
     * All directories of a disk (relative paths) for the "move" select, capped.
     *
     * @return array<int, string>
     */
    public function directories(string $disk, ?int $limit = null): array
    {
        $root = $this->root($disk);
        $limit = max(1, $limit ?? (int) config('media-manager.folder_select_limit', 500));
        $directories = [];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST,
                RecursiveIteratorIterator::CATCH_GET_CHILD
            );
        } catch (Throwable) {
            return $directories;
        }

        foreach ($iterator as $entry) {
            if (! $entry instanceof \SplFileInfo || ! $entry->isDir()) {
                continue;
            }

            $directories[] = str_replace(DIRECTORY_SEPARATOR, '/', ltrim(substr($entry->getPathname(), strlen($root)), DIRECTORY_SEPARATOR));

            if (count($directories) >= $limit) {
                break;
            }
        }

        sort($directories, SORT_NATURAL | SORT_FLAG_CASE);

        return $directories;
    }

    public function createFolder(string $disk, string $path, string $name): string
    {
        $name = $this->sanitizeFileName($name);

        if (str_contains($name, '.')) {
            // Keep folder names extension-free and dot-free (avoids confusion
            // with hidden folders and web-server handler tricks).
            $name = str_replace('.', '-', $name);
        }

        $target = $this->resolve($disk, $this->sanitizeRelativePath($path).'/'.$name, false);

        if (file_exists($target)) {
            throw new RuntimeException('پوشه‌ای با این نام از قبل وجود دارد.');
        }

        if (! @mkdir($target, 0755)) {
            throw new RuntimeException('ساخت پوشه ناموفق بود.');
        }

        return $name;
    }

    public function rename(string $disk, string $path, string $newName): string
    {
        $source = $this->resolve($disk, $path);

        if (! is_file($source)) {
            throw new RuntimeException('فایل مورد نظر یافت نشد.');
        }

        $newName = $this->sanitizeFileName($newName);
        $this->assertAllowedExtension($newName);

        $directory = dirname($this->sanitizeRelativePath($path));
        $directory = $directory === '.' ? '' : $directory;
        $target = $this->resolve($disk, $directory.'/'.$newName, false);

        if (file_exists($target)) {
            throw new RuntimeException('فایلی با این نام از قبل وجود دارد.');
        }

        if (! @rename($source, $target)) {
            throw new RuntimeException('تغییر نام فایل ناموفق بود.');
        }

        return $newName;
    }

    public function delete(string $disk, string $path): void
    {
        $absolute = $this->resolve($disk, $path);

        if (! is_file($absolute)) {
            throw new RuntimeException('فایل مورد نظر یافت نشد.');
        }

        if (! @unlink($absolute)) {
            throw new RuntimeException('حذف فایل ناموفق بود.');
        }
    }

    public function move(string $disk, string $path, string $targetDirectory): string
    {
        $source = $this->resolve($disk, $path);

        if (! is_file($source)) {
            throw new RuntimeException('فایل مورد نظر یافت نشد.');
        }

        $targetDir = $this->resolve($disk, $targetDirectory);

        if (! is_dir($targetDir)) {
            throw new RuntimeException('پوشه مقصد یافت نشد.');
        }

        $name = basename($source);
        $target = $targetDir.DIRECTORY_SEPARATOR.$name;

        if (file_exists($target)) {
            throw new RuntimeException('فایلی با همین نام در پوشه مقصد وجود دارد.');
        }

        if (! @rename($source, $target)) {
            throw new RuntimeException('انتقال فایل ناموفق بود.');
        }

        return ltrim($this->sanitizeRelativePath($targetDirectory).'/'.$name, '/');
    }

    /**
     * Store a Livewire temporary upload into a directory, keeping the
     * original (sanitized) client name and de-duplicating collisions.
     */
    public function storeUpload(string $disk, string $path, TemporaryUploadedFile $file): string
    {
        $directory = $this->resolve($disk, $path);

        if (! is_dir($directory)) {
            throw new RuntimeException('پوشه مقصد یافت نشد.');
        }

        $name = $this->sanitizeFileName($file->getClientOriginalName());
        $this->assertAllowedExtension($name);
        $name = $this->uniqueName($directory, $name);

        $stored = $file->storeAs($this->sanitizeRelativePath($path), $name, ['disk' => $disk]);

        if ($stored === false) {
            throw new RuntimeException('ذخیره فایل ناموفق بود.');
        }

        return $name;
    }

    /**
     * Download a remote file into a directory. Returns the stored file name.
     */
    public function downloadFromUrl(string $disk, string $path, string $url): string
    {
        $config = (array) config('media-manager.url_download', []);
        $maxBytes = max(1, (int) ($config['max_size_kb'] ?? 30720)) * 1024;
        $allowedMimes = $config['allowed_mime_types'] ?? null;

        return $this->fetchUrlToDirectory($disk, $path, $url, $maxBytes, is_array($allowedMimes) ? $allowedMimes : null);
    }

    /**
     * Fetch a webpage, extract <img> URLs and download them (capped) into a
     * "host-date" subfolder of the current directory.
     *
     * @return array{folder: string, found: int, saved: array<int, string>, failed: array<string, string>}
     */
    public function harvestImages(string $disk, string $path, string $url): array
    {
        $config = (array) config('media-manager.harvest', []);
        $timeout = (int) (config('media-manager.url_download.timeout') ?: 20);
        $maxImages = max(1, (int) ($config['max_images'] ?? 30));
        $maxImageBytes = max(1, (int) ($config['max_image_size_kb'] ?? 10240)) * 1024;
        $maxHtmlBytes = max(1, (int) ($config['max_html_size_kb'] ?? 5120)) * 1024;

        $this->validateRemoteUrl($url);

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; SobheSahelMediaManager/1.0)'])
                ->get($url);
        } catch (Throwable $e) {
            throw new RuntimeException('دریافت صفحه وب ناموفق بود: '.$e->getMessage());
        }

        if (! $response->successful()) {
            throw new RuntimeException('دریافت صفحه وب ناموفق بود (کد HTTP '.$response->status().').');
        }

        $html = substr($response->body(), 0, $maxHtmlBytes);

        if (trim($html) === '') {
            throw new RuntimeException('محتوای صفحه خالی است.');
        }

        $imageUrls = $this->extractImageUrls($html, $url);

        if ($imageUrls === []) {
            throw new RuntimeException('هیچ تصویری در این صفحه یافت نشد.');
        }

        $found = count($imageUrls);
        $imageUrls = array_slice($imageUrls, 0, $maxImages);

        $host = (string) parse_url($url, PHP_URL_HOST);
        $folderName = trim(preg_replace('/[^a-z0-9.-]+/i', '-', $host), '-.');
        $folderName = ($folderName !== '' ? $folderName : 'webpage').'-'.date('Y-m-d');

        $base = $this->sanitizeRelativePath($path);
        $subfolder = ltrim($base.'/'.$folderName, '/');
        $target = $this->resolve($disk, $subfolder, false);

        if (! is_dir($target) && ! @mkdir($target, 0755)) {
            throw new RuntimeException('ساخت پوشه مقصد ناموفق بود.');
        }

        $imageMimes = array_values(array_filter(
            (array) config('media-manager.allowed_mime_types', []),
            fn ($mime) => is_string($mime) && str_starts_with($mime, 'image/')
        ));

        $saved = [];
        $failed = [];

        foreach ($imageUrls as $imageUrl) {
            try {
                $saved[] = $this->fetchUrlToDirectory($disk, $subfolder, $imageUrl, $maxImageBytes, $imageMimes);
            } catch (Throwable $e) {
                $failed[$imageUrl] = $e->getMessage();
            }
        }

        return [
            'folder' => $subfolder,
            'found' => $found,
            'saved' => $saved,
            'failed' => $failed,
        ];
    }

    /**
     * Core "URL -> disk" fetch with timeout, streamed size cap and
     * Content-Type / extension allowlisting.
     */
    private function fetchUrlToDirectory(string $disk, string $path, string $url, int $maxBytes, ?array $allowedMimes = null): string
    {
        $this->validateRemoteUrl($url);

        $directory = $this->resolve($disk, $path);

        if (! is_dir($directory)) {
            throw new RuntimeException('پوشه مقصد یافت نشد.');
        }

        $timeout = (int) (config('media-manager.url_download.timeout') ?: 20);
        $allowedMimes ??= (array) config('media-manager.allowed_mime_types', []);

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; SobheSahelMediaManager/1.0)'])
                ->withOptions(['stream' => true])
                ->get($url);
        } catch (Throwable $e) {
            throw new RuntimeException('دریافت فایل ناموفق بود: '.$e->getMessage());
        }

        if (! $response->successful()) {
            throw new RuntimeException('دریافت فایل ناموفق بود (کد HTTP '.$response->status().').');
        }

        $contentLength = (int) $response->header('Content-Length');

        if ($contentLength > $maxBytes) {
            throw new RuntimeException('حجم فایل بیشتر از حد مجاز ('.$this->humanSize($maxBytes).') است.');
        }

        $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));

        if ($mime === '' || ! in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException('نوع محتوای فایل ('.($mime !== '' ? $mime : 'نامشخص').') مجاز نیست.');
        }

        // Read the body in chunks so the size cap is enforced while streaming.
        $body = '';

        try {
            $stream = $response->toPsrResponse()->getBody();

            while (! $stream->eof()) {
                $chunk = $stream->read(65536);

                if ($chunk === '') {
                    break;
                }

                $body .= $chunk;

                if (strlen($body) > $maxBytes) {
                    throw new RuntimeException('حجم فایل بیشتر از حد مجاز ('.$this->humanSize($maxBytes).') است.');
                }
            }
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RuntimeException('خواندن محتوای فایل ناموفق بود.');
        }

        if ($body === '') {
            throw new RuntimeException('فایل دریافتی خالی است.');
        }

        $name = $this->fileNameFromUrl($url, $mime);
        $this->assertAllowedExtension($name);
        $name = $this->uniqueName($directory, $name);

        if (@file_put_contents($directory.DIRECTORY_SEPARATOR.$name, $body) === false) {
            throw new RuntimeException('ذخیره فایل ناموفق بود.');
        }

        return $name;
    }

    /**
     * Only plain http/https URLs to public hosts are fetched (basic SSRF guard).
     */
    private function validateRemoteUrl(string $url): void
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('نشانی وارد شده معتبر نیست.');
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new RuntimeException('فقط نشانی‌های http و https مجاز هستند.');
        }

        $host = (string) parse_url($url, PHP_URL_HOST);

        if ($host === '' || strtolower($host) === 'localhost') {
            throw new RuntimeException('دسترسی به آدرس‌های داخلی مجاز نیست.');
        }

        $ip = filter_var($host, FILTER_VALIDATE_IP) !== false ? $host : gethostbyname($host);

        if (
            filter_var($ip, FILTER_VALIDATE_IP) !== false
            && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
        ) {
            throw new RuntimeException('دسترسی به آدرس‌های داخلی مجاز نیست.');
        }
    }

    /**
     * Extract absolute image URLs from HTML, guarded against libxml warnings.
     *
     * @return array<int, string>
     */
    private function extractImageUrls(string $html, string $baseUrl): array
    {
        $previous = libxml_use_internal_errors(true);

        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $urls = [];

        foreach ($document->getElementsByTagName('img') as $img) {
            $src = trim((string) $img->getAttribute('src'));
            $absolute = $this->absoluteUrl($src, $baseUrl);

            if ($absolute !== null) {
                $urls[$absolute] = true;
            }
        }

        return array_keys($urls);
    }

    private function absoluteUrl(string $src, string $baseUrl): ?string
    {
        if ($src === '' || str_starts_with($src, 'data:') || str_starts_with($src, 'blob:')) {
            return null;
        }

        if (preg_match('~^https?://~i', $src) === 1) {
            return $src;
        }

        $parts = parse_url($baseUrl);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';

        if ($host === '') {
            return null;
        }

        $origin = $scheme.'://'.$host.(isset($parts['port']) ? ':'.$parts['port'] : '');

        if (str_starts_with($src, '//')) {
            return $scheme.':'.$src;
        }

        if (str_starts_with($src, '/')) {
            return $origin.$src;
        }

        $directory = rtrim(str_replace('\\', '/', dirname($parts['path'] ?? '/')), '/');

        return $origin.$directory.'/'.$src;
    }

    private function fileNameFromUrl(string $url, string $mime): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $name = $this->sanitizeFileName(urldecode(basename($path)));
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = (array) config('media-manager.allowed_extensions', []);

        if ($extension === '' || ! in_array($extension, $allowed, true)) {
            $fallback = self::MIME_TO_EXTENSION[$mime] ?? null;

            if ($fallback === null) {
                throw new RuntimeException('پسوند فایل قابل تشخیص یا مجاز نیست.');
            }

            $base = pathinfo($name, PATHINFO_FILENAME);
            $base = $base !== '' ? $base : 'file-'.date('YmdHis');
            $name = $base.'.'.$fallback;
        }

        return $name;
    }

    /**
     * Keep only a safe basename: strips directory parts, control characters
     * and characters that are unsafe in file names, capping the length.
     */
    public function sanitizeFileName(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        $name = basename($name);
        $name = preg_replace('/[\x00-\x1F\x7F]+/u', '', $name) ?? '';
        $name = str_replace(['/', ':', '*', '?', '"', '<', '>', '|'], '-', $name);
        $name = trim($name, " \t.");

        if ($name === '' || $name === '.' || $name === '..') {
            $name = 'file-'.date('YmdHis');
        }

        if (mb_strlen($name) > 150) {
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $base = mb_substr(pathinfo($name, PATHINFO_FILENAME), 0, 120);
            $name = $extension !== '' ? $base.'.'.$extension : $base;
        }

        return $name;
    }

    private function assertAllowedExtension(string $name): void
    {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = (array) config('media-manager.allowed_extensions', []);

        if ($extension === '' || ! in_array($extension, $allowed, true)) {
            throw new RuntimeException('پسوند فایل «'.($extension !== '' ? $extension : 'بدون پسوند').'» مجاز نیست.');
        }
    }

    private function uniqueName(string $directory, string $name): string
    {
        if (! file_exists($directory.DIRECTORY_SEPARATOR.$name)) {
            return $name;
        }

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);

        for ($i = 1; $i <= 500; $i++) {
            $candidate = $extension !== ''
                ? $base.'-'.$i.'.'.$extension
                : $base.'-'.$i;

            if (! file_exists($directory.DIRECTORY_SEPARATOR.$candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('نام یکتا برای فایل پیدا نشد.');
    }
}
