@extends('admin.base_template')

@section('main')

<style>
    label {
        margin: 5px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Milk Records</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.farmers.records', $farmer_id) }}"><i class="fa fa-dashboard"></i> View Page</a></li>
            <li class="active">Milk Records</li>
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
                                            <th>Group</th>
                                            <th>Cattle Type</th>
                                            <th>Cattle Tag</th>
                                            <th>Milking Slot</th>
                                            <th>Milk Date</th>
                                            <th>Entry Milk</th>
                                            <th>Price Milk</th>
                                            <th>Fat</th>
                                            <th>SNF</th>
                                            <th>Total Price</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data_milk_records as $index => $data)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $data->information_type ?? 'N/A' }}</td>
                                                <td>
                                                    @php
                                                        $group = \App\Models\Group::find($data->group_id);
                                                    @endphp
                                                    {{ $group->name ?? 'N/A' }}
                                                </td>
                                                <td>{{ $data->cattle_type ?? 'N/A' }}</td>
                                                <td>{{ $data->tag_no ?? 'N/A' }}</td>
                                                <td>{{ $data->milking_slot ?? 'N/A' }}</td>
                                                <td>{{ $data->milk_date ?? 'N/A' }}</td>
                                                <td>{{ $data->entry_milk ?? 'N/A' }}</td>
                                                <td>{{ $data->price_milk ? '₹' . $data->price_milk : 'N/A' }}</td>
                                                <td>{{ $data->fat ?? 'N/A' }}</td>
                                                <td>{{ $data->snf ?? 'N/A' }}</td>
                                                <td>{{ $data->total_price ? '₹' . $data->total_price : 'N/A' }}</td>
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