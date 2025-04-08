@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Vendor Slider Requests</h1>
        <ol class="breadcrumb">
            {{-- <li><a href="{{ route('admin.home') }}"><i class="fa fa-dashboard"></i> Home</a></li> --}}
            <li><a href="{{ route('admin.vendorslider.view') }}"><i class="fa fa-dashboard"></i> All Vendor Sliders</a></li>
            <li class="active">View Vendor Slider Requests</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">View Vendor Slider Requests</h3>
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
                                            <th>Vendor Name</th>
                                            <th>Image</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($vendorslider_data as $index => $data)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    @php
                                                        $vendor = \App\Models\Vendor::where('id', $data->vendor_id)->first();
                                                    @endphp
                                                    {{ $vendor->name ?? 'Unknown Vendor' }}
                                                </td>
                                                <td>
                                                    @if($data->image1)
                                                        @php $images = json_decode($data->image1, true); @endphp
                                                        @if(is_array($images) && !empty($images))
                                                            @foreach($images as $image)
                                                                <img id="slide_img_path" height="70" width="120" src="{{ asset($image) }}" onclick="openFullImage(this.src)" alt="Slider Request Image">
                                                            @endforeach
                                                        @else
                                                            <img id="slide_img_path" height="70" width="120" src="{{ asset($data->image1) }}" onclick="openFullImage(this.src)" alt="Slider Request Image">
                                                        @endif
                                                    @else
                                                        Sorry, no image found.
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($data->is_active == 2)
                                                        <p class="label bg-green">Active</p>
                                                    @else
                                                        <p class="label bg-yellow">Inactive</p>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" id="btns{{ $index + 1 }}">
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                                Action <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                @if($data->is_active == 2)
                                                                    <li><a href="{{ route('admin.vendorslider.update_request', [base64_encode($data->id), 'inactive']) }}">Inactive</a></li>
                                                                @else
                                                                    <li><a href="{{ route('admin.vendorslider.update_request', [base64_encode($data->id), 'active']) }}">Active</a></li>
                                                                @endif
                                                            </ul>
                                                        </div>
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
    .label.bg-green { background-color: #00a65a !important; color: white; padding: 2px 5px; }
    .label.bg-yellow { background-color: #f39c12 !important; color: white; padding: 2px 5px; }
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
    });

    function openFullImage(imageSrc) {
        var modal = document.createElement('div');
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.zIndex = '9999';

        var fullImage = document.createElement('img');
        fullImage.src = imageSrc;
        fullImage.style.maxWidth = '90%';
        fullImage.style.maxHeight = '90%';

        modal.appendChild(fullImage);
        document.body.appendChild(modal);

        modal.onclick = function() {
            modal.remove();
        };
    }
</script>
@endpush