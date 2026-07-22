@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/news-rtl.css') }}"/>
    <style>
        .qaSec { padding: 30px 0; }
        .qaBreadcrumb { font-size: 13px; margin-bottom: 20px; }
        .qaBreadcrumb a { text-decoration: none; color: #0a7d5f; }
        .qaQuestionBox { border: 1px solid #e3e3e3; border-radius: 10px; padding: 20px; background: #fff; margin-bottom: 24px; }
        .qaQuestionBox h1 { font-size: 20px; margin-bottom: 12px; }
        .qaQuestionBox .qaBody { font-size: 14px; line-height: 2; color: #333; white-space: pre-line; }
        .qaMeta { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 12px; color: #888; margin-bottom: 14px; }
        .qaBadge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; background: #f0f0f0; }
        .qaBadge.expert { background: #0a7d5f; color: #fff; }
        .qaAttachment { margin-top: 14px; font-size: 13px; }
        .qaAttachment a { color: #0a7d5f; text-decoration: none; }
        .qaAttachment img { max-width: 320px; max-height: 240px; border-radius: 8px; display: block; margin-top: 8px; }
        .qaAnswersTitle { font-size: 16px; margin-bottom: 14px; }
        .qaAnswer { border: 1px solid #e3e3e3; border-right: 4px solid #ccc; border-radius: 10px; padding: 16px 18px; margin-bottom: 12px; background: #fff; }
        .qaAnswer.expert { border-right-color: #0a7d5f; background: #f4faf8; }
        .qaAnswer .qaAnswerHead { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 8px; font-size: 12px; color: #888; }
        .qaAnswer .qaAnswerBody { font-size: 14px; line-height: 2; color: #333; white-space: pre-line; }
        .qaFormBox { border: 1px solid #e3e3e3; border-radius: 10px; padding: 20px; margin-top: 30px; background: #fff; }
        .qaFormBox h2 { font-size: 17px; margin-bottom: 8px; }
        .qaFormBox p { font-size: 13px; color: #666; }
        .qaSubmitBtn { background: #0a7d5f; color: #fff; padding: 8px 30px; }
        .qaEmpty { padding: 20px 0; color: #888; font-size: 14px; }
    </style>
@endsection

@section('content')
    <section class="qaSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="qaBreadcrumb">
                        <a href="{{ route('website.rtl.qa') }}">پرسش و پاسخ</a>
                        @if($question->category)
                            /
                            <a href="{{ route('website.rtl.qa', ['category' => $question->category->slug]) }}">
                                {{ $question->category->title }}
                            </a>
                        @endif
                    </div>

                    <div class="qaQuestionBox">
                        <h1>{{ $question->title }}</h1>
                        <div class="qaMeta">
                            @if($question->category)
                                <span class="qaBadge">{{ $question->category->title }}</span>
                            @endif
                            <span>پرسشگر: {{ $question->name }}</span>
                            <span>{{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($question->created_at))->format('%d %B %Y') }}</span>
                        </div>
                        <div class="qaBody">{{ $question->body }}</div>

                        @if($question->attachment_url)
                            <div class="qaAttachment">
                                <strong>فایل ضمیمه:</strong>
                                @if(\Illuminate\Support\Str::endsWith(strtolower($question->attachment), ['.jpg', '.jpeg', '.png']))
                                    <a href="{{ $question->attachment_url }}" target="_blank" rel="noopener">
                                        <img src="{{ $question->attachment_url }}" alt="ضمیمه پرسش">
                                    </a>
                                @else
                                    <a href="{{ $question->attachment_url }}" target="_blank" rel="noopener">دانلود فایل ضمیمه (PDF)</a>
                                @endif
                            </div>
                        @endif
                    </div>

                    <h2 class="qaAnswersTitle">پاسخ‌ها ({{ $question->published_answers->count() }})</h2>

                    @forelse($question->published_answers as $answer)
                        <div class="qaAnswer {{ $answer->is_expert ? 'expert' : '' }}">
                            <div class="qaAnswerHead">
                                @if($answer->is_expert)
                                    <span class="qaBadge expert">پاسخ کارشناسی</span>
                                @endif
                                @if($answer->answerer_name)
                                    <span>{{ $answer->answerer_name }}</span>
                                @endif
                                <span>{{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($answer->created_at))->format('%d %B %Y') }}</span>
                            </div>
                            <div class="qaAnswerBody">{{ $answer->body }}</div>
                        </div>
                    @empty
                        <div class="qaEmpty">
                            هنوز پاسخی برای این پرسش ثبت نشده است.
                        </div>
                    @endforelse

                    @include('website.rtl.components.qa-form')
                </div>
            </div>
        </div>
    </section>
@endsection
