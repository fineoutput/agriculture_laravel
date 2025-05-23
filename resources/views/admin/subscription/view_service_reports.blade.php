@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">{{ $heading }} Report</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}"><i class="fa fa-dashboard"></i> {{ $heading }} Report</a></li> --}}
                        <li class="breadcrumb-item active">View {{ $heading }} Report</li>
                    </ol>
                </div>
            </div>
        </div>

        @if (Auth::guard('admin')->user()->position == 'Manager')
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> Access Denied!</h4>
                Managers cannot access the {{ $heading }} report.
            </div>
        @else
            <div class="page-content-wrapper mt-0">
                <!-- Flash Messages -->
                @if (session('smessage'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('smessage') }}
                    </div>
                @endif
                @if (session('emessage'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        {{ session('emessage') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-money fa-fw"></i> View {{ $heading }} Report</h3>
                            </div>
                            <div class="panel-body">
                                <div class="box-body table-responsive no-padding">
                                    <table class="table table-bordered table-hover table-striped" id="userTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Farmer</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($service_data as $index => $data)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $data->farmer ? $data->farmer->name . ' - ' . $data->farmer->phone : 'N/A' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($data->date)->format('F j, Y, g:i a') }}</td>
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

@endpush