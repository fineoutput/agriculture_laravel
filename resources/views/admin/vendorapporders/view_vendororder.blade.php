@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">All {{ $heading }} Orders</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">All {{ $heading }} Orders</li>
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

                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h4 class="mt-0 header-title"><i class="fa fa-list"></i> View All {{ $heading }} Orders</h4>
                                @if($count > 0)
                                    <h4>Total Admin Earning: ₹{{ number_format($count, 2) }}</h4>
                                @endif
                            </div>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <div class="table-rep-plugin">
                                <div class="table-responsive b-0">
                                    <table id="orderTable" class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Order Id</th>
                                                <th>Vendor Name</th>
                                                <th>Vendor Number</th>
                                                <th>Vendor Address</th>
                                                <th>Amount</th>
                                                <th>Charges</th>
                                                <th>Sub Total</th>
                                                <th>Date</th>
                                                <th>Payment Type</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $i = 1; @endphp
                                            @forelse($order1_data as $data)
                                                @php
                                                    $vendor = \App\Models\Vendor::where('id', $data->vendor_id)->first();
                                                @endphp
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>#{{ $data->id }}</td>
                                                    <td>{{ $data->name ?? $vendor->name ?? 'N/A' }}</td>
                                                    <td>{{ $data->phone ?? $vendor->phone ?? 'N/A' }}</td>
                                                    <td>{{ $data->address ?? $vendor->address ?? 'N/A' }}</td>
                                                    <td>₹{{ number_format($data->total_amount, 2) }}</td>
                                                    <td>₹{{ number_format($data->charges ?? 0, 2) }}</td>
                                                    <td>₹{{ number_format($data->final_amount, 2) }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($data->date)->format('F j, Y, g:i a') }}</td>
                                                    <td>
                                                        @if($data->payment_status == 1)
                                                            <span class="label bg-success">Online</span>
                                                        @elseif($data->payment_status == 2)
                                                            <span class="label bg-warning">COD</span>
                                                        @else
                                                            <span class="label bg-secondary">Unknown</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->order_status == 1)
                                                            <span class="label bg-warning">Pending</span>
                                                        @elseif($data->order_status == 2)
                                                            <span class="label bg-success">Accepted</span>
                                                        @elseif($data->order_status == 3)
                                                            <span class="label bg-orange">Dispatched</span>
                                                        @elseif($data->order_status == 4)
                                                            <span class="label bg-purple">Completed</span>
                                                        @elseif($data->order_status == 5)
                                                            <span class="label bg-danger">Rejected</span>
                                                        @elseif($data->order_status == 6)
                                                            <span class="label bg-danger">Cancelled</span>
                                                        @else
                                                            <span class="label bg-danger">Rejected</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                                Action <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                @if($data->is_admin == 1)
                                                                    @if($data->order_status == 1)
                                                                        <li><a href="{{ route('admin.vendorapporders.update_status', [base64_encode($data->id), 'confirmed']) }}">Accept</a></li>
                                                                        <li><a href="{{ route('admin.vendorapporders.update_status', [base64_encode($data->id), 'reject']) }}">Reject</a></li>
                                                                        <li><a href="{{ route('admin.vendorapporders.detail', [base64_encode($data->id)]) }}">Order Detail</a></li>
                                                                        <li><a href="{{ route('admin.vendorapporders.view_bill', [base64_encode($data->id)]) }}">View Bill</a></li>
                                                                    @elseif($data->order_status == 2)
                                                                        <li><a href="{{ route('admin.vendorapporders.update_status', [base64_encode($data->id), 'dispatched']) }}">Dispatch</a></li>
                                                                        <li><a href="{{ route('admin.vendorapporders.update_status', [base64_encode($data->id), 'reject']) }}">Reject</a></li>
                                                                        <li><a href="{{ route('admin.vendorapporders.detail', [base64_encode($data->id)]) }}">Order Detail</a></li>
                                                                        <li><a href="{{ route('admin.vendorapporders.view_bill', [base64_encode($data->id)]) }}">View Bill</a></li>
                                                                    @elseif($data->order_status == 3)
                                                                        <li><a href="{{ route('admin.vendorapporders.update_status', [base64_encode($data->id), 'completed']) }}">Complete</a></li>
                                                                        <li><a href="{{ route('admin.vendorapporders.detail', [base64_encode($data->id)]) }}">Order Detail</a></li>
                                                                        <li><a href="{{ route('admin.vendorapporders.view_bill', [base64_encode($data->id)]) }}">View Bill</a></li>
                                                                    @elseif($data->order_status == 4 || $data->order_status == 5 || $data->order_status == 6)
                                                                        <li><a href="{{ route('admin.vendorapporders.detail', [base64_encode($data->id)]) }}">Order Detail</a></li>
                                                                        <li><a href="{{ route('admin.vendorapporders.view_bill', [base64_encode($data->id)]) }}">View Bill</a></li>
                                                                    @endif
                                                                @else
                                                                    <li><a href="{{ route('admin.vendorapporders.detail', [base64_encode($data->id)]) }}">Order Detail</a></li>
                                                                    <li><a href="{{ route('admin.vendorapporders.view_bill', [base64_encode($data->id)]) }}">View Bill</a></li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="12" class="text-center">No orders found</td>
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
    .label { padding: 5px 10px; font-size: 12px; }
    .dropdown-menu { min-width: 120px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    $('#orderTable').DataTable({
        responsive: true,
        bStateSave: true,
        fnStateSave: function (oSettings, oData) {
            localStorage.setItem('offersDataTables', JSON.stringify(oData));
        },
        fnStateLoad: function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copyHtml5',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] }
            },
            {
                extend: 'csvHtml5',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] }
            },
            {
                extend: 'excelHtml5',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] }
            },
            {
                extend: 'pdfHtml5',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] }
            },
            {
                extend: 'print',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] }
            }
        ],
        pageLength: 10,
        order: [[1, 'desc']],
        columnDefs: [
            { orderable: false, targets: [11] } // Action column
        ]
    });
});
</script>
@endpush