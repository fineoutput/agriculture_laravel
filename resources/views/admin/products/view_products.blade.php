@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>All {{ $heading }} Products</h1>
        <ol class="breadcrumb">
            {{-- <li><a href="{{ route('admin.home') }}"><i class="fa fa-dashboard"></i> Home</a></li> --}}
            <li class="active">View All {{ $heading }} Products</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                @if ($is_admin == 1)
                    <a class="btn btn-info cticket" href="{{ route('admin.products.add') }}" role="button" style="margin-bottom:12px;">Add Product</a>
                @endif

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-box-open"></i> View All {{ $heading }} Products</h3>
                    </div>

                    <div class="panel panel-default">
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
                                <table class="table table-bordered table-hover table-striped" id="userTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            @if ($is_admin == 0)
                                                <th>Vendor Name</th>
                                                <th>Vendor Phone</th>
                                            @endif
                                            <th>Name (English)</th>
                                            <th>Name (Hindi)</th>
                                            <th>Name (Punjabi)</th>
                                            <th>Name (Marathi)</th>
                                            <th>Name (Gujrati)</th>
                                            <th>Description (English)</th>
                                            <th>Description (Hindi)</th>
                                            <th>Description (Punjabi)</th>
                                            <th>Description (Marathi)</th>
                                            <th>Description (Gujrati)</th>
                                            <th>Images</th>
                                            <th>Video</th>
                                            <th>MRP</th>
                                            <th>Selling Price</th>
                                            <th>GST%</th>
                                            <th>GST Price</th>
                                            <th>Selling Price (w/o GST)</th>
                                            <th>Inventory</th>
                                            <th>Suffix</th>
                                            <th>Minimum Qty</th>
                                            <th>Offer</th>
                                            <th>Status</th>
                                            <th>COD</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products_data as $index => $data)
                                            @php
                                                $vendor_data = $data->is_admin == 0 ? \App\Models\Vendor::where('id', $data->added_by)->first() : null;
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                @if ($data->is_admin == 0)
                                                    <td>{{ $vendor_data ? $vendor_data->name : 'Not Found!' }}</td>
                                                    <td>{{ $vendor_data ? $vendor_data->phone : 'Not Found!' }}</td>
                                                @endif
                                                <td>{{ $data->name_english }}</td>
                                                <td>{{ $data->name_hindi }}</td>
                                                <td>{{ $data->name_punjabi }}</td>
                                                <td>{{ $data->name_marathi }}</td>
                                                <td>{{ $data->name_gujrati }}</td>
                                                <td>{{ $data->description_english }}</td>
                                                <td>{{ $data->description_hindi }}</td>
                                                <td>{{ $data->description_punjabi }}</td>
                                                <td>{{ $data->description_marathi }}</td>
                                                <td>{{ $data->description_gujrati }}</td>
                                                <td>
                                                    @if($data->image)
                                                        @php $imageArray = json_decode($data->image, true); @endphp
                                                        @if(is_array($imageArray) && !empty($imageArray))
                                                            @foreach($imageArray as $imagePath)
                                                                <img id="slide_img_path" height="50" width="100" src="{{ asset($imagePath) }}" alt="Product Image">
                                                            @endforeach
                                                        @else
                                                            <img id="slide_img_path" height="50" width="100" src="{{ asset($data->image) }}" alt="Product Image">
                                                        @endif
                                                    @else
                                                        Sorry, no images found.
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($data->video)
                                                        <video height="100" width="100" controls>
                                                            <source src="{{ asset($data->video) }}" type="video/mp4">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    @else
                                                        Sorry, no video found.
                                                    @endif
                                                </td>
                                                <td>{{ $data->mrp ? '₹' . $data->mrp : '' }}</td>
                                                <td>{{ $data->selling_price ? '₹' . $data->selling_price : '' }}</td>
                                                <td>{{ $data->gst ? $data->gst . '%' : '' }}</td>
                                                <td>{{ $data->gst_price ? '₹' . $data->gst_price : '' }}</td>
                                                <td>{{ $data->selling_price_wo_gst ? '₹' . $data->selling_price_wo_gst : '' }}</td>
                                                <td>{{ $data->inventory }}</td>
                                                <td>{{ $data->suffix }}</td>
                                                <td>{{ $data->min_qty }}</td>
                                                <td>{{ $data->offer }}</td>
                                                <td>
                                                    @if($data->is_active)
                                                        <p class="label bg-green">Active</p>
                                                    @else
                                                        <p class="label bg-yellow">Inactive</p>
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="mycheckbox" data-id="{{ $data->id }}" name="checkbox" {{ $data->cod ? 1 : 0 }}>
                                                </td>
                                                <td>
                                                    <div class="btn-group" id="btns{{ $index + 1 }}">
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                                Action <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                @if($data->is_admin == 0 && $data->is_approved == 0)
                                                                    <li><a href="{{ route('admin.products.approve', base64_encode($data->id)) }}">Approve</a></li>
                                                                @endif
                                                                @if($data->is_active)
                                                                    <li><a href="{{ route('admin.products.update_status', [base64_encode($data->id), 'inactive']) }}">Inactive</a></li>
                                                                @else
                                                                    <li><a href="{{ route('admin.products.update_status', [base64_encode($data->id), 'active']) }}">Active</a></li>
                                                                @endif
                                                                <li><a href="{{ route('admin.products.update', base64_encode($data->id)) }}">Edit</a></li>
                                                                <li><a href="javascript:;" class="dCnf" data-mydata="{{ $index + 1 }}">Delete</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div style="display:none" id="cnfbox{{ $index + 1 }}">
                                                        <p>Are you sure you want to delete this?</p>
                                                        <a href="{{ route('admin.products.delete', base64_encode($data->id)) }}" class="btn btn-danger">Yes</a>
                                                        <a href="javascript:;" class="cans btn btn-default" data-mydatas="{{ $index + 1 }}">No</a>
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

<style>
    label {
        margin: 5px;
    }
    .label.bg-green {
        background-color: #00a65a !important;
        color: white;
        padding: 2px 5px;
    }
    .label.bg-yellow {
        background-color: #f39c12 !important;
        color: white;
        padding: 2px 5px;
    }
</style>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#userTable').DataTable({
            responsive: true
        });

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

        if ($('.mycheckbox').length) {
            $('.mycheckbox').on('change', function() {
                var isChecked = $(this).prop('checked');
                var userId = $(this).data('id');

                $.ajax({
                    type: 'POST',
                    url: '{{ route("admin.products.cod_data") }}',
                    data: {
                        userId: userId,
                        isChecked: isChecked,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert('Successfully updated');
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        } else {
            console.error('Checkbox element not found.');
        }
    });
</script>
@endpush