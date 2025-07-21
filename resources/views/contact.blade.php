@extends('frontend.common.app')

@section('main-container')
<main>
    <div class="bd-breadcrumb__area include__bg hero__overlay Breadcrumb__height d-flex align-items-center" data-background="{{ asset('assets/frontend/img/page-banner.jpg') }}">
        <div class="container fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bd-breadcrumb__menu">
                        <nav aria-label="Breadcrumbs" class="breadcrumb-trail breadcrumbs">
                            <ul class="trail-items">
                                <li class="trail-item trail-begin"><span><a href="{{ url('/') }}" style="color:#000;">Home</a></span></li>
                                <li class="trail-item trail-end"><span>Contact</span></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="bd-breadcrumb__title">
                        <h2 style="color:#000;">Get in Touch</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="bd-contact__info-area pt-120 pb-90">
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="bd-conatact__info text-center mb-30">
                        <div class="bd-conatact__info-icon"><img src="{{ asset('assets/frontend/img/icon/contact/01.png') }}" alt="conatact-icon"></div>
                        <div class="bd-conatact__info-content">
                            <h3>Call Us Here</h3><span><a href="tel:+917891029090">+91 7891029090</a></span><br>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="bd-conatact__info text-center mb-30">
                        <div class="bd-conatact__info-icon"><img src="{{ asset('assets/frontend/img/icon/contact/02.png') }}" alt="conatact-icon"></div>
                        <div class="bd-conatact__info-content">
                            <h3>Email Address</h3><span><a href="mailto:dairymuneem@gmail.com">dairymuneem@gmail.com</a></span><br>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="bd-conatact__info text-center mb-30">
                        <div class="bd-conatact__info-icon"><img src="{{ asset('assets/frontend/img/icon/contact/03.png') }}" alt="conatact-icon"></div>
                        <div class="bd-conatact__info-content">
                            <h3>Office Address</h3><span>HOUSE NO. 98, DREAM CITY, Suratgarh</span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="bd-conatact__info text-center mb-30">
                        <div class="bd-conatact__info-icon"><img src="{{ asset('assets/frontend/img/icon/contact/04.png') }}" alt="conatact-icon"></div>
                        <div class="bd-conatact__info-content">
                            <h3>Social Connect</h3><span><a href="#">skype.com/humble.cc</a></span><span><a href="#">linkdin.com/hamble.007</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="bd-contact__area pb-120">
        <div class="container">
            <div class="row">
                <div class="bd-contact__main">
                    <div class="row align-items-center">
                        <div class="col-xl-6 col-lg-6">
                            <div class="bd-google__map mb-60">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d5079832.1452336935!2d72.78131485754321!3d28.455546111210086!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39167387cec1e08f%3A0x7d59e0548352c27c!2sAgristar%20animal%20solution%20Pvt%20Ltd!5e0!3m2!1sen!2sin!4v1683628982845!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6">
                            <div class="bd-contact__wrapper mb-30">
                                <div class="bd-section__title-wrapper mb-50">
                                    <h2 class="bd-section__title mb-30">Newsletter Subscribe</h2>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <form action="#" method="POST">
                                            @csrf
                                            <div class="bd-contact__input mb-15">
                                                <input type="text" name="name" placeholder="Enter name" required>
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                            <div class="bd-contact__input mb-15">
                                                <input type="email" name="email" placeholder="Email address" required>
                                                <i class="fa-solid fa-envelope-open"></i>
                                            </div>
                                            <div class="bd-contact__input mb-15">
                                                <input type="text" name="company" placeholder="Company/Organization/Department">
                                                <i class="fa-solid fa-home"></i>
                                            </div>
                                            <div class="bd-contact__input mb-15">
                                                <input type="text" name="designation" placeholder="Designation">
                                                <i class="fa-solid fa-location-dot"></i>
                                            </div>
                                            <div class="bd-contact__input mb-15">
                                                <textarea name="message" placeholder="Message"></textarea>
                                                <i class="fa-solid fa-pen"></i>
                                            </div>
                                            <button type="submit" name="submit" class="newsletter-submit-btn" value="Send">Submit Now</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
