<header class="position-relative">


    @if(\Illuminate\Support\Facades\Auth::check() && request()->routeIs('website.rtl.single'))
        <style>

            .admin-header {
                background-color: #23282d;
                color: #fff;
                padding: 10px 16px;
                font-size: 14px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-radius: 6px;
                margin-bottom: 10px;
            }

            .admin-header .left {
                display: flex;
                align-items: center;
                gap: 15px;
                flex-wrap: wrap;
            }

            .view-box {
                background: #0073aa;
                padding: 4px 10px;
                border-radius: 4px;
                font-size: 13px;
                color: #fff;
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }

            .actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .btn {
                background-color: #0073aa;
                border: none;
                color: white;
                padding: 6px 12px;
                border-radius: 3px;
                text-decoration: none;
                font-size: 13px;
                transition: background-color 0.3s;
                cursor: pointer;
            }

            .btn:hover {
                background-color: #005d8f;
            }

            /* Dropdown */
            .dropdown {
                position: relative;
            }

            .dropdown-toggle {
                background-color: #0073aa;
                padding: 6px 10px;
                font-size: 16px;
                border: none;
                border-radius: 3px;
                cursor: pointer;
                color: #fff;
            }

            .dropdown-menu {
                display: none;
                position: absolute;
                right: 0;
                background-color: #fff;
                color: #000;
                border-radius: 4px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.2);
                margin-top: 6px;
                min-width: 120px;
                z-index: 100;
            }

            .dropdown-menu a {
                display: block;
                padding: 8px 12px;
                color: #000;
                text-decoration: none;
                font-size: 13px;
            }

            .dropdown-menu a:hover {
                background-color: #f1f1f1;
            }

            /* Show dropdown on hover */
            .dropdown:hover .dropdown-menu {
                display: block;
            }
        </style>

        @php
            $edit_url = \App\Constant\AppConstant::filament_model_map()[$type]::getUrl('edit',['record' => $post]);
        @endphp

        <div class="admin-header">
            <div class="left">
                {{ $post->title }}

                <div class="view-box">
                    👁️ <span>{{ $post->visits }}</span> بازدید
                </div>
            </div>

            <div class="actions">
{{--                <div class="dropdown">--}}
{{--                    <button class="btn dropdown-toggle">⋮</button>--}}
{{--                    <div class="dropdown-menu">--}}
{{--                        <a href="#" class="dropdown-item">حذف خبر</a>--}}
{{--                        <a href="#" class="dropdown-item">پیش‌نویس</a>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{-- --}}
                <a href="{{ $edit_url }}" class="btn">ویرایش خبر</a>
            </div>
        </div>
    @endif



    <div class="headerTop">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="hdrTopBx">
                        <div class="hdrTopRght">
                            <div class="hdrDateBx">
                                <strong>{{ \Morilog\Jalali\Jalalian::now()->format('d') }}</strong>
                                <div>
                                    <p>{{ \Morilog\Jalali\Jalalian::now()->format('%B') }}</p>
                                    <p>{{ \Morilog\Jalali\Jalalian::now()->format('Y') }}</p>
                                </div>
                            </div>
                            {{-- Live indicator: self-querying component, renders nothing unless a stream is live --}}
                            @includeIf('website.components.live-indicator')
                        </div>



                        <div class="hdrTopLeft">
                            <div class="selctTheme selctTheme1 position-relative">
                                <input
                                    type="checkbox"
                                    class="btn-check"
                                    id="themeCheck"
                                    autocomplete="off"
                                />
                                <label for="themeCheck">
                                    <i>حالت روز</i>
                                    <span class="icon-Group-2122"></span></label
                                ><br/>
                            </div>
                            <div class="langDrop">
                                <div class="dropSel">
                                    <div class="select">
                                        <div class="dropSelDiv">
                          <span>
                            <p>Farsi</p>
                          </span>
                                            <i class="icon-Vector11"></i>
                                        </div>
                                    </div>
                                    <input type="hidden"/>
                                    <ul class="dropdown-mnu">
                                        <li>
{{--                                            <a href="{{ route('website.ltr.home') }}">--}}
                                            <a href="#">
                                                <p>English</p>
                                            </a>
                                        </li>
                                        <li class="faDirction">
                                            <a href="{{ route('website.home') }}">
                                                <p>Farsi</p>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="headerSec">
        <div class="hdrSecDiv position-relative">
            <img
                src="{{ asset('asset/img/header-shape.png') }}"
                lightsrc="{{ asset('asset/img/header-shape.png') }}"
                darksrc="{{ asset('asset/img/header-shape-dark.png') }}"
                class="chngThemImg hdrVector"
                alt="vector"
            />
            <div class="container position-relative">
                <div class="row">
                    <div class="col-12">
                        <div class="hdrSecBox">
                            <a href="{{ route('website.home') }}" class="hdrLogo">
                                <img
                                    src="{{ asset('asset/img/logo.svg') }}"
                                    class="hdrLogoImg"
                                    alt="logo"
                                />
                            </a>
                            <div class="hdrLeftBx">
                                <span class="icon-Group-2298 openSidMnu"></span>
                                <div class="hdrSideBx transitionCls">
                                    <span class="icon-Close---SVG-1 closSideMnu"></span>
                                    <a href="#" class="sideLogo">
                                        <img
                                            src="{{ asset('asset/img/logo.svg') }}"
                                            class="hdrLogoImg"
                                            alt="logo"
                                        />
                                    </a>
                                    <div class="hedrMnuBx">
                                        @php
                                            $headerMenuItems = \App\Models\Menu::forLocation('header');
                                        @endphp
                                        @if(count($headerMenuItems) > 0)
                                            <ul class="hedrMnuUl">
                                                @foreach($headerMenuItems as $menuItem)
                                                    <li>
                                                        @if($menuItem['type'] === 'separator')
                                                            <span class="transitionCls">{{ $menuItem['title'] }}</span>
                                                        @else
                                                            <a href="{{ \App\Models\Menu::resolveUrl($menuItem['url']) }}"
                                                               target="{{ $menuItem['target'] ?? '_self' }}"
                                                               class="transitionCls">{{ $menuItem['title'] }}</a>
                                                        @endif
                                                        @if(!empty($menuItem['active_children_recursive']))
                                                            <ul>
                                                                @foreach($menuItem['active_children_recursive'] as $childItem)
                                                                    <li>
                                                                        @if($childItem['type'] === 'separator')
                                                                            <span class="transitionCls">{{ $childItem['title'] }}</span>
                                                                        @else
                                                                            <a href="{{ \App\Models\Menu::resolveUrl($childItem['url']) }}"
                                                                               target="{{ $childItem['target'] ?? '_self' }}"
                                                                               class="transitionCls">{{ $childItem['title'] }}</a>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                        <ul class="hedrMnuUl">
                                            <li>
                                                <a href="{{ route('website.home') }}"
                                                   class="transitionCls {{request()->routeIs('website.home') ? 'active' : ''}}">خانه</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.rtl.index',['type' => 'news']) }}" class="transitionCls {{request()->routeIs('profile') ? 'active' : ''}}">اخبار</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.rtl.index',['type' => 'note']) }}" class="transitionCls">یادداشت‌ها</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.rtl.archive') }}"
                                                   class="transitionCls {{request()->routeIs('website.rtl.archive') ? 'active' : ''}}">آرشیو
                                                    روزنامه‌ها</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.rtl.index',['type' => 'podcast']) }}"
                                                   class="transitionCls">پادکست</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.rtl.index',['type' => 'photo']) }}"
                                                   class="transitionCls {{request()->routeIs('website.rtl.gallery') ? 'active' : ''}}">عکس</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.rtl.index',['type' => 'video']) }}"
                                                   class="transitionCls {{request()->routeIs('website.rtl.video') ? 'active' : ''}}">ویدئو</a>
                                            </li>
                                        </ul>
                                        @endif
                                    </div>
                                </div>
                                <ul class="hdrLftMnu">
{{--                                    <li class="hdLftItem position-relative">--}}
{{--                                        <a href="{{ route('profile') }}">--}}
{{--                                            <i class="icon-Profile-1"></i>--}}
{{--                                        </a>--}}
{{--                                    </li>--}}
                                    {{--                                    <li class="hdLftItem position-relative srchIcon">--}}
                                    {{--                                        <span class="icon-Group-43"></span>--}}
                                    {{--                                    </li>--}}
                                    <li class="hdLftItem position-relative categoryBox">
                                        <span class="icon-Group-104 openCatIcon"></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="categoryMnu">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <form class="catSrchBx rounded-3" method="GET" action="{{ route('website.rtl.search') }}">
                        <input
                            type="text"
                            class="form-control rounded-2"
                            placeholder="جستجو..."
                            name="q"
                        />
                        <button type="submit" class="btn rounded-2">
                            <span class="icon-Group-2360"></span>
                        </button>
                    </form>
                    @php
                        $categories = \App\Models\Category::getCategoriesForWebsiteMenu();
                    @endphp

                    <div class="catList">
                        @foreach($categories as $category)
                            <div class="catCol text-end">
                                <a href="{{ route('website.rtl.category',['slug' => $category['slug']]) }}">
                                    <strong>{{ $category['title'] }}</strong>
                                </a>

                                @if(count($category['children_recursive']) != 0)
                                    <ul>
                                        @foreach($category['children_recursive'] as $child)
                                            <li>
                                                <a href="{{ route('website.rtl.category',['slug' => $child['slug']]) }}">
                                                    {{ $child['title'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
