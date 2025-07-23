@extends('admin.base_template')

@section('main')

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="page-title-box">
          <h4 class="page-title">Competition Ranking</h4>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Ranking</a></li>
            <li class="breadcrumb-item active">View Competition Ranking</li>
          </ol>
        </div>
      </div>
    </div>
<div class="container">
    <h2>Milk Rankings</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Farmer Name</th>
                <th>Weight</th>
                <!-- <th>Score</th> -->
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rankings as $index => $ranking)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $ranking->farmer->farmer_name ?? 'N/A' }}</td>
                    <td>{{ $ranking->weight }} L</td>
                    <!-- <td>{{ $ranking->score ?? '-' }}</td> -->
                    <td><img src="{{ asset('storage/' . $ranking->image) }}" width="80" /></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
</div>
@endsection
