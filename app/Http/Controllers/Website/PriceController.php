<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\MarketPrice;

class PriceController extends Controller
{
    public function index()
    {
        $groups = MarketPrice::activeGrouped();

        $website_title = 'قیمت‌ها | ' . (setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل');

        $seo = [
            'title' => 'قیمت‌ها',
            'description' => 'قیمت لحظه‌ای ارز، طلا، سکه و بازارها در پایگاه خبری صبح ساحل',
            'type' => 'website',
            'url' => route('website.rtl.prices'),
        ];

        return view('website.rtl.prices', compact('groups', 'website_title', 'seo'));
    }
}
