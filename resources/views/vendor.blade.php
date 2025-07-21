@extends('frontend.common.app')

@section('main-container')
<main>
    <div class="bd-breadcrumb__area include__bg hero__overlay Breadcrumb__height d-flex align-items-center" data-background="{{ asset('assets/frontend/img/vender-banner.jpg') }}">
        <div class="container fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bd-breadcrumb__menu">
                        <nav aria-label="Breadcrumbs" class="breadcrumb-trail breadcrumbs">
                            <ul class="trail-items">
                                <li class="trail-item trail-begin"><span><a href="{{ url('/') }}">Home</a></span></li>
                                <li class="trail-item trail-end"><span>Service</span></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="bd-breadcrumb__title">
                        <h2>Vendor</h2>
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
                        <img src="{{ asset('assets/frontend/img/service/details/vendor03.jpg') }}" alt="about-thumb">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bd-about__content-box-3 mb-60">
                        <div class="bd-section__title-wrapper mb-40">
                            <h2 class="bd-section__title mb-25">VENDORS</h2>
                            <p class="bd-section__paragraph">Overall Dairy muneem app creates a more connected ecosystem between vendors and farmers, enabling streamlined communication, efficient operations, and data-driven decision-making for both parties. On our app vendors can register their shop, update their products and sell their products.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

@endsection