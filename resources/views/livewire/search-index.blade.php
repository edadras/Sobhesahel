<div class="archiveRow" x-data="{}">
    <style>
        .search-highlight {
            background: #fff3cd;
            color: inherit;
            padding: 0 2px;
            border-radius: 2px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css">
    <script type="text/javascript" src="https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js"></script>
    

    <div class="archivRght" style="z-index: 9">
        <div class="titleBx">فیلتر ها</div>
        <div class="filterBox">
            <form wire:submit.prevent="getData">
                <div class="mb-3">
                    <label for="sel01" class="form-label">دسته بندی ها</label>
                    <select class="form-select" id="sel01" wire:model="options.category">
                        <option value="all">همه</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="sel02" class="form-label">نوع خبر</label>
                    <select class="form-select" id="sel02" wire:model="options.post_type">
                        <option value="all">همه</option>
                        <option value="news">خبر</option>
                        <option value="video">فیلم</option>
                        <option value="gallery">عکس</option>
                        <option value="note">یادداشت</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="from_date" class="form-label">از تاریخ</label>
                    <input type="text"
                           class="form-control"
                           id="from_date"
                           data-jdp
                           wire:model="options.from_date"
                    />
                </div>

                <div class="mb-3">
                    <label for="to_date" class="form-label">تا تاریخ</label>
                    <input type="text" data-jdp wire:model="options.to_date" class="form-control" id="to_date"/>
                </div>

                <button type="submit" class="btn filterBtn transitionCls">اعمال فیلتر</button>
            </form>
        </div>
    </div>

    <div class="archivLeft">
        <!-- Search Box -->
        <div class="mb-4">
            <form method="GET" action="{{ route('website.rtl.search') }}" class="d-flex align-items-center gap-2" style="max-width: 500px;" wire:ignore>
                <input
                    type="text"
                    name="q"
                    id="search-input"
                    class="form-control rounded-2"
                    placeholder="جستجو..."
                    value="{{ $query }}"
                    style="flex: 1; height: 40px; font-size: 14px;"
                />
                <button type="submit" 
                        class="btn rounded-2"
                        style="height: 40px; padding: 0 20px; min-width: 50px;">
                    <span class="icon-Group-2360"></span>
                </button>
            </form>
        </div>

        @if(!empty($query))
            <div class="archvLftTtl">نتایج جستجو برای " {{ $query }} "</div>
        @else
            <div class="archvLftTtl">جستجو در محتوای سایت</div>
        @endif
        <div class="searchPage">
            <div class="srchPgRght">
                <div class="head">
                    <h3>{{ $posts->total() }} نتیجه</h3>
                    <div class="dropSel">
                        <div class="select">
                            <div class="dropSelDiv">
                                <span>
                                    <p>جدید به قدیم</p>
                                </span>
                                <i class="icon-Group-2209 expndMrIcon transitionCls"></i>
                            </div>
                        </div>
                        <input type="hidden">
                        <ul class="dropdown-mnu">
                            <li>
                                <a href="#" wire:click="$set('options.order_type', 'DESC')">
                                    <p>جدید به قدیم</p>
                                </a>
                            </li>
                            <li class="faDirction">
                                <a href="#" wire:click="$set('options.order_type', 'ASC')">
                                    <p>قدیم به جدید</p>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                @foreach($posts as $item)
                    <a href="{{ $item->getUrl() }}" class="srchPgCard">
                        <div class="imgBx position-relative">
                            <img src="{{ $item->getImageUrl() }}" class="position-relative transitionCls" alt="img">
                        </div>
                        <div class="text text-end">
                            <h2>{!! $item->search_title ?? ($item->title ?? $item['title']) !!}</h2>
                            <p>{!! $item->search_snippet ?? ($item->short_description ?? $item['short_description']) !!}</p>
                            <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 12px; color: #666;">
                                <span>{{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($item->publish_at ?? $item['publish_at']))->format('Y/m/d H:i') }}</span>
                                @php
                                    $author = $item->author ?? null;
                                    $user = $item->user ?? null;
                                @endphp
                                @if($author && $author->name)
                                    <span class="author-name">
                                        <i class="icon-Profile-1" style="margin-left: 5px;"></i>
                                        {{ $author->name }}
                                    </span>
                                @elseif($user && $user->name)
                                    <span class="author-name">
                                        <i class="icon-Profile-1" style="margin-left: 5px;"></i>
                                        {{ $user->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach

                {{ $posts->links('vendor.pagination.custom') }}
            </div>
        </div>
        <script>
            jalaliDatepicker.startWatch();
        </script>
    </div>
</div>
