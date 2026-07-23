<footer class="position-relative">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="footerSec">
                    <a href="{{ route('website.ltr.home') }}" class="foterLogo">
                        <img
                            src="/asset/img/footer_logo.svg"
                            lightSrc="/asset/img/footer_logo.svg"
                            darkSrc="/asset/img/footer_logo2.svg"
                            class="chngThemImg"
                            alt="logo"
                        />
                    </a>
                    <ul class="fotrSocial">
                        @php
                            // Same settings source as the RTL pages; 'x' holds the Twitter/X link.
                            $social = array_merge(
                                ['telegram' => null, 'linkedin' => null, 'x' => null, 'twitter' => null, 'youtube' => null, 'instagram' => null],
                                (array) (\App\Helpers\SettingHelper::getWebsiteSocial() ?? [])
                            );
                            $social['twitter'] = $social['twitter'] ?? $social['x'];
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

                    @php
                        $categories = \App\Models\Category::getCategoriesForWebsiteMenu();
                    @endphp


                    <ul class="fotrLinks" style="margin-bottom: 0">
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('website.ltr.category',['slug' => $category['slug']]) }}"
                                   class="transitionCls">
                                    {{ $category['en_title'] ?? $category['title'] }}
                                </a>
                            </li>
                        @endforeach
                        <br/>
                    </ul>

                    <ul class="fotrLinks">
                        <li>
                            <a href="{{ route('website.ltr.index',['type' => 'video']) }}" class="transitionCls">Videos</a>
                        </li>
                        <li>
                            <a href="{{ route('website.ltr.index',['type' => 'photo']) }}" class="transitionCls">Photos</a>
                        </li>
                        <li>
                            <a href="{{ route('website.ltr.about') }}" class="transitionCls">About Us</a>
                        </li>
                        <li> 
                            <a href="{{ route('website.ltr.contact') }}" class="transitionCls">Contact Us</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="{{ asset('asset/js/jquery.min.js') }}"></script>
<script src="{{ asset('asset/js/bootstrap.min.js') }}"></script>

<script src="{{ asset('asset/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('asset/js/main.js') }}"></script>

<script src="{{ asset('asset/js/tabs.js') }}"></script>

<script src="{{ asset('asset/js/audioplayer.js') }}"></script>
<script src="{{ asset('asset/js/lg-video.min.js') }}"></script>
<script src="{{ asset('asset/js/video.js') }}"></script>
