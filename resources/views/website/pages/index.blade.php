<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>sobhe-sahel</title>
    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/animate.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/main-rtl.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/icomoon.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/swiper-bundle.min.css') }}"/>

    <link rel="stylesheet" href="{{ asset('asset/css/audioplayer.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/lg-video.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/video-js.css') }}"/>

    <link rel="icon" href="{{ asset('asset/img/logo.png') }}"/>
</head>

<body>


<header class="position-relative">
    <div class="headerTop">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="hdrTopBx">
                        <div class="hdrTopRght">
                            <div class="hdrDateBx">
                                <strong>26</strong>
                                <div>
                                    <p>مهر</p>
                                    <p>1402</p>
                                </div>
                            </div>
                            <ul class="hdrTopLnks">
                                <li>
                                    <a href="#" class="transitionCls">
                                        <span class="icon-Telegram"></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="transitionCls">
                                        <span class="icon-Linkedin"></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="transitionCls">
                                        <span class="icon-Twitter-X-1"></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="transitionCls">
                                        <span class="icon-Youtube"></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="transitionCls">
                                        <span class="icon-Instagram"></span>
                                    </a>
                                </li>
                            </ul>
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
                                            <a href="#">
                                                <p>English</p>
                                            </a>
                                        </li>
                                        <li class="faDirction">
                                            <a href="#">
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

    @auth
        <div class="headerTop">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="hdrTopBx">
                            <div class="hdrTopRght">
                                <div class="hdrDateBx">
                                    <strong>26</strong>
                                    <div>
                                        <p>مهر</p>
                                        <p>1402</p>
                                    </div>
                                </div>
                                <ul class="hdrTopLnks">
                                    <li>
                                        <a href="#" class="transitionCls">
                                            <span class="icon-Telegram"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="transitionCls">
                                            <span class="icon-Linkedin"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="transitionCls">
                                            <span class="icon-Twitter-X-1"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="transitionCls">
                                            <span class="icon-Youtube"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="transitionCls">
                                            <span class="icon-Instagram"></span>
                                        </a>
                                    </li>
                                </ul>
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
                                                <a href="#">
                                                    <p>English</p>
                                                </a>
                                            </li>
                                            <li class="faDirction">
                                                <a href="#">
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
        alksdjakldjs 
    @endauth

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
                            <a href="#" class="hdrLogo">
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
                                        <ul class="hedrMnuUl">
                                            <li>
                                                <a href="#" class="transitionCls active">خانه</a>
                                            </li>
                                            <li>
                                                <a href="#" class="transitionCls">اخبار من</a>
                                            </li>
                                            <li>
                                                <a href="#" class="transitionCls">یادداشت‌ها</a>
                                            </li>
                                            <li>
                                                <a href="#" class="transitionCls"
                                                >آرشیو روزنامه‌ها</a
                                                >
                                            </li>
                                            <li>
                                                <a href="#" class="transitionCls">پادکست</a>
                                            </li>
                                            <li>
                                                <a href="#" class="transitionCls">عکس</a>
                                            </li>
                                            <li>
                                                <a href="#" class="transitionCls">فیلم</a>
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
                                    <li class="hdLftItem position-relative srchIcon">
                                        <span class="icon-Group-43"></span>
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
                    <form class="catSrchBx rounded-3">
                        <input
                            type="text"
                            class="form-control rounded-2"
                            placeholder="جستجو..."
                        />
                        <button type="submit" class="btn rounded-2">
                            <span class="icon-Group-2360"></span>
                        </button>
                    </form>
                    <div class="catList">
                        <div class="catCol text-end">
                            <strong>ورزشی</strong>
                            <ul>
                                <li>
                                    <a href="#">فوتبال</a>
                                </li>
                                <li>
                                    <a href="#">هندبال</a>
                                </li>
                                <li>
                                    <a href="#">بسکتبال</a>
                                </li>
                                <li>
                                    <a href="#">بانوان</a>
                                </li>
                                <li>
                                    <a href="#">پارالمپیک</a>
                                </li>
                                <li>
                                    <a href="#">المپیک</a>
                                </li>
                            </ul>
                        </div>
                        <div class="catCol text-end">
                            <strong>فرهنگ و هنر</strong>
                            <ul>
                                <li>
                                    <a href="#">تاریخ</a>
                                </li>
                                <li>
                                    <a href="#">سبک زندگی</a>
                                </li>
                                <li>
                                    <a href="#">نقاشی</a>
                                </li>
                                <li>
                                    <a href="#">شرق</a>
                                </li>
                                <li>
                                    <a href="#">تمدن ایرانی</a>
                                </li>
                                <li>
                                    <a href="#">ادبیات</a>
                                </li>
                                <li>
                                    <a href="#">هنر</a>
                                </li>
                            </ul>
                        </div>
                        <div class="catCol text-end">
                            <strong>اقتصاد</strong>
                            <ul>
                                <li>
                                    <a href="#">دلار</a>
                                </li>
                                <li>
                                    <a href="#">ارزش طلا</a>
                                </li>
                                <li>
                                    <a href="#">اقتصاد</a>
                                </li>
                            </ul>
                        </div>
                        <div class="catCol text-end">
                            <strong>ویدئو</strong>
                            <ul>
                                <li>
                                    <a href="#">فوتبال</a>
                                </li>
                                <li>
                                    <a href="#">هندبال</a>
                                </li>
                                <li>
                                    <a href="#">بسکتبال</a>
                                </li>
                                <li>
                                    <a href="#">بانوان</a>
                                </li>
                                <li>
                                    <a href="#">پارالمپیک</a>
                                </li>
                                <li>
                                    <a href="#">المپیک</a>
                                </li>
                            </ul>
                        </div>
                        <div class="catCol text-end">
                            <strong>سیاست</strong>
                            <ul>
                                <li>
                                    <a href="#">تاریخ</a>
                                </li>
                                <li>
                                    <a href="#">سبک زندگی</a>
                                </li>
                                <li>
                                    <a href="#">هنر</a>
                                </li>
                                <li>
                                    <a href="#">غرب</a>
                                </li>
                                <li>
                                    <a href="#">شرق</a>
                                </li>
                            </ul>
                        </div>
                        <div class="catCol text-end">
                            <strong>تکنولوژی</strong>
                            <ul>
                                <li>
                                    <a href="#">موبایل</a>
                                </li>
                                <li>
                                    <a href="#">برنامه نویسی</a>
                                </li>
                                <li>
                                    <a href="#">سیستم</a>
                                </li>
                                <li>
                                    <a href="#">تبلت</a>
                                </li>
                            </ul>
                        </div>
                        <div class="catCol text-end">
                            <strong>تصاویر</strong>
                            <ul>
                                <li>
                                    <a href="#">فوتبال</a>
                                </li>
                                <li>
                                    <a href="#">هندبال</a>
                                </li>
                                <li>
                                    <a href="#">بسکتبال</a>
                                </li>
                                <li>
                                    <a href="#">بانوان</a>
                                </li>
                                <li>
                                    <a href="#">پارالمپیک</a>
                                </li>
                                <li>
                                    <a href="#">المپیک</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="topSec">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="topSecBx">
                    <div class="topSecRght">
                        <div class="swiper">
                            <div class="swiper-wrapper">
                                <a href="#" class="swiper-slide transitionCls">
                                    <img src="{{ asset('asset/img/image-01.jpg') }}" alt="img"/>
                                </a>
                                <a href="#" class="swiper-slide transitionCls">
                                    <img src="{{ asset('asset/img/image-01.jpg') }}" alt="img"/>
                                </a>
                            </div>
                            <div class="topRghtNav">
                                <div class="swiper-button-prev"></div>
                                <div>
                                    <p>روزنامه صبح ساحل</p>
                                    <ul>
                                        <li>شماره 4695</li>
                                        <hr/>
                                        <li>11 آبان 1402</li>
                                    </ul>
                                </div>
                                <div class="swiper-button-next"></div>
                            </div>
                        </div>
                    </div>
                    <div class="topSecLft">
                        <div class="topLftSldr">
                            <div class="swiper">
                                <div class="swiper-wrapper">
                                    <a href="#" class="swiper-slide transitionCls">
                                        <div class="topLftImg">
                                            <img src="{{ asset('asset/img/news01.jpg') }}" alt="img"/>
                                        </div>
                                        <div class="topLftInfo">
                          <span>
                            علیرضا علوی تبار در گفت‌و‌گو با «شبکه شرق» حوادث سال
                            گذشته را بررسی کرد- بخش اول؛
                          </span>
                                            <strong>
                                                دولت رئیسی به نتیجه رسیده است که باید موضوع برجام حل
                                                و فصل شود
                                            </strong>
                                            <p>
                                                محمد صدر از اعضای مجمع تشخیص مصلحت نظام در در مورد
                                                تمایل دولت به حل و فصل موضوع برجام و FATF می‌گوید: «
                                                معتقدم که اگر دولت فعلی خواهان پیوستن ایران به FATF
                                                باشد مجمع تشخیص مصلحت نظام با آن موافقت می‌کند و
                                                مسائل به سمت حل شدن پیش می‌رود.»
                                            </p>
                                        </div>
                                    </a>
                                    <a href="#" class="swiper-slide transitionCls">
                                        <div class="topLftImg">
                                            <img src="{{ asset('asset/img/news02.jpg') }}" alt="img"/>
                                        </div>
                                        <div class="topLftInfo">
                          <span>
                            علیرضا علوی تبار در گفت‌و‌گو با «شبکه شرق» حوادث سال
                            گذشته را بررسی کرد- بخش اول؛
                          </span>
                                            <strong>
                                                دولت رئیسی به نتیجه رسیده است که باید موضوع برجام حل
                                                و فصل شود
                                            </strong>
                                            <p>
                                                محمد صدر از اعضای مجمع تشخیص مصلحت نظام در در مورد
                                                تمایل دولت به حل و فصل موضوع برجام و FATF می‌گوید: «
                                                معتقدم که اگر دولت فعلی خواهان پیوستن ایران به FATF
                                                باشد مجمع تشخیص مصلحت نظام با آن موافقت می‌کند و
                                                مسائل به سمت حل شدن پیش می‌رود.»
                                            </p>
                                        </div>
                                    </a>
                                    <a href="#" class="swiper-slide transitionCls">
                                        <div class="topLftImg">
                                            <img src="{{ asset('asset/img/news03.jpg') }}" alt="img"/>
                                        </div>
                                        <div class="topLftInfo">
                          <span>
                            علیرضا علوی تبار در گفت‌و‌گو با «شبکه شرق» حوادث سال
                            گذشته را بررسی کرد- بخش اول؛
                          </span>
                                            <strong>
                                                دولت رئیسی به نتیجه رسیده است که باید موضوع برجام حل
                                                و فصل شود
                                            </strong>
                                            <p>
                                                محمد صدر از اعضای مجمع تشخیص مصلحت نظام در در مورد
                                                تمایل دولت به حل و فصل موضوع برجام و FATF می‌گوید: «
                                                معتقدم که اگر دولت فعلی خواهان پیوستن ایران به FATF
                                                باشد مجمع تشخیص مصلحت نظام با آن موافقت می‌کند و
                                                مسائل به سمت حل شدن پیش می‌رود.»
                                            </p>
                                        </div>
                                    </a>
                                </div>
                                <div class="topleftNav">
                                    <div class="topLftPgSc">
                                        <div class="swiper-pagination"></div>
                                        <div class="swiper-scrollbar"></div>
                                    </div>
                                    <div class="topLftBtns">
                                        <div class="swiper-button-prev"></div>
                                        <div class="swiper-button-next"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="topNewsRow">
                            <div class="topNewsBx position-relative">
                                <img
                                    src="{{ asset('asset/img/img01.jpg') }}"
                                    class="position-relative"
                                    alt="img"
                                />
                                <div class="topNewsCvr transitionCls">
                                    <p>
                                        دست خالی فوتبال ایران در شب انتخاب‌ ترین‌های آسیا
                                        تماشاچی پر از هیاهو
                                    </p>
                                </div>
                            </div>
                            <div class="topNewsBx position-relative">
                                <img
                                    src="{{ asset('asset/img/img02.jpg') }}"
                                    class="position-relative"
                                    alt="img"
                                />
                                <div class="topNewsCvr transitionCls">
                                    <p>
                                        دست خالی فوتبال ایران در شب انتخاب‌ ترین‌های آسیا
                                        تماشاچی پر از هیاهو
                                    </p>
                                </div>
                            </div>
                            <div class="topNewsBx position-relative">
                                <img
                                    src="{{ asset('asset/img/img03.jpg') }}"
                                    class="position-relative"
                                    alt="img"
                                />
                                <div class="topNewsCvr transitionCls">
                                    <p>
                                        دست خالی فوتبال ایران در شب انتخاب‌ ترین‌های آسیا
                                        تماشاچی پر از هیاهو
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="row">
        <div class="col-12">
            <a href="#" class="ads_bar">
                <img src="{{ asset('asset/img/ads_bar.jpg') }}" alt="ads"/>
            </a>
        </div>
    </div>
