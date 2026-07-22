@extends('website.ltr.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/news-ltr.css') }}"/>
@endsection

@section('script')
    <script src="{{asset('js/helper.js?ver=' . env('JS_ASSET_VER'))}}"></script>
@endsection

@section('content')
    <section class="newsPgSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="pageRow">
                        <div class="pageMainBx">
                            <div id="pageHead" data-lang="en" class="catPgHed">
                                <div class="title">
                                    <span></span>
                                    <strong>{{ $page_title }}</strong>
                                </div>
                                @if(isset($category_id))
                                    @auth
                                        @if($has_follow)
                                            <button @click="follow({{ $category_id }})" class="btn transitionCls" ref="follow_btn">
                                                <span class="icon-Group-2334"></span>
                                                <i>Following</i>
                                            </button>
                                        @else
                                            <button @click="follow({{ $category_id }})" class="btn transitionCls"
                                                    ref="follow_btn">
                                                <span class="icon-Group-2334"></span>
                                                <i>Follow</i>
                                            </button>
                                        @endif
                                    @endauth

                                    @guest
                                        <a href="{{ route('profile.login') }}">
                                            <button class="btn transitionCls">
                                                <span class="icon-Group-2334"></span>
                                                <i>Follow</i>
                                            </button>
                                        </a>
                                    @endguest
                                @endif
                            </div>

                            @foreach($posts->take(1) as $item)
                                <a href="{{ $item['url'] }}">
                                    <div class="catBigCard imageBx position-relative">
                                        <img src="{{ asset($item['image_large']) }}"
                                             class="position-relative"
                                             alt="img">
                                        <div class="position-absolute">
                                            <strong>{{ $item['title'] }}</strong>
                                           <p>
                                               {{ strip_tags($item['short_description']) }}
                                           </p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                            <div class="topNewsRow mb-4">
                                @foreach($posts->skip(1)->take(3) as $post)

                                    <div class="topNewsBx position-relative">
                                        <a href="{{ $post['url'] }}">
                                            <img src="{{ asset($post['image_large']) }}"
                                                 class="position-relative" alt="img">
                                            <div class="topNewsCvr transitionCls">
                                              <p>
                                                  {{ strip_tags($post['short_description']) }}
                                              </p>
                                            </div>
                                        </a>
                                    </div>

                                @endforeach
                            </div>
                            <div class="catMinCards">
                                @foreach($posts->skip(4) as $post)
                                    <a href="{{ $post['url'] }}"
                                       class="transitionCls">
                                        <div class="imgBx position-relative">
                                            <img src="{{ asset($post['image_large']) }}"
                                                 class="position-relative" alt="img">
                                            <div class="position-absolute">
                                                <span class="icon-Group-2329"></span>
                                            </div>
                                        </div>
                                        <div class="text text-start">
                                            <h2>
                                                {{ $post['title'] }}
                                            </h2>
                                            <p>
                                                {{ strip_tags($post['short_description']) }}
                                            </p>
                                            <span>{{ $post['posted_at'] }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            {{ $posts->links('vendor.pagination.custom') }}
                            {{--                            <a href="#" class="catMorCrd transitionCls">موارد بیشتر</a>--}}
                        </div>

                        @include('website.ltr.sidebar')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
