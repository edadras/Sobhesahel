@extends('website.ltr.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/news-ltr.css') }}"/>
    <meta name="csrf-token" content="{{csrf_token()}}">
@endsection

@section('script')
    <script src="{{ asset('js/comments.js?ver=' . env('JS_ASSET_VER')) }}"></script>
    <script src="{{ asset('js/helper.js?ver=' . env('JS_ASSET_VER')) }}"></script>
@endsection

@section('content')
    <section class="newsPgSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="pageRow">
                        <div class="pageMainBx">
                            <div class="noteUsrRow">
                                <div class="noteUsrBx">
                                    <div class="noteUsrImg">
                                        <img src="{{ asset($author['avatar']) }}" alt="img">
                                    </div>
                                    <div class="text-start">
                                        <strong>
                                            {{ $author['first_name'] . ' ' . $author['last_name'] }}
                                        </strong>
{{--                                        <p>سرپرست اسبق دادسرای جرایم رایانه‌ای</p>--}}
                                    </div>
                                </div>
                                <div class="noteUsrDate">
                                    <i>
                                        {{ $post['posted_at'] }}
                                    </i>
                                    <span class="icon-Vector-Stroke-4"></span>
                                    <i>
                                        {{ count($comments) }}
                                        Comment</i>
                                </div>
                            </div>
                            <div class="newsTitlBox text-start" id="pageHead">
                                <h1>
                                    {{ $post['title'] }}
                                </h1>
                               {!! $post['short_description'] !!}
                                <div>
                                    <p>Share:</p>
                                    <ul class="hdrTopLnks">
                                        @php
                                            $postUrl = urlencode(url()->current());
                                            $postTitle = urlencode($post->title ?? ''); // Optional: If you want to include a title for platforms like Twitter and LinkedIn
                                        @endphp
                                        <li>
                                            <a href="https://telegram.me/share/url?url={{ $postUrl }}" target="_blank" class="transitionCls">
                                                <span class="icon-Telegram-1"></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $postUrl }}" target="_blank" class="transitionCls">
                                                <span class="icon-Linkedin-1"></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="https://twitter.com/intent/tweet?url={{ $postUrl }}&text={{ $postTitle }}" target="_blank" class="transitionCls">
                                                <span class="icon-Twitter-X-1"></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="https://www.youtube.com/" target="_blank" class="transitionCls">
                                                <span class="icon-Youtube-1"></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="https://www.instagram.com/" target="_blank" class="transitionCls">
                                                <span class="icon-Instagram-1"></span>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-Vector-Stroke-4"></i>
                                        </li>
                                        <li>
                                            @guest
                                                <a href="{{ route('profile') }}" target="_blank"
                                                   class="transitionCls">
                                                    <small class="icon-Group-2344"></small>
                                                </a>
                                            @endguest

                                            @auth
                                                @if($is_marked)
                                                    <small @click="bookmark({{ $post['id'] }})" ref="bookmark_btn" class="icon-Group-2344 icon-active"></small>
                                                @else
                                                    <small @click="bookmark({{ $post['id'] }})" ref="bookmark_btn" class="icon-Group-2344"></small>
                                                @endif
                                            @endauth
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="newsText text-start">
                               {!! $post['post_body'] !!}
                            </div>
                            <div class="noteTopImg position-relative">
                                <img src="{{ asset($post['image_large']) }}" alt="img">
                            </div>
                            @if(count($post['tags']) != 0)
                                <div class="newsTags">
                                    <p>Tags:</p>
                                    <ul>
                                        @foreach($post['tags'] as $tag)
                                            @if($tag['name'] != '')
                                                <a href="{{ route('website.rtl.tag',['name' => $tag['name']]) }}">
                                                    <li>
                                                        #{{$tag['name']}}
                                                    </li>
                                                </a>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="newsAddCmnt" id="app">
                                <div class="head">
                                    <div>
                                        <strong>Comments</strong>
                                        <small>({{ count($comments) }})</small>
                                    </div>
                                    <button @click.prevent="submit_comment" class="btn transitionCls">Add Comment
                                    </button>
                                </div>
                                <div class="body">
                                    <div>
                                        <img src="/asset/img/user05.png" alt="img">
                                    </div>
                                    <span>
                                        <input v-model="comment.name" type="text" class="form-control" id="cmntArea"
                                               placeholder="Name" style="display: unset;">
                                        <input v-model="comment.email" type="text" class="form-control" id="cmntArea"
                                               placeholder="Email">
                                    </span>

                                    <textarea v-model="comment.text" class="form-control" id="cmntArea"
                                              placeholder="Write your opinion..."
                                              rows="3"></textarea>

                                </div>

                            </div>
                        </div>
                        @include('website.ltr.sidebar')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
