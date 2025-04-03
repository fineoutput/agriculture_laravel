<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>{{config('constants.options.SITE_NAME')}} | @if(!empty($title)) {{$title}}@else Admin @endif</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" href="{{asset('admin/assets/images/favicon.png')}}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{asset('admin/assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('admin/assets/css/style.css')}}" rel="stylesheet" type="text/css">
</head>

<body>
    <!-- ================================
    START ERROR AREA
================================= -->
    <section class="error-area section--padding text-center" style="background:white">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="error-img">
                        <img src="{{asset('404.gif')}}" style="height: 400px;">
                    </div><!-- end error-img-->
                    <div class="section-heading padding-top-35px">
                        <h2 class="sec__title mb-0">Ooops! This Page Does Not Exist</h2>
                        <p class="sec__desc pt-3">We're sorry, but it appears the website address you entered was <br> incorrect, or is temporarily unavailable.</p>
                    </div>
                    <div class="btn-box mb-5">
                        <a href="{{route('/')}}" class="theme-btn"><i class="la la-reply mr-1"></i> Back to Home</a>
                    </div>
                </div><!-- end col-lg-7 -->
            </div><!-- end row -->
        </div><!-- end container -->
    </section><!-- end error-area -->
    <!-- ================================
    END ERROR AREA
================================= -->
</body>

</html>