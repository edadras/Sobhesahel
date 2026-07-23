<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\CommentResource;
use App\Models\Comment;
use App\Services\CommentSpamGuard;
use App\Support\ApiContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * GET  /content/{type}/{code}/comments — approved (verified) comments.
 * POST /content/{type}/{code}/comment  — submit a comment (guest or member).
 *
 * Comments exist only for news and notes (the comments table has news_id /
 * note_id columns); other content types return an empty list / a 422.
 */
class CommentController extends ApiController
{
    public function index(string $type, string $code)
    {
        $column = $this->commentColumn($type);

        if ($column === null || ! Schema::hasTable('comments')) {
            return response()->json(['data' => []]);
        }

        $this->assertContentExists($type, $code);

        $comments = Comment::query()
            ->where($column, (int) $code)
            ->where('status', 'verified')
            ->whereNull('reply_to')
            ->with(['replies' => fn ($q) => $q->where('status', 'verified')])
            ->orderBy('id', 'DESC')
            ->get();

        return CommentResource::collection($comments);
    }

    public function store(Request $request, string $type, string $code, CommentSpamGuard $spamGuard)
    {
        if (! config('comments.enabled', true)) {
            return response()->json(['message' => 'ثبت دیدگاه در حال حاضر غیرفعال است.'], 403);
        }

        $column = $this->commentColumn($type);

        if ($column === null) {
            return response()->json(['message' => 'ثبت دیدگاه برای این نوع محتوا امکان‌پذیر نیست.'], 422);
        }

        $model = $this->assertContentExists($type, $code);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'email' => 'nullable|email|max:255',
        ], [
            'name.required' => 'لطفاً نام خود را وارد کنید.',
            'body.required' => 'لطفاً متن دیدگاه را وارد کنید.',
            'body.max' => 'متن دیدگاه نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'email.email' => 'ایمیل وارد شده معتبر نیست.',
        ]);

        $ip = $request->ip();

        // Per-IP rate limit (reuses the site's spam guard). The honeypot/
        // too-fast timing checks are form-specific and skipped for the native
        // client; content checks (blocked words, links) still apply below.
        if ($ip && $spamGuard->isRateLimited($ip)) {
            return response()->json([
                'message' => 'شما در مدت کوتاهی چندین دیدگاه ارسال کرده‌اید. لطفاً کمی بعد دوباره تلاش کنید.',
            ], 429);
        }

        $reason = $spamGuard->findBlockedWord($validated['body']);

        if ($reason === null && $spamGuard->countLinks($validated['body']) > (int) config('comments.max_links', 2)) {
            $reason = 'تعداد لینک بیش از حد مجاز';
        }

        $isSpam = $reason !== null;

        $comment = Comment::create([
            $column => (int) $code,
            'user_id' => optional($request->user())->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'comment' => $validated['body'],
            'status' => $isSpam ? 'rejected' : (string) config('comments.default_status', 'pending'),
            'spam_reason' => $isSpam ? $reason : null,
            'ip' => $ip,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
            'lang_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $spamGuard->registerAttempt($ip);

        return response()->json([
            'data' => (new CommentResource($comment))->toArray($request),
            'message' => 'دیدگاه شما با موفقیت ثبت شد و پس از تأیید نمایش داده خواهد شد.',
        ], 201);
    }

    /**
     * The comments column for a content type, or null when unsupported.
     */
    private function commentColumn(string $type): ?string
    {
        return match ($type) {
            'news' => 'news_id',
            'note' => 'note_id',
            default => null,
        };
    }

    /**
     * Ensure the target content exists and is published (else 404).
     */
    private function assertContentExists(string $type, string $code)
    {
        $modelClass = ApiContent::modelClass($type);

        if ($modelClass === null) {
            abort(404, 'محتوا یافت نشد.');
        }

        $model = $modelClass::query()->find($code);

        if ($model === null || ! $model->is_published) {
            abort(404, 'محتوا یافت نشد.');
        }

        return $model;
    }
}
