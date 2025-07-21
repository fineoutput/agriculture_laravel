
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
                                <li class="trail-item trail-end"><span>Shipping & Delivery Policy</span></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="bd-breadcrumb__title">
                        <h2 style="color:#000;">Shipping & Delivery Policy</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="terms-service-sec">
        <div class="container">
            <div class="main_content">
                <!-- STAT SECTION FAQ -->
                <div class="section">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="term_conditions">
                                    <h4>Shipping & Delivery Policy</h4>
                                    <p>We offer <b>free shipping</b> on all our products throughout India!</p>
                                    <!-- <p>Free bag is sent on all orders above <b>Rs. 2499.</b></p> -->
                                    <p>Usually, orders are dispatched within <b>2-4 working days</b> of the customer after placing the order.</p>
                                    <p>However, unusual circumstances may lead to delays beyond the specified period. In case of delay (product not dispatched within the estimated time period), you may write us at contactus@dairymuneem.in or connect us through <b>WhatsApp</b> link given on website.</p>
                                    <p>To ensure that the order reaches you in a good condition, in the shortest span of time, we ship through reputed logistics companies.</p>
                                    <p>If your enquiry is urgent, please email us at contactus@dairymuneem.in or message us through <b>WhatsApp</b> Link given on website and one of our team members will be in touch with you at the earliest.</p>
                                    <p style="color:red">*Please note that Saturdays, Sundays and Public Holidays are not considered as working days for standard deliveries.</p>
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