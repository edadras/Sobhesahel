@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/news-rtl.css') }}"/>
    <style>
        .qaSec { padding: 30px 0; }
        .qaHead { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
        .qaHead .title { display: flex; align-items: center; gap: 8px; }
        .qaSearchForm { display: flex; gap: 8px; flex-wrap: wrap; }
        .qaSearchForm input[type="text"], .qaSearchForm select { min-width: 200px; }
        .qaCats { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .qaCats a { display: inline-block; padding: 6px 14px; border-radius: 20px; border: 1px solid #ccc; text-decoration: none; color: inherit; font-size: 13px; }
        .qaCats a.active { background: #0a7d5f; color: #fff; border-color: #0a7d5f; }
        .qaCard { border: 1px solid #e3e3e3; border-radius: 10px; padding: 16px 18px; margin-bottom: 14px; background: #fff; }
        .qaCard h2 { font-size: 16px; margin-bottom: 8px; }
        .qaCard h2 a { text-decoration: none; color: inherit; }
        .qaCard p { font-size: 13px; color: #666; margin-bottom: 10px; }
        .qaMeta { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 12px; color: #888; }
        .qaBadge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; background: #f0f0f0; }
        .qaBadge.expert { background: #0a7d5f; color: #fff; }
        .qaBadge.featured { background: #c98a00; color: #fff; }
        .qaFormBox { border: 1px solid #e3e3e3; border-radius: 10px; padding: 20px; margin-top: 30px; background: #fff; }
        .qaFormBox h2 { font-size: 17px; margin-bottom: 8px; }
        .qaFormBox p { font-size: 13px; color: #666; }
        .qaSubmitBtn { background: #0a7d5f; color: #fff; padding: 8px 30px; }
        .qaEmpty { text-align: center; padding: 40px 0; color: #888; }
    </style>
@endsection

@section('content')
    <section class="qaSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="qaHead">
                        <div class="title">
                            <strong>پرسش و پاسخ</strong>
                        </div>
                        <form method="GET" action="{{ route('website.rtl.qa') }}" class="qaSearchForm">
                            <input type="text" name="q" class="form-control" placeholder="جستجو در پرسش‌ها..."
                                   value="{{ $search }}">
                            @if($current_category)
                                <input type="hidden" name="category" value="{{ $current_category->slug }}">
                            @endif
                            <button type="submit" class="btn transitionCls qaSubmitBtn">جستجو</button>
                        </form>
                    </div>

                    <div class="qaCats">
                        <a href="{{ route('website.rtl.qa', array_filter(['q' => $search])) }}"
                           class="transitionCls {{ $current_category ? '' : 'active' }}">همه</a>
                        @foreach($categories as $cat)
                            <a href="{{ route('website.rtl.qa', array_filter(['category' => $cat->slug, 'q' => $search])) }}"
                               class="transitionCls {{ ($current_category && $current_category->id === $cat->id) ? 'active' : '' }}">
                                {{ $cat->title }}
                            </a>
                        @endforeach
                    </div>

                    @forelse($questions as $q)
                        <div class="qaCard">
                            <h2>
                                <a href="{{ $q->getUrl() }}">{{ $q->title }}</a>
                            </h2>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($q->body), 180) }}</p>
                            <div class="qaMeta">
                                @if($q->category)
                                    <span class="qaBadge">{{ $q->category->title }}</span>
                                @endif
                                @if($q->is_featured)
                                    <span class="qaBadge featured">ویژه</span>
                                @endif
                                @if($q->hasExpertAnswer())
                                    <span class="qaBadge expert">پاسخ کارشناسی</span>
                                @endif
                                <span>{{ $q->published_answers_count }} پاسخ</span>
                                <span>{{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($q->created_at))->format('%d %B %Y') }}</span>
                                <span>{{ $q->name }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="qaEmpty">
                            پرسشی یافت نشد. اولین پرسش را شما ثبت کنید!
                        </div>
                    @endforelse

                    {{ $questions->links('vendor.pagination.custom') }}

                    @include('website.rtl.components.qa-form')
                </div>
            </div>
        </div>
    </section>
@endsection
