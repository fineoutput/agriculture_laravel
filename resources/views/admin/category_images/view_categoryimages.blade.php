@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Category Images</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li>
                        <li class="breadcrumb-item active">Category Images</li>
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

                            <h4 class="mt-0 header-title"><i class="fa fa-image"></i> View Category Images</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <div class="table-rep-plugin">
                                <div class="table-responsive b-0">
                                    <table id="dataTable" class="table table-bordered table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Image (English)</th>
                                                <th>Image (Hindi)</th>
                                                <th>Image (Punjabi)</th>
                                                <th>Image (Marathi)</th>
                                                <th>Edit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $i = 1; @endphp
                                            @forelse($category_images_data as $data)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $data->name ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($data->image)
                                                            <img src="{{ asset($data->image) }}" height="50" width="100" alt="English Image">
                                                        @else
                                                            <span>Sorry No Image Found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->image_hindi)
                                                            <img src="{{ asset($data->image_hindi) }}" height="50" width="100" alt="Hindi Image">
                                                        @else
                                                            <span>Sorry No Image Found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->image_punjabi)
                                                            <img src="{{ asset($data->image_punjabi) }}" height="50" width="100" alt="Punjabi Image">
                                                        @else
                                                            <span>Sorry No Image Found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->image_marathi)
                                                            <img src="{{ asset($data->image_marathi) }}" height="50" width="100" alt="Marathi Image">
                                                        @else
                                                            <span>Sorry No Image Found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.category_images.update', base64_encode($data->id)) }}" class="btn btn-primary btn-sm">Edit</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No category images found</td>
                                                </tr>
                                            @endforelse
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

@push('styles')
<style>
    label { margin: 5px; }
</style>
@endpush

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
    $('#dataTable').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copyHtml5',
                exportOptions: { columns: [0, 1, 2, 3, 4] }
            },
            {
                extend: 'csvHtml5',
                exportOptions: { columns: [0, 1, 2, 3, 4] }
            },
            {
                extend: 'excelHtml5',
                exportOptions: { columns: [0, 1, 2, 3, 4] }
            },
            {
                extend: 'pdfHtml5',
                exportOptions: { columns: [0, 1, 2, 3, 4] }
            },
            {
                extend: 'print',
                exportOptions: { columns: [0, 1, 2, 3, 4] }
            }
        ]
    });
});
</script>
@endpush