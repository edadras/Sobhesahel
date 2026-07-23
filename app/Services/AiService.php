<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * هوش مصنوعی — thin client for any OpenAI-compatible chat completions API.
 *
 * Fully inert unless config('ai.enabled') is true AND base_url/api_key are
 * set (see config/ai.php). Every request is wrapped in try/catch with a
 * bounded timeout and never throws: failures log a warning and return
 * null / [] so the editorial flow is never blocked by the AI layer.
 *
 * All helper prompts demand strict JSON output; responses are validated
 * with json_decode (code fences stripped, embedded JSON extracted) and fall
 * back to empty results when the model misbehaves.
 */
class AiService
{
    /**
     * Is the AI integration configured and switched on?
     */
    public function enabled(): bool
    {
        return (bool) config('ai.enabled')
            && filled(config('ai.base_url'))
            && filled(config('ai.api_key'));
    }

    /**
     * Core chat completion call. Returns the assistant message content, or
     * null on any failure (disabled, HTTP error, timeout, malformed body).
     */
    public function chat(string $system, string $user, int $maxTokens = 512): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        try {
            $response = Http::withToken((string) config('ai.api_key'))
                ->timeout((int) config('ai.timeout', 20))
                ->acceptJson()
                ->post(rtrim((string) config('ai.base_url'), '/') . '/chat/completions', [
                    'model' => (string) config('ai.model'),
                    'max_tokens' => $maxTokens,
                    'temperature' => 0.7,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('AiService: completion request failed', [
                    'status' => $response->status(),
                    'body' => mb_substr((string) $response->body(), 0, 500),
                ]);

                return null;
            }

            $content = $response->json('choices.0.message.content');

            return (is_string($content) && trim($content) !== '') ? trim($content) : null;
        } catch (\Throwable $e) {
            Log::warning('AiService: completion request exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * پیشنهاد برچسب — Persian tag suggestions for a news item.
     *
     * @return array<int, string> up to 8 trimmed, unique Persian tags
     */
    public function suggestTags(string $title, string $body): array
    {
        $system = 'تو دستیار سردبیر یک پایگاه خبری فارسی هستی. برای خبری که کاربر می‌فرستد حداکثر ۸ برچسب (تگ) کوتاه و دقیق فارسی پیشنهاد بده؛ '
            . 'نام اشخاص، مکان‌ها و موضوع‌های اصلی خبر را در اولویت بگذار. '
            . 'خروجی فقط و فقط یک آرایه JSON از رشته‌ها باشد، بدون هیچ توضیح یا متن اضافه. نمونه: ["هرمزگان","بندرعباس"]';

        $decoded = $this->decodeJson(
            $this->chat($system, $this->buildNewsPrompt($title, $body), 256)
        );

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
            ->map(fn ($tag) => trim($tag))
            ->unique()
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * پیشنهاد سئو — SEO title and meta description within length limits.
     *
     * @return array{seo_title?: string, meta_desc?: string} empty array on failure
     */
    public function suggestSeo(string $title, string $body): array
    {
        $system = 'تو کارشناس سئوی یک پایگاه خبری فارسی هستی. برای خبری که کاربر می‌فرستد یک عنوان سئو (حداکثر ۶۰ نویسه) '
            . 'و یک توضیح متا (حداکثر ۱۶۰ نویسه) به فارسی بنویس. '
            . 'خروجی فقط و فقط یک شیء JSON با همین دو کلید باشد، بدون هیچ توضیح اضافه: '
            . '{"seo_title":"...","meta_desc":"..."}';

        $decoded = $this->decodeJson(
            $this->chat($system, $this->buildNewsPrompt($title, $body), 300)
        );

        if (! is_array($decoded)) {
            return [];
        }

        $seoTitle = is_string($decoded['seo_title'] ?? null) ? trim($decoded['seo_title']) : '';
        $metaDesc = is_string($decoded['meta_desc'] ?? null) ? trim($decoded['meta_desc']) : '';

        if ($seoTitle === '' && $metaDesc === '') {
            return [];
        }

        return array_filter([
            'seo_title' => mb_substr($seoTitle, 0, 60),
            'meta_desc' => mb_substr($metaDesc, 0, 160),
        ], fn ($value) => $value !== '');
    }

    /**
     * پیشنهاد تیتر — 3 alternative Persian headlines.
     *
     * @return array<int, string> up to 3 headlines, empty array on failure
     */
    public function suggestHeadlines(string $title, string $body): array
    {
        $system = 'تو دبیر تحریریه یک پایگاه خبری فارسی هستی. برای خبری که کاربر می‌فرستد دقیقاً ۳ تیتر جایگزین جذاب، دقیق و خبری به فارسی پیشنهاد بده. '
            . 'تیترها کوتاه (حداکثر ۷۰ نویسه) و بدون اغراق باشند. '
            . 'خروجی فقط و فقط یک آرایه JSON از ۳ رشته باشد، بدون هیچ توضیح اضافه. نمونه: ["تیتر یک","تیتر دو","تیتر سه"]';

        $decoded = $this->decodeJson(
            $this->chat($system, $this->buildNewsPrompt($title, $body), 300)
        );

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->filter(fn ($headline) => is_string($headline) && trim($headline) !== '')
            ->map(fn ($headline) => trim($headline))
            ->unique()
            ->take(3)
            ->values()
            ->all();
    }

    /**
     * تولید متن تبلیغاتی — Persian ad/campaign copy from a brief.
     */
    public function generateAdCopy(string $brief): ?string
    {
        $system = 'تو کپی‌رایتر حرفه‌ای فارسی‌زبان یک گروه رسانه‌ای هستی. بر اساس بریفی که کاربر می‌فرستد یک متن تبلیغاتی روان، '
            . 'جذاب و رسمی به فارسی بنویس (حداکثر ۱۲۰ کلمه) که برای انتشار در سایت خبری و شبکه‌های اجتماعی مناسب باشد. '
            . 'فقط متن نهایی را برگردان، بدون عنوان، توضیح یا قالب‌بندی اضافه.';

        return $this->chat($system, mb_substr(trim($brief), 0, 2000), 600);
    }

    /**
     * خلاصه‌سازی — short Persian summary of pasted text.
     */
    public function summarize(string $text): ?string
    {
        $system = 'تو دستیار تحریریه یک پایگاه خبری فارسی هستی. متن ارسالی کاربر را در حداکثر ۳ جمله روان فارسی خلاصه کن. '
            . 'فقط متن خلاصه را برگردان، بدون عنوان یا توضیح اضافه.';

        return $this->chat($system, $this->cleanText($text), 400);
    }

    /**
     * Build the user prompt for the news helpers, bounded in size.
     */
    protected function buildNewsPrompt(string $title, string $body): string
    {
        return 'تیتر خبر: ' . trim($title) . "\n\nمتن خبر:\n" . $this->cleanText($body);
    }

    /**
     * Strip tags and bound the text so prompts stay reasonably small.
     */
    protected function cleanText(string $text, int $limit = 4000): string
    {
        return mb_substr(trim(strip_tags($text)), 0, $limit);
    }

    /**
     * Tolerant JSON decoding: strips markdown code fences and, as a
     * fallback, extracts the first JSON object/array embedded in the text.
     */
    protected function decodeJson(?string $content)
    {
        if ($content === null || trim($content) === '') {
            return null;
        }

        $content = trim(preg_replace('/^```(?:json)?\s*|\s*```$/u', '', trim($content)));

        $decoded = json_decode($content, true);

        if ($decoded !== null) {
            return $decoded;
        }

        if (preg_match('/[\[{].*[\]}]/su', $content, $matches)) {
            return json_decode($matches[0], true);
        }

        return null;
    }
}
