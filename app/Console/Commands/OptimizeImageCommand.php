<?php

namespace App\Console\Commands;

use App\Models\Archive;
use App\Models\News;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class OptimizeImageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:images {--model=news : The model to optimize images for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize images for specified model';

    protected $totalOriginalSize =  0;
    protected $totalOptimizedSize = 0;
    protected $totalImages = 0;
    protected $startTime;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Increase memory limit temporarily
        ini_set('memory_limit', '1024M');

        // Disable Telescope to reduce memory usage
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }

        $this->startTime = microtime(true);
        $model = $this->option('model');

        $this->info("Starting image optimization for {$model}...");
        $this->newLine();

        // Define cache key based on model
        $cacheKey = "optimize_images_{$model}_ids";

        // Get record IDs to process, with caching
        $recordIds = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($model) {
            $query = match ($model) {
                'archive' => Archive::where('image_small', '=', null)->orderBy('id', 'DESC'),
                'news' => News::orderBy('id', 'DESC'),
                'video' => Video::where('image_small', '=', null)->orderBy('id', 'DESC'),
                default => throw new \InvalidArgumentException("Unsupported model: {$model}")
            };
            return $query->pluck('id')->toArray();
        });

        $total = count($recordIds);
        $this->info("Found {$total} records to process");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // Process records in smaller chunks
        collect($recordIds)->chunk(5)->each(function (Collection $chunkIds) use ($model, $bar) {
            $records = match ($model) {
                'archive' => Archive::whereIn('id', $chunkIds)->get(),
                'news' => News::whereIn('id', $chunkIds)->get(),
                'video' => Video::whereIn('id', $chunkIds)->get(),
            };

            foreach ($records as $record) {

                if (!$record->image_large) {
                    $bar->advance();
                    continue;
                }


                if ($record->image_small != null) {
                    $old = $record->getTitleValueField('original_image');

                    if ($old == null){
                        $old = str_replace('news/','',$record->image_small);

                        $old = str_replace('_thumbnail.webp','',$old);

                        $formats = ['jpg','jpeg','png','gif','webp'];

                        foreach ($formats as $format){
                            if (file_exists( Storage::disk('public')->path($old.'.'.$format) )){
                                $old = $old . '.' . $format;

                                break;
                            }
                        }
                    }

                    $record->update([
                        'image_original' => $old
                    ]);

                    $bar->advance();
                    $this->alert("Already optimized for post {$record->id}");
                    continue;
                }

                try {
                    // Check both storage disks
                    $originalPath = $record->image_large;
                    $originalDisk = 'public';

                    if (!Storage::disk('public')->exists($originalPath)) {
                        if (Storage::disk('media')->exists($originalPath)) {
                            $originalDisk = 'media';
                        } else {
                            $this->warn("\nImage not found in both public and media disks for record {$record->id}: {$originalPath}");
                            $bar->advance();
                            continue;
                        }
                    }

                    $original_image_path = Storage::disk($originalDisk)->path($originalPath);
                    $original_image_info = getimagesize($original_image_path);

                    $optimized_image_path = Storage::disk('media')->path('');

                    $op = new \App\Helpers\ImageOptimizer();

                    $optimized_images = $op->convertImageToWebP(
                        path: $original_image_path,
                        destinationDir: $optimized_image_path,
                        isDirectPath: true,
                        parentClass: get_class($record)
                    );

                    // Add original image information
                    $optimized_images['original_image'] = [
                        'path' => $originalPath,
                        'size' => filesize($original_image_path),
                        'width' => $original_image_info[0],
                        'height' => $original_image_info[1]
                    ];

                    // Fix paths to be relative to media directory
                    foreach ($optimized_images as $key => $image) {
                        if (isset($image['path'])) {
                            $path = $image['path'];
                            if (preg_match('/media\/(.*)/', $path, $matches)) {
                                $optimized_images[$key]['path'] = $matches[1];
                            }
                        }
                    }

                    $record->update([
                        'image_large' => $optimized_images['original']['path'],
                        'image_medium' => $optimized_images['small']['path'],
                        'image_small' => $optimized_images['thumbnail']['path'],
                        'image_original' => $originalPath
                    ]);

                    // Track statistics
                    $this->totalImages++;
                    $this->totalOriginalSize += $optimized_images['original_image']['size'];
                    $this->totalOptimizedSize += $optimized_images['original']['size'];

                    // Log individual optimization results
                    $this->logOptimizationResult($record, $optimized_images);

                    // Clear memory
                    unset($optimized_images, $original_image_path, $original_image_info);
                } catch (\Exception $e) {
                    $this->error("\nError processing record {$record->id}: {$e->getMessage()}");
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        // Display final statistics
        $this->displayFinalStatistics();

        // Re-enable Telescope if it was disabled
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::startRecording();
        }
    }

    protected function logOptimizationResult($record, array $result): void
    {
        $originalSize = $this->formatBytes($result['original_image']['size']);
        $optimizedSize = $this->formatBytes($result['original']['size']);
        $savings = $this->formatBytes($result['original_image']['size'] - $result['original']['size']);
        $savingsPercentage = round((($result['original_image']['size'] - $result['original']['size']) / $result['original_image']['size']) * 100, 2);

        $this->line("\nRecord ID: {$record->id}");
        $this->line("Original: {$originalSize} ({$result['original_image']['width']}x{$result['original_image']['height']})");
        $this->line("Optimized: {$optimizedSize} ({$result['original']['width']}x{$result['original']['height']})");
        $this->line("Savings: {$savings} ({$savingsPercentage}%)");
        $this->line("Paths:");
        $this->line("  - Large: {$result['original']['path']}");
        $this->line("  - Medium: {$result['small']['path']}");
        $this->line("  - Small: {$result['thumbnail']['path']}");
        $this->newLine();
    }

    protected function displayFinalStatistics(): void
    {
        $duration = round(microtime(true) - $this->startTime, 2);
        $totalSavings = $this->formatBytes($this->totalOriginalSize - $this->totalOptimizedSize);
        $savingsPercentage = round((($this->totalOriginalSize - $this->totalOptimizedSize) / $this->totalOriginalSize) * 100, 2);

        $this->info('Optimization Complete!');
        $this->newLine();
        $this->line("Total Images Processed: {$this->totalImages}");
        $this->line("Total Original Size: " . $this->formatBytes($this->totalOriginalSize));
        $this->line("Total Optimized Size: " . $this->formatBytes($this->totalOptimizedSize));
        $this->line("Total Storage Saved: {$totalSavings} ({$savingsPercentage}%)");
        $this->line("Total Time: {$duration} seconds");
        $this->line("Average Time per Image: " . ($this->totalImages ? round($duration / $this->totalImages, 2) : 0) . " seconds");
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
