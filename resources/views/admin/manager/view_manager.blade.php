@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Managers</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li> --}}
                        <li class="breadcrumb-item active">View Managers</li>
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
                                    <h4 class="mt-0 header-title">Manager List</h4>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.manager.add') }}" class="btn btn-info">Add New</a>
                                </div>
                            </div>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <div class="table-rep-plugin">
                                <div class="table-responsive b-0" data-pattern="priority-columns">
                                    <table id="managerTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Address</th>
                                                <th>Aadhar</th>
                                                <th>Refer Code</th>
                                                <th>Images</th>
                                                <th>Install Details</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $count = 1; @endphp
                                            @foreach($manager_data as $manager)
                                                <tr>
                                                    <td>{{ $count++ }}</td>
                                                    <td>{{ $manager->name }}</td>
                                                    <td>{{ $manager->email }}</td>
                                                    <td>{{ $manager->phone ?? 'N/A' }}</td>
                                                    <td>{{ $manager->address }}</td>
                                                    <td>{{ $manager->aadhar ?? 'N/A' }}</td>
                                                    <td>{{ $manager->refer_code ?? 'N/A' }}</td>
                                                    <td>
                                                        @php
                                                            $images = json_decode($manager->images, true);
                                                        @endphp
                                                        @if(is_array($images) && !empty($images))
                                                            @foreach($images as $img)
                                                                <img src="{{ asset($img) }}" style="height: 50px; width: 100px; margin-right: 10px;" alt="Manager Image" />
                                                            @endforeach
                                                        @else
                                                            <span>Sorry, no image found.</span>
                                                        @endif
                                                    </td>
                                                    <td style="display: flex; justify-content: space-around;">
                                                        <a href="{{ route('admin.manager.view_farmers', base64_encode($manager->refer_code)) }}" style="text-align: center;">
                                                            Farmer Installs<br><br>
                                                            <span>{{ \App\Models\Farmer::where('refer_code', $manager->refer_code)->count() }}</span>
                                                        </a>
                                                        <a href="{{ route('admin.manager.view_vendors', base64_encode($manager->refer_code)) }}" style="text-align: center; margin-left: 10px;">
                                                            Vendor Installs<br><br>
                                                            <span>{{ \App\Models\Vendor::where('refer_code', $manager->refer_code)->count() }}</span>
                                                        </a>
                                                        <a href="{{ route('admin.manager.view_doctors', base64_encode($manager->refer_code)) }}" style="text-align: center; margin-left: 10px;">
                                                            Doctor Installs<br><br>
                                                            <span>{{ \App\Models\Doctor::where('refer_code', $manager->refer_code)->count() }}</span>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        @if($manager->is_active)
                                                            <span class="badge badge-success">Active</span>
                                                        @else
                                                            <span class="badge badge-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($manager->id)
                                                            <div class="btn-group" id="btns{{ $count - 1 }}">
                                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                                    Action <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu" role="menu">
                                                                    @if($manager->is_active)
                                                                        <li><a href="{{ route('admin.manager.update_status', [base64_encode($manager->id), 'inactive']) }}">Inactive</a></li>
                                                                    @else
                                                                        <li><a href="{{ route('admin.manager.update_status', [base64_encode($manager->id), 'active']) }}">Active</a></li>
                                                                    @endif
                                                                    <li>
    <form action="{{ route('admin.manager.delete', base64_encode($manager->id)) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete The manager?');" style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-link" style="padding: 0; color: #337ab7;">Delete</button>
</form>

</li>
                                                                </ul>
                                                            </div>
                                                            <div style="display:none" id="cnfbox{{ $count - 1 }}" class="confirmation-box">
                                                                <p>Are you sure you want to delete this?</p>
                                                                <form action="{{ route('admin.manager.delete', base64_encode($manager->id)) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('POST')
                                                                    <button type="submit" class="btn btn-danger btn-sm">Yes</button>
                                                                </form>
                                                                <button class="btn btn-default btn-sm cans" data-mydatas="{{ $count - 1 }}">No</button>
                                                            </div>
                                                        @else
                                                            <span class="text-danger">Invalid Manager ID</span>
                                                        @endif
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
{{-- <script type="text/javascript">
    $(document).ready(function() {
        $('#managerTable').DataTable({
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
</script> --}}
@endpush