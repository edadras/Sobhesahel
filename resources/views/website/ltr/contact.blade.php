@extends('website.ltr.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/archive-ltr.css') }}"/>
@endsection

@section('script')
    <script src="{{ asset('js/contact.js?ver=' . env('JS_ASSET_VER')) }}"></script>
@endsection

@section('content')
    <section class="contactSec">
        <div class="container" id="contact" data-lang="en">
            <div class="row">
                <div class="col-12">
                    <div class="contactRow">
                        <div class="sendMsgBox">
                            <h2>Send Message</h2>
                            <p>
                                Your comments, suggestions and constructive criticism are always very valuable for us. Therefore, if you have any questions, criticisms and suggestions, please share them with us through the form below.
                            </p>
                            <form @submit.prevent="send" class="row g-3">
                                <div class="col-md-6">
                                    <label for="inpt01" class="form-label">Name and Family</label>
                                    <input v-model="contact.name" type="text" class="form-control" id="inpt01">
                                </div>
                                <div class="col-md-6">
                                    <label for="inpt02" class="form-label">Email</label>
                                    <input v-model="contact.email" type="email" class="form-control" id="inpt02">
                                </div>
                                <div class="col-12">
                                    <label for="inpt03" class="form-label">Text</label>
                                    <textarea v-model="contact.text" class="form-control" id="inpt03" rows="3"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn transitionCls">
                                        Send
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="contactBox">
                            <h2>Contact us</h2>
                            <div class="through">

                                @php
                                    // Same settings source as the RTL contact page (fa_* keys there, en_* keys here).
                                    $contact = (array) (setting('contact') ?? []);
                                @endphp
                                <div class="right">
                                    <a href="{{ $contact['en_whatsapp'] ?? $contact['fa_whatsapp'] ?? '#' }}" class="smallBx box transitionCls">
                                        <div class="flex-end">
                                            <p>Whatsapp</p>
                                            <i>Click...</i>
                                        </div>
                                        <span class="icon-Whatsapp"></span>
                                    </a>
                                    <div class="bigBx box transitionCls">
                                        <div class="flex-end">
                                            <p>Address</p>
                                            <i>
                                               {{ $contact['en_address'] ?? '' }}
                                            </i>
                                        </div>
                                        <span class="icon-Location-1"></span>
                                    </div>
                                    <a href="#" class="smallBx box transitionCls">
                                        <div class="flex-end">
                                            <p>Phone number</p>
                                            <i>
                                                {{ $contact['en_phone'] ?? $contact['en_number'] ?? '' }}
                                            </i>
                                        </div>
                                        <span class="icon-Call-1"></span>
                                    </a>
                                    <a href="#" class="bigBx box transitionCls">
                                        <div class="flex-end">
                                            <p>Email</p>
                                            <i>
                                                {{ $contact['en_email'] ?? '' }}
                                            </i>
                                        </div>
                                        <span class="icon-mail-2"></span>
                                    </a>
                                </div>
                                <div class="left">
                                    <ul>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
