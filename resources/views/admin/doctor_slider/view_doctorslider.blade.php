@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Doctor Slider</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li>
                        <li class="breadcrumb-item active">View Doctor Slider</li>
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

                            <div class="row">
                                <div class="col-md-10">
                                    <h4 class="mt-0 header-title">View Doctor Slider</h4>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.doctor_slider.add') }}" class="btn btn-info">Add Doctor Slider</a>
                                </div>
                            </div>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <div class="table-rep-plugin">
                                <div class="table-responsive b-0" data-pattern="priority-columns">
                                    <table id="doctorSliderTable" class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Image</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $i = 1; @endphp
                                            @foreach($doctorslider_data as $data)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>
                                                        @if($data->image)
                                                            <img src="{{ asset($data->image) }}" height="50" width="100" alt="Slider Image">
                                                        @else
                                                            <span>Sorry No Image Found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->is_active)
                                                            <span class="label bg-green">Active</span>
                                                        @else
                                                            <span class="label bg-yellow">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" id="btns{{ $i - 1 }}">
                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                                Action <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                @if($data->is_active)
                                                                    <li><a href="{{ route('admin.doctor_slider.update_status', [base64_encode($data->id), 'inactive']) }}">Inactive</a></li>
                                                                @else
                                                                    <li><a href="{{ route('admin.doctor_slider.update_status', [base64_encode($data->id), 'active']) }}">Active</a></li>
                                                                @endif
                                                                <li><a href="{{ route('admin.doctor_slider.update', base64_encode($data->id)) }}">Edit</a></li>
                                                                <li><a href="javascript:;" class="dCnf" data-mydata="{{ $i - 1 }}">Delete</a></li>
                                                            </ul>
                                                        </div>
                                                        <div style="display:none" id="cnfbox{{ $i - 1 }}" class="confirmation-box">
                                                            <p>Are you sure you want to delete this?</p>
                                                            <form action="{{ route('admin.doctor_slider.delete', base64_encode($data->id)) }}" method="POST" style="display: inline;">
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

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#doctorSliderTable').DataTable({
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