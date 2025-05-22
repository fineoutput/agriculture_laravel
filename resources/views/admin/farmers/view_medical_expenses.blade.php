@extends('admin.base_template')

@section('main')

<style>
    label {
        margin: 5px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Medical Expenses</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.farmers.records', $farmer_id) }}"><i class="fa fa-dashboard"></i> View Page</a></li>
            <li class="active">Medical Expenses</li>
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
                                            <th>Expense Date</th>
                                            <th>Doctor Visit Fees</th>
                                            <th>Treatment Expenses</th>
                                            <th>Vaccination Expenses</th>
                                            <th>Deworming Expenses</th>
                                            <th>Other1</th>
                                            <th>Other2</th>
                                            <th>Other3</th>
                                            <th>Other4</th>
                                            <th>Other5</th>
                                            <th>Total Price</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data_medical_expenses as $index => $data)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $data->expense_date ?? 'N/A' }}</td>
                                                <td>{{ $data->doctor_visit_fees ? '₹' . $data->doctor_visit_fees : 'N/A' }}</td>
                                                <td>{{ $data->treatment_expenses ? '₹' . $data->treatment_expenses : 'N/A' }}</td>
                                                <td>{{ $data->vaccination_expenses ? '₹' . $data->vaccination_expenses : 'N/A' }}</td>
                                                <td>{{ $data->deworming_expenses ? '₹' . $data->deworming_expenses : 'N/A' }}</td>
                                                <td>{{ $data->other1 ?? 'N/A' }}</td>
                                                <td>{{ $data->other2 ?? 'N/A' }}</td>
                                                <td>{{ $data->other3 ?? 'N/A' }}</td>
                                                <td>{{ $data->other4 ?? 'N/A' }}</td>
                                                <td>{{ $data->other5 ?? 'N/A' }}</td>
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