@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Competition Rankings</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('admin.competition.index') }}"><i class="fa fa-dashboard"></i> Competition Entries</a></li>
            <li class="active">Competition Rankings</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            Rankings for Competition #{{ $competition->id }}
                            <span class="pull-right">
                                <a href="{{ route('admin.competition.index') }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to Competitions
                                </a>
                            </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Competition Details:</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Competition Date:</th>
                                        <td>{{ $competition->competition_date ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Start Date:</th>
                                        <td>{{ $competition->start_date ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>End Date:</th>
                                        <td>{{ $competition->end_date ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Entry Fees:</th>
                                        <td>₹{{ $competition->entry_fees ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <h4>Rankings ({{ $rankings->count() }} entries):</h4>
                        
                        @if($rankings->count() > 0)
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-bordered table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Farmer Name</th>
                                            <th>Weight (kg)</th>
                                            <th>Image</th>
                                            <th>Submission Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rankings as $index => $ranking)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-{{ $index == 0 ? 'success' : ($index == 1 ? 'warning' : ($index == 2 ? 'info' : 'default')) }}">
                                                        {{ $index + 1 }}
                                                    </span>
                                                </td>
                                                <td>{{ $ranking->farmer->name ?? 'N/A' }}</td>
                                                <td><strong>{{ $ranking->weight }}</strong></td>
                                               
                                                <td>
                            @if($ranking->image)
                                <img src="{{ $ranking->image }}" alt="milk" width="80">
                            @endif
                        </td>
                                                <td>{{ $ranking->created_at->format('d M Y, h:i A') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <h4><i class="icon fa fa-info"></i> No Rankings Found</h4>
                                No milk rankings have been submitted for this competition yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection 