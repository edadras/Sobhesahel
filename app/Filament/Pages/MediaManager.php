<?php

namespace App\Filament\Pages;

use App\Services\MediaManagerService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class MediaManager extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationGroup = 'محتوا';

    protected static ?string $navigationLabel = 'مدیریت فایل‌ها';

    protected static ?string $title = 'مدیریت فایل‌ها';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'media-manager';

    protected static string $view = 'filament.pages.media-manager';

    public string $disk = '';

    public string $path = '';

    public string $search = '';

    public int $filesPage = 1;

    public function mount(): void
    {
        $disks = $this->service()->disks();
        $this->disk = $disks[0] ?? 'public';
    }

    protected function service(): MediaManagerService
    {
        return app(MediaManagerService::class);
    }

    /**
     * @return array<string, string>
     */
    public function getDisks(): array
    {
        return $this->service()->diskLabels();
    }

    /**
     * Listing consumed by the blade view. All paths are validated inside the
     * service; on any violation we fall back to the disk root.
     *
     * @return array<string, mixed>
     */
    public function getListing(): array
    {
        $empty = [
            'folders' => [],
            'files' => [],
            'total' => 0,
            'page' => 1,
            'pages' => 1,
            'searching' => false,
            'error' => null,
        ];

        try {
            $this->service()->assertDisk($this->disk);
        } catch (RuntimeException) {
            $disks = $this->service()->disks();

            if ($disks === []) {
                return array_merge($empty, ['error' => 'هیچ دیسک مجازی پیکربندی نشده است.']);
            }

            $this->disk = $disks[0];
            $this->path = '';
        }

        try {
            if (trim($this->search) !== '') {
                $matches = $this->service()->search($this->disk, $this->path, $this->search);
                $perPage = max(1, (int) config('media-manager.per_page', 48));
                $total = count($matches);
                $pages = max(1, (int) ceil($total / $perPage));
                $this->filesPage = min(max(1, $this->filesPage), $pages);

                return array_merge($empty, [
                    'files' => array_slice($matches, ($this->filesPage - 1) * $perPage, $perPage),
                    'total' => $total,
                    'page' => $this->filesPage,
                    'pages' => $pages,
                    'searching' => true,
                ]);
            }

            $listing = $this->service()->listDirectory($this->disk, $this->path, $this->filesPage);
            $this->filesPage = $listing['page'];

            return array_merge($empty, $listing);
        } catch (RuntimeException $e) {
            $this->path = '';
            $this->filesPage = 1;

            return array_merge($empty, ['error' => $e->getMessage()]);
        }
    }

    public function setDisk(string $disk): void
    {
        try {
            $this->service()->assertDisk($disk);
        } catch (RuntimeException $e) {
            $this->notifyError($e->getMessage());

            return;
        }

        $this->disk = $disk;
        $this->path = '';
        $this->search = '';
        $this->filesPage = 1;
    }

    public function openFolder(string $path): void
    {
        try {
            $this->path = $this->service()->sanitizeRelativePath($path);
        } catch (RuntimeException $e) {
            $this->notifyError($e->getMessage());

            return;
        }

        $this->search = '';
        $this->filesPage = 1;
    }

    public function setFilesPage(int $page): void
    {
        $this->filesPage = max(1, $page);
    }

    public function updatedSearch(): void
    {
        $this->filesPage = 1;
    }

    public function download(string $path): ?StreamedResponse
    {
        try {
            $file = $this->service()->info($this->disk, $path);
        } catch (RuntimeException $e) {
            $this->notifyError($e->getMessage());

            return null;
        }

        $ascii = trim(Str::ascii($file['name']));

        if ($ascii === '' || str_contains($ascii, '?')) {
            $ascii = 'download'.($file['extension'] !== '' ? '.'.$file['extension'] : '');
        }

        $absolute = $this->service()->resolve($this->disk, $path);

        return response()->streamDownload(function () use ($absolute): void {
            $handle = fopen($absolute, 'rb');

            if ($handle === false) {
                return;
            }

            while (! feof($handle)) {
                echo fread($handle, 65536);
            }

            fclose($handle);
        }, $ascii);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadFiles')
                ->label('آپلود فایل')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('آپلود فایل در پوشه فعلی')
                ->modalSubmitActionLabel('آپلود')
                ->modalCancelActionLabel('انصراف')
                ->form([
                    FileUpload::make('files')
                        ->label('فایل‌ها')
                        ->multiple()
                        ->required()
                        ->storeFiles(false)
                        ->maxSize((int) config('media-manager.upload.max_size_kb', 51200))
                        ->maxFiles((int) config('media-manager.upload.max_files', 100))
                        ->acceptedFileTypes((array) config('media-manager.allowed_mime_types', []))
                        ->helperText('حداکثر حجم هر فایل: '.$this->service()->humanSize((int) config('media-manager.upload.max_size_kb', 51200) * 1024)),
                ])
                ->action(function (array $data): void {
                    $saved = 0;
                    $errors = [];

                    foreach ((array) ($data['files'] ?? []) as $file) {
                        if (! $file instanceof TemporaryUploadedFile) {
                            continue;
                        }

                        try {
                            $this->service()->storeUpload($this->disk, $this->path, $file);
                            $saved++;
                        } catch (Throwable $e) {
                            $errors[] = $file->getClientOriginalName().': '.$e->getMessage();
                        }
                    }

                    if ($errors === []) {
                        Notification::make()
                            ->title($saved.' فایل با موفقیت آپلود شد')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title($saved.' فایل آپلود شد؛ '.count($errors).' فایل ناموفق بود')
                            ->body(implode("\n", array_slice($errors, 0, 10)))
                            ->warning()
                            ->send();
                    }
                }),

            Action::make('createFolder')
                ->label('پوشه جدید')
                ->icon('heroicon-o-folder-plus')
                ->color('gray')
                ->modalHeading('ساخت پوشه جدید')
                ->modalSubmitActionLabel('ساخت')
                ->modalCancelActionLabel('انصراف')
                ->form([
                    TextInput::make('name')
                        ->label('نام پوشه')
                        ->required()
                        ->maxLength(100),
                ])
                ->action(function (array $data): void {
                    try {
                        $name = $this->service()->createFolder($this->disk, $this->path, (string) $data['name']);

                        Notification::make()
                            ->title('پوشه «'.$name.'» ساخته شد')
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        $this->notifyError($e->getMessage());
                    }
                }),

            Action::make('downloadFromUrl')
                ->label('دانلود فایل از URL')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('gray')
                ->modalHeading('دانلود فایل از URL')
                ->modalDescription('فایل در پوشه فعلی ذخیره می‌شود. حداکثر حجم: '.$this->service()->humanSize((int) config('media-manager.url_download.max_size_kb', 30720) * 1024))
                ->modalSubmitActionLabel('دانلود')
                ->modalCancelActionLabel('انصراف')
                ->form([
                    TextInput::make('url')
                        ->label('نشانی فایل')
                        ->placeholder('https://example.com/image.jpg')
                        ->url()
                        ->required()
                        ->extraInputAttributes(['dir' => 'ltr']),
                ])
                ->action(function (array $data): void {
                    try {
                        $name = $this->service()->downloadFromUrl($this->disk, $this->path, (string) $data['url']);

                        Notification::make()
                            ->title('فایل «'.$name.'» دانلود و ذخیره شد')
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('دانلود فایل ناموفق بود')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('harvestImages')
                ->label('دانلود تصاویر یک صفحه وب')
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->modalHeading('دانلود تصاویر یک صفحه وب')
                ->modalDescription('تصاویر صفحه (حداکثر '.(int) config('media-manager.harvest.max_images', 30).' تصویر) در یک زیرپوشه با نام دامنه و تاریخ ذخیره می‌شوند.')
                ->modalSubmitActionLabel('دریافت تصاویر')
                ->modalCancelActionLabel('انصراف')
                ->form([
                    TextInput::make('url')
                        ->label('نشانی صفحه وب')
                        ->placeholder('https://example.com/article')
                        ->url()
                        ->required()
                        ->extraInputAttributes(['dir' => 'ltr']),
                ])
                ->action(function (array $data): void {
                    try {
                        $result = $this->service()->harvestImages($this->disk, $this->path, (string) $data['url']);
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('دانلود تصاویر ناموفق بود')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $body = $result['found'].' تصویر در صفحه یافت شد و '.count($result['saved']).' تصویر در پوشه «'.$result['folder'].'» ذخیره شد.';

                    if ($result['failed'] !== []) {
                        $lines = [];

                        foreach (array_slice($result['failed'], 0, 10, true) as $failedUrl => $reason) {
                            $lines[] = $failedUrl.' — '.$reason;
                        }

                        $body .= "\n".count($result['failed']).' مورد ناموفق:'."\n".implode("\n", $lines);
                    }

                    Notification::make()
                        ->title('دانلود تصاویر صفحه وب')
                        ->body($body)
                        ->{$result['failed'] === [] ? 'success' : 'warning'}()
                        ->send();
                }),
        ];
    }

    public function previewFileAction(): Action
    {
        return Action::make('previewFile')
            ->label('پیش‌نمایش')
            ->iconButton()
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalHeading(fn (array $arguments): string => $this->safeInfo($arguments)['name'] ?? 'پیش‌نمایش')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('بستن')
            ->modalContent(function (array $arguments) {
                return view('filament.pages.media-manager-preview', [
                    'file' => $this->safeInfo($arguments),
                ]);
            });
    }

    public function renameFileAction(): Action
    {
        return Action::make('renameFile')
            ->label('تغییر نام')
            ->iconButton()
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->modalHeading('تغییر نام فایل')
            ->modalSubmitActionLabel('ذخیره')
            ->modalCancelActionLabel('انصراف')
            ->fillForm(fn (array $arguments): array => [
                'name' => basename((string) ($arguments['path'] ?? '')),
            ])
            ->form([
                TextInput::make('name')
                    ->label('نام جدید')
                    ->required()
                    ->maxLength(150),
            ])
            ->action(function (array $data, array $arguments): void {
                try {
                    $name = $this->service()->rename($this->disk, (string) ($arguments['path'] ?? ''), (string) $data['name']);

                    Notification::make()
                        ->title('نام فایل به «'.$name.'» تغییر کرد')
                        ->success()
                        ->send();
                } catch (Throwable $e) {
                    $this->notifyError($e->getMessage());
                }
            });
    }

    public function moveFileAction(): Action
    {
        return Action::make('moveFile')
            ->label('انتقال')
            ->iconButton()
            ->icon('heroicon-o-arrow-right-circle')
            ->color('gray')
            ->modalHeading('انتقال فایل به پوشه دیگر')
            ->modalSubmitActionLabel('انتقال')
            ->modalCancelActionLabel('انصراف')
            ->form([
                Select::make('target')
                    ->label('پوشه مقصد')
                    ->options($this->folderOptions())
                    ->searchable()
                    ->required(),
            ])
            ->action(function (array $data, array $arguments): void {
                $target = (string) $data['target'];
                $target = $target === '/' ? '' : $target;

                try {
                    $this->service()->move($this->disk, (string) ($arguments['path'] ?? ''), $target);

                    Notification::make()
                        ->title('فایل با موفقیت منتقل شد')
                        ->success()
                        ->send();
                } catch (Throwable $e) {
                    $this->notifyError($e->getMessage());
                }
            });
    }

    public function deleteFileAction(): Action
    {
        return Action::make('deleteFile')
            ->label('حذف')
            ->iconButton()
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('حذف فایل')
            ->modalDescription(fn (array $arguments): string => 'فایل «'.basename((string) ($arguments['path'] ?? '')).'» برای همیشه حذف می‌شود. این عملیات قابل بازگشت نیست.')
            ->modalSubmitActionLabel('حذف')
            ->modalCancelActionLabel('انصراف')
            ->action(function (array $arguments): void {
                try {
                    $this->service()->delete($this->disk, (string) ($arguments['path'] ?? ''));

                    Notification::make()
                        ->title('فایل حذف شد')
                        ->success()
                        ->send();
                } catch (Throwable $e) {
                    $this->notifyError($e->getMessage());
                }
            });
    }

    /**
     * @return array<string, string>
     */
    protected function folderOptions(): array
    {
        $options = ['/' => 'ریشه'];

        try {
            foreach ($this->service()->directories($this->disk) as $directory) {
                $options[$directory] = $directory;
            }
        } catch (RuntimeException) {
            // Keep root-only options if the disk is temporarily unavailable.
        }

        return $options;
    }

    /**
     * Re-derive file info server-side from the (untrusted) action arguments.
     *
     * @return array<string, mixed>
     */
    protected function safeInfo(array $arguments): array
    {
        try {
            return $this->service()->info($this->disk, (string) ($arguments['path'] ?? ''));
        } catch (Throwable) {
            return [
                'name' => 'فایل نامعتبر',
                'path' => '',
                'type' => 'other',
                'url' => '',
                'size_human' => '',
                'modified_jalali' => '',
                'extension' => '',
            ];
        }
    }

    protected function notifyError(string $message): void
    {
        Notification::make()
            ->title('خطا')
            ->body($message)
            ->danger()
            ->send();
    }
}
