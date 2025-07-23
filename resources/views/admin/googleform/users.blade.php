@extends('admin.base_template')

@section('main')

<style>
    input.qtydiscount { width: 61px; }
    label { margin: 5px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Milking Competition Users</h1>
        <ol class="breadcrumb">
            <li class="active">Milking Competition Users</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-users"></i> Users List</h3>
                    </div>

                    @if (session('smessage'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong><i class="fa fa-check"></i></strong> {{ session('smessage') }}
                        </div>
                    @endif
                    @if (session('emessage'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong><i class="fa fa-ban"></i></strong> {{ session('emessage') }}
                        </div>
                    @endif

                    <div class="panel-body">
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-bordered table-hover table-striped" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Farmer Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Village</th>
                                        <th>District</th>
                                        <th>State</th>
                                        <th>Animal ID</th>
                                        <th>Breed</th>
                                        <th>Lactation No</th>
                                        <th>Date Of Calving</th>
                                        <th>Milk Yield</th>
                                        <th>Aadhar Number</th>
                                        <th>Animal Photo</th>
                                        <th>Farmer Photo</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                        <th>Action</th>
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
                                            <td>
                                                @php
                                                    $statusLabels = [
                                                        0 => ['label' => 'Pending', 'class' => 'badge bg-warning'],
                                                        1 => ['label' => 'Accepted', 'class' => 'badge bg-success'],
                                                        2 => ['label' => 'Rejected', 'class' => 'badge bg-danger'],
                                                        3 => ['label' => 'Rejected', 'class' => 'badge bg-danger'],
                                                    ];
                                                @endphp
                                                <span class="{{ $statusLabels[$row->status]['class'] }}">
                                                    {{ $statusLabels[$row->status]['label'] }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($row->status == 0)
                                                    <form method="POST" action="{{ route('admin.googleform.accept', $row->id) }}" style="display:inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.googleform.reject', $row->id) }}" style="display:inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                                    </form>
                                                @else

                                                @if($row->status == 1)
    <a href="{{ route('admin.googleform.disqualify', $row->id) }}" class="btn btn-warning btn-sm">Disqualify</a>
@endif
N/A
                                                @endif
                                            </td>
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
