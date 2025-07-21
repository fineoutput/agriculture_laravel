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
                                <li class="trail-item trail-end"><span>About</span></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="bd-breadcrumb__title">
                        <h2 style="color:#000;">About Us</h2>
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
                        <table>
                            <tr>
                                <th colspan="3"><img src="{{ asset('assets/frontend/img/progress-step-tree.jpg') }}"></th>
                            </tr>
                            <tr class="text-center"> 
                                <td>Startup of Exotic Breed Solution in 2014</td> 
                                <td>Establishment of Agristar Animal Nutrition in 2021</td> 
                                <td>Launching of Dairy Muneem App in 2023</td> 
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bd-about__content-box-3 mb-60">
                        <div class="bd-section__title-wrapper mb-40"><span class="bd-sub__title">Establishment</span>
                            <h2 class="bd-section__title mb-25">EXOTIC BREED SOLUTION (2014)</h2>
                            <p>At the time of startup of our Exotic Breed Solution in 2014 we provided breeding solution to the farmers.</p>
                            <p>In 2021 we dealt with farmers by providing upgradation of feed value through nutritional supplements with our concern Agristar Animal Nutrition.</p>
                            <p>Recently we are expanding to provide better services of feeding, breeding management treatment DFI etc through our Dairy Muneem App.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="bd-brand__area pb-120">
        <div class="container">
            <div class="bd-brand__dashed">
                <div class="bd-dashed__line"></div>
            </div>
            <div class="row align-items-center justify-content-between">
                <div class="col-12">
                    <div class="bd-brand-active swiper-container">
                        <div class="swiper-wrapper">  
                            <div class="swiper-slide">
                                <div class="bd-single__brand"><a href="#"><img src="{{ asset('assets/frontend/img/Pandemic.png') }}" alt="Pandemic-png"></a></div>
                                <h5 class="text-center"><b>Pandemic, increased demand for professional Dairy Service</b></h5>
                            </div>
                            <div class="swiper-slide">
                                <div class="bd-single__brand"><a href="#"><img src="{{ asset('assets/frontend/img/Population.png') }}" alt="Population-png"></a></div>
                                <h5 class="text-center"><b>Population Increase, Rapid urbanization, & growing</b></h5>
                            </div>
                            <div class="swiper-slide">
                                <div class="bd-single__brand"><a href="#"><img src="{{ asset('assets/frontend/img/Establishment.png') }}" alt="Establishment-png"></a></div>
                                <h5 class="text-center"><b>Establishment of leading multinational companies</b></h5>
                            </div>
                            <div class="swiper-slide">
                                <div class="bd-single__brand"><a href="#"><img src="{{ asset('assets/frontend/img/Rising.png') }}" alt="Rising-png"></a></div>
                                <h5 class="text-center"><b>Rising disposable incomes & Growing Dairy sectors</b></h5>
                            </div>
                            <div class="swiper-slide">
                                <div class="bd-single__brand"><a href="#"><img src="{{ asset('assets/frontend/img/Favorable.png') }}" alt="Favorable-png"></a></div>
                                <h5 class="text-center"><b>Favorable government initiatives</b></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@endsection
    