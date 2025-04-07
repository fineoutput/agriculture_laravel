@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Farmer To Doctor Requests</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li> --}}
                        <li class="breadcrumb-item active">View Farmer To Doctor Requests</li>
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

                            <div class="row" style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="col-md-10">
                                    <h4 class="mt-0 header-title">Farmer To Doctor Requests List</h4>
                                </div>
                                <div class="col-md-2">
                                    {{-- <h4>Total Admin Earning: ₹{{ $count }}</h4> --}}
                                </div>
                            </div>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <div class="table-rep-plugin">
                                <div class="table-responsive b-0" data-pattern="priority-columns">
                                    <table id="requestTable" class="table table-striped">
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
                                            @php $i = 1; @endphp
                                            @foreach($doctor_data as $request)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $request->farmer ? $request->farmer->name : 'Farmer not found' }}</td>
                                                    <td>{{ $request->farmer ? $request->farmer->phone : 'Farmer not found' }}</td>
                                                    <td>{{ $request->doctor ? $request->doctor->name : 'Doctor not found' }}</td>
                                                    <td>{{ $request->doctor ? $request->doctor->phone : 'Doctor not found' }}</td>
                                                    <td>
                                                        @if($request->is_expert)
                                                            <span class="badge badge-success">Expert</span>
                                                        @else
                                                            <span class="badge badge-primary">Normal</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $request->reason }}</td>
                                                    <td>{{ $request->description }}</td>
                                                    <td>₹{{ $request->fees ?? 0 }}</td>
                                                    <td>
                                                        @php
                                                            $earning = $request->paymentTransactions->first() && $request->paymentTransactions->first()->cr 
                                                                    ? ($request->fees - $request->paymentTransactions->first()->cr) 
                                                                    : 0;
                                                        @endphp
                                                        ₹{{ $earning }}
                                                    </td>
                                                    <td>
                                                        @foreach(['image1', 'image2', 'image3', 'image4', 'image5'] as $img)
                                                            @if($request->$img)
                                                                <a href="{{ asset($request->$img) }}" target="_blank" rel="noopener noreferrer">
                                                                    <img src="{{ asset($request->$img) }}" style="border: solid #008000 1px; padding: 5px;" height="50" width="80">
                                                                </a>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @if($request->status == 0)
                                                            <span class="badge badge-warning">Pending</span>
                                                        @else
                                                            <span class="badge badge-success">Completed</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.doctor.view-pdf', ['idd' => base64_encode($request->id)]) }}" 
                                                           class="btn btn-success btn-sm">PDF</a>
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
    label {
        margin: 5px;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#requestTable').DataTable({
            responsive: true
        });
    });
</script>
@endpush