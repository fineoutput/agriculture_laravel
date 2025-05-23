@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Dashboard <small>Version 2.0</small></h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>

        @if (Auth::guard('admin')->user()->position == 'Manager')
            <div class="alert alert-danger">
                <h4><i class="icon fa fa-ban"></i> Access Denied!</h4>
                Managers cannot access the dashboard.
            </div>
        @else
            <div class="page-content-wrapper mt-0">
                <!-- General Statistics -->
                <div class="row">
                    <div class="col-xl-3 col-md-6">
    <div class="card bg-primary mini-stat position-relative">
        <a href="{{ route('admin.doctor.normal') }}">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase verti-label text-white-50">Doctors</h6>
                    <div class="text-white">
                        <h6 class="text-uppercase mt-0 text-white-50">Normal Doctors</h6>
                        <h3 class="mb-3 mt-0">{{ number_format($normal) }}</h3>
                        <div class="">
                            <span class="ml-2">Total Normal Doctors</span>
                        </div>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="fa fa-user-md display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card bg-danger mini-stat position-relative">
        <a href="{{ route('admin.doctor.accepted') }}">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase verti-label text-white-50">Doctors</h6>
                    <div class="text-white">
                        <h6 class="text-uppercase mt-0 text-white-50">Expert Doctors</h6>
                        <h3 class="mb-3 mt-0">{{ number_format($expert) }}</h3>
                        <div class="">
                            <span class="ml-2">Total Expert Doctors</span>
                        </div>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="fa fa-plus-square display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card bg-success mini-stat position-relative">
        <a href="{{ route('admin.farmers.index') }}">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase verti-label text-white-50">Farmers</h6>
                    <div class="text-white">
                        <h6 class="text-uppercase mt-0 text-white-50">Total Farmers</h6>
                        <h3 class="mb-3 mt-0">{{ number_format($farmer) }}</h3>
                        <div class="">
                            <span class="ml-2">Registered Farmers</span>
                        </div>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="ion ion-ios-people-outline display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                   <div class="col-xl-3 col-md-6">
    <div class="card bg-warning mini-stat position-relative">
        <a href="{{ route('admin.vendor.accepted') }}">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase verti-label text-white-50">Vendors</h6>
                    <div class="text-white">
                        <h6 class="text-uppercase mt-0 text-white-50">Total Vendors</h6>
                        <h3 class="mb-3 mt-0">{{ number_format($vendor) }}</h3>
                        <div class="">
                            <span class="ml-2">Approved Vendors</span>
                        </div>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="fa fa-users display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                </div>
                 <div class="row">
                   <div class="col-xl-3 col-md-6">
    <div class="card bg-dark mini-stat position-relative">
        <a href="{{ route('admin.products.view') }}">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase verti-label text-white-50">Products</h6>
                    <div class="text-white">
                        <h6 class="text-uppercase mt-0 text-white-50">Admin Products</h6>
                        <h3 class="mb-3 mt-0">{{ number_format($admin_product) }}</h3>
                        <div class="">
                            <span class="ml-2">Listed by Admin</span>
                        </div>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="fa fa-cube display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card bg-info mini-stat position-relative">
        <a href="{{ route('admin.products.vendor_accepted') }}">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase verti-label text-white-50">Products</h6>
                    <div class="text-white">
                        <h6 class="text-uppercase mt-0 text-white-50">Vendor Products</h6>
                        <h3 class="mb-3 mt-0">{{ number_format($vendor_product) }}</h3>
                        <div class="">
                            <span class="ml-2">Listed by Vendors</span>
                        </div>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="fa fa-caret-square-o-up display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card bg-primary mini-stat position-relative">
        <a href="{{ route('admin.doctor.new') }}">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase verti-label text-white-50">Doctors</h6>
                    <div class="text-white">
                        <h6 class="text-uppercase mt-0 text-white-50">New Doctor Requests</h6>
                        <h3 class="mb-3 mt-0">{{ number_format($total_doctor_requests) }}</h3>
                        <div class="">
                            <span class="ml-2">Pending Approval</span>
                        </div>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="fa fa-user-md display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                   <div class="col-xl-3 col-md-6">
    <div class="card bg-secondary mini-stat position-relative">
        <a href="{{ route('admin.vendor.new') }}">
            <div class="card-body">
                <div class="mini-stat-desc">
                    <h6 class="text-uppercase verti-label text-white-50">Vendors</h6>
                    <div class="text-white">
                        <h6 class="text-uppercase mt-0 text-white-50">New Vendor Requests</h6>
                        <h3 class="mb-3 mt-0">{{ number_format($new) }}</h3>
                        <div class="">
                            <span class="ml-2">Pending Approval</span>
                        </div>
                    </div>
                    <div class="mini-stat-icon">
                        <i class="fa fa-caret-square-o-up display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                </div>

                <!-- Service Reports -->
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="mt-4">Service Reports</h3>
                    </div>
                </div>
                <div class="row">
                 <div class="col-xl-3 col-md-6">
    <div class="card mini-stat position-relative" style="background-color: #8d8671;">
        <a href="{{ route('admin.view_service_report', ['type' => 'weight_calculator']) }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Services</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Weight Calculator</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($service_report->weight_calculator ?? 0) }}</h3>
                    <span class="ml-2">Used Tool</span>
                    <div class="mini-stat-icon">
                        <i class="fa fa-calculator display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card bg-danger mini-stat position-relative">
        <a href="{{ route('admin.view_service_report', ['type' => 'dmi_calculator']) }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Services</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">DMI Calculator</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($service_report->dmi_calculator ?? 0) }}</h3>
                    <span class="ml-2">Used Tool</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-arrows-move display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card mini-stat position-relative" style="background-color: #414556;">
        <a href="{{ route('admin.view_service_report', ['type' => 'feed_calculator']) }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Services</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Feed Calculator</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($service_report->feed_calculator ?? 0) }}</h3>
                    <span class="ml-2">Used Tool</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-border-style display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card mini-stat position-relative" style="background-color: #680000;">
        <a href="{{ route('admin.view_service_report', ['type' => 'preg_calculator']) }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Services</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Pregnancy Calculator</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($service_report->preg_calculator ?? 0) }}</h3>
                    <span class="ml-2">Used Tool</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-calendar2-event-fill display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                <div class="row">
                    <div class="col-xl-3 col-md-6">
    <div class="card mini-stat position-relative" style="background-color: #680000;">
        <a href="{{ route('admin.view_service_report', ['type' => 'silage_making']) }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Services</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Silage Making</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($service_report->silage_making ?? 0) }}</h3>
                    <span class="ml-2">Used Tool</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-shop display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                   <div class="col-xl-3 col-md-6">
    <div class="card bg-primary mini-stat position-relative">
        <a href="{{ route('admin.view_service_report', ['type' => 'animal_req']) }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Services</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Animal Requirement</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($service_report->animal_req ?? 0) }}</h3>
                    <span class="ml-2">Used Tool</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-meta display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                   <div class="col-xl-3 col-md-6">
    <div class="card mini-stat position-relative" style="background-color: #35bf8a;">
        <a href="{{ route('admin.view_service_report', ['type' => 'pro_req']) }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Services</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Project Requirement</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($service_report->pro_req ?? 0) }}</h3>
                    <span class="ml-2">Used Tool</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-pie-chart-fill display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                </div>

                <!-- Admin Orders -->
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="mt-4">Admin Orders</h3>
                    </div>
                </div>
                <div class="row">
                   <div class="col-xl-3 col-md-6">
    <div class="card bg-info mini-stat position-relative">
        <a href="{{ route('admin.orders.all') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Orders</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Total Orders</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($total_orders) }}</h3>
                    <span class="ml-2">Count</span>
                    <div class="mini-stat-icon">
                        <i class="fa fa-calculator display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card bg-info mini-stat position-relative">
        <a href="{{ route('admin.orders.today') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Orders</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Today's Orders</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($today) }}</h3>
                    <span class="ml-2">Today</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-amd display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card bg-danger mini-stat position-relative">
        <a href="{{ route('admin.orders.new') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Orders</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">New Orders</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($new_orders) }}</h3>
                    <span class="ml-2">Pending</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-node-plus display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card bg-danger mini-stat position-relative">
        <a href="{{ route('admin.orders.accepted') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Orders</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Accepted Orders</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($accepted_orders) }}</h3>
                    <span class="ml-2">Confirmed</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-bezier display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                </div>
                <div class="row">
                    <div class="col-xl-3 col-md-6">
    <div class="card bg-success mini-stat position-relative">
        <a href="{{ route('admin.orders.dispatched') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Orders</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Dispatched Orders</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($dispatched_orders) }}</h3>
                    <span class="ml-2">Shipped</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-boxes display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                   <div class="col-xl-3 col-md-6">
    <div class="card bg-danger mini-stat position-relative">
        <a href="{{ route('admin.orders.completed') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Orders</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Completed Orders</h6>
                    <h3 class="mb-3 mt-0">{{ number_format($delivered_orders) }}</h3>
                    <span class="ml-2">Delivered</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-brilliance display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                   <div class="col-xl-3 col-md-6">
    <div class="card bg-warning mini-stat position-relative">
        <a href="{{ route('admin.orders.completed') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Sales</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Total Sales</h6>
                    <h3 class="mb-3 mt-0">₹{{ number_format($total_earning, 2) }}</h3>
                    <span class="ml-2">Revenue</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-bullseye display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card bg-warning mini-stat position-relative">
        <a href="{{ route('admin.orders.completed') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Sales</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Total COD Sales</h6>
                    <h3 class="mb-3 mt-0">₹{{ number_format($total_cod_earning, 2) }}</h3>
                    <span class="ml-2">COD Revenue</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-bullseye display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                </div>
                <div class="row">
                    <div class="col-xl-3 col-md-6">
    <div class="card bg-dark mini-stat position-relative">
        <a href="{{ route('admin.doctor.view_doctor_requests') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Doctors</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Doctors Earning</h6>
                    <h3 class="mb-3 mt-0">₹{{ number_format(max(0, $total_d_orders - $doctors_earning), 2) }}</h3>
                    <span class="ml-2">Pending to Doctor</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-capsule display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card bg-primary mini-stat position-relative">
        <a href="{{ route('admin.vendor_orders.completed') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Vendors</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Vendors Earning</h6>
                    <h3 class="mb-3 mt-0">₹{{ number_format(max(0, $total_v_orders - $vendor_earning), 2) }}</h3>
                    <span class="ml-2">Pending to Vendor</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-cassette-fill display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                   <div class="col-xl-3 col-md-6">
    <div class="card mini-stat position-relative" style="background-color: #5f9d69;">
        <a href="{{ route('admin.doctor.accepted') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Doctors</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Payments to Doctors</h6>
                    <h3 class="mb-3 mt-0">₹{{ number_format($total_payments_processed_to_doctor, 2) }}</h3>
                    <span class="ml-2">Transferred</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-clipboard-data display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                    <div class="col-xl-3 col-md-6">
    <div class="card mini-stat position-relative" style="background-color: #5f9d69;">
        <a href="{{ route('admin.vendor.accepted') }}">
            <div class="card-body">
                <div class="mini-stat-desc text-white">
                    <h6 class="text-uppercase verti-label text-white-50">Vendors</h6>
                    <h6 class="text-uppercase mt-0 text-white-50">Payments to Vendors</h6>
                    <h3 class="mb-3 mt-0">₹{{ number_format($total_payments_processed_to_vendor, 2) }}</h3>
                    <span class="ml-2">Transferred</span>
                    <div class="mini-stat-icon">
                        <i class="bi bi-clipboard-data display-2"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

                </div>
                <div class="row">
                    <div class="col-xl-3 col-md-6">
  <div class="card bg-dark mini-stat position-relative">
    <a href="{{ route('admin.doctor.new') }}">
      <div class="card-body">
        <div class="mini-stat-desc">
          <h6 class="text-uppercase verti-label text-white-50">Total Doctor Requests</h6>
          <div class="text-white">
            <h6 class="text-uppercase mt-0 text-white-50">Total Doctor Requests</h6>
            <h3 class="mb-3 mt-0">{{ number_format($total_doctor_requests) }}</h3>
            <div><span class="ml-2">Total Doctor Requests</span></div>
          </div>
          <div class="mini-stat-icon">
            <i class="bi bi-diamond-half display-2"></i>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>
                    <div class="col-xl-3 col-md-6">
  <div class="card" style="background-color: #663259;" class="mini-stat position-relative">
    <a href="{{ route('admin.vendor_orders.completed') }}">
      <div class="card-body">
        <div class="mini-stat-desc">
          {{-- <h6 class="text-uppercase verti-label text-white-50">Total Vendor Orders</h6> --}}
          <div class="text-white">
            <h6 class="text-uppercase mt-0 text-white-50">Total Vendor Orders</h6>
            <h3 class="mb-3 mt-0">{{ number_format($total_vendor_orders) }}</h3>
            <div><span class="ml-2">Total Vendor Orders</span></div>
          </div>
          
        </div>
      </div>
    </a>
  </div>