</div>

<section class="catOneSec">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="catOneRow">
                    <div class="catOneBox articlsCat">
                        <a href="#" class="head">سر مقاله ها</a>
                        <div class="body">
                            <div class="swiper">
                                <div class="swiper-wrapper">
                                    <a href="#" class="swiper-slide">
                                        <div class="artclAuthor">
                                            <i><img src="{{ asset('asset/img/user.png') }}" alt="user"/></i>
                                            <div class="text-end">
                                                <p>سیدمصطفی هاشمی‌طبا</p>
                                                <span>نویسنده و روزنامه‌نگار</span>
                                            </div>
                                        </div>
                                        <div class="artclText">
                                            <h2>ثروتمند ناتوان در میدان</h2>
                                            <p>
                                                متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                                استفاده از طراحان گرافیک است. چاپگرها و متون بلکه
                                                روزنامه و مجله در ستون و سطرآنچنان که لازم است.
                                                طراحان گرافیک تولید سادگی نامفهوم از صنعت چاپ و با
                                                استفاده از طراحان گرافیک است.
                                            </p>
                                        </div>
                                    </a>
                                    <a href="#" class="swiper-slide">
                                        <div class="artclAuthor">
                                            <i><img src="{{ asset('asset/img/user.png') }}" alt="user"/></i>
                                            <div class="text-end">
                                                <p>سیدمصطفی هاشمی‌طبا</p>
                                                <span>نویسنده و روزنامه‌نگار</span>
                                            </div>
                                        </div>
                                        <div class="artclText">
                                            <h2>ثروتمند ناتوان در میدان</h2>
                                            <p>
                                                متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                                استفاده از طراحان گرافیک است. چاپگرها و متون بلکه
                                                روزنامه و مجله در ستون و سطرآنچنان که لازم است.
                                                طراحان گرافیک تولید سادگی نامفهوم از صنعت چاپ و با
                                                استفاده از طراحان گرافیک است.
                                            </p>
                                        </div>
                                    </a>
                                    <a href="#" class="swiper-slide">
                                        <div class="artclAuthor">
                                            <i><img src="{{ asset('asset/img/user.png') }}" alt="user"/></i>
                                            <div class="text-end">
                                                <p>سیدمصطفی هاشمی‌طبا</p>
                                                <span>نویسنده و روزنامه‌نگار</span>
                                            </div>
                                        </div>
                                        <div class="artclText">
                                            <h2>ثروتمند ناتوان در میدان</h2>
                                            <p>
                                                متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                                استفاده از طراحان گرافیک است. چاپگرها و متون بلکه
                                                روزنامه و مجله در ستون و سطرآنچنان که لازم است.
                                                طراحان گرافیک تولید سادگی نامفهوم از صنعت چاپ و با
                                                استفاده از طراحان گرافیک است.
                                            </p>
                                        </div>
                                    </a>
                                </div>
                                <div class="swiper-pagination"></div>
                            </div>
                        </div>
                    </div>
                    <div class="catOneBox notesCat">
                        <a href="#" class="head">یادداشت ها</a>
                        <div class="body">
                            <ul class="notesList">
                                <li>
                                    <a href="#">
                                        <i>
                                            <img src="{{ asset('asset/img/user.png') }}" alt="user"/>
                                        </i>
                                        <div class="text-end">
                                            <p>هزار لاله نشکفته در لاله‌زار</p>
                                            <span>سیدمصطفی هاشمی‌طبا</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i>
                                            <img src="{{ asset('asset/img/user.png') }}" alt="user"/>
                                        </i>
                                        <div class="text-end">
                                            <p>هزار لاله نشکفته در لاله‌زار</p>
                                            <span>سیدمصطفی هاشمی‌طبا</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i>
                                            <img src="{{ asset('asset/img/user.png') }}" alt="user"/>
                                        </i>
                                        <div class="text-end">
                                            <p>هزار لاله نشکفته در لاله‌زار</p>
                                            <span>سیدمصطفی هاشمی‌طبا</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i>
                                            <img src="{{ asset('asset/img/user.png') }}" alt="user"/>
                                        </i>
                                        <div class="text-end">
                                            <p>هزار لاله نشکفته در لاله‌زار</p>
                                            <span>سیدمصطفی هاشمی‌طبا</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i>
                                            <img src="{{ asset('asset/img/user.png') }}" alt="user"/>
                                        </i>
                                        <div class="text-end">
                                            <p>هزار لاله نشکفته در لاله‌زار</p>
                                            <span>سیدمصطفی هاشمی‌طبا</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i>
                                            <img src="{{ asset('asset/img/user.png') }}" alt="user"/>
                                        </i>
                                        <div class="text-end">
                                            <p>هزار لاله نشکفته در لاله‌زار</p>
                                            <span>سیدمصطفی هاشمی‌طبا</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i>
                                            <img src="{{ asset('asset/img/user.png') }}" alt="user"/>
                                        </i>
                                        <div class="text-end">
                                            <p>هزار لاله نشکفته در لاله‌زار</p>
                                            <span>سیدمصطفی هاشمی‌طبا</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i>
                                            <img src="{{ asset('asset/img/user.png') }}" alt="user"/>
                                        </i>
                                        <div class="text-end">
                                            <p>هزار لاله نشکفته در لاله‌زار</p>
                                            <span>سیدمصطفی هاشمی‌طبا</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="catOneBox podcastCat">
                        <div class="podRight">
                            <a href="#" class="head">پادکست</a>
                            <div class="body">
                                <div class="podcstUser">
                                    <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                    <div class="text-end">
                                        <p>سیاست شکست‌خورده</p>
                                        <span>قسمت اول</span>
                                    </div>
                                </div>
                                <div class="pdcstAudio position-relative">
                                    <div class="audioList">
                                        <audio src="{{ asset('asset/songs/2.mp3') }}"></audio>
                                        <audio src="{{ asset('asset/songs/2.mp3') }}"></audio>
                                        <audio src="{{ asset('asset/songs/2.mp3') }}"></audio>
                                    </div>
                                    <div class="app-cover">
                                        <div class="player">
                                            <div class="player-track">
                                                <div id="track-time">
                                                    <div id="track-length"></div>
                                                    <div id="current-time"></div>
                                                </div>
                                                <div id="s-area">
                                                    <div id="ins-time"></div>
                                                    <div id="s-hover"></div>
                                                    <div id="seek-bar"></div>
                                                </div>
                                            </div>
                                            <div class="player-content">
                                                <div class="player-controls">
                                                    <div class="control">
                                                        <div class="button transitionCls play-previous">
                                                            <i class="icon-Play-7-1 transitionCls"></i>
                                                        </div>
                                                    </div>
                                                    <div class="control">
                                                        <div
                                                            class="button transitionCls play-pause-button"
                                                        >
                                                            <i
                                                                class="icon-play-circle-rounded transitionCls"
                                                            ></i>
                                                        </div>
                                                    </div>
                                                    <div class="control">
                                                        <div class="button transitionCls play-next">
                                                            <i class="icon-Play-8-1 transitionCls"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pdcstCatImg">
                            <img src="{{ asset('asset/img/img04.jpg') }}" alt="img"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="catTwoSec">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="catTwoRow">
                    <div class="catTwoBox">
                        <div class="head homPgTitle">
                            <span></span>
                            <p>سیاست</p>
                        </div>
                        <a href="#" class="top position-relative transitionCls">
                            <img
                                src="{{ asset('asset/img/news03.jpg') }}"
                                class="position-relative"
                                alt="img"
                            />
                            <div>
                                <h2>امن‌ترین نقشه اسرائیل دیگر امن نیست</h2>
                                <p>3 روز قبل</p>
                            </div>
                        </a>
                        <div class="body">
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="catTwoBox">
                        <div class="head homPgTitle">
                            <span></span>
                            <p>اقتصاد</p>
                        </div>
                        <a href="#" class="top position-relative transitionCls">
                            <img
                                src="{{ asset('asset/img/img03.jpg') }}"
                                class="position-relative"
                                alt="img"
                            />
                            <div>
                                <h2>امن‌ترین نقشه اسرائیل دیگر امن نیست</h2>
                                <p>3 روز قبل</p>
                            </div>
                        </a>
                        <div class="body">
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="catTwoBox">
                        <div class="head homPgTitle">
                            <span></span>
                            <p>اجتماعی</p>
                        </div>
                        <a href="#" class="top position-relative transitionCls">
                            <img
                                src="{{ asset('asset/img/img02.jpg') }}"
                                class="position-relative"
                                alt="img"
                            />
                            <div>
                                <h2>امن‌ترین نقشه اسرائیل دیگر امن نیست</h2>
                                <p>3 روز قبل</p>
                            </div>
                        </a>
                        <div class="body">
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mediaSec position-relative mb-5">
    <div class="mediaSecBg position-absolute"></div>
    <div class="container position-relative">
        <div class="row">
            <div class="col-12">
                <div class="homPgTitle">
                    <span></span>
                    <strong>ویدئو ها</strong>
                </div>
                <div class="videoSecBx">
                    <div class="videoList">
                        <a href="#" class="transitionCls">
                            <div class="position-relative">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <p class="position-absolute">
                                    <small> 15:20 </small>
                                    <span class="icon-Player---Play---SVG-1"></span>
                                </p>
                            </div>
                            <i
                            >اینک پوپولیست‌ها باید بیش از دیگران نگران پوپولیسم باشند.
                                توده‌های بزرگی که در چند ‌...</i
                            >
                        </a>
                        <a href="#" class="transitionCls">
                            <div class="position-relative">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <p class="position-absolute">
                                    <small> 15:20 </small>
                                    <span class="icon-Player---Play---SVG-1"></span>
                                </p>
                            </div>
                            <i
                            >اینک پوپولیست‌ها باید بیش از دیگران نگران پوپولیسم باشند.
                                توده‌های بزرگی که در چند ‌...</i
                            >
                        </a>
                        <a href="#" class="transitionCls">
                            <div class="position-relative">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <p class="position-absolute">
                                    <small> 15:20 </small>
                                    <span class="icon-Player---Play---SVG-1"></span>
                                </p>
                            </div>
                            <i
                            >اینک پوپولیست‌ها باید بیش از دیگران نگران پوپولیسم باشند.
                                توده‌های بزرگی که در چند ‌...</i
                            >
                        </a>
                        <a href="#" class="transitionCls">
                            <div class="position-relative">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <p class="position-absolute">
                                    <small> 15:20 </small>
                                    <span class="icon-Player---Play---SVG-1"></span>
                                </p>
                            </div>
                            <i
                            >اینک پوپولیست‌ها باید بیش از دیگران نگران پوپولیسم باشند.
                                توده‌های بزرگی که در چند ‌...</i
                            >
                        </a>
                        <a href="#" class="transitionCls">
                            <div class="position-relative">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <p class="position-absolute">
                                    <small> 15:20 </small>
                                    <span class="icon-Player---Play---SVG-1"></span>
                                </p>
                            </div>
                            <i
                            >اینک پوپولیست‌ها باید بیش از دیگران نگران پوپولیسم باشند.
                                توده‌های بزرگی که در چند ‌...</i
                            >
                        </a>
                    </div>
                    <div class="mainVideo position-relative">
                        <video
                            id="my-player-1"
                            class="video-js position-relative"
                            controls="true"
                            preload="auto"
                            poster="../asset/img/img06.jpg"
                            width="100%"
                            height="100%"
                            data-setup='{"fluid": false}'
                        >
                            <source src="{{ asset('asset/video/video1.mp4') }}" type="video/mp4"/>
                            Your browser does not support the video tag.
                        </video>
                        <div class="vidOverlay position-absolute">
                            <strong>پایان تلخ برای دختران امیدوار ایران</strong>
                            <p>
                                اینک پوپولیست‌ها باید بیش از دیگران نگران پوپولیسم باشند.
                                توده‌های بزرگی که در چند دهۀ اخیر به کار دولت‌ها آمده و آنان
                                را به قدرت رسانده یا مخالفان‌...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="catTwoSec">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="catTwoRow">
                    <div class="catTwoBox">
                        <div class="head homPgTitle">
                            <span></span>
                            <p>حوادث</p>
                        </div>
                        <a href="#" class="top position-relative transitionCls">
                            <img
                                src="{{ asset('asset/img/news01.jpg') }}"
                                class="position-relative"
                                alt="img"
                            />
                            <div>
                                <h2>امن‌ترین نقشه اسرائیل دیگر امن نیست</h2>
                                <p>3 روز قبل</p>
                            </div>
                        </a>
                        <div class="body">
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="catTwoBox">
                        <div class="head homPgTitle">
                            <span></span>
                            <p>ورزشی</p>
                        </div>
                        <a href="#" class="top position-relative transitionCls">
                            <img
                                src="{{ asset('asset/img/img02.jpg') }}"
                                class="position-relative"
                                alt="img"
                            />
                            <div>
                                <h2>امن‌ترین نقشه اسرائیل دیگر امن نیست</h2>
                                <p>3 روز قبل</p>
                            </div>
                        </a>
                        <div class="body">
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="catTwoBox">
                        <div class="head homPgTitle">
                            <span></span>
                            <p>صدای مردم</p>
                        </div>
                        <a href="#" class="top position-relative transitionCls">
                            <img
                                src="{{ asset('asset/img/news01.jpg') }}"
                                class="position-relative"
                                alt="img"
                            />
                            <div>
                                <h2>امن‌ترین نقشه اسرائیل دیگر امن نیست</h2>
                                <p>3 روز قبل</p>
                            </div>
                        </a>
                        <div class="body">
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                            <a href="#" class="transitionCls">
                                <img src="{{ asset('asset/img/user2.jpg') }}" alt="img"/>
                                <div class="text-end pt-1">
                                    <span>3 روز قبل</span>
                                    <p>
                                        متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با
                                        استفاده از طراحان گرافیک است.
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="citiesSec">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="homPgTitle">
                    <span></span>
                    <strong>شهرستان ها</strong>
                </div>
                <div class="citiesBox">
                    <a href="#" class="right transitionCls">
                        <img src="{{ asset('asset/img/img06.jpg') }}" alt="img"/>
                        <div class="body">
                            <strong>پایان تلخ برای دختران امیدوار ایران</strong>
                            <p>
                                اینک پوپولیست‌ها باید بیش از دیگران نگران پوپولیسم باشند.
                                توده‌های بزرگی که در چند دهۀ اخیر به کار دولت‌ها آمده و آنان
                                را به قدرت رسانده یا مخالفان‌...
                            </p>
                        </div>
                    </a>
                    <div class="left">
                        <a href="#" class="transitionCls">
                            <img src="{{ asset('asset/img/news03.jpg') }}" alt="img"/>
                            <div class="body">
                                <span>اصفهان</span>
                                <p>
                                    متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده
                                    از طراحان گرافیک است.
                                </p>
                            </div>
                        </a>
                        <a href="#" class="transitionCls">
                            <img src="{{ asset('asset/img/news03.jpg') }}" alt="img"/>
                            <div class="body">
                                <span>اصفهان</span>
                                <p>
                                    متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده
                                    از طراحان گرافیک است.
                                </p>
                            </div>
                        </a>
                        <a href="#" class="transitionCls">
                            <img src="{{ asset('asset/img/news03.jpg') }}" alt="img"/>
                            <div class="body">
                                <span>اصفهان</span>
                                <p>
                                    متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده
                                    از طراحان گرافیک است.
                                </p>
                            </div>
                        </a>
                        <a href="#" class="transitionCls">
                            <img src="{{ asset('asset/img/news03.jpg') }}" alt="img"/>
                            <div class="body">
                                <span>اصفهان</span>
                                <p>
                                    متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده
                                    از طراحان گرافیک است.
                                </p>
                            </div>
                        </a>
                        <a href="#" class="transitionCls">
                            <img src="{{ asset('asset/img/news03.jpg') }}" alt="img"/>
                            <div class="body">
                                <span>اصفهان</span>
                                <p>
                                    متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده
                                    از طراحان گرافیک است.
                                </p>
                            </div>
                        </a>
                        <a href="#" class="transitionCls">
                            <img src="{{ asset('asset/img/news03.jpg') }}" alt="img"/>
                            <div class="body">
                                <span>اصفهان</span>
                                <p>
                                    متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده
                                    از طراحان گرافیک است.
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gulfNewsSec">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="homPgTitle">
                    <span></span>
                    <strong>خبرهای حوزه خلیج فارس</strong>
                </div>
                <div class="gulfNewsBox">
                    <a href="#" class="right position-relative transitionCls">
                        <img
                            src="{{ asset('asset/img/img02.jpg') }}"
                            class="position-relative"
                            alt="img"
                        />
                        <div class="position-absolute">
                            <strong>پایان تلخ برای دختران امیدوار ایران</strong>
                            <p>تعمیرات و نگهداری ٣۶ پروژه پل‌ بزرگ آذربایجان‌غربی</p>
                        </div>
                    </a>
                    <div class="left">
                        <a href="#" class="transitionCls">
                            <img src="{{ asset('asset/img/news03.jpg') }}" alt="img"/>
                            <div>
                                <strong
                                >دیدار وزیران دفاع مصر و انگلیس و گفت وگو درباره تحولات
                                    منطقه</strong
                                >
                                <p>
                                    منابع خبری گزارش دادند، وزیران دفاع مصر و انگلیس در قاهره
                                    با هم دیدار و درباره آخرین تحولات منطقه و جهان گفت وگو
                                    کردند.
                                </p>
                            </div>
                        </a>
                        <a href="#" class="transitionCls">
                            <img src="{{ asset('asset/img/img09.jpg') }}" alt="img"/>
                            <div>
                                <strong
                                >دیدار وزیران دفاع مصر و انگلیس و گفت وگو درباره تحولات
                                    منطقه</strong
                                >
                                <p>
                                    منابع خبری گزارش دادند، وزیران دفاع مصر و انگلیس در قاهره
                                    با هم دیدار و درباره آخرین تحولات منطقه و جهان گفت وگو
                                    کردند.
                                </p>
                            </div>
                        </a>
                        <a href="#" class="transitionCls">
                            <img src="{{ asset('asset/img/news01.jpg') }}" alt="img"/>
                            <div>
                                <strong
                                >دیدار وزیران دفاع مصر و انگلیس و گفت وگو درباره تحولات
                                    منطقه</strong
                                >
                                <p>
                                    منابع خبری گزارش دادند، وزیران دفاع مصر و انگلیس در قاهره
                                    با هم دیدار و درباره آخرین تحولات منطقه و جهان گفت وگو
                                    کردند.
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mediaSec position-relative">
    <div class="mediaSecBg position-absolute"></div>
    <div class="container position-relative">
        <div class="row">
            <div class="col-12">
                <div class="homPgTitle">
                    <span></span>
                    <strong>عکس ها</strong>
                </div>
                <div class="imagSecBx">
                    <div class="top">
                        <a href="#" class="imageBx position-relative transitionCls">
                            <img
                                src="{{ asset('asset/img/img07.jpg') }}"
                                class="position-relative"
                                alt="img"
                            />
                            <div class="position-absolute">
                                <strong>پایان تلخ برای دختران امیدوار ایران</strong>
                                <p>
                                    اینک پوپولیست‌ها باید بیش از دیگران نگران پوپولیسم باشند.
                                    توده‌های بزرگی که در چند دهۀ اخیر به کار دولت‌ها آمده و
                                    آنان را به قدرت رسانده یا مخالفان‌...
                                </p>
                            </div>
                        </a>
                        <a href="#" class="imageBx position-relative transitionCls">
                            <img
                                src="{{ asset('asset/img/img06.jpg') }}"
                                class="position-relative"
                                alt="img"
                            />
                            <div class="position-absolute">
                                <strong>پایان تلخ برای دختران امیدوار ایران</strong>
                                <p>
                                    اینک پوپولیست‌ها باید بیش از دیگران نگران پوپولیسم باشند.
                                    توده‌های بزرگی که در چند دهۀ اخیر به کار دولت‌ها آمده و
                                    آنان را به قدرت رسانده یا مخالفان‌...
                                </p>
                            </div>
                        </a>
                    </div>
                    <div class="bottom">
                        <a href="#" class="imgCard transitionCls">
                            <img src="{{ asset('asset/img/img08.jpg') }}" alt="img"/>
                            <div class="body">
                                <span>3 روز قبل</span>
                                <p>تعمیرات و نگهداری ٣۶ پروژه پل‌ بزرگ آذربایجان‌غربی</p>
                            </div>
                        </a>
                        <a href="#" class="imgCard transitionCls">
                            <img src="{{ asset('asset/img/img09.jpg') }}" alt="img"/>
                            <div class="body">
                                <span>3 روز قبل</span>
                                <p>تعمیرات و نگهداری ٣۶ پروژه پل‌ بزرگ آذربایجان‌غربی</p>
                            </div>
                        </a>
                        <a href="#" class="imgCard transitionCls">
                            <img src="{{ asset('asset/img/img07.jpg') }}" alt="img"/>
                            <div class="body">
                                <span>3 روز قبل</span>
                                <p>تعمیرات و نگهداری ٣۶ پروژه پل‌ بزرگ آذربایجان‌غربی</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="floutNews">
    <div>
        <i></i>
        <strong>خـبر فوری:</strong>
    </div>
    <p>
        سیدحسن نصرالله: ایران از محور مقاومت حمایت می‌کند ولی در طوفان‌الاقصی
        دخالتی نداشت.
    </p>
    <span class="icon-Group-2168 clsFloutNws"></span>
</div>

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
                        <li>
                            <a href="#" class="transitionCls">
                                <span class="icon-Telegram"></span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">
                                <span class="icon-Linkedin"></span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">
                                <span class="icon-Twitter-X-1"></span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">
                                <span class="icon-Youtube"></span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">
                                <span class="icon-Instagram"></span>
                            </a>
                        </li>
                    </ul>
                    <ul class="fotrLinks">
                        <li>
                            <a href="#" class="transitionCls">اقتصاد</a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">سیاست</a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">ورزش</a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">تاریخ</a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">بین‌الملل</a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">فناوری</a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">هنر و فرهنگ</a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">علمی</a>
                        </li>
                        <br/>
                        <li>
                            <a href="#" class="transitionCls">تماس با ما</a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">ارتباط با ما</a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">عکس</a>
                        </li>
                        <li>
                            <a href="#" class="transitionCls">فیلم</a>
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
</body>
</html>
