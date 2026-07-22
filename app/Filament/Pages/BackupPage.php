<?php

namespace App\Filament\Pages;

use App\Services\BackupService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BackupPage extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?string $navigationLabel = 'پشتیبان‌گیری';

    protected static ?string $title = 'پشتیبان‌گیری';

    protected static ?string $slug = 'backups';

    protected static string $view = 'filament.pages.backup-page';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runBackup')
                ->label('اجرای پشتیبان‌گیری')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('اجرای پشتیبان‌گیری')
                ->modalDescription('پشتیبان‌گیری کامل (پایگاه داده و فایل‌ها) اکنون اجرا می‌شود. این عملیات ممکن است چند دقیقه طول بکشد.')
                ->modalSubmitActionLabel('اجرا')
                ->modalCancelActionLabel('انصراف')
                ->action(fn () => $this->runBackupNow()),
        ];
    }

    public function runBackupNow(): void
    {
        try {
            $exitCode = Artisan::call('app:backup');

            if ($exitCode === 0) {
                Notification::make()
                    ->title('پشتیبان‌گیری با موفقیت انجام شد')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('پشتیبان‌گیری با خطا مواجه شد')
                    ->body('برای جزئیات بیشتر لاگ سیستم را بررسی کنید.')
                    ->danger()
                    ->send();
            }
        } catch (Throwable $e) {
            Notification::make()
                ->title('پشتیبان‌گیری با خطا مواجه شد')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function download(string $type, string $name): ?BinaryFileResponse
    {
        $name = basename($name);
        $path = app(BackupService::class)->path($type).DIRECTORY_SEPARATOR.$name;

        if (! in_array($type, ['db', 'files'], true) || ! str_starts_with($name, 'backup-') || ! is_file($path)) {
            Notification::make()
                ->title('فایل پشتیبان یافت نشد')
                ->danger()
                ->send();

            return null;
        }

        return response()->download($path);
    }

    /**
     * @return array<int, array{type: string, name: string, path: string, size: int, size_for_humans: string, modified: \Illuminate\Support\Carbon}>
     */
    public function getBackups(): array
    {
        return app(BackupService::class)->listBackups();
    }
}
