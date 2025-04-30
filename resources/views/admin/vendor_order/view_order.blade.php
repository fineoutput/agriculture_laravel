@extends('admin.base_template')

@section('main')

<style>
    label {
        margin: 5px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>{{ $heading }} Order</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_index') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">{{ $heading }} Orders</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-money fa-fw"></i> {{ $heading }} Orders</h3>
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
                                            <th>Vendor Name</th>
                                            <th>Farmer Name</th>
                                            <th>Total Amount</th>
                                            <th>Promocode Discount</th>
                                            <th>Final Amount</th>
                                            <th>Payment Type</th>
                                            <th>Phone</th>
                                            <th>Village</th>
                                            <th>District</th>
                                            <th>Vendor Shop Name</th>
                                            <th>Vendor Address</th>
                                            <th>State</th>
                                            <th>City</th>
                                            <th>Email</th>
                                            <th>Address</th>
                                            <th>Date</th>
                                            @if ($order_type == 1)
                                                <th>Status</th>
                                            @endif
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $order_typee = ($order_type == 1) ? 1 : 2;
                                        @endphp
                                        @foreach ($order1_data as $index => $data)
                                            @php
                                                $farmer = \App\Models\Farmer::find($data->farmer_id);
                                                $vendor = \App\Models\Vendor::find($data->vendor_id);
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $data->id }}</td>
                                                <td>{{ $vendor ? $vendor->name : 'Vendor not found' }}</td>
                                                <td>{{ $farmer ? $farmer->name : 'Farmer not found' }}</td>
                                                <td>₹{{ $data->total_amount }}</td>
                                                <td>{{ $data->promocode ? '₹' . $data->promo_discount : 'NA' }}</td>
                                                <td>₹{{ $data->final_amount }}</td>
                                                <td>
                                                    {{ $data->payment_type == 1 ? 'COD' : 'Online Payment' }}
                                                </td>
                                                <td>{{ $data->phone }}</td>
                                                <td>{{ $farmer ? $farmer->village : 'N/A' }}</td>
                                                <td>{{ $farmer ? $farmer->district : 'N/A' }}</td>
                                                <td>{{ $vendor ? $vendor->name : 'N/A' }}</td>
                                                <td>{{ $vendor ? $vendor->address : 'N/A' }}</td>
                                                <td>{{ $data->state }}</td>
                                                <td>{{ $data->city }}</td>
                                                <td>{{ $data->email }}</td>
                                                <td>{{ $data->address }}</td>
                                                <td>{{ \Carbon\Carbon::parse($data->date)->format('F j, Y, g:i a') }}</td>
                                                @if ($order_type == 1)
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
                                                @endif
                                                <td>
                                                    <div class="btn-group" id="btns{{ $index + 1 }}">
                                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                            Action <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            @if ($order_typee == 1)
                                                                @if ($data->order_status == 1)
                                                                    <li><a href="{{ route('admin.vendor_orders.update_status', [base64_encode($data->id), 'confirmed']) }}">Accepted</a></li>
                                                                    <li><a href="{{ route('admin.vendor_orders.update_status', [base64_encode($data->id), 'reject']) }}">Reject</a></li>
                                                                    <li><a href="{{ route('admin.vendor_orders.detail', base64_encode($data->id)) }}">Order Detail</a></li>
                                                                    <li><a href="{{ route('admin.vendor_orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
                                                                @elseif ($data->order_status == 2)
                                                                    <li><a href="{{ route('admin.vendor_orders.update_status', [base64_encode($data->id), 'dispatched']) }}">Dispatched</a></li>
                                                                    <li><a href="{{ route('admin.vendor_orders.update_status', [base64_encode($data->id), 'reject']) }}">Reject</a></li>
                                                                    <li><a href="{{ route('admin.vendor_orders.detail', base64_encode($data->id)) }}">Order Detail</a></li>
                                                                    <li><a href="{{ route('admin.vendor_orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
                                                                @elseif ($data->order_status == 3)
                                                                    <li><a href="{{ route('admin.vendor_orders.update_status', [base64_encode($data->id), 'completed']) }}">Completed</a></li>
                                                                    <li><a href="{{ route('admin.vendor_orders.detail', base64_encode($data->id)) }}">Order Detail</a></li>
                                                                    <li><a href="{{ route('admin.vendor_orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
                                                                @elseif ($data->order_status == 4 || $data->order_status == 5)
                                                                    <li><a href="{{ route('admin.vendor_orders.detail', base64_encode($data->id)) }}">Order Detail</a></li>
                                                                    <li><a href="{{ route('admin.vendor_orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
                                                                @endif
                                                            @else
                                                                <li><a href="{{ route('admin.vendor_orders.detail', [$order_typee == 2 ? base64_encode($data->id) . '/' . base64_encode($data->id) : base64_encode($data->id)]) }}">Order Detail</a></li>
                                                                <li><a href="{{ route('admin.vendor_orders.view_bill', base64_encode($data->id)) }}">View Bill</a></li>
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
                localStorage.setItem('vendorOrdersDataTables', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('vendorOrdersDataTables'));
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copyHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17] } },
                { extend: 'csvHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17] } },
                { extend: 'excelHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17] } },
                { extend: 'pdfHtml5', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17] } },
                { extend: 'print', exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17] } }
            ]
        });
    });
</script>
@endpush
@endsection