@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Check My Feed Data</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}"><i class="fa fa-dashboard"></i> All Check My Feed Data</a></li> --}}
                        <li class="breadcrumb-item active">View Check My Feed Data</li>
                    </ol>
                </div>
            </div>
        </div>

        @if (Auth::guard('admin')->user()->position == 'Manager')
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Access Denied!</h4>
                Managers cannot access Check My Feed data.
            </div>
        @else
            <div class="page-content-wrapper mt-0">
                <!-- Flash Messages -->
                @if (session('smessage'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('smessage') }}
                    </div>
                @endif
                @if (session('emessage'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        {{ session('emessage') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-money fa-fw"></i> View Check My Feed Data</h3>
                            </div>
                            <div class="panel-body">
                                <div class="box-body table-responsive no-padding">
                                    <table class="table table-bordered table-hover table-striped" id="checkFeedTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Farmer</th>
                                                <th>Price</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($check_feed_data as $index => $data)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $data->farmer ? $data->farmer->name . ' - ' . $data->farmer->phone : 'N/A' }}</td>
                                                    <td>₹{{ number_format($data->price ?? 0, 2) }}</td>
                                                    <td>{{ $data->date ? \Carbon\Carbon::parse($data->date)->format('F j, Y, g:i a') : 'N/A' }}</td>
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
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#checkFeedTable').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                emptyTable: "No Check My Feed data available"
            }
        });
    });
</script>
@endpush