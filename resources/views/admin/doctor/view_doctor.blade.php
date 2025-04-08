@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">All New Doctors</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li> --}}
                        <li class="breadcrumb-item active">View Doctors</li>
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

                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="mt-0 header-title"><i class="fa fa-users"></i> View Doctors</h4>
                                </div>
                            </div>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <div class="table-rep-plugin">
                                <div class="table-responsive b-0" data-pattern="priority-columns">
                                    <table id="dataTable" class="table table-striped">
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
                                                <th>Commission(%)</th>
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
                                            @php $i = 1; @endphp
                                            @foreach($doctor_data as $doctor)
                                                <tr>
                                                    <td>{{ $i }}</td>
                                                    <td>{{ $doctor->name }}</td>
                                                    <td>
                                                        @php
                                                            $expert_categories = $doctor->expert_category;
                                                            if (is_array($expert_categories)) {
                                                                $names = \App\Models\ExpertiseCategory::whereIn('id', $expert_categories)
                                                                    ->pluck('name')
                                                                    ->implode(', ');
                                                                echo $names;
                                                            }
                                                        @endphp
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
                                                    <td>{{ $doctor->degree ?? 'N/A' }}</td>
                                                    <td>{{ $doctor->experience ?? 'N/A' }}</td>
                                                    <td>{{ $doctor->fees ? '₹' . $doctor->fees : '' }}</td>
                                                    <td>{{ $doctor->expertise ?? 'N/A' }}</td>
                                                    <td>{{ $doctor->commission ? $doctor->commission . '%' : '' }}</td>
                                                    <td>{{ $doctor->qualification ?? 'N/A' }}</td>
                                                    <td>
                                                        @php
                                                            $state = \App\Models\State::find($doctor->state);
                                                            echo $state ? $state->state_name : 'N/A';
                                                        @endphp
                                                    </td>
                                                    <td>{{ $doctor->district }}</td>
                                                    <td>{{ $doctor->city }}</td>
                                                    <td>{{ $doctor->pincode }}</td>
                                                    <td>{{ $doctor->phone ?? 'N/A' }}</td>
                                                    <td>{{ $doctor->account ? '₹' . $doctor->account : '₹0' }}</td>
                                                    <td>
                                                        @if($doctor->is_expert)
                                                            <span class="badge badge-success">Expert</span>
                                                        @else
                                                            <span class="badge badge-primary">Normal</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($doctor->is_active)
                                                            <span class="badge badge-warning">Unblocked</span>
                                                        @else
                                                            <span class="badge badge-danger">Blocked</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" id="btns{{ $i }}">
                                                            @if($doctor->is_approved != 2)
                                                                <div class="dropdown">
                                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                                                        Action <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu" role="menu">
                                                                        @if($doctor->is_approved == 0)
                                                                            <li><a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'approve']) }}">Approve</a></li>
                                                                            <li><a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'reject']) }}">Reject</a></li>
                                                                        @elseif($doctor->is_approved == 1)
                                                                            <li>
                                                                                @if($doctor->is_active)
                                                                                    <a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'inactive']) }}">Blocked</a>
                                                                                @else
                                                                                    <a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'active']) }}">Unblocked</a>
                                                                                @endif
                                                                            </li>
                                                                            <li>
                                                                                @if($doctor->is_expert)
                                                                                    <a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'normal']) }}">Convert To Normal</a>
                                                                                @else
                                                                                    <a href="{{ route('admin.doctor.update-status', ['idd' => base64_encode($doctor->id), 't' => 'expert']) }}">Convert To Expert</a>
                                                                                @endif
                                                                            </li>
                                                                            @if($doctor->is_expert)
                                                                                <li><a href="{{ route('admin.doctor.set-commission', ['idd' => base64_encode($doctor->id)]) }}">Update Expert Doctor</a></li>
                                                                                {{-- <li><a href="{{ route('admin.payments.doctor-txn', ['idd' => base64_encode($doctor->id)]) }}">Payment Transactions</a></li> --}}
                                                                            @endif
                                                                            <li><a href="{{ route('admin.doctor.edit', ['idd' => base64_encode($doctor->id)]) }}">Edit</a></li>
                                                                        @endif
                                                                    </ul>
                                                                </div>
                                                            @else
                                                                NA
                                                            @endif
                                                        </div>
                                                        <div style="display:none" id="cnfbox{{ $i }}">
                                                            <p>Are you sure delete this?</p>
                                                            <a href="{{ route('admin.doctor.delete', ['idd' => base64_encode($doctor->id)]) }}" class="btn btn-danger">Yes</a>
                                                            <a href="javascript:;" class="cans btn btn-default" mydatas="{{ $i }}">No</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @php $i++; @endphp
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
</div>
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
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#dataTable').DataTable({
            responsive: true,
            "bStateSave": true,
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('offersDataTables', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('offersDataTables'));
            },
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
            var i = $(this).attr("mydata");
            $("#btns" + i).hide();
            $("#cnfbox" + i).show();
        });

        $(document.body).on('click', '.cans', function() {
            var i = $(this).attr("mydatas");
            $("#btns" + i).show();
            $("#cnfbox" + i).hide();
        });
    });
</script>
@endpush