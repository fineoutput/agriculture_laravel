@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Farmer {{ $heading }}</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">View Farmer {{ $heading }}</li>
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

                            <h4 class="mt-0 header-title"><i class="fa fa-list"></i> View Farmer {{ $heading }}</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <div class="table-rep-plugin">
                                <div class="table-responsive b-0">
                                    <table id="userTable" class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Farmer Name</th>
                                                <th>Farmer Phone</th>
                                                <th>Farmer City</th>
                                                <th>Information Type</th>
                                                <th>Animal Name</th>
                                                <th>Milk Production</th>
                                                <th>Lactation</th>
                                                <th>Location</th>
                                                <th>Pastorate Pregnant</th>
                                                <th>Expected Price</th>
                                                <th>Animal Type</th>
                                                <th>Description</th>
                                                <th>Remarks</th>
                                                <th>Image1</th>
                                                <th>Image2</th>
                                                <th>Image3</th>
                                                <th>Image4</th>
                                                <th>Video</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $i = 1; @endphp
                                            @forelse($sale_purchase as $data)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $farmer_details[$data->id]->name ?? 'Unknown' }}</td>
                                                    <td>{{ $farmer_details[$data->id]->phone ?? 'N/A' }}</td>
                                                    <td>{{ $farmer_details[$data->id]->city ?? 'N/A' }}</td>
                                                    <td>{{ $data->information_type ?? 'N/A' }}</td>
                                                    <td>{{ $data->animal_name ?? 'N/A' }}</td>
                                                    <td>{{ $data->milk_production ?? 'N/A' }}</td>
                                                    <td>{{ $data->lactation ?? 'N/A' }}</td>
                                                    <td>{{ $data->location ?? 'N/A' }}</td>
                                                    <td>{{ $data->pastorate_pregnant ?? 'N/A' }}</td>
                                                    <td>₹{{ number_format($data->expected_price ?? 0, 2) }}</td>
                                                    <td>{{ $data->animal_type ?? 'N/A' }}</td>
                                                    <td>{{ $data->description ?? 'N/A' }}</td>
                                                    <td>{{ $data->remarks ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($data->image1)
                                                            <img src="{{ asset($data->image1) }}" height="50" width="100" alt="Image1">
                                                        @else
                                                            Sorry, No image found
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->image2)
                                                            <img src="{{ asset($data->image2) }}" height="50" width="100" alt="Image2">
                                                        @else
                                                            Sorry, No image found
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->image3)
                                                            <img src="{{ asset($data->image3) }}" height="50" width="100" alt="Image3">
                                                        @else
                                                            Sorry, No image found
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->image4)
                                                            <img src="{{ asset($data->image4) }}" height="50" width="100" alt="Image4">
                                                        @else
                                                            Sorry, No image found
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->video)
                                                            <video width="100" height="50" controls>
                                                                <source src="{{ asset($data->video) }}" type="video/mp4">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        @else
                                                            Sorry, No video found
                                                        @endif
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($data->date)->format('F j, Y, g:i a') }}</td>
                                                    <td>
                                                        @if($data->status <= 1)
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                                    Action <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu" role="menu">
                                                                    @if($data->status == 0)
                                                                        <li><a href="{{ route('admin.animal_sale_purchase.update_status', [base64_encode($data->id), 'accept']) }}">Accept</a></li>
                                                                        <li><a href="{{ route('admin.animal_sale_purchase.update_status', [base64_encode($data->id), 'reject']) }}">Reject</a></li>
                                                                    @elseif($data->status == 1)
                                                                        <li><a href="{{ route('admin.animal_sale_purchase.update_status', [base64_encode($data->id), 'complete']) }}">Complete</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @else
                                                            NA
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="21" class="text-center">No sale/purchase records found</td>
                                                </tr>
                                            @endforelse
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
    .table th, .table td { vertical-align: middle; text-align: center; }
    .dropdown-menu { min-width: 120px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>

<script type="text/javascript">
$(document).ready(function() {
    $('#userTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [20] } // Action column
        ]
    });
});
</script>
@endpush