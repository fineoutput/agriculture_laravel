@extends('frontend.common.app')

@section('main-container')
<div class="rs-breadcrumbs sec-color">
    <img src="{{ asset('assets/frontend/frontend/images/tennis/bg11.jpg') }}" alt="Breadcrumbs" />
    <div class="breadcrumbs-inner">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h1 class="page-title">Privacy Policy</h1>
                    <ul>
                        <li>
                            <a class="active" href="{{ url('/') }}">Home</a>
                        </li>
                        <li>Privacy Policy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Breadcrumbs Section End -->
<!-- Club Section Start -->
<div class="rs-club sec-spacer">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-ms-12">
                <div class="rs-club-text">
                    {!! $privacy_data[0]->content !!}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection