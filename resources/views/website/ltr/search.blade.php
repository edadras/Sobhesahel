@extends('website.ltr.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/archive-ltr.css') }}"/>
@endsection

@section('script')
    <script src="{{ asset('js/search.js?ver=' . env('JS_ASSET_VER')) }}"></script>
@endsection

@section('content')
    <section class="archiveSec">
        <div class="container" id="app" data-lang="en">
            <div class="row">
                <div class="col-12">
                    <div class="archiveRow">
                        <div class="archivRght">
                            <div class="titleBx">Filters</div>
                            <div class="filterBox">
                                <form class="">
                                    <div class="mb-3">
                                        <label for="sel01" class="form-label">Category</label>
                                        <select class="form-select" id="sel01" aria-label="Default select" v-model="options.category">
                                            <option selected="" value="all">All</option>
                                            <option v-for="item in categories" :value="item.id">
                                                @{{ item.en_title }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="sel02" class="form-label">Type</label>
                                        <select class="form-select" id="sel02" aria-label="Default select" v-model="options.post_type">
                                            <option selected="" value="all">All</option>
                                            <option value="news">News</option>
                                            <option value="video">Video</option>
                                            <option value="image">Photo</option>
                                        </select>
                                    </div>

                                    <button @click="get_data" type="button" class="btn filterBtn transitionCls">
                                        Apply
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="archivLeft">
                            <div class="archvLftTtl">Search result for
                                “ {{request()->get('q')}} ”
                            </div>
                            <div class="searchPage">
                                <div class="srchPgRght">
                                    <div class="head">
                                        <h3>
                                            @{{ posts.total }}
                                            result</h3>
                                        <div class="dropSel">
                                            <div class="select">
                                                <div class="dropSelDiv">
                            <span>
                              <p>New to old</p>
                            </span>
                                                    <i class="icon-Group-2209 expndMrIcon transitionCls"></i>
                                                </div>
                                            </div>
                                            <input type="hidden">
                                            <ul class="dropdown-mnu">
                                                <li>
                                                    <a href="#" @click.prevent="options.order_type = 'DESC'">
                                                        <p>New to old</p>
                                                    </a>
                                                </li>
                                                <li class="faDirction">
                                                    <a href="#" @click.prevent="options.order_type = 'ASC'">
                                                        <p>Old to new</p>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>


                                        <a v-for="item in posts.data" :href="item.url" class="srchPgCard">
                                            <div class="imgBx position-relative">
                                                <img :src="'/' + item.image_large" class="position-relative transitionCls"
                                                     alt="img">

                                            </div>
                                            <div class="text text-start">
                                                <h2>
                                                  @{{ item.title }}
                                                </h2>
                                              <p v-html="item.short_description"></p>
                                                <span>
                                                    @{{ item.posted_at }}
                                                </span>
                                            </div>
                                        </a>


                                </div>
                                <div class="srchPgLeft">
                                    <a href="#" class="lftSidImg">
                                        <img src="/asset/img/img04.jpg" alt="img">
                                    </a>
                                    <div class="lftSidBox">
                                        <div class="head">
                                            <span></span>
                                            <p>تبلیغات متنی</p>
                                        </div>
                                    </div>
                                    <div class="lftSidBox">
                                        <div class="lftSidTags">
                                            <div class="lftSidTagBx transitionCls">خودکار بیک</div>
                                            <div class="lftSidTagBx transitionCls">
                                                کفش آذر جهان
                                            </div>
                                            <div class="lftSidTagBx transitionCls">متن تبلیغ</div>
                                            <div class="lftSidTagBx transitionCls">
                                                خرید دوربین شکاری
                                            </div>
                                            <div class="lftSidTagBx transitionCls">
                                                کفش آذر جهان
                                            </div>
                                            <div class="lftSidTagBx transitionCls">
                                                شیر آلات شودر
                                            </div>
                                            <div class="lftSidTagBx transitionCls">
                                                ارز دیجیتال بیت کوین
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
