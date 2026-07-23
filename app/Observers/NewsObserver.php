<?php

namespace App\Observers;

use App\Models\News;
use App\Models\NewsRevision;
use App\Services\SocialPublishService;
use Filament\Notifications\Notification;

class NewsObserver
{
    /**
     * Fields that should never be logged in the revision history.
     * (deleted_at is covered by the deleted/restored actions, is_published is
     * derived from status, visits changes on every page view, the *_sent_at
     * markers are stamped automatically by SocialPublishService.)
     */
    protected array $ignoredFields = [
        'created_at',
        'updated_at',
        'deleted_at',
        'is_published',
        'visits',
        'lang_id',
        'telegram_sent_at',
        'whatsapp_sent_at',
    ];

    /**
     * Body-sized fields: only a hash/length is stored, never the content.
     */
    protected array $hashedFields = [
        'body',
        'short_description',
    ];

    public function created(News $news): void
    {
        $this->record($news, 'created', [
            'title' => ['old' => null, 'new' => $news->title],
            'status' => ['old' => null, 'new' => $news->status],
        ]);

        if ($news->status === News::STATUS_PENDING_REVIEW) {
            $this->notifyApprovers($news);
        }

        if ($news->status === 'published') {
            $this->autoPublishToSocial($news);
        }
    }

    public function updated(News $news): void
    {
        $changes = [];

        foreach ($news->getChanges() as $field => $newValue) {
            if (in_array($field, $this->ignoredFields, true)) {
                continue;
            }

            $oldValue = $news->getRawOriginal($field);

            if (in_array($field, $this->hashedFields, true)) {
                $changes[$field] = [
                    'old' => $this->summarize($oldValue),
                    'new' => $this->summarize($newValue),
                ];
            } else {
                $changes[$field] = [
                    'old' => $this->normalize($oldValue),
                    'new' => $this->normalize($newValue),
                ];
            }
        }

        $oldStatus = $changes['status']['old'] ?? null;
        $newStatus = $changes['status']['new'] ?? null;

        // گردش کار تحریریه — attach the reject reason to the revision entry.
        if ($newStatus !== null && $news->workflowRejectReason !== null) {
            $changes['review_reason'] = ['old' => null, 'new' => $news->workflowRejectReason];
        }

        if ($changes !== []) {
            $this->record($news, $this->resolveAction($oldStatus, $newStatus), $changes);
        }

        // Workflow notifications (never block saving).
        if ($newStatus === News::STATUS_PENDING_REVIEW) {
            $this->notifyApprovers($news);
        } elseif ($oldStatus === News::STATUS_PENDING_REVIEW && $newStatus === 'draft') {
            $this->notifyAuthorOfRejection($news, $news->workflowRejectReason);
        }

        $news->workflowRejectReason = null;

        // ارسال خودکار به شبکه‌های اجتماعی on the transition to published.
        if ($newStatus === 'published' && $oldStatus !== 'published') {
            $this->autoPublishToSocial($news);
        }
    }

    public function deleted(News $news): void
    {
        if (method_exists($news, 'isForceDeleting') && $news->isForceDeleting()) {
            return;
        }

        $this->record($news, 'deleted');
    }

    public function restored(News $news): void
    {
        $this->record($news, 'restored');
    }

    /**
     * Map a status transition onto a workflow action for the audit trail.
     */
    protected function resolveAction(?string $oldStatus, ?string $newStatus): string
    {
        if ($newStatus === null) {
            return 'updated';
        }

        if ($newStatus === News::STATUS_PENDING_REVIEW) {
            return 'submitted_for_review';
        }

        if ($oldStatus === News::STATUS_PENDING_REVIEW && in_array($newStatus, ['published', 'scheduled'], true)) {
            return 'approved';
        }

        if ($oldStatus === News::STATUS_PENDING_REVIEW && $newStatus === 'draft') {
            return 'rejected';
        }

        return 'status_changed';
    }

    /**
     * Filament database notification to every user who can approve news.
     * Requires ->databaseNotifications() on the panel to be visible in the
     * UI; sending is fully guarded either way.
     */
    protected function notifyApprovers(News $news): void
    {
        try {
            $submitter = auth()->user()?->name ?? 'نامشخص';

            foreach (News::approvers() as $approver) {
                Notification::make()
                    ->title('خبر در انتظار تأیید')
                    ->body('خبر «' . $news->title . '» توسط ' . $submitter . ' برای تأیید ارسال شد.')
                    ->icon('heroicon-o-clock')
                    ->warning()
                    ->sendToDatabase($approver);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Notify the author (creator) that their news was sent back, with the
     * editor's reason.
     */
    protected function notifyAuthorOfRejection(News $news, ?string $reason): void
    {
        try {
            $author = $news->user;

            if (! $author) {
                return;
            }

            Notification::make()
                ->title('بازگشت خبر برای اصلاح')
                ->body(
                    'خبر «' . $news->title . '» برای اصلاح بازگردانده شد.'
                    . ($reason ? ' دلیل: ' . $reason : '')
                )
                ->icon('heroicon-o-arrow-uturn-right')
                ->danger()
                ->sendToDatabase($author);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Fire the enabled social sends once (duplicate-safe via the *_sent_at
     * columns). Never blocks saving.
     */
    protected function autoPublishToSocial(News $news): void
    {
        try {
            app(SocialPublishService::class)->autoPublish($news);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Store hash/length only for body-sized fields.
     */
    protected function summarize($value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;

        return [
            'length' => mb_strlen($value),
            'sha1' => sha1($value),
        ];
    }

    protected function normalize($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_scalar($value) || $value === null) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    protected function record(News $news, string $action, ?array $changes = null): void
    {
        try {
            NewsRevision::create([
                'news_id' => $news->id,
                'user_id' => auth()->id(),
                'action' => $action,
                'changed_fields' => $changes,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // The audit trail must never break the editorial flow
            // (e.g. before the news_revisions migration has run).
            report($e);
        }
    }
}
