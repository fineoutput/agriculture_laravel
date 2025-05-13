@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>All Sliders</h1>
        <ol class="breadcrumb">
            {{-- <li><a href="{{ route('admin.home') }}"><i class="fa fa-dashboard"></i> Home</a></li> --}}
            <li class="active">View Sliders</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <a class="btn btn-info cticket" href="{{ route('admin.Slider.add') }}" role="button" style="margin-bottom:12px;">Add Slider</a>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-image"></i> View All Sliders</h3>
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
                                <table class="table table-bordered table-hover table-striped" id="sliderTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Image</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($slider_data as $index => $slider)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    @if($slider->image)
                                                        <img src="{{ asset($slider->image) }}" height="50" width="100" alt="Slider Image">
                                                    @else
                                                        No Image
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($slider->is_active)
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
                                                                @if($slider->is_active)
                                                                    <li><a href="{{ route('admin.Slider.update_status', [base64_encode($slider->id), 'inactive']) }}">Inactive</a></li>
                                                                @else
                                                                    <li><a href="{{ route('admin.Slider.update_status', [base64_encode($slider->id), 'active']) }}">Active</a></li>
                                                                @endif
                                                                <li><a href="{{ route('admin.Slider.update', base64_encode($slider->id)) }}">Edit</a></li>
                                                                <li><a href="javascript:;" class="dCnf" data-mydata="{{ $index + 1 }}">Delete</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div style="display:none" id="cnfbox{{ $index + 1 }}">
                                                        <p>Are you sure you want to delete this?</p>
                                                        <a href="{{ route('admin.Slider.delete', base64_encode($slider->id)) }}" class="btn btn-danger">Yes</a>
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
    .label.bg-green { background-color: #00a65a !important; color: white; padding: 2px 5px; }
    .label.bg-yellow { background-color: #f39c12 !important; color: white; padding: 2px 5px; }
</style>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#sliderTable').DataTable({
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
    });
</script>
@endpush