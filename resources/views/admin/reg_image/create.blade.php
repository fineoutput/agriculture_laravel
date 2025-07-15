@extends('admin.base_template')

@section('main')

 <h2 style="margin-top: 200px">Upload New Image</h2>

    <form method="POST" action="{{ route('admin.reg_image.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>Select Image:</label>
            <input type="file" name="image" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success mt-2">Upload</button>
    </form>

@endsection
