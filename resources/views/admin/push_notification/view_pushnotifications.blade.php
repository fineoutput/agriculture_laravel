```blade
@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Push Notifications</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Dashboard</a></li> --}}
                        <li class="breadcrumb-item active">View Push Notifications</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="page-content-wrapper">
            <div class="row">
                <div class="col-12">
                    <div class="card m-b-20">
                        <div class="card-body">
                            @if (session('smessage'))
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <h4><i class="icon fa fa-check"></i> Success!</h4>
                                    {{ session('smessage') }}
                                </div>
                            @endif
                            @if (session('emessage'))
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <h4><i class="icon fa fa-ban"></i> Error!</h4>
                                    {{ session('emessage') }}
                                </div>
                            @endif

                            <h4 class="mt-0 header-title"><i class="fa fa-bell"></i> Push Notifications</h4>
                            @if (Auth::guard('admin')->user()->position != 'Manager')
                                <a href="{{ route('admin.pushnotifications.add') }}" class="btn btn-info mb-3">Send Push Notification</a>
                            @endif
                            <hr style="margin-bottom: 20px; background-color: darkgrey;">

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>App</th>
                                            <th>Title</th>
                                            <th>Image</th>
                                            <th>Content</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pushnotifications_data as $index => $notification)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $notification->App == 1 ? 'Vendor' : 'Farmer' }}</td>
                                                <td>{{ $notification->title }}</td>
                                                <td>
                                                    @if($notification->image)
                                                        <img src="{{ url($notification->image) }}" height="50" width="100" alt="Notification Image">
                                                    @else
                                                        Sorry No File Found
                                                    @endif
                                                </td>
                                                <td>{{ $notification->content }}</td>
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
    </div>
</div>

<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            responsive: true,
            stateSave: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copyHtml5',
                    exportOptions: { columns: [1, 2, 3, 4] }
                },
                {
                    extend: 'csvHtml5',
                    exportOptions: { columns: [1, 2, 3, 4] }
                },
                {
                    extend: 'excelHtml5',
                    exportOptions: { columns: [1, 2, 3, 4] }
                },
                {
                    extend: 'pdfHtml5',
                    exportOptions: { columns: [1, 2, 3, 4] }
                },
                {
                    extend: 'print',
                    exportOptions: { columns: [1, 2, 3, 4] }
                }
            ]
        });
    });
</script>
@endsection

@push('styles')
<style>
    label {
        margin: 5px;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css">
@endpush

@push('scripts')

@endpush