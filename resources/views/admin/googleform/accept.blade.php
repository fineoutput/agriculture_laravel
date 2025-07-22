@extends('admin.base_template')

@section('main')

<style>
    input.qtydiscount { width: 61px; }
    label { margin: 5px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Accepted Milking Competition Users</h1>
        <ol class="breadcrumb">
            <li class="active">Accepted Users</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-users"></i> Accepted Users List</h3>
                    </div>
                    <div class="panel-body">
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-bordered table-hover table-striped" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>ID</th><th>Farmer Name</th><th>Email</th><th>Mobile</th><th>Village</th>
                                        <th>District</th><th>State</th><th>Animal ID</th><th>Breed</th>
                                        <th>Lactation No</th><th>Date Of Calving</th><th>Milk Yield</th>
                                        <th>Aadhar Number</th><th>Animal Photo</th><th>Farmer Photo</th><th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($form_data as $row)
                                        <tr>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->farmer_name }}</td>
                                            <td>{{ $row->Email }}</td>
                                            <td>{{ $row->mobile_number }}</td>
                                            <td>{{ $row->village_Town }}</td>
                                            <td>{{ $row->district }}</td>
                                            <td>{{ $row->state }}</td>
                                            <td>{{ $row->animal_ID }}</td>
                                            <td>{{ $row->breed }}</td>
                                            <td>{{ $row->lactation_no }}</td>
                                            <td>{{ $row->date_of_calving }}</td>
                                            <td>{{ $row->milk_yield }}</td>
                                            <td>{{ $row->aadhar_number }}</td>
                                            <td>
                                                @php $photos = json_decode($row->animal_photo_upload, true); @endphp
                                                @if (!empty($photos))
                                                    @foreach ($photos as $photo)
                                                        <img src="{{ asset($photo) }}" width="80" style="margin-right:5px; margin-bottom:5px;">
                                                    @endforeach
                                                @else N/A @endif
                                            </td>
                                            <td>
                                                @if (!empty($row->farmer_photo_upload))
                                                    <img src="{{ asset($row->farmer_photo_upload) }}" width="80">
                                                @else N/A @endif
                                            </td>
                                            <td>{{ $row->created_at }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div> {{-- panel-body --}}
                </div> {{-- panel --}}
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap.js') }}"></script>
<script>
    $(document).ready(function () {
        $('#dataTable').DataTable({
            responsive: true,
            stateSave: true
        });
    });
</script>
@endpush

@endsection
