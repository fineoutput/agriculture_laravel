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
                                <li class="trail-item trail-end"><span>Gallery</span></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="bd-breadcrumb__title">
                        <h2 style="color:#000;">Gallery</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bd-trem__gallery pt-120 pb-90">
        <div class="container">
            <div class="row">
                <div class="bd-gallery__button p-relative mb-40">
                    <button class="active" data-filter="*">All Overview</button>
                    <button data-filter=".c-1">Farm House</button>
                    <button data-filter=".c-2">Soil Field</button>
                    <button data-filter=".c-3">Grass Field</button>
                    <button data-filter=".c-4">Organic Food</button>
                </div>
            </div>
            <div class="row grid gallery-grid-items">
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-2 c-3">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery01.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery01.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-4">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery-02.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery-02.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-2">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery-07.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery-07.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-1 c-4">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery-04.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery-04.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-2 c-4">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery-08.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery-08.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-2 c-1">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery-02.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery-02.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-1">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery-06.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery-06.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-1">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery-03.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery-03.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-2 c-1">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery-09.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery-09.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-1 c-3">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery-05.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery-05.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 grid-item c-2 c-3">
                    <div class="bd-singel__gallery-item mb-30">
                        <div class="bd-gallery__thumb">
                            <a href="#"><img src="{{ asset('assets/frontend/img/gallery/gallery-10.jpg') }}" alt="gallery-thumb"></a>
                        </div>
                        <span class="bd-gallery__action">
                            <a class="popup-image" href="{{ asset('assets/frontend/img/gallery/gallery-10.jpg') }}"><i class="fa-regular fa-face-cowboy-hat"></i></a>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="bg-gallery__btn text-center mb-30 mt-30">
                    <a class="bd-theme__btn-7" href="#">Load More <i class="fa-regular fa-plus"></i></a>
                </div>
            </div>
        </div>
    </div>
</main>

@endsection