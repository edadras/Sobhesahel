<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsRevision extends Model
{
    /**
     * Revisions are immutable log entries — only created_at is kept.
     */
    const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'changed_fields' => 'array',
    ];

    public const ACTION_LABELS = [
        'created' => 'ایجاد خبر',
        'updated' => 'ویرایش خبر',
        'status_changed' => 'تغییر وضعیت',
        'deleted' => 'حذف خبر',
        'restored' => 'بازیابی خبر',
        'submitted_for_review' => 'ارسال برای تأیید',
        'approved' => 'تأیید و انتشار',
        'rejected' => 'بازگشت برای اصلاح',
    ];

    public const FIELD_LABELS = [
        'title' => 'تیتر',
        'sub_title' => 'روتیتر',
        'slug' => 'نامک',
        'body' => 'متن خبر',
        'short_description' => 'متن کوتاه',
        'subtitles' => 'سوتیترها',
        'status' => 'وضعیت انتشار',
        'publish_at' => 'تاریخ انتشار',
        'author_id' => 'نویسنده',
        'user_id' => 'کاربر',
        'image_original' => 'تصویر خبر',
        'image_second' => 'تصویر مکمل ۱',
        'image_third' => 'تصویر مکمل ۲',
        'media_file' => 'فایل صوتی/تصویری',
        'pre_message' => 'پیام ابتدای خبر',
        'post_message' => 'پیام انتهای خبر',
        'title_color' => 'رنگ تیتر',
        'show_visits' => 'نمایش بازدید',
        'show_comments' => 'نمایش دیدگاه‌ها',
        'seo_title' => 'عنوان سئو',
        'meta_desc' => 'کلمات کلیدی',
        'review_reason' => 'دلیل بازگشت برای اصلاح',
        'auto_send_telegram' => 'ارسال خودکار به تلگرام',
        'auto_send_whatsapp' => 'ارسال خودکار به واتساپ',
    ];

    public const STATUS_LABELS = [
        'draft' => 'پیش‌نویس',
        'pending_review' => 'در انتظار تأیید',
        'scheduled' => 'زمان‌بندی‌شده',
        'published' => 'منتشرشده',
        'suspended' => 'معلق',
    ];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }

    /**
     * Human readable (Persian) summary of the changed fields.
     */
    public function getChangedFieldsSummaryAttribute(): array
    {
        $summary = [];

        foreach ((array) $this->changed_fields as $field => $change) {
            $label = self::FIELD_LABELS[$field] ?? $field;

            $old = $this->formatValue($field, $change['old'] ?? null);
            $new = $this->formatValue($field, $change['new'] ?? null);

            $summary[] = [
                'label' => $label,
                'old' => $old,
                'new' => $new,
            ];
        }

        return $summary;
    }

    protected function formatValue(string $field, $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        // Body-sized fields are stored as ['length' => ..., 'sha1' => ...].
        if (is_array($value)) {
            if (array_key_exists('length', $value)) {
                return 'محتوا (' . $value['length'] . ' نویسه)';
            }

            return (string) json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if ($field === 'status') {
            return self::STATUS_LABELS[$value] ?? (string) $value;
        }

        if (is_bool($value)) {
            return $value ? 'فعال' : 'غیرفعال';
        }

        $value = (string) $value;

        return mb_strlen($value) > 80 ? mb_substr($value, 0, 80) . '…' : $value;
    }
}
