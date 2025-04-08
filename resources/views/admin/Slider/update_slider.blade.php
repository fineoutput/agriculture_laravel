@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Update Slider</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        @if (session('emessage'))
                            <div class="alert alert-danger">{{ session('emessage') }}</div>
                        @endif
                        <form action="{{ route('admin.slider.add_data', [base64_encode(2), $id]) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Current Image</label>
                                @if($slider->image)
                                    <img src="{{ asset($slider->image) }}" height="50" width="100" alt="Slider Image">
                                @endif
                                <label>Upload New Image</label>
                                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png">
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection