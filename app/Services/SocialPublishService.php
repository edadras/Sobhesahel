<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ارسال خبر به تلگرام و واتساپ.
 *
 * Fully guarded: both channels are disabled by default (config/social-publish.php),
 * every request has a short timeout and is wrapped in try/catch — a failed or
 * misconfigured send never blocks saving/publishing a news item.
 *
 * Duplicate protection: telegram_sent_at / whatsapp_sent_at columns on the
 * news table are stamped (quietly) after a successful send.
 */
class SocialPublishService
{
    /**
     * Automatic sends on transition to published — called by NewsObserver.
     * Only fires channels whose per-news toggle is on, that are enabled in
     * config, and that have not already been sent.
     */
    public function autoPublish(News $news): void
    {
        try {
            if ($news->auto_send_telegram && ! $news->telegram_sent_at) {
                $this->sendToTelegram($news);
            }

            if ($news->auto_send_whatsapp && ! $news->whatsapp_sent_at) {
                $this->sendToWhatsApp($news);
            }
        } catch (\Throwable $e) {
            Log::error('SocialPublish autoPublish failed for news ' . $news->id, ['exception' => $e]);
        }
    }

    /**
     * Manual send (row action). Attempts both channels and returns a
     * per-channel Persian result message for the UI.
     *
     * @return array<string, array{ok: bool, message: string}>
     */
    public function manualPublish(News $news): array
    {
        return [
            'telegram' => $this->sendToTelegram($news),
            'whatsapp' => $this->sendToWhatsApp($news),
        ];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function sendToTelegram(News $news): array
    {
        $config = config('social-publish.telegram', []);

        if (! ($config['enabled'] ?? false)) {
            return ['ok' => false, 'message' => 'ارسال به تلگرام غیرفعال است'];
        }

        if (blank($config['bot_token'] ?? null) || blank($config['channel_id'] ?? null)) {
            return ['ok' => false, 'message' => 'تنظیمات تلگرام کامل نیست'];
        }

        if ($news->telegram_sent_at) {
            return ['ok' => false, 'message' => 'قبلاً به تلگرام ارسال شده است'];
        }

        try {
            $message = $this->buildMessage($news);
            $imageUrl = $this->absoluteImageUrl($news);

            $base = rtrim($config['api_url'] ?? 'https://api.telegram.org', '/')
                . '/bot' . $config['bot_token'];

            if ($imageUrl) {
                // Telegram caption limit is 1024 characters.
                $response = $this->http()->post($base . '/sendPhoto', [
                    'chat_id' => $config['channel_id'],
                    'photo' => $imageUrl,
                    'caption' => Str::limit($message, 1000, '…'),
                ]);
            } else {
                $response = $this->http()->post($base . '/sendMessage', [
                    'chat_id' => $config['channel_id'],
                    'text' => Str::limit($message, 4000, '…'),
                ]);
            }

            if ($response->successful() && ($response->json('ok') === true)) {
                $this->markSent($news, 'telegram_sent_at');

                return ['ok' => true, 'message' => 'با موفقیت به تلگرام ارسال شد'];
            }

            Log::warning('SocialPublish telegram send failed for news ' . $news->id, [
                'status' => $response->status(),
                'body' => Str::limit((string) $response->body(), 500),
            ]);

            return ['ok' => false, 'message' => 'ارسال به تلگرام ناموفق بود'];
        } catch (\Throwable $e) {
            Log::error('SocialPublish telegram exception for news ' . $news->id, ['exception' => $e]);

            return ['ok' => false, 'message' => 'خطا در ارسال به تلگرام'];
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function sendToWhatsApp(News $news): array
    {
        $config = config('social-publish.whatsapp', []);

        if (! ($config['enabled'] ?? false)) {
            return ['ok' => false, 'message' => 'ارسال به واتساپ غیرفعال است'];
        }

        if (blank($config['endpoint'] ?? null)) {
            return ['ok' => false, 'message' => 'تنظیمات واتساپ کامل نیست'];
        }

        if ($news->whatsapp_sent_at) {
            return ['ok' => false, 'message' => 'قبلاً به واتساپ ارسال شده است'];
        }

        try {
            $message = $this->buildMessage($news);
            $imageUrl = $this->absoluteImageUrl($news);

            $request = $this->http();

            if (filled($config['token'] ?? null)) {
                $request = $request->withToken($config['token']);
            }

            if (filled($config['phone_id'] ?? null)) {
                // WhatsApp Cloud API shape: {endpoint}/{phone_id}/messages
                $url = rtrim($config['endpoint'], '/') . '/' . $config['phone_id'] . '/messages';

                $payload = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $config['to'] ?? '',
                    'type' => 'text',
                    'text' => [
                        'preview_url' => true,
                        'body' => Str::limit($message, 4000, '…'),
                    ],
                ];
            } else {
                // Generic webhook shape.
                $url = $config['endpoint'];

                $payload = [
                    'news_id' => $news->id,
                    'title' => $news->title,
                    'message' => Str::limit($message, 4000, '…'),
                    'image' => $imageUrl,
                    'link' => $this->shortLink($news),
                ];
            }

            $response = $request->post($url, $payload);

            if ($response->successful()) {
                $this->markSent($news, 'whatsapp_sent_at');

                return ['ok' => true, 'message' => 'با موفقیت به واتساپ ارسال شد'];
            }

            Log::warning('SocialPublish whatsapp send failed for news ' . $news->id, [
                'status' => $response->status(),
                'body' => Str::limit((string) $response->body(), 500),
            ]);

            return ['ok' => false, 'message' => 'ارسال به واتساپ ناموفق بود'];
        } catch (\Throwable $e) {
            Log::error('SocialPublish whatsapp exception for news ' . $news->id, ['exception' => $e]);

            return ['ok' => false, 'message' => 'خطا در ارسال به واتساپ'];
        }
    }

    /**
     * Fill the configured template with title / lead / short link.
     */
    protected function buildMessage(News $news): string
    {
        $lead = trim(strip_tags((string) $news->short_description));

        return strtr((string) config('social-publish.template', "{title}\n\n{lead}\n\n{link}"), [
            '{title}' => (string) $news->title,
            '{lead}' => Str::limit($lead, 500, '…'),
            '{link}' => $this->shortLink($news),
        ]);
    }

    protected function shortLink(News $news): string
    {
        try {
            return (string) $news->getShortUrl();
        } catch (\Throwable) {
            try {
                return (string) $news->getUrl();
            } catch (\Throwable) {
                return '';
            }
        }
    }

    /**
     * Absolute URL of the main news image (Telegram requires a URL that is
     * reachable from outside), or null when the news has no image.
     */
    protected function absoluteImageUrl(News $news): ?string
    {
        try {
            $url = $news->getFileUrl($news->image_original);

            if (blank($url)) {
                return null;
            }

            return Str::startsWith($url, ['http://', 'https://']) ? $url : url($url);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        $timeout = max(1, (int) config('social-publish.timeout', 5));

        return Http::timeout($timeout)->connectTimeout($timeout);
    }

    /**
     * Stamp the sent marker without firing model events (avoids observer
     * loops and extra revision entries).
     */
    protected function markSent(News $news, string $column): void
    {
        try {
            $timestamp = now();

            News::withTrashed()
                ->whereKey($news->getKey())
                ->update([$column => $timestamp]);

            $news->{$column} = $timestamp;
            $news->syncOriginalAttribute($column);
        } catch (\Throwable $e) {
            Log::error('SocialPublish could not mark ' . $column . ' for news ' . $news->id, ['exception' => $e]);
        }
    }
}
