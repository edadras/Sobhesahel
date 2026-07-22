@extends('website.ltr.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/archive-ltr.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/persian-datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/audioplayer.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/lg-video.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/video-js.css') }}"/>
@endsection
@section('script')
    <script src="{{ asset('js/archive.js?ver=' . env('JS_ASSET_VER')) }}"></script>
    <script src="{{asset('asset/js/tabs.js')}}"></script>
    <script src="{{asset('asset/js/persian-date.min.js')}}"></script>
    <script src="{{asset('asset/js/persian-datepicker.min.js')}}"></script>
    <script src="{{asset('asset/js/lg-video.min.js')}}"></script>
    <script src="{{asset('asset/js/video.js')}}"></script>
@endsection


@section('content')
    <section class="archiveSec" id="app" data-lang="en">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="archiveRow">
                        <div class="archivRght">
                            <div class="titleBx">Filters</div>
                            <div class="filterBox">
                                <form @submit.prevent="get_data">
                                    <div class="mb-3">
                                        <label for="sel01" class="form-label">Archive</label>
                                        <select v-model="options.archive"
                                            class="form-select"
                                            id="sel01"
                                            aria-label="Default select"
                                        >
                                            <option selected value="all">All</option>
                                            <option v-for="(name,key) in list" :value="key">@{{ name }}</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="inpt02" class="form-label"
                                        >Select number</label
                                        >
                                        <input type="text" v-model="options.number" class="form-control" id="inpt02"/>
                                    </div>

                                    <div class="mb-3">
                                        <label for="datepicker1" class="form-label"
                                        >From</label
                                        >
                                        <input
                                            type="text"
                                            class="form-control datepickrInpt"
                                            id="datepicker1"
                                            aria-label="date1"
                                            aria-describedby="date1"
                                            placeholder="Select"
                                            v-model="options.from_date"
                                        />
                                    </div>

                                    <div class="mb-3">
                                        <label for="datepicker2" class="form-label"
                                        >Until</label
                                        >
                                        <input
                                            type="text"
                                            class="form-control datepickrInpt"
                                            id="datepicker2"
                                            aria-label="date2"
                                            aria-describedby="date2"
                                            placeholder="Select"
                                            v-model="options.to_date"
                                        />
                                    </div>
                                    <button type="submit" class="btn filterBtn transitionCls">
                                        Apply
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="archivLeft">
                            <div class="archivSort">
                                <div class="form-check position-relative">
                                    <input
                                        class="form-check-input position-absolute"
                                        type="radio"
                                        name="all"
                                        id="all"
                                        value="all"
                                        v-model="options.archive"
                                    />
                                    <label
                                        class="form-check-label transitionCls position-relative"
                                        for="all"
                                    >
                                       All
                                    </label>
                                </div>
                                <div class="form-check position-relative" v-for="(name,key) in list">
                                    <input
                                        class="form-check-input position-absolute"
                                        type="radio"
                                        :name="key"
                                        :id="key"
                                        :value="key"
                                        v-model="options.archive"
                                    />
                                    <label
                                        class="form-check-label transitionCls position-relative"
                                        :for="key"
                                    >
                                       @{{ name }}
                                    </label>
                                </div>
                            </div>

                            <div class="archiveList">
{{--                                @foreach($posts as $item)--}}
                                    <a :href="'/pdf/' + item.archive_category + '/' + item.archive_number" v-for="item in archives.data" class="archiveCard transitionCls">
                                        <div class="imgBox">
                                            <img :src="'/' + item.image_large" alt="img"/>
                                        </div>
                                        <div class="text">
                                            <p>
                                                @{{ item.archive }}
                                            </p>
                                            <div>
                                                <span>
                                                    Number
                                                        @{{ item.archive_number }}
                                                </span>
                                                <span>@{{ item.archive_date }}</span>
                                            </div>
                                        </div>
                                    </a>
{{--                                @endforeach--}}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
