@extends('admin.base_template')

@section('main')

<style>
    label {
        margin: 5px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Order Details</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_index') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            @if ($status == 1)
                <li><a href="{{ route('admin.orders.new') }}"><i class="fa fa-undo" aria-hidden="true"></i> View New Orders</a></li>
            @elseif ($status == 2)
                <li><a href="{{ route('admin.orders.accepted') }}"><i class="fa fa-undo" aria-hidden="true"></i> View Accepted Orders</a></li>
            @elseif ($status == 3)
                <li><a href="{{ route('admin.orders.dispatched') }}"><i class="fa fa-undo" aria-hidden="true"></i> View Dispatched Orders</a></li>
            @elseif ($status == 4)
                <li><a href="{{ route('admin.orders.completed') }}"><i class="fa fa-undo" aria-hidden="true"></i> View Completed Orders</a></li>
            @elseif ($status == 5 || $status == 6)
                <li><a href="{{ route('admin.orders.cancelled') }}"><i class="fa fa-undo" aria-hidden="true"></i> View Rejected/Cancelled Orders</a></li>
            @endif
            <li class="active">Order Details</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-money fa-fw"></i> View Order</h3>
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
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Selling Price</th>
                                            <th>Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order2_data as $index => $data)
                                            @php
                                                $product = \App\Models\Product::find($data->product_id);
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $product ? $product->name_english : 'Product not found' }}</td>
                                                <td>{{ $data->qty }}</td>
                                                <td>₹{{ $data->selling_price }}</td>
                                                <td>₹{{ $data->total_amount }}</td>
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

<!-- Include DataTables Scripts -->
@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#orderTable').DataTable({
            responsive: true,
            "bStateSave": true,
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('orderDetailsDataTables', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('orderDetailsDataTables'));
            }
        });
    });
</script>
@endpush
@endsection