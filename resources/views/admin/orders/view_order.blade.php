@extends('admin.base_template')

@section('main')

<style>
    label {
        margin: 5px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>All {{ $heading }} Orders</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_index') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">{{ $heading }} Orders</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading" style="display:flex; justify-content: space-between;">
                        <h3 class="panel-title"><i class="fa fa-shopping-cart"></i> View All {{ $heading }} Orders</h3>
                        @if (!empty($count))
                            <h4>Total Admin Earning: ₹{{ $count }}</h4>
                        @endif
                    </div>
                    <div class="panel panel-default">
                        <!-- Flash Messages -->
                        @if (session('smessage'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-check"></i> Alert!</h4>
                                {{ session('smessage') }}
                            </div>
                        @endif
                        @if (session('emessage'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                                {{ session('emessage') }}
                            </div>
                        @endif

                        <div class="panel-body">
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-bordered table-hover table-striped" id="orderTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Order Id</th>
                                            <th>Farmer Name</th>
                                            <th>Farmer Number</th>
                                            <th>Amount</th>
                                            <th>Charges</th>
                                            <th>Sub Total</th>
                                            <th>Admin Earning</th>
                                            <th>Date</th>
                                            <th>Payment Type</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order1_data as $index => $data)
                                            @php
                                                $farmer = \App\Models\Farmer::find($data->farmer_id);
                                                $payment_txn = \App\Models\PaymentTransaction::where('req_id', $data->id)
                                                    ->where('vendor_id', $data->vendor_id)
                                                    ->first();
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>#{{ $data->id }}</td>
                                                <td>{{ $farmer ? $farmer->name : 'User not found' }}</td>
                                                <td>{{ $farmer ? $farmer->phone : 'User not found' }}</td>
                                                <td>₹{{ $data->total_amount }}</td>
                                                <td>₹{{ $data->charges }}</td>
                                                <td>₹{{ $data->final_amount }}</td>
                                                <td>
                                                    @if ($payment_txn && $payment_txn->cr)
                                                        ₹{{ $data->total_amount - $payment_txn->cr }}
                                                    @else
                                                        ₹0
                                                    @endif
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($data->date)->format('F j, Y, g:i a') }}</td>
                                                <td>
                                                    @if ($data->payment_status == 1)
                                                        <p class="label bg-green">Online</p>
                                                    @elseif ($data->payment_status == 2)
                                                        <p class="label bg-orange">COD</p>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($data->order_status == 1)
                                                        <p class="label bg-yellow">Pending</p>
                                                    @elseif ($data->order_status == 2)
                                                        <p class="label bg-green">Accepted</p>
                                                    @elseif ($data->order_status == 3)
                                                        <p class="label bg-orange">Dispatched</p>
                                                    @elseif ($data->order_status == 4)
                                                        <p class="label bg-purple">Completed</p>
                                                    @elseif ($data->order_status == 5)
                                                        <p class="label bg-red">Rejected</p>
                                                    @elseif ($data->order_status == 6)
                                                        <p class="label bg-red">Cancelled</p>
                                                    @else
                                                        <p class="label bg-red">Rejected</p>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" id="btns{{ $index + 1 }}">
                                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                            Action <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            @if ($data->order_status == 1 && $data->is_admin == 1)
                                                                <li><a href="{{ route('admin.orders.update_status', [base64_encode($data->id), 'confirmed']) }}">Accept</a></li>
                                                                <li><a href="{{ route('admin.orders.update_status', [base64_encode($data->id), 'reject']) }}">Reject</a></li>
                                                                <li><a href="{{ route('admin.orders.detail', base64_encode($data->id)) }}">Order Detail</a></li>
                                                                <li><a href="{{ route('admin.orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
                                                            @elseif ($data->order_status == 2 && $data->is_admin == 1)
                                                                <li><a href="{{ route('admin.orders.update_status', [base64_encode($data->id), 'dispatched']) }}">Dispatch</a></li>
                                                                <li><a href="{{ route('admin.orders.update_status', [base64_encode($data->id), 'reject']) }}">Reject</a></li>
                                                                <li><a href="{{ route('admin.orders.detail', base64_encode($data->id)) }}">Order Detail</a></li>
                                                                <li><a href="{{ route('admin.orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
                                                            @elseif ($data->order_status == 3 && $data->is_admin == 1)
                                                                <li><a href="{{ route('admin.orders.update_status', [base64_encode($data->id), 'completed']) }}">Complete</a></li>
                                                                <li><a href="{{ route('admin.orders.detail', base64_encode($data->id)) }}">Order Detail</a></li>
                                                                <li><a href="{{ route('admin.orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
                                                            @elseif ($data->order_status == 4 && $data->is_admin == 1)
                                                                <li><a href="{{ route('admin.orders.detail', base64_encode($data->id)) }}">Order Detail</a></li>
                                                                <li><a href="{{ route('admin.orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
                                                            @elseif ($data->order_status == 5 && $data->is_admin == 1)
                                                                <li><a href="{{ route('admin.orders.detail', base64_encode($data->id)) }}">Order Detail</a></li>
                                                                <li><a href="{{ route('admin.orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
                                                            @else
                                                                <li><a href="{{ route('admin.orders.detail', base64_encode($data->id)) }}">Order Detail</a></li>
                                                                <li><a href="{{ route('admin.orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
                                                            @endif
                                                        </ul>
                                                    </div>
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
    </section>
</div>

<!-- Include DataTables and Export Scripts -->
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
        $('#orderTable').DataTable({
            responsive: true,
            "bStateSave": true,
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('ordersDataTables', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('ordersDataTables'));
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copyHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] } },
                { extend: 'csvHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] } },
                { extend: 'excelHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] } },
                { extend: 'pdfHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] } },
                { extend: 'print', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] } }
            ]
        });
    });
</script>
@endpush
@endsection