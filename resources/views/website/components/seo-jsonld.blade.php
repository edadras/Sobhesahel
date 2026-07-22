{{--
    JSON-LD structured data partial.

    - On the home page: Organization (NewsMediaOrganization) + WebSite (with SearchAction).
    - On single post pages (when $post is an Eloquent model and $seo['type'] === 'article'):
      NewsArticle (news) / Article (other content types).

    All lookups are guarded so a missing image/summary/author never breaks the page.
--}}
@php
    $ldBrandName = trim((string) (setting('general.fa_brand_name') ?? '')) ?: 'گروه رسانه‌ای صبح‌ساحل';
    $ldSiteUrl = rtrim((string) config('app.url'), '/');
    $ldLogoUrl = url('asset/img/logo.png');

    $ldPublisher = [
        '@type' => 'NewsMediaOrganization',
        'name' => $ldBrandName,
        'url' => $ldSiteUrl,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $ldLogoUrl,
        ],
    ];

    $ldSchemas = [];

    if (request()->routeIs('website.home')) {
        $ldSchemas[] = ['@context' => 'https://schema.org'] + $ldPublisher;

        $ldSchemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $ldBrandName,
            'url' => $ldSiteUrl,
            'inLanguage' => 'fa-IR',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $ldSiteUrl . '/search?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    if (isset($post)
        && $post instanceof \Illuminate\Database\Eloquent\Model
        && (($seo['type'] ?? null) === 'article')) {

        try {
            $ldArticleUrl = method_exists($post, 'getUrl') ? $post->getUrl() : url()->current();
        } catch (\Throwable $e) {
            $ldArticleUrl = url()->current();
        }

        try {
            $ldArticleImage = method_exists($post, 'getImageUrl') ? $post->getImageUrl() : null;
        } catch (\Throwable $e) {
            $ldArticleImage = null;
        }

        if (!empty($ldArticleImage) && !preg_match('#^https?://#i', $ldArticleImage)) {
            $ldArticleImage = url(ltrim($ldArticleImage, '/'));
        }

        $ldAuthorName = null;
        if (isset($author) && is_array($author)) {
            $ldAuthorName = trim((string) ($author['name'] ?? '')) ?: (trim((string) ($author['nik_name'] ?? '')) ?: null);
        }

        $ldPublished = null;
        $ldModified = null;

        try {
            $ldPublished = !empty($post->publish_at)
                ? \Carbon\Carbon::parse($post->publish_at)->toIso8601String()
                : null;
        } catch (\Throwable $e) {
            $ldPublished = null;
        }

        try {
            $ldModified = !empty($post->updated_at)
                ? \Carbon\Carbon::parse($post->updated_at)->toIso8601String()
                : null;
        } catch (\Throwable $e) {
            $ldModified = null;
        }

        $ldHeadline = \Illuminate\Support\Str::limit(trim(strip_tags((string) ($post->title ?? ''))), 110, '');
        $ldDescription = \Illuminate\Support\Str::limit(
            trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) ($post->short_description ?? '')))),
            300,
            ''
        );

        $ldSchemas[] = array_filter([
            '@context' => 'https://schema.org',
            '@type' => (($type ?? null) === 'news') ? 'NewsArticle' : 'Article',
            'headline' => $ldHeadline !== '' ? $ldHeadline : null,
            'description' => $ldDescription !== '' ? $ldDescription : null,
            'image' => !empty($ldArticleImage) ? [$ldArticleImage] : null,
            'datePublished' => $ldPublished,
            'dateModified' => $ldModified ?? $ldPublished,
            'inLanguage' => 'fa-IR',
            'author' => [[
                '@type' => 'Person',
                'name' => $ldAuthorName ?? $ldBrandName,
            ]],
            'publisher' => $ldPublisher,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $ldArticleUrl,
            ],
            'url' => $ldArticleUrl,
        ]);
    }
@endphp
@foreach($ldSchemas as $ldSchema)
<script type="application/ld+json">{!! json_encode($ldSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endforeach
