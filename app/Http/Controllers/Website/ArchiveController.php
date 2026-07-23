<?php

namespace App\Http\Controllers\Website;

use App\Constant\AppConstant;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Archive;
use App\Models\News;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    use \App\Http\Controllers\Website\Concerns\RendersEnglishSite;

    public function index()
    {
        $posts = Archive::getLatest()->resolve();

        $website_title = 'آرشیو روزنامه | ' . (setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل');

        $seo = [
            'title' => 'آرشیو روزنامه',
            'description' => 'آرشیو نسخه‌های PDF روزنامه صبح ساحل',
            'type' => 'website',
            'url' => route('website.rtl.archive'),
        ];

        return view('website.rtl.archive', compact('posts', 'website_title', 'seo'));
    }

    /**
     * English newspaper archive (en/pdf) — mirrors index() with English
     * lang-filtered archives and the LTR archive view (WP-15).
     */
    public function en_index()
    {
        try {
            $posts = \App\Http\Resources\ContentMetaDataResource::collection(
                Archive::where('lang_id', $this->englishLangId())
                    ->where('is_published', true)
                    ->orderBy('id', 'DESC')
                    ->take(12)
                    ->get()
            )->resolve();

            $posts = $this->normalizeLtrItems($posts);

            $website_title = 'Newspaper Archive | ' . $this->enBrandName();

            $seo = [
                'title' => 'Newspaper Archive',
                'description' => 'PDF archive of the Sobhe Sahel newspaper',
                'type' => 'website',
                'url' => url()->current(),
            ];

            return $this->ltrView('website.ltr.archive', compact('posts', 'website_title', 'seo'), 'Newspaper Archive');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return $this->comingSoon('Newspaper Archive');
        }
    }

    public function get_data(Request $request)
    {
        $posts = Post::orderBy('id', 'DESC')
            ->where('lang_id', $request->get('lang_id'))->where('post_type', 'archive');

        if ($request->has('archive') && $request->get('archive') != 'all') {
            $posts->whereHas('PostData', function ($query) use ($request) {
                $query->where('title', 'category')->where('value', $request->get('archive'));
            });
        }

        if ($request->has('number') && $request->get('number') != '') {
            $posts->whereHas('PostData', function ($query) use ($request) {
                $query->where('title', 'number')->where('value', $request->get('number'));
            });
        }

        $posts = $posts->paginate(12)
            ->through(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            });

        return ResponseHelper::basic_response(true, [
            'archives' => $posts,
            'list' => $request->get('lang_id') == 1 ? AppConstant::archives_type() : AppConstant::en_archives_type()
        ]);
    }

    public function pdf($archive, $number)
    {
        $post = Archive::with(['news' => fn ($q) => $q->where('is_published', true)])
            ->whereHas('archive_category', function ($q) use ($archive) {
                $q->where('slug', $archive);
            })->where('archive_number',$number)->firstOrFail();

        // اشتراک دیجیتال نشریات: when enabled in config/payments.php, the PDF
        // is only served to visitors holding an active subscription access
        // code (stored in session). Default (gate_archive=false) keeps the
        // archive free, exactly as before.
        if (config('payments.gate_archive', false)) {
            $active = \App\Models\Subscription::findActiveByCode(session('subscription_access_code'));

            if ($active === null) {
                session()->forget('subscription_access_code');

                return view('website.rtl.archive_access', [
                    'post' => $post,
                    'website_title' => 'نسخه دیجیتال نشریات | ' . (setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل'),
                ]);
            }
        }

        $url = Storage::url($post->archive_file);

        return view('website.pages.pdf',compact('url','post'));
    }
}
