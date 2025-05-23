@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Pop-up Images</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}"><i class="fa fa-dashboard"></i> Home</a></li> --}}
                        <li class="breadcrumb-item active">Pop-up Images</li>
                    </ol>
                </div>
            </div>
        </div>

        @if (Auth::guard('admin')->user()->position == 'Manager')
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Access Denied!</h4>
                Managers cannot manage pop-up images.
            </div>
        @else
            <div class="page-content-wrapper">
                @if (session('smessage'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('smessage') }}
                    </div>
                @endif
                @if (session('emessage'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        {{ session('emessage') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-image fa-fw"></i> Manage Pop-up Images</h3>
                            </div>
                            <div class="panel-body">
                                <!-- Upload Form -->
                                <form action="{{ route('admin.popup_images.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="image">Image (JPEG/PNG, Max 2MB)</label>
                                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary mb-3">Upload Image</button>
                                </form>

                                <!-- Image Table -->
                                <div class="box-body table-responsive no-padding">
                                    <table class="table table-bordered table-hover table-striped" id="popupImageTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                {{-- <th>Title</th> --}}
                                                <th>Image</th>
                                                <th>Uploaded At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($popup_images as $index => $image)
                                                <tr>
                                                    {{-- <td>{{ $index + 1 }}</td> --}}
                                                    <td>{{ $image->title }}</td>
                                                    <td>
                                                        <a href="{{ $image->image }}" target="_blank">
                                                            <img src="{{ $image->image }}" alt="{{ $image->title }}" style="max-width: 100px; max-height: 100px;">
                                                        </a>
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($image->created_at)->format('F j, Y, g:i a') }}</td>
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
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#popupImageTable').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                emptyTable: "No pop-up images available"
            }
        });
    });
</script>
@endpush