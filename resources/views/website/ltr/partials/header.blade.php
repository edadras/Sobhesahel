<header class="position-relative">
    <div class="headerTop">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="hdrTopBx">
                        <div class="hdrTopRght">
                            <div class="hdrDateBx">
                                <strong>{{ \Carbon\Carbon::now()->format('d') }}</strong>
                                <div>
                                    <p>{{ \Carbon\Carbon::now()->format('M') }}</p>
                                    <p>{{ \Carbon\Carbon::now()->format('Y') }}</p>
                                </div>
                            </div>
                            <ul class="hdrTopLnks">
                                @php
                                    $social = \App\Models\AppSetting::getWebsiteSocial();
                                @endphp
                                @if($social['telegram'])
                                    <li>
                                        <a href="{{ $social['telegram'] }}" target="_blank" class="transitionCls">
                                            <span class="icon-Telegram"></span>
                                        </a>
                                    </li>
                                @endif
                                @if($social['linkedin'])
                                    <li>
                                        <a href="{{ $social['linkedin'] }}" target="_blank" class="transitionCls">
                                            <span class="icon-Linkedin"></span>
                                        </a>
                                    </li>
                                @endif
                                @if($social['twitter'])
                                    <li>
                                        <a href="{{ $social['twitter'] }}" target="_blank" class="transitionCls">
                                            <span class="icon-Twitter-X-1"></span>
                                        </a>
                                    </li>
                                @endif
                                @if($social['youtube'])
                                    <li>
                                        <a href="{{ $social['youtube'] }}" target="_blank" class="transitionCls">
                                            <span class="icon-Youtube"></span>
                                        </a>
                                    </li>
                                @endif
                                @if($social['instagram'])
                                    <li>
                                        <a href="{{ $social['instagram'] }}" target="_blank" class="transitionCls">
                                            <span class="icon-Instagram"></span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="hdrTopLeft">
                            <div class="selctTheme selctTheme2 position-relative">
                                <input
                                    type="checkbox"
                                    class="btn-check"
                                    id="themeCheck"
                                    autocomplete="off"
                                />
                                <label for="themeCheck">
                                    <i>light</i>
                                    <span class="icon-Group-2122"></span> </label
                                ><br />
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
                                    <input type="hidden" />
                                    <ul class="dropdown-mnu">
                                        <li>
                                            <a href="{{ route('website.ltr.home') }}">
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
                src="/asset/img/header-shape.png"
                lightSrc="/asset/img/header-shape.png"
                darkSrc="/asset/img/header-shape-dark.png"
                class="chngThemImg hdrVector"
                alt="vector"
            />
            <div class="container position-relative">
                <div class="row">
                    <div class="col-12">
                        <div class="hdrSecBox">
                            <a href="{{ route('website.ltr.home') }}" class="hdrLogo">
                                <img
                                    src="/asset/img/logo.svg"
                                    class="hdrLogoImg"
                                    alt="logo"
                                />
                            </a>
                            <div class="hdrLeftBx">
                                <span class="icon-Group-2298 openSidMnu"></span>
                                <div class="hdrSideBx transitionCls">
                                    <span class="icon-Close---SVG-1 closSideMnu"></span>
                                    <a href="{{ route('website.ltr.home') }}" class="sideLogo">
                                        <img
                                            src="/asset/img/logo.svg"
                                            class="hdrLogoImg"
                                            alt="logo"
                                        />
                                    </a>
                                    <div class="hedrMnuBx">
                                        <ul class="hedrMnuUl">
                                            <li>
                                                <a href="{{ route('website.ltr.home') }}" class="transitionCls {{request()->routeIs('website.ltr.home') ? 'active' : ''}}">Home</a>
                                            </li>
                                            <li>
                                                <a href="#" class="transitionCls">My news</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.ltr.index',['type' => 'note']) }}" class="transitionCls">Notes</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.ltr.archive') }}" class="transitionCls">Archives</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.ltr.index',['type' => 'podcast']) }}" class="transitionCls">Podcast</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.ltr.index',['type' => 'photo']) }}" class="transitionCls">Photos</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('website.ltr.index',['type' => 'video']) }}" class="transitionCls">Videos</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <ul class="hdrLftMnu">
                                    <li class="hdLftItem position-relative">
                                        <a href="#">
                                            <i class="icon-Profile-1"></i>
                                        </a>
                                    </li>
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
                    <form class="catSrchBx rounded-3"  method="GET" action="{{ route('website.ltr.search') }}">
                        <input
                            type="text"
                            class="form-control rounded-2"
                            placeholder="Search..."
                            name="query"
                        />
                        <button type="submit" class="btn rounded-2">
                            <span class="icon-Group-2358"></span>
                        </button>
                    </form>
                    @php
                        $categories = \App\Models\Category::getCategoriesForWebsiteMenu();
                    @endphp

                    <div class="catList">
                        @foreach($categories as $category)
                            <div class="catCol text-start">
                                <a href="{{ route('website.ltr.category',['slug' => $category['slug']]) }}">
                                    <strong>{{ $category['en_name'] }}</strong>
                                </a>

                                @if(count($category['children_recursive']) != 0)
                                    <ul>
                                        @foreach($category['children_recursive'] as $child)
                                            <li>
                                                <a href="{{ route('website.rtl.category',['slug' => $child['slug']]) }}">
                                                    {{ $child['en_name'] }}
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
