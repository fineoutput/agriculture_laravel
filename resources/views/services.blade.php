@extends('frontend.common.app')

@section('main-container')

<main>
    <div class="bd-breadcrumb__area include__bg hero__overlay Breadcrumb__height d-flex align-items-center" data-background="{{ asset('assets/img/breadcrumb.jpg') }}">
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
                        <h2>What We Do</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="bd-serrvice__area service__bg">
        <div class="bd-trem__gallery pt-120">
            <div class="container">
                <div class="row">   
                    <div class="bd-gallery__button p-relative mb-40">
                        <button data-filter=".c-1" class="btn1 active" onclick="fun1(1)">Feed</button>
                        <button data-filter=".c-2" class="btn2" onclick="fun1(2)">Breed</button>
                        <button data-filter=".c-3" class="btn3" onclick="fun1(3)">Management</button>
                        <button data-filter=".c-4" class="btn4" onclick="fun1(4)">Tools</button>
                    </div>
                </div>
                <div class="row grid gallery-grid-items">
                    <div class="col-12 col-lg-12 col-md-6 grid-item c-1" id="feed">
                        <div class="bd-singel__gallery-item mb-30">
                            <div class="bd-services__details-area">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="bd-services__details-wrapper mb-60">
                                                <div class="bd-services__details-thumb w-img mb-50">
                                                    <img src="{{ asset('assets/img/service-details-01.jpg') }}" alt="service-thumb">
                                                </div>
                                                <div class="bd-services__details-content mb-40">
                                                    <div class="bd-services__details-text mb-45">
                                                        <h3 class="bd-services__details-title"> Feed Service </h3>
                                                        <p class="mb-30">Our feed Features were designed to help you optimize your herd's nutrition and improve overall farm profitability. We offer several features that cater to the specific needs of dairy farmers. With our weight calculator, DMI calculator, and animal requirement tool, farmers can ensure that their cows receive the right amount of feed and nutrition they need. Our feed calculator, on the other hand, provides a convenient and efficient way to calculate the necessary amount of feed for a certain number of cows, helping farmers optimize their feed usage and reduce waste.</p>
                                                    </div>
                                                    <div class="bd-services__details-features mb-40">
                                                        <div class="bd-services__features-grid">
                                                            <div class="bd-services__features-item">
                                                                <div class="bd-services__features-icon">
                                                                    <span><img src="{{ asset('assets/img/service/details/breed-icon-01.png') }}" alt="features-icon"></span>
                                                                </div>
                                                                <div class="bd-services__features-content">
                                                                    <h3><a href="#">Weight Calculator</a></h3>
                                                                    <p> This tool allows you to accurately measure the weight of your cows, ensuring that you provide them with the right amount of feed to meet their nutritional needs.</p>
                                                                </div>
                                                            </div>
                                                            <div class="bd-services__features-item">
                                                                <div class="bd-services__features-icon">
                                                                    <span><img src="{{ asset('assets/img/service/details/feed-icon-02.png') }}" alt="features-icon"></span>
                                                                </div>
                                                                <div class="bd-services__features-content">
                                                                    <h3><a href="#">DMI Calculator</a></h3>
                                                                    <p>Our Dry Matter Intake (DMI) Calculator helps you calculate the amount of feed your cows need based on their weight, age, breed, and production level.</p>
                                                                </div>
                                                            </div>
                                                            <div class="bd-services__features-item">
                                                                <div class="bd-services__features-icon">
                                                                    <span><img src="{{ asset('assets/img/service/details/feed-icon-01.png') }}" alt="features-icon"></span>
                                                                </div>
                                                                <div class="bd-services__features-content">
                                                                    <h3><a href="#">Animal Requirement</a></h3>
                                                                    <p> This tool helps you determine the specific nutritional requirements of each of your cows, based on their individual characteristics and needs.</p>
                                                                </div>
                                                            </div>
                                                            <div class="bd-services__features-item">
                                                                <div class="bd-services__features-icon">
                                                                    <span><img src="{{ asset('assets/img/service/details/breed-icon-04.png') }}" alt="features-icon"></span>
                                                                </div>
                                                                <div class="bd-services__features-content">
                                                                    <h3><a href="#">Feed Calculator</a></h3>
                                                                    <p>Our Feed Calculator lets you calculate the cost and nutritional value of different feed options, helping you make informed decisions about your herd's diet.</p>
                                                                </div>
                                                            </div>
                                                            <div class="bd-services__features-item">
                                                                <div class="bd-services__features-icon">
                                                                    <span><img src="{{ asset('assets/img/service/details/breed-icon-02.png') }}" alt="features-icon"></span>
                                                                </div>
                                                                <div class="bd-services__features-content">
                                                                    <h3><a href="#">Check My Feed</a></h3>
                                                                    <p>Our feed Features were designed to help you optimize your herd's nutrition and improve overall farm profitability. We offer several features that cater to the specific needs of dairy farmers.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="bd-services__founder mb-45">
                                                        <div class="bd-services__founder-content">
                                                            <h3>With Dairy Muneem's feed management tools, you can track your cows' nutrition in real-time, make data-driven decisions about their feed, and optimize their diet for maximum health and productivity.</h3>
                                                        </div>
                                                        <div class="bd-services__founder-icon">
                                                            <img src="{{ asset('assets/img/service/details/message.png') }}" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row grid gallery-grid-items" id="service1" style="display: none;">
                        <div class="col-12 col-lg-12 col-md-6 grid-item c-2">
                            <div class="bd-singel__gallery-item mb-30">
                                <div class="bd-services__details-area">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="bd-services__details-wrapper mb-60">
                                                    <div class="bd-services__details-thumb w-img mb-50">
                                                        <img src="{{ asset('assets/img/service-details-02.jpg') }}" alt="service-thumb">
                                                    </div>
                                                    <div class="bd-services__details-content mb-40">
                                                        <div class="bd-services__details-text mb-45">
                                                            <h3 class="bd-services__details-title"> Breed Service </h3>
                                                            <p class="mb-30">Breed selection is one of the most crucial decisions for any dairy farmer. A well-planned breeding program can lead to healthier, more productive cows with desirable traits such as increased milk production, longevity, and disease resistance. At Dairy Muneem, we understand the importance of breed selection, and we provide a range of tools and resources to help you make informed decisions. From tracking the health of your animals to managing breeding records, our breed services are designed to optimize your herd's genetics and ensure long-term profitability for your dairy farm.</p>
                                                        </div>
                                                        <div class="bd-services__details-features mb-40">
                                                            <div class="bd-services__features-grid">
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/feed-icon-01.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">My Animal</a></h3>
                                                                        <p>This allows you to keep track of your cows' individual characteristics, such as breed, age, and pedigree, helping you make informed breeding decisions.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/icon-02.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Health Info</a></h3>
                                                                        <p>Our Health Info tool provides you with up-to-date information on your cows' health status, including vaccination records, disease history, and any current health issues.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/breed-icon-01.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Breeding Record</a></h3>
                                                                        <p>Our Breeding Record tool helps you keep track of your cows' breeding history, including mating dates, pregnancy status, and calving dates.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="bd-services__founder mb-45">
                                                            <div class="bd-services__founder-content">
                                                                <h3>With Dairy Muneem's breed management tools, you can optimize your herd's genetics, improve breeding outcomes, and ensure the overall health and productivity of your herd.</h3>
                                                            </div>
                                                            <div class="bd-services__founder-icon">
                                                                <img src="{{ asset('assets/img/service/details/message.png') }}" alt="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row grid gallery-grid-items" id="service2" style="display: none;">
                        <div class="col-12 col-lg-12 col-md-6 grid-item c-3">
                            <div class="bd-singel__gallery-item mb-30">
                                <div class="bd-services__details-area">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="bd-services__details-wrapper mb-60">
                                                    <div class="bd-services__details-thumb w-img mb-50">
                                                        <img src="{{ asset('assets/img/service-details-03.jpg') }}" alt="service-thumb">
                                                    </div>
                                                    <div class="bd-services__details-content mb-40">
                                                        <div class="bd-services__details-text mb-45">
                                                            <h3 class="bd-services__details-title"> Management Service </h3>
                                                            <p class="mb-30">Welcome to Dairy Muneem's Management Services section! Our suite of management tools is designed to help you efficiently manage your dairy farming operations, track your daily activities, and make informed decisions based on real-time data. With our app, you can easily record milk production, track medical expenses, manage daily records, and generate detailed reports, among other features. Download our app today to streamline your dairy farming operations and take your business to the next level!</p>
                                                        </div>
                                                        <div class="bd-services__details-features mb-40">
                                                            <div class="bd-services__features-grid">
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/breed-icon-01.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Daily Record</a></h3>
                                                                        <p>Dairy Muneem provides a daily record of milk production and sales, as well as a streamlined order management system to optimize your dairy farm's operation</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/breed-icon-02.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Milk Recording</a></h3>
                                                                        <p>Our Milk Recording service aims to keep a daily record of milk produced by your cows, helping you maintain accurate records and optimize your dairy farm's performance.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/breed-icon-03.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Medical Expenses</a></h3>
                                                                        <p>Our Medical Expenses tool lets you keep track of your cows' medical expenses, including medication and veterinary bills, ensuring that you stay within budget and minimize costs.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/breed-icon-04.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Reports</a></h3>
                                                                        <p>Our Reports tool provides you with real-time data on your herd's performance, allowing you to make informed decisions and optimize farm operations.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/icon-01.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Farm Summary</a></h3>
                                                                        <p> Our Farm Summary tool provides you with a comprehensive overview of your farm's performance, including milk production, breeding outcomes, and financial performance.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/icon-02.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Sale Purchase</a></h3>
                                                                        <p>Our Sale Purchase feature provides you with a comprehensive record of your milk sales and production costs, allowing you to accurately calculate your profits and make informed business decisions. With Dairy Muneem, you can optimize your dairy farming operations and maximize your profitability.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/info.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Disease Info</a></h3>
                                                                        <p>Our Disease Info tool provides you with up-to-date information on common cattle diseases, helping you identify symptoms and take appropriate action to protect your herd's health.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/handle-with-care.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Stock Handling</a></h3>
                                                                        <p>Stock handling is a crucial aspect of dairy farming, and Dairy Muneem feature set includes a comprehensive stock management system designed to help you keep accurate records of remaining stock levels.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="bd-services__features-item">
                                                                    <div class="bd-services__features-icon">
                                                                        <span><img src="{{ asset('assets/img/service/details/tank.png') }}" alt="features-icon"></span>
                                                                    </div>
                                                                    <div class="bd-services__features-content">
                                                                        <h3><a href="#">Semen Tank</a></h3>
                                                                        <p> Our Semen Tank tool allows you to manage your herd's breeding program, ensuring that you have an accurate record of semen inventory and usage</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="bd-services__founder mb-45">
                                                            <div class="bd-services__founder-content">
                                                                <h3>Effective dairy management is the cornerstone of a profitable and sustainable dairy farming business. At Dairy Muneem, our aim is to empower dairy farmers with the tools and insights needed to optimize every aspect of their operations, from cow health and nutrition to breeding management, and have a daily record of sale and purchase to make your farming profitable, feed and expense management, and beyond. By leveraging innovative technology and data-driven solutions, we are committed to helping dairy farmers grow their businesses and achieve success.</h3>
                                                            </div>
                                                            <div class="bd-services__founder-icon">
                                                                <img src="{{ asset('assets/img/service/details/message.png') }}" alt="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row grid gallery-grid-items" id="service3" style="display: none;">
                            <div class="col-12 col-lg-12 col-md-6 grid-item c-4">
                                <div class="bd-singel__gallery-item mb-30">
                                    <div class="bd-services__details-area">
                                        <div class="container">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="bd-services__details-wrapper mb-60">
                                                        <div class="bd-services__details-thumb w-img mb-50">
                                                            <img src="{{ asset('assets/img/service-details-04.jpg') }}" alt="service-thumb">
                                                        </div>
                                                        <div class="bd-services__details-content mb-40">
                                                            <div class="bd-services__details-text mb-45">
                                                                <h3 class="bd-services__details-title">Tools Service</h3>
                                                                <p class="mb-30">Welcome to the Tools section of Dairy Muneem website. Our suite of tools is designed to provide you with the resources you need to manage your dairy farm operations more efficiently. Whether you need help with feed management, breeding, or disease control, our tools can help you optimize your farm operations and improve your cow's health and productivity. To access our full range of tools, we invite you to download our Dairy Muneem app, available for free on both iOS and Android platforms. With the app, you can easily manage your farm operations on the go and stay connected with a community of farmers and industry experts.</p>
                                                            </div>
                                                            <div class="bd-services__details-features mb-40">
                                                                <div class="bd-services__features-grid">
                                                                    <div class="bd-services__features-item">
                                                                        <div class="bd-services__features-icon">
                                                                            <span><img src="{{ asset('assets/img/service/details/icon-03.png') }}" alt="features-icon"></span>
                                                                        </div>
                                                                        <div class="bd-services__features-content">
                                                                            <h3><a href="#">Silage Making</a></h3>
                                                                            <p> Our Silage Making tool helps you produce high-quality silage, ensuring that your cows receive the proper nutrition they need to maintain optimal health and productivity.</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bd-services__features-item">
                                                                        <div class="bd-services__features-icon">
                                                                            <span><img src="{{ asset('assets/img/service/details/feed-icon-01.png') }}" alt="features-icon"></span>
                                                                        </div>
                                                                        <div class="bd-services__features-content">
                                                                            <h3><a href="#">Pregnancy Calculator</a></h3>
                                                                            <p> Our Pregnancy Calculator tool lets you predict your cow's due date based on their breeding date, allowing you to plan for the arrival of new calves.</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bd-services__features-item">
                                                                        <div class="bd-services__features-icon">
                                                                            <span><img src="{{ asset('assets/img/service/details/tank.png') }}" alt="features-icon"></span>
                                                                        </div>
                                                                        <div class="bd-services__features-content">
                                                                            <h3><a href="#">Dairy Mart</a></h3>
                                                                            <p> Our Dairy Mart tool connects you with buyers and sellers of cattle and dairy products, allowing you to buy and sell with ease.</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bd-services__features-item">
                                                                        <div class="bd-services__features-icon">
                                                                            <span><img src="{{ asset('assets/img/service/details/breed-icon-03.png') }}" alt="features-icon"></span>
                                                                        </div>
                                                                        <div class="bd-services__features-content">
                                                                            <h3><a href="#">Doctor on Call</a></h3>
                                                                            <p>Our Doctor on Call tool lets you connect with veterinarians and animal health experts, ensuring that you have access to professional advice and support when you need it.</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bd-services__features-item">
                                                                        <div class="bd-services__features-icon">
                                                                            <span><img src="{{ asset('assets/img/service/details/icon-01.png') }}" alt="features-icon"></span>
                                                                        </div>
                                                                        <div class="bd-services__features-content">
                                                                            <h3><a href="#">Expert</a></h3>
                                                                            <p>Our Expert tool provides you with access to a network of experienced farmers and industry experts, offering guidance and support on a range of farming topics.</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bd-services__features-item">
                                                                        <div class="bd-services__features-icon">
                                                                            <span><img src="{{ asset('assets/img/service/details/feed-icon-02.png') }}" alt="features-icon"></span>
                                                                        </div>
                                                                        <div class="bd-services__features-content">
                                                                            <h3><a href="#">Thi Calculator</a></h3>
                                                                            <p> Our Thi Calculator tool helps you maintain optimal cow health by calculating the balance of dietary energy and protein.</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bd-services__features-item">
                                                                        <div class="bd-services__features-icon">
                                                                            <span><img src="{{ asset('assets/img/service/details/icon-02.png') }}" alt="features-icon"></span>
                                                                        </div>
                                                                        <div class="bd-services__features-content">
                                                                            <h3><a href="#">Connect with Vendor</a></h3>
                                                                            <p>Our Connect with Vendor tool lets you connect with suppliers of feed, equipment, and other farm-related products, ensuring that you have access to the resources you need to run your farm efficiently.</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bd-services__features-item">
                                                                        <div class="bd-services__features-icon">
                                                                            <span><img src="{{ asset('assets/img/service/details/breed-icon-02.png') }}" alt="features-icon"></span>
                                                                        </div>
                                                                        <div class="bd-services__features-content">
                                                                            <h3><a href="#">Requirements</a></h3>
                                                                            <p>Our Requirements tool provides you with up-to-date information on regulatory requirements, ensuring that you comply with relevant laws and regulations.</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="bd-services__founder mb-45">
                                                                <div class="bd-services__founder-content">
                                                                    <h3>Our Dairy Muneem app and website aim to support profitable dairy businesses by providing farmers with the tools they need to manage their operations more effectively. We connect farmers with vendors, experts, and veterinary doctors, providing a platform for farmers to seek advice, support, and resources to optimize their operations. Our app and website also enable farmers to track their expenses and sales, monitor cow health and nutrition, and manage their breeding programs. By offering these features, we hope to improve the efficiency and profitability of dairy businesses while promoting the health and welfare of cows.</h3>
                                                                    <h3>Download the Dairy Muneem app from the Play Store to access all of our innovative features, from comprehensive cow health and nutrition management to breeding, feed, and expense management tools. Empower your dairy farming business with data-driven solutions and take your operations to the next level today</h3>
                                                                </div>
                                                                <div class="bd-services__founder-icon">
                                                                    <img src="{{ asset('assets/img/service/details/message.png') }}" alt="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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