@extends('admin.base_template')

@section('main')

<style>
    label {
        margin: 5px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Sale/Purchase</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.farmers.records', $farmer_id) }}"><i class="fa fa-dashboard"></i> View Page</a></li>
            <li class="active">Sale/Purchase</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>{{ $farmer->name ?? 'Unknown Farmer' }}</h3>
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
                                <table class="table table-bordered table-hover table-striped" id="userTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Information Type</th>
                                            <th>Animal Name</th>
                                            <th>Milk Production</th>
                                            <th>Lactation</th>
                                            <th>Location</th>
                                            <th>Pastorate Pregnant</th>
                                            <th>Expected Price</th>
                                            <th>Animal Type</th>
                                            <th>Description</th>
                                            <th>Remarks</th>
                                            <th>Image1</th>
                                            <th>Image2</th>
                                            <th>Image3</th>
                                            <th>Image4</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data_sale_purchase as $index => $data)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $data->information_type ?? 'N/A' }}</td>
                                                <td>{{ $data->animal_name ?? 'N/A' }}</td>
                                                <td>{{ $data->milk_production ?? 'N/A' }}</td>
                                                <td>{{ $data->lactation ?? 'N/A' }}</td>
                                                <td>{{ $data->location ?? 'N/A' }}</td>
                                                <td>{{ $data->pastorate_pregnant ?? 'N/A' }}</td>
                                                <td>{{ $data->expected_price ? '₹' . $data->expected_price : 'N/A' }}</td>
                                                <td>{{ $data->animal_type ?? 'N/A' }}</td>
                                                <td>{{ $data->description ?? 'N/A' }}</td>
                                                <td>{{ $data->remarks ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($data->image1)
                                                        <img src="{{ asset($data->image1) }}" height="50" width="100" alt="Image1">
                                                    @else
                                                        Sorry No image Found
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($data->image2)
                                                        <img src="{{ asset($data->image2) }}" height="50" width="100" alt="Image2">
                                                    @else
                                                        Sorry No image Found
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($data->image3)
                                                        <img src="{{ asset($data->image3) }}" height="50" width="100" alt="Image3">
                                                    @else
                                                        Sorry No image Found
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($data->image4)
                                                        <img src="{{ asset($data->image4) }}" height="50" width="100" alt="Image4">
                                                    @else
                                                        Sorry No image Found
                                                    @endif
                                                </td>
                                                <td>{{ $data->date ?? 'N/A' }}</td>
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

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#userTable').DataTable({
            responsive: true,
        });
    });
</script>
@endpush
@endsection