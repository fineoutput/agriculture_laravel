<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>{{config('constants.options.SITE_NAME')}} | @if(!empty($title)) {{$title}}@else Admin @endif</title>
    <meta content="Admin Dashboard" name="description" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" href="{{asset('admin/assets/images/favicon.png')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{asset('admin/assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.3.3/css/bootstrap-colorpicker.min.css" rel="stylesheet">
    <link href="{{asset('admin/assets/css/metismenu.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('admin/assets/css/icons.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('admin/assets/css/style.css')}}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />


</head>

<body>
    <input type="hidden" id="base_path" value="{{config('constants.options.SITE_NAME')}}>">
    <!-- Begin page -->
    <div id="wrapper">
        <!-- Top Bar Start -->
        <div class="topbar">
            <!-- LOGO -->
            <div class="topbar-left">
                <a href="{{route('admin_index')}}" class="logo">
                    <span>
                        <img src="{{asset('admin/assets/images/logo.png')}}" alt="" style="width: 90px;">
                    </span>
                    <i>
                        <img src="{{asset('admin/assets/images/favicon-bae.png')}}" alt="">
                    </i>
                </a>
            </div>
            <nav class="navbar-custom">
                <ul class="navbar-right d-flex list-inline float-right mb-0">
                    <li class="dropdown notification-list d-none d-sm-block">
                        <form role="search" class="app-search" style="display:none">
                            <div class="form-group mb-0">
                                <input type="text" class="form_control" placeholder="Search..">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </div>
                        </form>
                    </li>
                    <li class="dropdown notification-list" style="display:none">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="mdi mdi-bell noti-icon"></i>
                            <span class="badge badge-pill badge-info noti-icon-badge">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg">
                            <!-- item-->
                            <h6 class="dropdown-item-text">
                                Notifications (37)
                            </h6>
                            <div class="slimscroll notification-item-list">
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item active">
                                    <div class="notify-icon bg-success"><i class="mdi mdi-cart-outline"></i></div>
                                    <p class="notify-details">Your order is placed<span class="text-muted">Dummy text of the printing and typesetting industry.</span></p>
                                </a>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <div class="notify-icon bg-warning"><i class="mdi mdi-message"></i></div>
                                    <p class="notify-details">New Message received<span class="text-muted">You have 87 unread messages</span></p>
                                </a>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <div class="notify-icon bg-info"><i class="mdi mdi-flag"></i></div>
                                    <p class="notify-details">Your item is shipped<span class="text-muted">It is a long established fact that a reader will</span></p>
                                </a>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <div class="notify-icon bg-primary"><i class="mdi mdi-cart-outline"></i></div>
                                    <p class="notify-details">Your order is placed<span class="text-muted">Dummy text of the printing and typesetting industry.</span></p>
                                </a>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <div class="notify-icon bg-danger"><i class="mdi mdi-message"></i></div>
                                    <p class="notify-details">New Message received<span class="text-muted">You have 87 unread messages</span></p>
                                </a>
                            </div>
                            <!-- All-->
                            <a href="javascript:void(0);" class="dropdown-item text-center text-primary">
                                View all <i class="fi-arrow-right"></i>
                            </a>
                        </div>
                    </li>
                    <li class="dropdown notification-list">
                        <p class="nav-link" style="color:white">Hello, <span>{{ucwords(Session::get('admin_name'))}}</span> </p>
                    </li>
                    <li class="dropdown notification-list">
                        <div class="dropdown notification-list nav-pro-img">
                            <a class="dropdown-toggle nav-link arrow-none waves-effect nav-user waves-light" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                @php
                                $admin_image = Session::get('admin_image');
                                if (!empty($admin_image)) {
                                @endphp
                                <img src="{{asset($admin_image)}}" alt="user" class="rounded-circle">
                                @php
                                } else {
                                @endphp
                                <img src="{{asset('admin/assets/images/users/profile.png')}}" alt="user" class="rounded-circle">
                                @php } @endphp
                            </a>
                            <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                                <!-- item-->
                                <a class="dropdown-item" href="{{route('admin_profile')}}"><i class="mdi mdi-account-circle m-r-5"></i> Profile</a>
                                <div class="dropdown-divider"></div>
                                @php
                                $admin_data = Session::get('admin_id');
                                @endphp

                                @if (!empty($admin_data))
                                <a class="dropdown-item text-danger" href="{{ route('admin_logout') }}">
                                    <i class="mdi mdi-power text-danger"></i> Logout
                                </a>
                                @endif

                            </div>
                        </div>
                    </li>
                </ul>
                <ul class="list-inline menu-left mb-0">
                    <li class="float-left">
                        <button class="button-menu-mobile open-left waves-effect waves-light">
                            <i class="mdi mdi-menu"></i>
                        </button>

                    </li>
                    <li class="d-none d-sm-block">
                    </li>
                </ul>
            </nav>
        </div>
        <!-- Top Bar End -->
        <!-- ========== Left Sidebar Start ========== -->
        <div class="left side-menu">
            <div class="slimscroll-menu" id="remove-scroll">
                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu" id="side-menu">
                        <li class="list-style" style="position: sticky;top: -36px;z-index: 99;">
                            <a class="waves-effect  metismenu-2" style="padding: 13px 0px;"><button class="search-btn" type="submit"><i class="fa fa-search  metismenu-3 search-btn" style="color:#70767b"></i></button><span class="main-boxx">
                                    <form role="search" class="app-search  metismenu-4">
                                        <div class="form-group mb-0">
                                            <input type="text" id="searchInput" class="form_control" placeholder="Search.." style="margin: 0px 5px;width:auto;color:#70767b;background:white;border: 1px solid #70767b;" class="input-1" autocomplete="off">
                                            <!-- <button type="submit"><i class="fa fa-search" style=
                                             "color:#70767b"></i></button> -->
                                        </div>
                                    </form>
                                </span>
                            </a>
                        </li>
                        <li class="menu-title">Main</li>
                        <li>
                            <a href="{{route('/')}}" target="_blank" class="waves-effect"><i class="bi bi-eye-fill"></i><span> View Site </span></a>
                        </li>
                        <?php
                        $admin_services = Session::get('services');
                        $ser = json_decode($admin_services);
                        // print_r($ser); die();
                        if ($ser[0] == "999") {
                            $admin_sidebar = App\adminmodel\AdminSidebar::OrderBy('seq', 'asc')->get();
                            // print_r($admin); die();
                            if (!empty($admin_sidebar)) {
                                foreach ($admin_sidebar as $sidebar) {
                        ?>
                                    <?php if ($sidebar->url == "#") { ?>
                                        <li>
                                            <a href="javascript:void(0);" class="waves-effect"><i class="<?= $sidebar->icon ?>"></i><span> <?= $sidebar->name; ?> <span class="float-right menu-arrow"><i class="mdi mdi-plus"></i></span> </span></a>
                                            <ul class="submenu">
                                                <?php
                                                $admin_sidebar2 = App\adminmodel\AdminSidebar2::where('main_id', $sidebar->id)->get();
                                                // print_r($admin); die();
                                                if (!empty($admin_sidebar2)) {
                                                    foreach ($admin_sidebar2 as $sidebar2) {
                                                ?>
                                                        <li><a href="{{route($sidebar2->url)}}">
                                                                <?= $sidebar2->name; ?>
                                                            </a></li>
                                                <?php  }
                                                }   ?>
                                            </ul>
                                        </li>
                                    <?php } else { ?>


                                        <li>
                                            <a href="{{route($sidebar->url)}}" class="waves-effect"><i class="<?= $sidebar->icon ?>"></i><span> <?= $sidebar->name; ?> </span></a>
                                        </li>
                                    <?php } ?>
                            <?php
                                }
                            }
                            ?>
                            <?php } else {
                            foreach ($ser as $s) {
                                $sidebar = App\adminmodel\AdminSidebar::where('id', $s)->first();
                                // print_r($admin); die();
                                if (!empty($sidebar)) {
                            ?>
                                    <?php if ($sidebar->url == "#") { ?>
                                        <li>
                                            <a href="javascript:void(0);" class="waves-effect"><i class="<?= $sidebar->icon ?>"></i><span> <?= $sidebar->name; ?> <span class="float-right menu-arrow"><i class="mdi mdi-plus"></i></span> </span></a>
                                            <ul class="submenu">
                                                <?php
                                                $admin_sidebar2 = App\adminmodel\AdminSidebar2::where('main_id', $sidebar->id)->get();
                                                // print_r($admin); die();
                                                if (!empty($admin_sidebar2)) {
                                                    foreach ($admin_sidebar2 as $sidebar2) {
                                                ?>
                                                        <li><a href="{{route($sidebar2->url)}}">
                                                                <?= $sidebar2->name; ?>
                                                            </a></li>
                                                <?php  }
                                                }   ?>
                                            </ul>
                                        </li>
                                    <?php } else { ?>
                                        <li>
                                            <a href="{{route($sidebar->url)}}" class="waves-effect"><i class="mdi mdi-email"></i><span> <?= $sidebar->name; ?> </span></a>
                                        </li>
                                    <?php } ?>
                                <?php
                                }
                                ?>
                        <?php }
                        } ?>
                    </ul>
                </div>
                <!-- Sidebar -->
                <div class="clearfix"></div>
            </div>
            <!-- Sidebar -left -->
        </div>
        <!-- Left Sidebar End -->
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="content-page">