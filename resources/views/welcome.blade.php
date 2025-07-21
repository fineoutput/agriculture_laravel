@extends('frontend.common.app')

@section('main-container')
    <section class="bd-hero__area">
        <div class="hero__active swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="bd-singel__hero">
                        <div class="hero__height d-flex align-items-center p-relative">
                            <a href="https://play.google.com/store/apps/details?id=com.fineoutput.dairymuneem.com" target="_blank"><img src="{{ asset('assets/frontend/img/banner01.jpg') }}" width="100%" alt=""></a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="bd-singel__hero">
                        <div class="hero__height d-flex align-items-center p-relative">
                            <a href="https://play.google.com/store/apps/details?id=com.fineoutput.dairymuneemdoctor.com" target="_blank"><img src="{{ asset('assets/frontend/img/banner02.jpg') }}" width="100%" alt=""></a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="bd-singel__hero">   
                        <div class="hero__height d-flex align-items-center p-relative">
                            <a href="https://play.google.com/store/apps/details?id=com.fineoutput.dairymuneemvendor.com" target="_blank"><img src="{{ asset('assets/frontend/img/banner03.jpg') }}" width="100%" alt=""></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bd-features__area" data-background="{{ asset('assets/frontend/img/bg/section-bg.jpg') }}">
        <div class="bd-features__wrapper">
            <div class="container">
                <div class="bd-features__item-wrapper pb-10" style="padding-top: 50px;">
                    <div class="row">
                        <div class="bd-section__title-wrapper text-center mb-50">
                            <h2 class="bd-section__title">What We Provide</h2>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6">
                            <div class="bd-features__item text-center mb-60">
                                <div class="" style="padding-bottom: 20px;">
                                    <a href="{{ url('farmer') }}"><img src="{{ asset('assets/frontend/img/service/details/farmer01.jpg') }}" alt="features-image"></a>
                                </div>
                                <div class="bd-features__content">
                                    <h3><a href="{{ url('farmer') }}">FARMER</a></h3>
                                    <p>Our feed Features were designed to help you optimize your herd's nutrition and improve overall farm profitability.</p>   
                                    <a class="bd-link__btn" href="https://play.google.com/store/apps/details?id=com.fineoutput.dairymuneem.com"><img src="{{ asset('assets/frontend/img/play-store.png') }}" height="40px"></a>
                                    <a class="bd-link__btn" href="#"><img src="{{ asset('assets/frontend/img/apple-store.png') }}" height="40px"></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6">
                            <div class="bd-features__item text-center mb-60">
                                <div class="" style="padding-bottom: 20px;">
                                    <a href="{{ url('vendor') }}"><img src="{{ asset('assets/frontend/img/service/details/vendor01.jpg') }}" alt="features-image"></a>
                                </div>   
                                <div class="bd-features__content">
                                    <h3><a href="{{ url('vendor') }}">VENDOR</a></h3>
                                    <p>Overall Dairy Muneem app creates a more connected ecosystem between vendors and farmers.</p>
                                    <a class="bd-link__btn" href="https://play.google.com/store/apps/details?id=com.fineoutput.dairymuneemvendor.com"><img src="{{ asset('assets/frontend/img/play-store.png') }}" height="40px"></a>
                                    <a class="bd-link__btn" href="#"><img src="{{ asset('assets/frontend/img/apple-store.png') }}" height="40px"></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-12">
                            <div class="bd-features__item text-center mb-60">
                                <div class="" style="padding-bottom: 20px;">
                                    <a href="{{ url('doctor') }}"><img src="{{ asset('assets/frontend/img/service/details/doctor01.jpg') }}" alt="features-image"></a>
                                </div>
                                <div class="bd-features__content">
                                    <h3><a href="{{ url('doctor') }}">DOCTOR</a></h3>
                                    <p>Doctor easily Connect with farmers. Online Consultation to the farmer. Doctor can maintain Semen Tank...</p>
                                    <a class="bd-link__btn" href="https://play.google.com/store/apps/details?id=com.fineoutput.dairymuneemdoctor.com"><img src="{{ asset('assets/frontend/img/play-store.png') }}" height="40px"></a>
                                    <a class="bd-link__btn" href="#"><img src="{{ asset('assets/frontend/img/apple-store.png') }}" height="40px"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bd-about__area pt-120 pb-55" data-background="{{ asset('assets/frontend/img/bg/section-bg.jpg') }}">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="bd-about__image-wrapper p-relative mb-60">
                        <img class="bd-about__image-1" src="{{ asset('assets/frontend/img/about-img-01.jpg') }}" alt="about-img">
                        <div class="bd-about__image-2 text-sm-end">
                            <img src="{{ asset('assets/frontend/img/about-img-02.jpg') }}" alt="about-img">
                        </div>
                        <div class="bd-about__shape">
                            <img src="{{ asset('assets/frontend/img/about/about-cow.png') }}" alt="about-cow-icon"><img class="bd-about__dashed-icon" src="{{ asset('assets/frontend/img/about/about-dashed.png') }}" alt="about-dashed">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bd-about__content-wrapper mb-60">
                        <div class="bd-section__title-wrapper">
                            <span class="bd-sub__title">About Us</span>
                            <h4 style="margin-bottom: 15px;">Know About Our Dairy Muneem</h4>
                            <p>Dairy Muneem, a pioneering brand in the dairy farm management service whose mission is clear to become the number one choice for dairy farmers worldwide by providing comprehensive A to Z solutions that optimize their operations and maximize their success. It includes a wide range of activities, including as managing finances, milk quality, breeding, and general farm management, in addition to monitoring animal health and nutrition in dairy farms. On our app you input and access data related to individual animals, track their health parameters, record treatments administered, and monitor overall herd health. Also, we serve as a valuable tool for knowledge-sharing, consultation, farming and data-driven decision-making, ultimately contributing to the improved health and welfare of dairy animals.</p>
                            <h4 style="margin-bottom: 15px;">Our Vision</h4>
                            <p>To improve the social economy of dairy farmers with technology and awareness.</p>
                        </div>
                        <div class="bd-about__features-wrapper mb-25">
                            <div class="bd-about__features">
                                <div class="bd-about__features-title">
                                    <h4>Our Mission</h4>
                                </div>
                                <div class="bd-about__features-list">
                                    <p>To become No. 1 brand in the Dairy farm management service by providing A to Z solutions to the dairy farmers across the globe.</p>
                                </div>
                            </div>
                            <div class="bd-about__experience text-center"><span class="number counter">10</span><span class="plus">+</span>
                                <p>Years experience</p>
                            </div>
                        </div><a class="bd-theme__btn-1" href="#">Get in Touch</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="video-popup-sec">
        <div class="container">
            <div class="row pt-95 pb-90">
                <div class="col-sm-12">
                    <p class="text-center mt-5 mb-20">
                        <a href="#headerPopup" id="headerVideoLink" target="_blank" class="bd-play__btn btn popup-modal">
                            <i class="fa-solid fa-play"></i>
                        </a>
                    </p>
                    <div class="text-center bd-section__title-wrapper mb-45">
                        <span class="bd-sub__title">Intro Video</span>
                        <h2 class="bd-section__title">Ready to Experience & <br> Work Difference</h2>
                    </div>
                    <div id="headerPopup" class="mfp-hide embed-responsive embed-responsive-21by9">
                        <iframe class="embed-responsive-item" width="auto" height="480" src="{{ asset('assets/frontend/img/dairy-muneemVideo.mp4') }}" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bd-gallery__area gallery__overlay fix pt-120 pb-120">
        <div class="bd-gallery__bg"><img src="{{ asset('assets/frontend/img/bg/gallery-bg.png') }}" alt="gallery-bg"></div>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bd-gallery__inner p-relative">
                        <div class="bd-section__title-wrapper mb-45"><span class="bd-sub__title">Farm Overview</span>
                            <h2 class="bd-section__title">Farm Gallery</h2>
                        </div>
                        <div class="bd-gallery__navigatin"><button class="gallery-button-prev"><i class="far fa-long-arrow-left"></i></button><button class="gallery-button-next"><i class="far fa-long-arrow-right"></i></button></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="bd-gallery__wrapper">
                        <div class="bd-gallery-active swiper-container">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div class="bd-gallery__item">
                                        <div class="bd-gallery__image w-img"><img src="{{ asset('assets/frontend/img/gallery-slid01.jpg') }}" alt="gallery-image"></div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="bd-gallery__item">
                                        <div class="bd-gallery__image w-img"><img src="{{ asset('assets/frontend/img/gallery-slid02.jpg') }}" alt="gallery-image"></div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="bd-gallery__item">
                                        <div class="bd-gallery__image w-img"><img src="{{ asset('assets/frontend/img/gallery-slid03.jpg') }}" alt="gallery-image"></div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="bd-gallery__item">
                                        <div class="bd-gallery__image w-img"><img src="{{ asset('assets/frontend/img gallery-slid04.jpg') }}" alt="gallery-image"></div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="bd-gallery__item">
                                        <div class="bd-gallery__image w-img"><img src="{{ asset('assets/frontend/img/gallery-slid05.jpg') }}" alt="gallery-image"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="bd-testimonial__area grey-bg pt-120 pb-90">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="p-relative">
                        <div class="bd-section__title-wrapper mb-50">
                            <h2 class="bd-section__title">Our Testimonial</h2>
                        </div>
                        <!-- If we need navigation buttons -->
                        <div class="bd-testimonial__navigatin">
                            <button class="testimonial-button-prev" tabindex="0" aria-label="Previous slide" aria-controls="swiper-wrapper-e6f5c5b5ddf82b98" aria-disabled="false"><i class="far fa-long-arrow-left"></i></button>
                            <button class="testimonial-button-next" tabindex="0" aria-label="Next slide" aria-controls="swiper-wrapper-e6f5c5b5ddf82b98" aria-disabled="false"><i class="far fa-long-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-3 col-lg-4 col-md-12">
                    <div class="bd-testimonial__card text-center mb-30">
                        <div class="bd-testimonial__text">
                            <div class="bd-testimonial__icon">
                                <img src="{{ asset('assets/frontend/img/testimonial-icon.png') }}" alt="testimonial-icon">
                            </div>
                            <p>Happy Customer</p>
                            <div class="bd-testimonial__count">
                                <span class="counter">2332</span><span>+</span>
                            </div>
                            <div class="bd-testimonial__cercle-1"></div>
                            <div class="bd-testimonial__cercle-2"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8 col-md-12">
                    <div class="bd-testimonial__wrapper mb-30">
                        <div class="testimonial-slide swiper-container swiper-container-initialized swiper-container-horizontal swiper-container-pointer-events">
                            <div class="swiper-wrapper" id="swiper-wrapper-e6f5c5b5ddf82b98" aria-live="off" style="transition-duration: 0ms; transform: translate3d(-1550px, 0px, 0px);">
                                <div class="swiper-slide" role="group" aria-label="1 / 3" style="width: 765px; margin-right: 10px;">
                                    <div class="bd-testimonial__content">
                                        <div class="bd-testimonial__icon">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-regular fa-star"></i>
                                        </div>
                                        <img src="{{ asset('assets/frontend/img/farmer-testimonial.jpg') }}">
                                        <h3>“I used the services of Dairy Muneem, and I must say, my experience was amazing. As a farmer, I rely heavily on dependable and trustworthy resources for my dairy needs, and Dairy Muneem surpassed all my expectations. Their app and website was easy to navigate, allowing me to explore their extensive range of products effortlessly.”</h3>
                                        <div class="bd-testimonial__meta-text">
                                            <h4>Farmer</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide" role="group" aria-label="2 / 3" style="width: 765px; margin-right: 10px;">
                                    <div class="bd-testimonial__content">
                                        <div class="bd-testimonial__icon">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-regular fa-star"></i>
                                        </div>
                                        <img src="{{ asset('assets/frontend/img/vendor-testimonial.jpg') }}">
                                        <h3>“From the moment I registered as a vendor on the Dairy Muneem app, I was impressed by the user-friendly interface and the seamless onboarding process. The platform provided clear and detailed guidelines for setting up my vendor profile, uploading product information, and managing inventory. It was evident that Dairy Muneem prioritizes efficiency and understands the needs of vendors.”</h3>
                                        <div class="bd-testimonial__meta-text">
                                            <h4>Vendor</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide" role="group" aria-label="3 / 3" style="width: 765px; margin-right: 10px;">
                                    <div class="bd-testimonial__content">
                                        <div class="bd-testimonial__icon">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-regular fa-star"></i>
                                        </div>
                                        <img src="{{ asset('assets/frontend/img/doctor-testimonial.jpg') }}">
                                        <h3>“Dairy Muneem has proven to be a trusted partner for veterinarians like me. I highly recommend Dairy Muneem to my fellow veterinarians who are seeking reliable and top-notch products to support their practice. Working with Dairy Muneem has been a positive and rewarding experience, and I look forward to continued collaboration with them in the future.”</h3>
                                        <div class="bd-testimonial__meta-text">
                                            <h4>Doctor</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
                        </div>
                        <div class="swiper-container testimonial-nav swiper-container-initialized swiper-container-horizontal swiper-container-pointer-events swiper-container-free-mode swiper-container-thumbs">
                            <div class="swiper-wrapper" id="swiper-wrapper-8243b10613c0198d2" aria-live="off" style="transition-duration: 0ms; transform: translate3d(-1020px, 0px, 0px);">
                            <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
