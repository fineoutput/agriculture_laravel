@extends('admin.base_template')

@section('main')

<style>
    label {
        margin: 5px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Stock List</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.farmers.records', $farmer_id) }}"><i class="fa fa-dashboard"></i> View Page</a></li>
            <li class="active">Stock List</li>
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
                                            <th>Stock Date</th>
                                            <th>Animal Name</th>
                                            <th>Green Forage</th>
                                            <th>Dry Fodder</th>
                                            <th>Silage</th>
                                            <th>Cake</th>
                                            <th>Grains</th>
                                            <th>Bioproducts</th>
                                            <th>Churi</th>
                                            <th>Oil Seeds</th>
                                            <th>Minerals</th>
                                            <th>Bypass Fat</th>
                                            <th>Toxins</th>
                                            <th>Buffer</th>
                                            <th>Yeast</th>
                                            <th>Calcium</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data_stock_handling as $index => $data)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $data->stock_date ?? 'N/A' }}</td>
                                                <td>{{ $data->animal_name ?? 'N/A' }}</td>
                                                <td>{{ $data->green_forage ?? 'N/A' }}</td>
                                                <td>{{ $data->dry_fodder ?? 'N/A' }}</td>
                                                <td>{{ $data->silage ?? 'N/A' }}</td>
                                                <td>{{ $data->cake ?? 'N/A' }}</td>
                                                <td>{{ $data->grains ?? 'N/A' }}</td>
                                                <td>{{ $data->bioproducts ?? 'N/A' }}</td>
                                                <td>{{ $data->churi ?? 'N/A' }}</td>
                                                <td>{{ $data->oil_seeds ?? 'N/A' }}</td>
                                                <td>{{ $data->minerals ?? 'N/A' }}</td>
                                                <td>{{ $data->bypass_fat ?? 'N/A' }}</td>
                                                <td>{{ $data->toxins ?? 'N/A' }}</td>
                                                <td>{{ $data->buffer ?? 'N/A' }}</td>
                                                <td>{{ $data->yeast ?? 'N/A' }}</td>
                                                <td>{{ $data->calcium ?? 'N/A' }}</td>
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