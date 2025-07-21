<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dairy Muneem</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="Aspire Technosys Pvt Ltd">
    <meta property="og:title" content="Dairy Muneem">
    <meta property="og:description" content="">
    <meta property="og:image" content="">
    <meta property="og:site_name" content="">
    <meta property="og:url" content="">
    <meta name="facebook-domain-verification" content="kmhz0xv28399j3d7sb40qqrz43uj8e" />

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/frontend/img/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/meanmenu.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/owl-carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/swiper-bundle.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/backtotop.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/font-awesome-pro.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/spacing.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/main.css') }}">
    
    <style>
        #headerPopup {
            width: 75%;
            margin: 0 auto;
        }

        #headerPopup iframe {
            width: 100%;
            margin: 0 auto;
        }

        .video-popup-sec p {
            display: flex;
            justify-content: center;
        }

        .video-popup-sec a {
            border: none;
        }
    </style>
</head>
<body>
    {{-- Preloader --}}
    <div class="preloader">
        <div class="loader rubix-cube">
            @for ($i = 1; $i <= 8; $i++)
                <div class="layer layer-{{ $i }}{{ $i === 3 ? ' color-1' : '' }}"></div>
            @endfor
        </div>
    </div>

    {{-- Sidebar buttons --}}
    <div class="sidebar-btn">
        <div class="side-calling-btn"><a href="tel:+91 7891029090"><i class="fa fa-phone"></i></a></div>
        <div class="side-whatsapp-btn"><a href="https://wa.me/+917891029090"><i class="fa-brands fa-whatsapp"></i></a></div>
    </div>

    <div id="google_translate_element"></div>

    {{-- Offcanvas Sidebar --}}
    <div class="fix">
        <div class="offcanvas__info">
            <div class="offcanvas__wrapper">
                <div class="offcanvas__content">
                    <div class="offcanvas__top d-flex justify-content-between align-items-center">
                        <div class="offcanvas__logo logo">
                            <a href="{{ url('/') }}">
                                <img src="{{ asset('assets/frontend/img/logo.png') }}" alt="logo">
                            </a>
                        </div>
                        <div class="offcanvas__close">
                            <button><i class="fal fa-times"></i></button>
                        </div>
                    </div>

                    {{-- Mobile Menu Placeholder --}}
                    <div class="mobile-menu fix mb-40 mean-container">
                        <div class="mean-bar">
                            <a href="#nav" class="meanmenu-reveal" style="right: 0px; left: auto; display: inline;">
                                <span><span><span></span></span></span>
                            </a>
                        </div>
                    </div>

                    {{-- Contact Info --}}
                    <div class="offcanvas__contact mt-30 mb-20">
                        <h4>Contact Info</h4>
                        <ul>
                            <li class="d-flex align-items-center">
                                <div class="offcanvas__contact-icon mr-15">
                                    <i class="fal fa-map-marker-alt"></i>
                                </div>
                                <div class="offcanvas__contact-text">
                                    <a target="_blank" href="#">House No. 98, Dream City, Suratgarh, Ganganagar-335804, Rajasthan</a>
                                </div>
                            </li>
                            <li class="d-flex align-items-center">
                                <div class="offcanvas__contact-icon mr-15">
                                    <i class="far fa-phone"></i>
                                </div>
                                <div class="offcanvas__contact-text">
                                    <a href="tel:+91 7891029090">+91 7891029090</a>
                                </div>
                            </li>
                            <li class="d-flex align-items-center">
                                <div class="offcanvas__contact-icon mr-15">
                                    <i class="fal fa-envelope"></i>
                                </div>
                                <div class="offcanvas__contact-text">
                                    <a href="mailto:dairymuneem@gmail.com">dairymuneem@gmail.com</a>
                                </div>
                            </li>
                        </ul>
                    </div>

                    {{-- Social Links --}}
                    <div class="offcanvas__social">
                        <ul>
                            <li><a href="https://www.facebook.com/profile.php?id=100092988491189" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="https://www.instagram.com/dairymuneem_dm/" target="_blank"><i class="fab fa-instagram"></i></a></li>
                            <li><a href="https://www.youtube.com/channel/UC_WTz7ccSTiFP8BWN9EeDUw" target="_blank"><i class="fab fa-youtube"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas-overlay"></div>
    <div class="offcanvas-overlay-white"></div>

    {{-- Header --}}
    <header>
        <div class="bd-header__top-area pg-bg d-none d-md-block">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-8 col-lg-8 col-md-9 col-8">
                        <div class="bd-header__contact-spacing">
                            <div class="bd-header__contact">
                                <ul>
                                    <li><a href="mailto:dairymuneem@gmail.com"><i class="fa-solid fa-envelope-open"></i> dairymuneem@gmail.com</a></li>
                                    <li><a href="tel:+91 7891029090"><i class="fa-solid fa-phone"></i> +91 7891029090</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-3 col-4">
                        <div class="bd-header__social text-end">
                            <ul>
                                <li><a href="https://www.facebook.com/profile.php?id=100092988491189" target="_blank"><i class="fa-brands fa-facebook-f"></i></a></li>
                                <li><a href="https://www.instagram.com/dairymuneem_dm/" target="_blank"><i class="fa-brands fa-instagram"></i></a></li>
                                <li><a href="https://www.youtube.com/channel/UC_WTz7ccSTiFP8BWN9EeDUw" target="_blank"><i class="fa-brands fa-youtube"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="header-sticky" class="bd-header__area">
            <div class="container-fluid p-0">
                <div class="row g-0 align-items-center">
                    <div class="col-xl-2 col-lg-2 col-md-4 col-4 p-0">
                        <div class="bd-header__logo">
                            <a href="{{ url('/') }}">
                                <img src="{{ asset('assets/frontend/img/logo.png') }}" alt="logo">
                            </a>
                        </div>
                    </div>
                    <div class="col-xl-8 col-lg-9 col-md-4 d-none d-md-block">
                        <div class="bd-header__menu-wrapper d-flex justify-content-center">
                            <div class="main-menu d-none d-lg-block">
                                <nav id="mobile-menu">
                                    <ul>
                                        <li><a href="{{ url('/') }}">Home</a></li>
                                        <li><a href="{{ url('about') }}">About</a></li>
                                        <li class="has-dropdown">
                                            <a href="#">Service</a>
                                            <ul class="submenu">
                                                <li><a href="{{ url('farmer') }}">Farmer</a></li>
                                                <li><a href="{{ url('vendor') }}">Vendor</a></li>
                                                <li><a href="{{ url('doctor') }}">Doctor</a></li>
                                            </ul>
                                        </li>
                                        <li><a href="{{ url('gallery') }}">Gallery</a></li>
                                        {{-- <li><a href="{{ url('contact') }}">Contact</a></li> --}}
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-1 col-md-4 col-8">
                        <div class="bd-header__right d-flex align-items-center justify-content-end">
                            <div class="bd-header__hamburger">
                                <div class="bd-header__hamburger-icon">
                                    <button class="side-toggle">
                                        <img src="{{ asset('assets/frontend/img/icon/hamburger-icon.png') }}" alt="hamburger-icon">
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
