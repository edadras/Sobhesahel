<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;

class LiveController extends Controller
{
    /**
     * Public "پخش زنده" page: live-now streams first, then upcoming ones.
     */
    public function index()
    {
        $streams = LiveStream::activeStreams();

        $live_streams = $streams->filter(fn ($stream) => $stream->is_live)->values();
        $upcoming_streams = $streams->reject(fn ($stream) => $stream->is_live)->values();

        $page_title = 'پخش زنده';
        $website_title = $page_title . ' | ' . (setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل');

        $seo = [
            'title' => $page_title,
            'description' => 'پخش زنده صبح ساحل؛ تماشای آنلاین رویدادها، نشست‌های خبری و برنامه‌های زنده هرمزگان و ایران.',
            'type' => 'website',
            'url' => url()->current(),
        ];

        return view('website.rtl.live', compact('live_streams', 'upcoming_streams', 'page_title', 'website_title', 'seo'));
    }
}
