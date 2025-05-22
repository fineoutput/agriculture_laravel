@extends('admin.base_template')

@section('main')

<style>
    label {
        margin: 5px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Daily Records</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.farmers.records', $farmer_id) }}"><i class="fa fa-dashboard"></i> View Page</a></li>
            <li class="active">Daily Records</li>
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
                        <div class="panel-body">
                            <form action="{{ route('admin.farmers.view_daily_records', $farmer_id) }}" method="GET" id="slide_frm" enctype="multipart/form-data">
                                <div style="display:flex; justify-content: space-between;">
                                    <div>
                                        <a class="btn btn-sm btn-primary" href="{{ route('admin.farmers.view_daily_records', $farmer_id) }}">Clear</a>
                                    </div>
                                    <div style="display:flex;">
                                        <div style="display:flex; align-items: center;">
                                            <label>Start Date</label>
                                            <input type="date" name="start_date" class="form-control" required style="width:60%; margin-left: 5px;" value="{{ request('start_date') }}" />
                                        </div>
                                        <div style="display:flex; align-items: center; margin-left: 10px;">
                                            <label>End Date</label>
                                            <input type="date" name="end_date" class="form-control" required style="width:60%; margin-left: 5px;" value="{{ request('end_date') }}" />
                                        </div>
                                        <input type="submit" class="btn btn-success" value="Save" style="margin-left: 10px;">
                                    </div>
                                </div>
                            </form>
                            <hr />

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

                            <div class="box-body table-responsive no-padding">
                                <table class="table table-bordered table-hover table-striped" id="userTable">
                                    <thead>
                                        <tr>
                                            <th>Entry Id</th>
                                            <th>Record Date</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data_daily_records as $data)
                                            <tr>
                                                <td>{{ $data->entry_id }}</td>
                                                <td>{{ $data->record_date ?? 'N/A' }}</td>
                                                <td>{{ $data->date ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a class="btn btn-primary btn-sm" href="{{ route('admin.farmers.view_details_dr', [$farmer_id, $data->entry_id]) }}">View Details</a>
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