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
                                <li class="trail-item trail-end"><span>Privacy Policy</span></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="bd-breadcrumb__title">
                        <h2 style="color:#000;">Privacy Policy</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="terms-service-sec">
        <div class="container">
            <div class="main_content">
                <!-- STAT SECTION FAQ -->
                <div class="main_content">
                    <!-- STAT SECTION FAQ -->
                    <div class="section">
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="term_conditions">
                                        <h4>Privacy Policy</h4>
                                        <p>We take privacy and security concerns seriously. At the same time, we would like to give you the best possible experience when visiting our websites. Please read the following policy to understand how your personal information will be treated as you make full use of our Site. If you visit us at https://dairymuneem.in (Website) you accept these terms and conditions:</p>
                                        <hr>
                                        <h5>1) Your Privacy</h5>
                                        <p>We are extremely proud of our commitment to protect your privacy. We value the trust you place on us. We will work hard to earn your confidence so that you can enthusiastically use our services. Protecting your privacy is very important to us. We have accordingly developed this Privacy Policy to protect your personal information and keep it confidential. We endeavor to comply with laws of other countries but cannot and do not warrant that we do. We do everything we reasonably can to protect your rights of privacy on systems and the Website controlled by us, but we are not liable for any unauthorized or unlawful disclosures of your personal and confidential information made by third parties who are not subject to our control, for example advertisers and websites that have links to our Website. You should take note that the information and privacy practices of our business partners, advertisers, sponsors or other sites to which we provide hyperlinks, may be different from ours. Our privacy policy is subject to change at any time without notice. To make sure you are aware of any changes, please review this policy periodically.</p>
                                        <hr>
                                        <h5>2) Privacy Assurance</h5>
                                        <p>We will not share or rent your information with third parties except our agents and alliance partners. We use personal information to provide the services you request. Any third party with whom we share the information shall maintain reasonable level of confidentiality and data security as required under law.</p>
                                        <hr>
                                        <h5>3) Electronic Communication</h5>
                                        <p>When you visit our site or send e-mails to us or give us your feedback, you are communicating with us electronically. You consent to receive communications from us electronically. You agree that all agreements, notices, disclosures and other communications that we provide to you electronically satisfy any legal requirement that such communications be in writing</p>
                                        <hr>
                                        <h5>4) Payment and Account Information</h5>
                                        <p>Your account history with us including all billing information and communications, payment history etc. We maintain this in encrypted form on secure servers.</p>
                                        <hr>
                                        <h5>5) Transactional Information</h5>
                                        <p>Transactional history (other than banking details) about your e-commerce activities.</p>
                                        <hr>
                                        <h5>6) Service Usage</h5>
                                        <p>Information about your navigation using our Services, for example the URLs of websites, which you visit and from which you shop.</p>
                                        <hr>
                                        <h5>7) Deletion</h5>
                                        <p>We will process and store your personal data for as long as it is necessary and permitted under applicable data protection law.</p>
                                        <hr>
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