<?php

namespace App\Http\Controllers\Website;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use \App\Http\Controllers\Website\Concerns\RendersEnglishSite;

    public function index()
    {
        $website_title = 'تماس با ما | ' . (setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل');

        $seo = [
            'title' => 'تماس با ما',
            'description' => 'راه‌های ارتباط با تحریریه و دفتر گروه رسانه‌ای صبح ساحل',
            'type' => 'website',
            'url' => route('website.rtl.contact'),
        ];

        return view('website.rtl.contact', compact('website_title', 'seo'));
    }

    /**
     * English contact page (en/contact-us) — mirrors index() (WP-15).
     */
    public function en_index()
    {
        $website_title = 'Contact Us | ' . $this->enBrandName();

        $seo = [
            'title' => 'Contact Us',
            'description' => 'How to reach the Sobhe Sahel Media Group newsroom and office',
            'type' => 'website',
            'url' => url()->current(),
        ];

        return $this->ltrView('website.ltr.contact', compact('website_title', 'seo'), 'Contact Us');
    }

    public function message(Request $request)
    {
        $lang_id = ($request->has('lang') && $request->get('lang') == 'en') ? 2 : 1;

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'text' => 'required',
        ]);

        $contact = Contact::create(
            array_merge(
                $request->only(['name','email','text']),
                ['ip_address' => $request->ip(),  'lang_id' => $lang_id]
            )
        );

        $status = ($contact instanceof Contact);

        if ($lang_id == 1){
            $message = ($status) ? 'پیام شما با موفقیت ثبت شد' : 'خطایی در ثبت پیام شما رخ داده';
        } else {
            $message = ($status) ? 'Your message has been successfully submitted' : 'An error occurred while submitting your message';
        }

        return ResponseHelper::simple_response($status,$message);
    }

    public function about_index()
    {
        $about = setting('about.fa') ?? '';

        $website_title = 'درباره ما | ' . (setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل');

        $seo = [
            'title' => 'درباره ما',
            'description' => trim((string) strip_tags($about)) ?: 'آشنایی با گروه رسانه‌ای صبح ساحل، پایگاه خبری استان هرمزگان',
            'type' => 'website',
            'url' => route('website.rtl.about'),
        ];

        return view('website.rtl.about-us',compact('about','website_title','seo'));
    }

    /**
     * English about page (en/about-us) — mirrors about_index() (WP-15).
     */
    public function en_about_index()
    {
        $about = setting('about.en') ?? '';

        $website_title = 'About Us | ' . $this->enBrandName();

        $seo = [
            'title' => 'About Us',
            'description' => trim((string) strip_tags($about)) ?: 'About the Sobhe Sahel Media Group, the news website of Hormozgan province',
            'type' => 'website',
            'url' => url()->current(),
        ];

        return $this->ltrView('website.ltr.about-us', compact('about', 'website_title', 'seo'), 'About Us');
    }
}
