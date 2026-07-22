<footer class="position-relative">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="footerSec">
                    <a href="#" class="foterLogo">
                        <img
                            src="{{ asset('asset/img/footer_logo.svg') }}"
                            lightsrc="{{ asset('asset/img/footer_logo.svg') }}"
                            darksrc="{{ asset('asset/img/footer_logo2.svg') }}"
                            class="chngThemImg"
                            alt="logo"
                        />
                    </a>
                    <ul class="fotrSocial">
                        @php
                            $social = \App\Helpers\SettingHelper::getWebsiteSocial();
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
                        @if($social['x'])
                            <li>
                                <a href="{{ $social['x'] }}" target="_blank" class="transitionCls">
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
                                <a href="{{ route('website.rtl.category',['slug' => $category['slug']]) }}"
                                   class="transitionCls">
                                    {{ $category['title'] }}
                                </a>
                            </li>
                        @endforeach
                        <br/>
                    </ul>
                    @php
                        $footerMenuItems = \App\Models\Menu::forLocation('footer');
                    @endphp
                    @if(count($footerMenuItems) > 0)
                        <ul class="fotrLinks">
                            @foreach($footerMenuItems as $menuItem)
                                <li>
                                    @if($menuItem['type'] === 'separator')
                                        <span class="transitionCls">{{ $menuItem['title'] }}</span>
                                    @else
                                        <a href="{{ \App\Models\Menu::resolveUrl($menuItem['url']) }}"
                                           target="{{ $menuItem['target'] ?? '_self' }}"
                                           class="transitionCls">{{ $menuItem['title'] }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                    <ul class="fotrLinks">
                        <li>
                            <a href="{{ route('website.rtl.contact') }}" class="transitionCls">تماس با ما</a>
                        </li>
                        <li>
                            <a href="{{ route('website.rtl.about') }}" class="transitionCls">درباره ما</a>
                        </li>
                        <li>
                            <a href="{{ route('website.rtl.index',['type' => 'photo']) }}" class="transitionCls">عکس</a>
                        </li>
                        <li>
                            <a href="{{ route('website.rtl.index',['type' => 'video']) }}" class="transitionCls">فیلم</a>
                        </li>
                    </ul>
                    @endif
                </div>
                <div id="div_eRasanehTrustseal_87336"></div>
                <script src="https://trustseal.e-rasaneh.ir/trustseal.js"></script>
                <script>eRasaneh_Trustseal(87336, true);</script>

                <div id="div_eRasanehTrustseal_87336"></div>
                <script src="https://trustseal.e-rasaneh.ir/trustseal.js"></script>
                <script>eRasaneh_Trustseal(87336, false);</script>

            </div>
        </div>
    </div>
</footer>

@vite(['resources/js/website.js'])

<script src="{{ asset('asset/js/jquery.min.js') }}"></script>
<script src="{{ asset('asset/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('asset/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('asset/js/lg-video.min.js') }}"></script>

@livewireScripts

<x-livewire-alert::scripts />

