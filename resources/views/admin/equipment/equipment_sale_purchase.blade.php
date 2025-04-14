@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Equipment {{ $heading }}</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li>
                        <li class="breadcrumb-item active">Equipment {{ $heading }}</li>
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

                            <h4 class="mt-0 header-title"><i class="fa fa-list"></i> Equipment {{ $heading }}</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <div class="table-rep-plugin">
                                <div class="table-responsive b-0">
                                    <table id="dataTable" class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Farmer Name</th>
                                                <th>Farmer Phone</th>
                                                <th>Farmer City</th>
                                                <th>Information Type</th>
                                                <th>Equipment Type</th>
                                                <th>Company Name</th>
                                                <th>Year Old</th>
                                                <th>Price</th>
                                                <th>Remark</th>
                                                <th>Image 1</th>
                                                <th>Image 2</th>
                                                <th>Image 3</th>
                                                <th>Image 4</th>
                                                <th>Video</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $i = 1; @endphp
                                            @forelse($sale_purchase as $data)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ htmlspecialchars($farmer_details[$data->id]->name ?? 'N/A') }}</td>
                                                    <td>{{ htmlspecialchars($farmer_details[$data->id]->phone ?? 'N/A') }}</td>
                                                    <td>{{ htmlspecialchars($farmer_details[$data->id]->city ?? 'N/A') }}</td>
                                                    <td>{{ $data->information_type ?? 'N/A' }}</td>
                                                    <td>{{ $data->equipment_type ?? 'N/A' }}</td>
                                                    <td>{{ $data->company_name ?? 'N/A' }}</td>
                                                    <td>{{ $data->year_old ?? 'N/A' }}</td>
                                                    <td>{{ $data->price ?? 'N/A' }}</td>
                                                    <td>{{ $data->remark ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($data->image1)
                                                            <img src="{{ asset($data->image1) }}" height="50" width="100" alt="Image 1">
                                                        @else
                                                            <span>Sorry, No Image Found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->image2)
                                                            <img src="{{ asset($data->image2) }}" height="50" width="100" alt="Image 2">
                                                        @else
                                                            <span>Sorry, No Image Found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->image3)
                                                            <img src="{{ asset($data->image3) }}" height="50" width="100" alt="Image 3">
                                                        @else
                                                            <span>Sorry, No Image Found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->image4)
                                                            <img src="{{ asset($data->image4) }}" height="50" width="100" alt="Image 4">
                                                        @else
                                                            <span>Sorry, No Image Found</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data->video)
                                                            <video height="50" width="100" controls>
                                                                <source src="{{ asset($data->video) }}" type="video/mp4">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        @else
                                                            <span>Sorry, No Video Found</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $data->date ? \Carbon\Carbon::parse($data->date)->format('Y-m-d') : 'N/A' }}</td>
                                                    <td>
                                                        @if($data->status <= 1)
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                                    Action <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu" role="menu">
                                                                    @if($data->status == 0)
                                                                        <li><a href="{{ route('admin.equipment_sale_purchase.update_status', [base64_encode($data->id), 'accept']) }}">Accept</a></li>
                                                                        <li><a href="{{ route('admin.equipment_sale_purchase.update_status', [base64_encode($data->id), 'reject']) }}">Reject</a></li>
                                                                    @elseif($data->status == 1)
                                                                        <li><a href="{{ route('admin.equipment_sale_purchase.update_status', [base64_encode($data->id), 'complete']) }}">Complete</a></li>
                                                                        <li><a href="{{ route('admin.equipment_sale_purchase.update_status', [base64_encode($data->id), 'reject']) }}">Reject</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span>NA</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="17" class="text-center">No sale/purchase records found</td>
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
    .table th, .table td { vertical-align: middle; text-align: center; }
    img, video { object-fit: cover; }
    .dropdown-menu { min-width: 100px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
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
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 15] }
            },
            {
                extend: 'csvHtml5',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 15] }
            },
            {
                extend: 'excelHtml5',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 15] }
            },
            {
                extend: 'pdfHtml5',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 15] }
            },
            {
                extend: 'print',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 15] }
            }
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [10, 11, 12, 13, 14, 16] }
        ]
    });
});
</script>
@endpush