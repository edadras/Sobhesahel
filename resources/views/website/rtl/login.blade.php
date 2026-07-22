<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ورود / ثبت نام | صبح ساحل</title>
    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('asset/css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('asset/css/main-rtl.css') }}" />
    <link rel="stylesheet" href="{{ asset('asset/css/archive-rtl.css') }}" />
    <link rel="stylesheet" href="{{ asset('asset/css/icomoon.css') }}" />

    <link rel="icon" href="{{ asset('asset/img/logo.png') }}" />
</head>

<body>
<section class="loginSec" id="app">
    <div class="container" v-if="page === 'main'">
        <div class="row">
            <div class="col-12">
                <div class="loginSecBx">
                    <a href="#" class="loginLogo">
                        <img
                            src="{{ asset('asset/img/footer_logo.svg') }}"
                            lightsrc="{{ asset('asset/img/footer_logo.svg') }}"
                            darksrc="{{ asset('asset/img/footer_logo2.svg') }}"
                            class="chngThemImg"
                            alt="logo"
                        />
                    </a>
                    <h1>ورود یا ثبت‌نام</h1>
                    <form @submit.prevent="otp_login(false)">
                        <div class="mb-3">
                            <label for="logInpt1" class="form-label"
                            >شماره تلفن همراه خود را وارد کنید</label
                            >
                            <input
                                v-model="mobile"
                                type="text"
                                class="form-control frmNum"
                                id="logInpt1"
                                placeholder=""
                            />
                        </div>
                        <button type="submit"  class="btn transitionCls">
                            ورود به صبح ساحل
                        </button>
                    </form>
                    <div class="logFrmText">
                        <p>ورود شما به منزله‌ی پذیرش</p>
                        <a href="#">قوانین و مقررات</a>
                        <p>است.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container" v-if="page === 'verify'">
        <form action="#" ref="post_login" method="POST">
            <input type="hidden" ref="mobile">
            <input type="hidden" ref="code">
            {{csrf_field()}}
        </form>

        <div class="row">
            <div class="col-12">
                <div class="loginSecBx">
                    <a href="#" class="loginLogo">
                        <img
                            src="{{ asset('asset/img/footer_logo.svg') }}"
                            lightsrc="{{ asset('asset/img/footer_logo.svg') }}"
                            darksrc="{{ asset('asset/img/footer_logo2.svg') }}"
                            class="chngThemImg"
                            alt="logo"
                        />
                    </a>
                    <h1>کد تایید</h1>
                    <form @submit.prevent="verify_otp">
                        <label for="vcode1" class="form-label">
                            کد ارسال شده به شماره <strong>@{{ mobile }}</strong> را وارد
                            کنید
                        </label>
                        <div class="enterPhonBx">
                            <input
                                autocomplete="off"
                                type="tel"
                                pattern="[0-9]"
                                class="input-space"
                                v-model="vcode[0]"
                                maxlength="1"
                            />
                            <input
                                autocomplete="off"
                                type="tel"
                                pattern="[0-9]"
                                class="input-space"
                                v-model="vcode[1]"
                                maxlength="1"
                            />
                            <input
                                autocomplete="off"
                                type="tel"
                                pattern="[0-9]"
                                class="input-space"
                                v-model="vcode[2]"
                                maxlength="1"
                            />
                            <input
                                autocomplete="off"
                                type="tel"
                                pattern="[0-9]"
                                class="input-space"
                                v-model="vcode[3]"
                                maxlength="1"
                            />
                            <input
                                autocomplete="off"
                                type="tel"
                                pattern="[0-9]"
                                class="input-space"
                                v-model="vcode[4]"
                                maxlength="1"
                            />
                            <input
                                autocomplete="off"
                                type="tel"
                                pattern="[0-9]"
                                class="input-space"
                                v-model="vcode[5]"
                                maxlength="1"
                            />
                        </div>

                        <div class="resendTime">
                            <p class="countdown" v-if="resend_code_in > 0">
                                ارسال مجدد کد در
                                @{{ resend_code_in }}
                            </p>
                            <p v-else class="countdown" @click="otp_login(true)">
                                ارسال مجدد
                            </p>
                        </div>
                        <button type="submit" class="btn transitionCls">
                            ورود به صبح ساحل
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="{{ asset('asset/js/jquery.min.js') }}"></script>
<script src="{{ asset('asset/js/bootstrap.min.js') }}"></script>

<script src="{{ asset('asset/js/main.js') }}"></script>
<script src="{{ asset('js/login.js?ver=' . env('JS_ASSET_VER')) }}"></script>
</body>
</html>
