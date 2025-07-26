@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Edit Milk Ranking</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('admin.competition.index') }}"><i class="fa fa-dashboard"></i> Competition Entries</a></li>
            <li><a href="{{ route('admin.competition.rankings', $competition->id) }}"><i class="fa fa-dashboard"></i> Competition Rankings</a></li>
            <li class="active">Edit Ranking</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            Edit Ranking for Competition #{{ $competition->id }}
                            <span class="pull-right">
                                <a href="{{ route('admin.competition.rankings', $competition->id) }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to Rankings
                                </a>
                            </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        @if(session('message'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <h4><i class="icon fa fa-check"></i> Alert!</h4>
                                {{ session('message') }}
                            </div>
                        @endif

                        @if(session('emessage'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                                {{ session('emessage') }}
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <h4>Farmer Details:</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Farmer Name:</th>
                                        <td>{{ $ranking->farmer->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Competition ID:</th>
                                        <td>{{ $competition->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Submission Date:</th>
                                        <td>{{ $ranking->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <form action="{{ route('admin.competition.ranking.update', $ranking->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('POST')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="weight">Weight (kg) <span style="color:red;">*</span></label>
                                        <input type="number" step="0.01" name="weight" id="weight" class="form-control" value="{{ $ranking->weight }}" required>
                                        @error('weight')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="position">Assigned Position</label>
                                        <select name="position" id="position" class="form-control">
                                            <option value="">No Rank</option>
                                            <option value="1" {{ $ranking->position == 1 ? 'selected' : '' }}>1st Place</option>
                                            <option value="2" {{ $ranking->position == 2 ? 'selected' : '' }}>2nd Place</option>
                                            <option value="3" {{ $ranking->position == 3 ? 'selected' : '' }}>3rd Place</option>
                                        </select>
                                        @error('position')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="image">Current Image:</label>
                                        @if($ranking->image)
                                            <div class="mb-2">
                                                <img src="{{ $ranking->image }}" alt="Current milk image" width="200" class="img-thumbnail">
                                            </div>
                                        @else
                                            <p class="text-muted">No image uploaded</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="image">Update Image (Optional):</label>
                                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                        <small class="text-muted">Leave empty to keep current image. Supported formats: JPEG, PNG, JPG, GIF. Max size: 2MB</small>
                                        @error('image')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa fa-save"></i> Update Ranking
                                        </button>
                                        <a href="{{ route('admin.competition.rankings', $competition->id) }}" class="btn btn-default">
                                            <i class="fa fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

