<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * شهروند خبرنگار — a news report submitted by a member of the public
 * (text + optional photos/video), reviewed by the editorial team and
 * optionally converted into a draft News record.
 */
class CitizenReport extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_NEW => 'جدید',
        self::STATUS_REVIEWING => 'در حال بررسی',
        self::STATUS_ACCEPTED => 'تبدیل‌شده به خبر',
        self::STATUS_REJECTED => 'ردشده',
    ];

    /** Disk that attachment paths are stored on. */
    public const ATTACHMENT_DISK = 'public';

    protected $guarded = ['id'];

    protected $casts = [
        'attachments' => 'array',
    ];

    protected static function booted(): void
    {
        // Deleting a report removes its uploaded files. Guarded: a storage
        // failure must never block deleting the database record.
        static::deleted(function (CitizenReport $report) {
            foreach ((array) $report->attachments as $attachment) {
                $path = is_array($attachment) ? ($attachment['path'] ?? null) : $attachment;

                if (! $path) {
                    continue;
                }

                try {
                    Storage::disk(self::ATTACHMENT_DISK)->delete($path);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function convertedNews()
    {
        return $this->belongsTo(News::class, 'converted_news_id');
    }

    /**
     * Attachments of a given type ("image" or "video") as [path, name] arrays.
     */
    public function attachmentsOfType(string $type): array
    {
        return array_values(array_filter(
            (array) $this->attachments,
            fn ($attachment) => is_array($attachment) && ($attachment['type'] ?? null) === $type
        ));
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? (string) $this->status;
    }
}
