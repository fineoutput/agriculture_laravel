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
                <li><a href="{{ route('admin.vendor_orders.new') }}"><i class="fa fa-undo" aria-hidden="true"></i> View New Orders</a></li>
            @elseif ($status == 2)
                <li><a href="{{ route('admin.vendor_orders.accepted') }}"><i class="fa fa-undo" aria-hidden="true"></i> View Accepted Orders</a></li>
            @elseif ($status == 3)
                <li><a href="{{ route('admin.vendor_orders.dispatched') }}"><i class="fa fa-undo" aria-hidden="true"></i> View Dispatched Orders</a></li>
            @elseif ($status == 4)
                <li><a href="{{ route('admin.vendor_orders.completed') }}"><i class="fa fa-undo" aria-hidden="true"></i> View Completed Orders</a></li>
            @elseif ($status == 5 || $status == 6)
                <li><a href="{{ route('admin.vendor_orders.cancelled') }}"><i class="fa fa-undo" aria-hidden="true"></i> View Rejected/Cancelled Orders</a></li>
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
                                            <th>Size</th>
                                            <th>Color</th>
                                            @if ($order_type == 1)
                                                <th>Model Name</th>
                                            @endif
                                            <th>Quantity</th>
                                            <th>Selling Price</th>
                                            <th>Total Amount</th>
                                            @if ($order_type == 1)
                                                <th>Reference Code</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order2_data as $index => $data)
                                            @php
                                                $product = \App\Models\Product::find($data->product_id);
                                                $type = \App\Models\Type::find($data->type_id);
                                                $size = $type ? \App\Models\Size::find($type->size_id) : null;
                                                $color = $type ? \App\Models\Colour::find($type->colour_id) : null;
                                                $user = $order_type == 1 && $data->user_id ? \App\Models\User::find($data->user_id) : null;
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $product ? $product->name : 'Product not found' }}</td>
                                                <td>{{ $size ? $size->name : 'NA' }}</td>
                                                <td>
                                                    @if ($color)
                                                        <span style="background-color: {{ $color->name }}; border-radius: 50%; display: inline-block; width: 20px; height: 20px;"></span>
                                                        {{ $color->colour_name }}
                                                    @else
                                                        NA
                                                    @endif
                                                </td>
                                                @if ($order_type == 1)
                                                    <td>{{ $user ? $user->f_name . ' ' . $user->l_name : 'NA' }}</td>
                                                @endif
                                                <td>{{ $data->quantity }}</td>
                                                <td>₹{{ $data->selling_price }}</td>
                                                <td>₹{{ $data->total_amount }}</td>
                                                @if ($order_type == 1)
                                                    <td>{{ $data->reference_code ?? 'NA' }}</td>
                                                @endif
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
                localStorage.setItem('vendorOrderDetailsDataTables', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('vendorOrderDetailsDataTables'));
            }
        });
    });
</script>
@endpush
@endsection