
@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">All Expert Doctors</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li> --}}
                        <li class="breadcrumb-item active">View Expert Doctors</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="page-content-wrapper">
            <div class="row">
                <div class="col-12">
                    <div class="card m-b-20">
                        <div class="card-body">
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

                            <h4 class="mt-0 header-title"><i class="fa fa-users"></i> View Expert Doctors</h4>
                            <hr style="margin-bottom: 20px; background-color: darkgrey;">

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Expertise Categories</th>
                                            <th>Email</th>
                                            <th>Image</th>
                                            <th>Aadhar No</th>
                                            <th>Type</th>
                                            <th>Degree</th>
                                            <th>Experience</th>
                                            <th>Fees</th>
                                            <th>Expertise</th>
                                            <th>Commission (%)</th>
                                            <th>Qualification</th>
                                            <th>State</th>
                                            <th>District</th>
                                            <th>City</th>
                                            <th>Pincode</th>
                                            <th>Phone</th>
                                            <th>Account</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($doctors as $index => $doctor)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $doctor->name }}</td>
                                                <td>
                                                    @if($doctor->expert_category && is_array($doctor->expert_category))
                                                        @foreach($doctor->expert_category as $categoryId)
                                                            @php
                                                                $category = \App\Models\ExpertiseCategory::where('id', $categoryId)->first();
                                                            @endphp
                                                            @if($category)
                                                                {{ $category->name . ',' }}
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td>{{ $doctor->email }}</td>
                                                <td>
                                                    @if($doctor->image)
                                                        <img src="{{ asset($doctor->image) }}" height="50" width="100" alt="Doctor Image">
                                                    @else
                                                        Sorry No image Found
                                                    @endif
                                                </td>
                                                <td>{{ $doctor->aadhar_no }}</td>
                                                <td>{{ $doctor->type }}</td>
                                                <td>{{ $doctor->degree }}</td>
                                                <td>{{ $doctor->experience }}</td>
                                                <td>{{ $doctor->fees ? '₹' . $doctor->fees : '' }}</td>
                                                <td>{{ $doctor->expertise }}</td>
                                                <td>{{ $doctor->commission ? $doctor->commission . '%' : '' }}</td>
                                                <td>{{ $doctor->qualification }}</td>
                                                <td>
                                                    @php
                                                        $state = \App\Models\State::where('id', $doctor->state)->first();
                                                    @endphp
                                                    {{ $state ? $state->state_name : '' }}
                                                </td>
                                                <td>{{ $doctor->district }}</td>
                                                <td>{{ $doctor->city }}</td>
                                                <td>{{ $doctor->pincode }}</td>
                                                <td>{{ $doctor->phone }}</td>
                                                <td>{{ $doctor->account ? '₹' . $doctor->account : '₹0' }}</td>
                                                <td>
                                                    @if($doctor->is_expert)
                                                        <span class="label bg-green">Expert</span>
                                                    @else
                                                        <span class="label bg-blue">Normal</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($doctor->is_active)
                                                        <span class="label bg-yellow">Unblocked</span>
                                                    @else
                                                        <span class="label bg-red">Blocked</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($doctor->is_approved != 2)
                                                        <div class="btn-group" id="btns{{ $index + 1 }}">
                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                                Action <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                @if($doctor->is_approved == 0)
                                                                    <li><a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'approve']) }}">Approve</a></li>
                                                                    <li><a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'reject']) }}">Reject</a></li>
                                                                @elseif($doctor->is_approved == 1)
                                                                    @if($doctor->is_active)
                                                                        <li><a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'inactive']) }}">Block</a></li>
                                                                    @else
                                                                        <li><a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'active']) }}">Unblock</a></li>
                                                                    @endif
                                                                    @if($doctor->is_expert)
                                                                        <li><a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'normal']) }}">Convert To Normal</a></li>
                                                                        <li><a href="{{ route('admin.doctor.set_commission', ['idd' => base64_encode($doctor->id)]) }}">Update Expert Doctor</a></li>
                                                                        {{-- <li><a href="{{ route('admin.doctor.transactions', ['idd' => base64_encode($doctor->id)]) }}">Payment Transactions</a></li> --}}
                                                                    @else
                                                                        <li><a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'expert']) }}">Convert To Expert</a></li>
                                                                    @endif
                                                                    <li><a href="{{ route('admin.doctor.edit_doctor', ['idd' => base64_encode($doctor->id)]) }}">Edit</a></li>
                                                                    <li><a href="javascript:;" class="dCnf" data-id="{{ $index + 1 }}">Delete</a></li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                        <div style="display:none" id="cnfbox{{ $index + 1 }}">
                                                            <p>Are you sure you want to delete this?</p>
                                                            <a href="{{ route('admin.doctor.delete', ['idd' => base64_encode($doctor->id)]) }}" class="btn btn-danger">Yes</a>
                                                            <a href="javascript:;" class="cans btn btn-default" data-id="{{ $index + 1 }}">No</a>
                                                        </div>
                                                    @else
                                                        NA
                                                    @endif
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
    </div>
</div>

<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            responsive: true,
            stateSave: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copyHtml5',
                    exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18] }
                },
                {
                    extend: 'csvHtml5',
                    exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18] }
                },
                {
                    extend: 'excelHtml5',
                    exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18] }
                },
                {
                    extend: 'pdfHtml5',
                    exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18] }
                },
                {
                    extend: 'print',
                    exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18] }
                }
            ]
        });

        $(document.body).on('click', '.dCnf', function() {
            var i = $(this).attr("data-id");
            $("#btns" + i).hide();
            $("#cnfbox" + i).show();
        });

        $(document.body).on('click', '.cans', function() {
            var i = $(this).attr("data-id");
            $("#btns" + i).show();
            $("#cnfbox" + i).hide();
        });
    });
</script>
@endsection

@push('styles')
<style>
    label {
        margin: 5px;
    }
    .label {
        padding: 5px 10px;
        color: #fff;
        border-radius: 3px;
    }
    .bg-green {
        background-color: #28a745;
    }
    .bg-blue {
        background-color: #007bff;
    }
    .bg-yellow {
        background-color: #ffc107;
    }
    .bg-red {
        background-color: #dc3545;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css">
@endpush

@push('scripts')

@endpush