</div>
                    <div class="col-xl-3 col-md-6">
  <div class="card bg-primary mini-stat position-relative">
    <a href="{{ route('admin.subscription.view_subscribed_data') }}">
      <div class="card-body">
        <div class="mini-stat-desc">
          <h6 class="text-uppercase verti-label text-white-50">Subscriptions Purchased</h6>
          <div class="text-white">
            <h6 class="text-uppercase mt-0 text-white-50">Subscriptions Purchased</h6>
            <h3 class="mb-3 mt-0">₹{{ number_format($subscriptions_purchased, 2) }}</h3>
            <div><span class="ml-2">Subscriptions Purchased</span></div>
          </div>
          <div class="mini-stat-icon">
            <i class="bi bi-droplet-half display-2"></i>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>
                    <div class="col-xl-3 col-md-6">
  <div class="card" style="background-color: #4b47a3;" class="mini-stat position-relative">
    <a href="{{ route('admin.subscription.view_check_feed') }}">
      <div class="card-body">
        <div class="mini-stat-desc">
          <h6 class="text-uppercase verti-label text-white-50">Check My Feed Purchased</h6>
          <div class="text-white">
            <h6 class="text-uppercase mt-0 text-white-50">Check My Feed Purchased</h6>
            <h3 class="mb-3 mt-0">₹{{ number_format($check_my_feed, 2) }}</h3>
            <div><span class="ml-2">Check My Feed Purchased</span></div>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>
                </div> 
            </div>
        @endif
    </div>
</div>


@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
<style>
    .info-box {
        min-height: 90px;
        margin-bottom: 20px;
    }
    .info-box-icon {
        font-size: 45px;
        line-height: 90px;
    }
    .info-box-content {
        padding: 5px 10px;
    }
    .info-box-text {
        font-size: 14px;
        white-space: normal;
    }
    .info-box-number {
        font-size: 24px;
    }
    .mt-4 {
        margin-top: 1.5rem;
    }
</style>
@endpush
@endsection