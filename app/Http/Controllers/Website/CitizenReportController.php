<?php

namespace App\Http\Controllers\Website;

use App\Helpers\ModirSmsHelper;
use App\Http\Controllers\Controller;
use App\Models\CitizenReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * شهروند خبرنگار — public submission form for citizen-journalist reports.
 */
class CitizenReportController extends Controller
{
    public function index()
    {
        abort_unless((bool) config('citizen-report.enabled', true), 404);

        $brand = setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل';

        $website_title = 'شهروند خبرنگار | ' . $brand;

        $seo = [
            'title' => 'شهروند خبرنگار',
            'description' => 'سوژه‌ها و گزارش‌های خبری خود را همراه با عکس و فیلم برای تحریریه گروه رسانه‌ای صبح ساحل ارسال کنید',
            'type' => 'website',
            'url' => route('website.rtl.citizen_report'),
        ];

        return view('website.rtl.citizen-report', compact('website_title', 'seo'));
    }

    public function store(Request $request)
    {
        abort_unless((bool) config('citizen-report.enabled', true), 404);

        // Honeypot: real users never fill this hidden field — silently discard.
        if (filled($request->get('website'))) {
            return redirect()->route('website.rtl.citizen_report');
        }

        // Per-IP rate limit (default 3 reports per hour).
        $ip = $request->ip();
        $limiterKey = 'citizen-report:' . $ip;
        $maxPerWindow = (int) config('citizen-report.rate_limit.max_per_window', 3);
        $windowMinutes = max(1, (int) config('citizen-report.rate_limit.window_minutes', 60));

        if ($ip && RateLimiter::tooManyAttempts($limiterKey, $maxPerWindow)) {
            return redirect()
                ->route('website.rtl.citizen_report')
                ->withInput($request->except(['photos', 'video']))
                ->withErrors(['rate_limit' => 'شما در یک ساعت گذشته چند گزارش ارسال کرده‌اید. لطفاً کمی بعد دوباره تلاش کنید.']);
        }

        $photoMaxKb = (int) config('citizen-report.files.photo_max_kb', 4096);
        $videoMaxKb = (int) config('citizen-report.files.video_max_kb', 30720);
        $maxPhotos = (int) config('citizen-report.files.max_photos', 4);
        $maxFiles = (int) config('citizen-report.files.max_files', 4);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'email' => 'nullable|email|max:190',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'location' => 'nullable|string|max:255',
            'photos' => 'nullable|array|max:' . $maxPhotos,
            'photos.*' => 'file|mimes:jpg,jpeg,png|max:' . $photoMaxKb,
            'video' => 'nullable|file|mimes:mp4|max:' . $videoMaxKb,
        ], [
            'name.required' => 'وارد کردن نام الزامی است',
            'mobile.required' => 'وارد کردن شماره موبایل الزامی است',
            'mobile.regex' => 'شماره موبایل باید به صورت ۱۱ رقمی و با ۰۹ شروع شود',
            'email.email' => 'ایمیل وارد شده معتبر نیست',
            'title.required' => 'وارد کردن عنوان گزارش الزامی است',
            'body.required' => 'وارد کردن متن گزارش الزامی است',
            'body.max' => 'متن گزارش طولانی‌تر از حد مجاز است',
            'photos.max' => 'حداکثر ' . $maxPhotos . ' تصویر می‌توانید ارسال کنید',
            'photos.*.file' => 'فایل تصویر معتبر نیست',
            'photos.*.mimes' => 'فرمت تصاویر باید JPG یا PNG باشد',
            'photos.*.max' => 'حجم هر تصویر حداکثر ۴ مگابایت است',
            'video.file' => 'فایل ویدیو معتبر نیست',
            'video.mimes' => 'فرمت ویدیو باید MP4 باشد',
            'video.max' => 'حجم ویدیو حداکثر ۳۰ مگابایت است',
        ]);

        // Cap the total number of attachments (photos + video).
        $photoCount = count($request->file('photos', []));
        $videoCount = $request->hasFile('video') ? 1 : 0;

        if (($photoCount + $videoCount) > $maxFiles) {
            return redirect()
                ->back()
                ->withInput($request->except(['photos', 'video']))
                ->withErrors(['photos' => 'در مجموع حداکثر ' . $maxFiles . ' فایل (عکس و ویدیو) می‌توانید ارسال کنید']);
        }

        $directory = trim((string) config('citizen-report.files.directory', 'citizen-reports'), '/');

        $attachments = [];

        foreach ($request->file('photos', []) as $photo) {
            $attachments[] = [
                'path' => $photo->store($directory, CitizenReport::ATTACHMENT_DISK),
                'type' => 'image',
                'name' => $photo->getClientOriginalName(),
                'size' => $photo->getSize(),
            ];
        }

        if ($request->hasFile('video')) {
            $video = $request->file('video');

            $attachments[] = [
                'path' => $video->store($directory, CitizenReport::ATTACHMENT_DISK),
                'type' => 'video',
                'name' => $video->getClientOriginalName(),
                'size' => $video->getSize(),
            ];
        }

        $report = CitizenReport::create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'] ?? null,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'location' => $validated['location'] ?? null,
            'attachments' => $attachments ?: null,
            'status' => CitizenReport::STATUS_NEW,
            'ip' => $ip,
        ]);

        RateLimiter::hit($limiterKey, $windowMinutes * 60);

        $this->sendAcknowledgmentSms($report);

        return redirect()
            ->route('website.rtl.citizen_report')
            ->with('citizen_report_success', 'گزارش شما با موفقیت ثبت شد و پس از بررسی تحریریه، در صورت تأیید منتشر خواهد شد. از همراهی شما سپاسگزاریم.');
    }

    /**
     * Optional SMS acknowledgment. ModirSmsHelper only supports pattern-based
     * sending, so this is a no-op unless a pattern id is configured and the
     * toggle is on. Never allowed to break the submission flow.
     */
    protected function sendAcknowledgmentSms(CitizenReport $report): void
    {
        if (! config('citizen-report.sms_enabled', false)) {
            return;
        }

        $patternId = (string) config('citizen-report.sms_pattern_id', '');

        if ($patternId === '') {
            return;
        }

        try {
            ModirSmsHelper::send_pattern($patternId, $report->mobile, [
                'name' => $report->name,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
