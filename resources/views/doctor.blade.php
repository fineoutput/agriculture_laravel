@extends('frontend.common.app')

@section('main-container')

<main>
    <div class="bd-breadcrumb__area include__bg hero__overlay Breadcrumb__height d-flex align-items-center" data-background="{{ asset('assets/frontend/img/doctor-banner.jpg') }}">
        <div class="container fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bd-breadcrumb__menu">
                        <nav aria-label="Breadcrumbs" class="breadcrumb-trail breadcrumbs">
                            <ul class="trail-items">
                                <li class="trail-item trail-begin"><span><a href="{{ url('/') }}" style="color:#000;">Home</a></span></li>
                                <li class="trail-item trail-end"><span>Service</span></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="bd-breadcrumb__title">
                        <h2 style="color:#000;">Doctor</h2>  
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="bd-about__area pt-120 pb-60">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="bd-about__thumb w-img mb-60">
                        <img src="{{ asset('assets/frontend/img/service/details/doctor02.jpg') }}" alt="about-thumb">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bd-about__content-box-3 mb-60">
                        <div class="bd-section__title-wrapper mb-40">
                            <h2 class="bd-section__title mb-25">DOCTORS</h2>
                            <p class="bd-section__paragraph">Doctor easily Connect with farmers. Online Consultation to the farmer. Doctor can maintain Semen Tank inventory with record n also they can send Message to farmer before goint to their area. With this app , after registering veterinarians can connect, share knowledge, and exchange best practices related to dairy animal care with our customers. Also,our  app  includes telemedicine component, allowing veterinarians to give remotely consult regarding specific cases and people can seek advice on complex issues. They can keep track on previous reports and can maintain their Ai management.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

@endsection