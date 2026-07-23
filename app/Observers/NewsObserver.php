<?php

namespace App\Observers;

use App\Models\News;
use App\Models\NewsRevision;

class NewsObserver
{
    /**
     * Fields that should never be logged in the revision history.
     * (deleted_at is covered by the deleted/restored actions, is_published is
     * derived from status, visits changes on every page view.)
     */
    protected array $ignoredFields = [
        'created_at',
        'updated_at',
        'deleted_at',
        'is_published',
        'visits',
        'lang_id',
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

        if ($changes === []) {
            return;
        }

        $action = array_key_exists('status', $changes) ? 'status_changed' : 'updated';

        $this->record($news, $action, $changes);
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
