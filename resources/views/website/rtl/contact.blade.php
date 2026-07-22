@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/archive-rtl.css') }}"/>
@endsection

@section('script')
    <script src="{{ asset('js/contact.js?ver=' . env('JS_ASSET_VER')) }}"></script>
@endsection



@section('content')
    <section class="contactSec">
        <div class="container" id="contact" data-lang="fa">
            <div class="row">
                <div class="col-12">
                    <div class="contactRow">
                        <div class="sendMsgBox">
                            <h2>ارسال پیام</h2>
                            <p>
                                همواره نظرات، پیشنهادات و انتقادات سازنده شما برای ما بسیار
                                ارزشمند است. از اینرو هر گونه پرسش، انتقاد و پیشنهادی دارید از
                                طریق فرم زیر با ما در میان بگذارید.
                            </p>
                            @livewire('contact-form')
                        </div>
                        <div class="contactBox">
                            <h2>ارتباط با ما</h2>
                            <div class="through">

                                @php
                                    $contact = setting('contact');
                                @endphp
                                <div class="right">
                                    <a href="{{ $contact['fa_whatsapp'] }}" class="smallBx box transitionCls">
                                        <div class="flex-end">
                                            <p>از طریق واتس اپ</p>
                                            <i>کلیک کنید...</i>
                                        </div>
                                        <span class="icon-Whatsapp"></span>
                                    </a>
                                    <div class="bigBx box transitionCls">
                                        <div class="flex-end">
                                            <p>موقعیت مکانی</p>
                                            <i>
                                               {{ $contact['fa_address'] }}
                                            </i>
                                        </div>
                                        <span class="icon-Location-1"></span>
                                    </div>
                                    <a href="#" class="smallBx box transitionCls">
                                        <div class="flex-end">
                                            <p>از طریق شماره تلفن</p>
                                            <i>
                                                {{ $contact['fa_phone'] }}
                                            </i>
                                        </div>
                                        <span class="icon-Call-1"></span>
                                    </a>
                                    <a href="#" class="bigBx box transitionCls">
                                        <div class="flex-end">
                                            <p>ایمیل</p>
                                            <i>
                                                {{ $contact['fa_email'] }}
                                            </i>
                                        </div>
                                        <span class="icon-mail-2"></span>
                                    </a>
                                </div>
                                <div class="left">
                                    <ul>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
