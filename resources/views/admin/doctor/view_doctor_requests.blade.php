@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">View Farmer To Doctor Requests</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}"><i class="fa fa-dashboard"></i> Home</a></li> --}}
                        <li class="breadcrumb-item active">View Farmer To Doctor Requests</li>
                    </ol>
                </div>
            </div>
        </div>

        @if (Auth::guard('admin')->user()->position == 'Manager')
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Access Denied!</h4>
                Managers cannot access farmer to doctor requests.
            </div>
        @else
            <div class="page-content-wrapper mt-0">
                <!-- Flash Messages -->
                @if (session('smessage'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('smessage') }}
                    </div>
                @endif
                @if (session('emessage'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        {{ session('emessage') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                                <h3 class="panel-title"><i class="fa fa-money fa-fw"></i> View Farmer To Doctor Requests</h3>
                                {{-- <h4>Total Admin Earning: ₹{{ number_format($total_admin_earning, 2) }}</h4> --}}
                            </div>
                            <div class="panel-body">
                                <div class="box-body table-responsive no-padding">
                                    <table class="table table-bordered table-hover table-striped" id="doctorRequestTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Farmer Name</th>
                                                <th>Farmer Number</th>
                                                <th>Doctor Name</th>
                                                <th>Doctor Number</th>
                                                <th>Type</th>
                                                <th>Reason</th>
                                                <th>Description</th>
                                                <th>Fees</th>
                                                <th>Admin Earning</th>
                                                <th>Images</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($request_data as $index => $data)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $data->farmer ? $data->farmer->name : 'Farmer not found' }}</td>
                                                    <td>{{ $data->farmer ? $data->farmer->phone : 'Farmer not found' }}</td>
                                                    <td>{{ $data->doctor ? $data->doctor->name : 'Doctor not found' }}</td>
                                                    <td>{{ $data->doctor ? $data->doctor->phone : 'Doctor not found' }}</td>
                                                    <td>
                                                        @if ($data->is_expert)
                                                            <span class="label bg-green">Expert</span>
                                                        @else
                                                            <span class="label bg-blue">Normal</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $data->reason ?? 'N/A' }}</td>
                                                    <td>{{ $data->description ?? 'N/A' }}</td>
                                                    <td>₹{{ number_format($data->fees ?? 0, 2) }}</td>
                                                    <td>₹{{ number_format($data->admin_earning ?? 0, 2) }}</td>
                                                    <td>
                                                        @foreach (['image1', 'image2', 'image3', 'image4', 'image5'] as $img)
                                                            @if ($data->$img)
                                                                <a href="{{ asset('storage/doctor_requests/' . $data->$img) }}" target="_blank" rel="noopener noreferrer">
                                                                    <img style="border: solid #008000 1px; padding: 5px;" height="50" width="80" src="{{ asset('storage/doctor_requests/' . $data->$img) }}" alt="Request Image">
                                                                </a>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @if ($data->status == 0)
                                                            <span class="label bg-yellow">Pending</span>
                                                        @else
                                                            <span class="label bg-green">Completed</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.doctor.view_pdf', ['id' => $data->id]) }}" style="background-color: green; color: white; padding: 5px;" class="btn btn-sm">PDF</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .label {
        font-size: 12px;
        padding: 5px 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#doctorRequestTable').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                emptyTable: "No doctor requests available"
            },
            columnDefs: [
                { orderable: false, targets: [10, 12] } // Disable sorting on Images and Action
            ]
        });
    });
</script>
@endpush