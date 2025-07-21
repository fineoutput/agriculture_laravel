<footer>
    <section class="bd-footer__area green-bg foo p-relative pt-95">
        <div class="bd-footer__bg w-img">
            <img src="{{ asset('assets/frontend/img/bg/footer-bg.png') }}" alt="footer-bg">
        </div>
        <div class="container">
            <div class="row">
                {{-- About Us --}}
                <div class="col-xl-4 col-lg-4 col-md-6">
                    <div class="bd-footer__widget footer-col-1 mb-60">
                        <div class="bd-footer__title">
                            <h4>About Us</h4>
                        </div>
                        <div class="bd-footer__paragraph">
                            <p>Dairy Muneem is a comprehensive platform designed to support dairy farmers worldwide. Our app and website provide tools to help farmers manage their operations, track expenses, monitor cow health, and improve breeding programs.</p>
                        </div>
                    </div>
                </div>

                {{-- Services --}}
                <div class="col-xl-2 col-lg-4 col-md-6">
                    <div class="bd-footer__widget footer-col-2 mb-60">
                        <div class="bd-footer__title">
                            <h4>Services</h4>
                        </div>
                        <div class="bd-footer__link">
                            <ul style="line-height: 2.5;">
                                <li><a href="{{ url('farmer') }}">Farmer</a></li>
                                <li><a href="{{ url('vendor') }}">Vendor</a></li>
                                <li><a href="{{ url('doctor') }}">Doctor</a></li>
                                <li><a href="{{ url('terms-and-conditions') }}">Terms Of Service</a></li>
                                <li><a href="{{ url('refund-cancellation-policy') }}">Cancellation Policy</a></li>
                                <li><a href="{{ url('shipping-delivery') }}">Shipping & Delivery</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Useful Links --}}
                <div class="col-xl-2 col-lg-4 col-md-6">
                    <div class="bd-footer__widget footer-col-2 mb-60">
                        <div class="bd-footer__title">
                            <h4>Useful Links</h4>
                        </div>
                        <div class="bd-footer__link">
                            <ul style="line-height: 2.5;">
                                <li><a href="{{ url('/') }}">Home</a></li>
                                <li><a href="{{ url('about') }}">About</a></li>
                                <li><a href="{{ url('gallery') }}">Gallery</a></li>
                                <li><a href="{{ url('contact') }}">Contact Us</a></li>
                                <li><a href="{{ url('privacy') }}">Privacy Policy</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div class="col-xl-4 col-lg-8 col-md-6">
                    <div class="bd-footer__widget footer-col-4 mb-60">
                        <div class="bd-footer__title">
                            <h4>Contact Us</h4>
                        </div>
                        <div class="bd-footer social-icon">
                            <ul>
                                <li><i class="fa-solid fa-phone"></i> <a href="tel:+91 7891029090">+91 7891029090</a></li>
                                <li><i class="fa-solid fa-envelope"></i> <a href="mailto:dairymuneem@gmail.com">dairymuneem@gmail.com</a></li>
                                <li style="line-height: 2; display: flex;">
                                    <i class="fa-solid fa-location-dot" style="margin-top:10px;"></i>&nbsp;
                                    House No. 98, Dream City, Suratgarh, Ganganagar-335804, Rajasthan
                                </li>
                            </ul>

                            {{-- Social Links --}}
                            <div class="bd-footer__social" style="margin-top: 30px;">
                                <a href="https://www.facebook.com/profile.php?id=100092988491189" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                                <a href="https://www.instagram.com/dairymuneem_dm/" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                                <a href="https://www.youtube.com/channel/UC_WTz7ccSTiFP8BWN9EeDUw" target="_blank"><i class="fa-brands fa-youtube"></i></a>
                                <a href="https://twitter.com/dairymuneem" target="_blank"><i class="fa-brands fa-twitter"></i></a>
                                <a href="https://www.linkedin.com/in/dairy-muneem-5287a7224" target="_blank"><i class="fa-brands fa-linkedin"></i></a>
                            </div>

                            <div class="text-hlight">
                                <p>This website is owned by Agristar Animal Solution Private Limited</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Copyright --}}
        <div class="bd-copyright__area">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="bd-copyright__main">
                            <div class="bd-copyright__border">
                                <div class="bd-copyright__text">
                                    <p>&copy; 2023 Dairy Muneem. All Rights Reserved.</p>
                                    <p>Design & Development By <span><a target="_blank" href="https://www.fineoutput.com/">Fineoutput Technologies</a></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</footer>

{{-- Back to Top --}}
<div class="progress-wrap">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
    </svg>
</div>

{{-- JS Scripts --}}
<script src="{{ asset('assets/frontend/js/vendor/jquery.js') }}"></script>
<script src="{{ asset('assets/frontend/js/vendor/waypoints.js') }}"></script>
<script src="{{ asset('assets/frontend/js/bootstrap-bundle.js') }}"></script>
<script src="{{ asset('assets/frontend/js/meanmenu.js') }}"></script>
<script src="{{ asset('assets/frontend/js/swiper-bundle.js') }}"></script>
<script src="{{ asset('assets/frontend/js/owl-carousel.js') }}"></script>
<script src="{{ asset('assets/frontend/js/magnific-popup.js') }}"></script>
<script src="{{ asset('assets/frontend/js/parallax.js') }}"></script>
<script src="{{ asset('assets/frontend/js/backtotop.js') }}"></script>
<script src="{{ asset('assets/frontend/js/nice-select.js') }}"></script>
<script src="{{ asset('assets/frontend/js/counterup.js') }}"></script>
<script src="{{ asset('assets/frontend/js/wow.js') }}"></script>
<script src="{{ asset('assets/frontend/js/isotope-pkgd.js') }}"></script>
<script src="{{ asset('assets/frontend/js/imagesloaded-pkgd.js') }}"></script>
<script src="{{ asset('assets/frontend/js/ajax-form.js') }}"></script>
<script src="{{ asset('assets/frontend/js/main.js') }}"></script>

{{-- Video Popup --}}
<script>
    $(document).ready(function () {
        $('#headerVideoLink').magnificPopup({
            type: 'inline',
            midClick: true
        });
    });
</script>

{{-- Google Translate --}}
<script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement(
            {
                pageLanguage: 'en',
                includedLanguages: 'hi,en',
            },
            'google_translate_element'
        );
    }
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
