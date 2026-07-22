<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    public function index($slug)
    {
        $organization = Organization::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $logo = $organization->logo ? Storage::url($organization->logo) : null;
        } catch (\Exception $e) {
            $logo = null;
        }

        $website_title = $organization->name . ' | ' . (setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل');

        $seo = [
            'title' => $organization->name,
            'description' => trim((string) ($organization->description ?? '')) ?: ('معرفی ' . $organization->name . ' در پایگاه خبری صبح ساحل'),
            'type' => 'website',
            'image' => $logo,
            'url' => route('website.rtl.organization', ['slug' => $organization->slug]),
        ];

        return view('website.rtl.organization', compact('organization', 'logo', 'website_title', 'seo'));
    }
}
