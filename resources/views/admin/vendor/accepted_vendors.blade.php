@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">All Accepted Vendors</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li> --}}
                        <li class="breadcrumb-item active">Accepted Vendors</li>
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

                            <h4 class="mt-0 header-title"><i class="fa fa-users"></i> Accepted Vendors</h4>
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
    @if(!empty($data->id))
        <form action="{{ route('admin.vendor.store_cod', ['id' => $data->id]) }}" method="POST">
            @csrf
            <input type="checkbox"
                   name="cod"
                   onchange="this.form.submit()"
                   {{ $data->cod == 1 ? 'checked' : '' }}>
        </form>
    @else
        <span>No ID</span>
    @endif
</td>
                                                    <td>
                                                        <input type="text" class="qtydiscount" data-id="{{ $data->id }}" name="qty_discount" value="{{ $data->qty_discount ?? '' }}" style="width: 100px;">
                                                    </td>
                                                    <td>
                                                        @if($data->is_active)
                                                            <span class="label bg-green">Unblocked</span>
                                                        @else
                                                            <span class="label bg-yellow">Blocked</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" id="btns{{ $i - 1 }}">
                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                                Action <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                @if($data->is_active)
                                                                    <li><a href="{{ route('admin.vendor.update_status', [base64_encode($data->id), 'inactive']) }}">Block</a></li>
                                                                @else
                                                                    <li><a href="{{ route('admin.vendor.update_status', [base64_encode($data->id), 'active']) }}">Unblock</a></li>
                                                                @endif
                                                                <li><a href="{{ route('admin.vendor.set_commission', base64_encode($data->id)) }}">Update Commission(%)</a></li>
                                                                {{-- <li><a href="{{ route('admin.payments.vendor_txn', base64_encode($data->id)) }}">Payment Transactions</a></li> --}}
                                                                <li><a href="{{ route('admin.vendor.update', base64_encode($data->id)) }}">Edit</a></li>
                                                                <li><a href="javascript:;" class="dCnf" data-mydata="{{ $i - 1 }}">Delete</a></li>
                                                            </ul>
                                                        </div>
                                                        <div style="display:none" id="cnfbox{{ $i - 1 }}" class="confirmation-box">
                                                            <p>Are you sure you want to delete this?</p>
                                                            <form action="{{ route('admin.vendor.delete', base64_encode($data->id)) }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('POST')
                                                                <button type="submit" class="btn btn-danger btn-sm">Yes</button>
                                                            </form>
                                                            <button class="btn btn-default btn-sm cans" data-mydatas="{{ $i - 1 }}">No</button>
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

{{-- <script type="text/javascript">
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
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16] }
            },
            {
                extend: 'csvHtml5',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16] }
            },
            {
                extend: 'excelHtml5',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16] }
            },
            {
                extend: 'pdfHtml5',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16] }
            },
            {
                extend: 'print',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16] }
            }
        ]
    });

    // Delete Confirmation
    $(document.body).on('click', '.dCnf', function() {
        var i = $(this).data('mydata');
        $("#btns" + i).hide();
        $("#cnfbox" + i).show();
    });

    $(document.body).on('click', '.cans', function() {
        var i = $(this).data('mydatas');
        $("#btns" + i).show();
        $("#cnfbox" + i).hide();
    });

    // COD Checkbox AJAX
    if ($('.mycheckbox').length) {
        $('.mycheckbox').on('change', function() {
            var isChecked = $(this).prop('checked');
            var userId = $(this).data('id');

            $.ajax({
                type: 'POST',
                url: '{{ route("admin.vendor.store_cod") }}',
                data: {
                    userId: userId,
                    isChecked: isChecked,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert('Successfully updated');
                    console.log(response);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        });
    }

    // Quantity Discount AJAX
    if ($('.qtydiscount').length) {
        $('.qtydiscount').on('change', function() {
            var qtyDiscount = $(this).val() || '0';
            var userId = $(this).data('id');

            $.ajax({
                type: 'POST',
                url: '{{ route("admin.vendor.qty_update") }}',
                data: {
                    userId: userId,
                    qtyDiscount: qtyDiscount,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert('Successfully updated');
                    console.log(response);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        });
    }
});
</script> --}}
@endpush