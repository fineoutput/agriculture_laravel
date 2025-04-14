@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">All Rejected Vendors</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li> --}}
                        <li class="breadcrumb-item active">Rejected Vendors</li>
                    </ol>
                </div>
            </div>
        </div>
        <!-- end row -->

        <div class="page-content-wrapper">
            <div class="row">
                <div class="col-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            <!-- Success & Error Messages -->
                            @if (session('smessage'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('smessage') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                            @endif
                            @if (session('emessage'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('emessage') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                            @endif
                            <!-- End Messages -->

                            <h4 class="mt-0 header-title"><i class="fa fa-users"></i> Rejected Vendors</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <div class="table-rep-plugin">
                                <div class="table-responsive b-0" data-pattern="priority-columns">
                                    <table id="dataTable" class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Shop Name</th>
                                                <th>Address</th>
                                                <th>State</th>
                                                <th>City</th>
                                                <th>District</th>
                                                <th>Pin Code</th>
                                                <th>Commission(%)</th>
                                                <th>GST No</th>
                                                <th>Aadhar No</th>
                                                <th>Image</th>
                                                <th>Phone</th>
                                                <th>PAN Number</th>
                                                <th>Email</th>
                                                <th>Account</th>
                                                <th>COD</th>
                                                <th>Qty Discount</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $i = 1; @endphp
                                            @foreach($vendor_data as $data)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $data->name }}</td>
                                                    <td>{{ $data->shop_name }}</td>
                                                    <td>{{ $data->address }}</td>
                                                    <td>
                                                        @php
                                                            $state = \App\Models\State::where('id', $data->state)->first();
                                                            echo $state ? $state->state_name : $data->state;
                                                        @endphp
                                                    </td>
                                                    <td>{{ $data->district }}</td>
                                                    <td>{{ $data->city }}</td>
                                                    <td>{{ $data->pincode }}</td>
                                                    <td>{{ $data->comission ? $data->comission . '%' : '' }}</td>
                                                    <td>{{ $data->gst_no }}</td>
                                                    <td>{{ $data->aadhar_no }}</td>
                                                    <td>
                                                        @if($data->image)
                                                            <img src="{{ asset($data->image) }}" height="50" width="100" alt="Vendor Image">
                                                        @else
                                                            <span>Sorry No Image Found</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $data->phone }}</td>
                                                    <td>{{ $data->pan_number }}</td>
                                                    <td>{{ $data->email }}</td>
                                                    <td>{{ $data->account ? '₹' . $data->account : '₹0' }}</td>
                                                    <td>
                                                        <input type="checkbox" class="mycheckbox" data-id="{{ $data->id }}" name="checkbox" {{ $data->cod ? 'checked' : '' }} disabled>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="qtydiscount" data-id="{{ $data->id }}" name="qty_discount" value="{{ $data->qty_discount ?? '' }}" style="width: 100px;" disabled>
                                                    </td>
                                                    <td>
                                                        @if($data->is_active)
                                                            <span class="label bg-green">Unblocked</span>
                                                        @else
                                                            <span class="label bg-yellow">Blocked</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                                Action <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                <li><a href="{{ route('admin.vendor.update_status', [base64_encode($data->id), 'approve']) }}">Approve</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> <!-- card-body -->
                    </div> <!-- card -->
                </div> <!-- end col -->
            </div> <!-- end row -->
        </div> <!-- end page-content-wrapper -->
    </div> <!-- container-fluid -->
</div> <!-- content -->
@endsection

@push('styles')
<style>
    label { margin: 5px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>


@endpush