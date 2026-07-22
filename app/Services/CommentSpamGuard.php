<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Lightweight anti-spam checks for publicly submitted comments.
 *
 * Verdicts:
 *  - OK       : the comment is clean and may be stored as "pending".
 *  - DROP     : bot behaviour (honeypot filled) — silently discard.
 *  - THROTTLE : too fast / too many submissions — show a Persian error, do not store.
 *  - REJECT   : content looks like spam — store with status "rejected" and a reason.
 */
class CommentSpamGuard
{
    public const OK = 'ok';
    public const DROP = 'drop';
    public const THROTTLE = 'throttle';
    public const REJECT = 'reject';

    /**
     * Inspect a comment submission.
     *
     * @param  string       $comment    The comment text.
     * @param  string|null  $honeypot   Value of the hidden honeypot field.
     * @param  int|null     $renderedAt Unix timestamp of when the form was rendered.
     * @param  string|null  $ip         Client IP address.
     * @return array{verdict: string, reason: ?string, message: ?string}
     */
    public function inspect(string $comment, ?string $honeypot, ?int $renderedAt, ?string $ip): array
    {
        // 1) Honeypot: humans never fill this hidden field.
        if (filled($honeypot)) {
            return $this->verdict(self::DROP, 'honeypot');
        }

        // 2) Submitted too fast after the form was rendered (bot behaviour).
        if ($this->isTooFast($renderedAt)) {
            return $this->verdict(
                self::THROTTLE,
                'too_fast',
                'ارسال دیدگاه خیلی سریع انجام شد. لطفاً چند لحظه صبر کنید و دوباره تلاش کنید.'
            );
        }

        // 3) Per-IP rate limit.
        if ($ip && $this->isRateLimited($ip)) {
            return $this->verdict(
                self::THROTTLE,
                'rate_limited',
                'شما در مدت کوتاهی چندین دیدگاه ارسال کرده‌اید. لطفاً کمی بعد دوباره تلاش کنید.'
            );
        }

        // 4) Blocked words.
        if ($word = $this->findBlockedWord($comment)) {
            return $this->verdict(self::REJECT, 'کلمه غیرمجاز: ' . $word);
        }

        // 5) Too many links.
        $links = $this->countLinks($comment);
        if ($links > $this->maxLinks()) {
            return $this->verdict(self::REJECT, 'تعداد لینک بیش از حد مجاز (' . $links . ' لینک)');
        }

        return $this->verdict(self::OK);
    }

    /**
     * Record a submission attempt for the given IP (counts toward the rate limit).
     */
    public function registerAttempt(?string $ip): void
    {
        if ($ip) {
            RateLimiter::hit($this->rateLimiterKey($ip), $this->windowMinutes() * 60);
        }
    }

    public function isTooFast(?int $renderedAt): bool
    {
        $minSeconds = (int) config('comments.min_seconds', 5);

        if ($minSeconds <= 0) {
            return false;
        }

        // Missing or bogus timestamp means the form state was tampered with.
        if (! $renderedAt || $renderedAt > time()) {
            return true;
        }

        return (time() - $renderedAt) < $minSeconds;
    }

    public function isRateLimited(string $ip): bool
    {
        $max = (int) config('comments.rate_limit.max_per_window', 5);

        return RateLimiter::tooManyAttempts($this->rateLimiterKey($ip), $max);
    }

    public function findBlockedWord(string $text): ?string
    {
        $words = (array) config('comments.blocked_words', []);

        foreach ($words as $word) {
            if ($word !== '' && mb_stripos($text, $word) !== false) {
                return $word;
            }
        }

        return null;
    }

    public function countLinks(string $text): int
    {
        return preg_match_all('~(https?://|www\.)\S+~iu', $text);
    }

    protected function maxLinks(): int
    {
        return (int) config('comments.max_links', 2);
    }

    protected function windowMinutes(): int
    {
        return max(1, (int) config('comments.rate_limit.window_minutes', 10));
    }

    protected function rateLimiterKey(string $ip): string
    {
        return 'comments:' . $ip;
    }

    /**
     * @return array{verdict: string, reason: ?string, message: ?string}
     */
    protected function verdict(string $verdict, ?string $reason = null, ?string $message = null): array
    {
        return [
            'verdict' => $verdict,
            'reason' => $reason,
            'message' => $message,
        ];
    }
}
