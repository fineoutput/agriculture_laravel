@extends('admin.base_template')
@section('main')
<!-- Start content -->
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="page-title-box">
          <h4 class="page-title">Edit Farmer Slider</h4>
          <ol class="breadcrumb">
            <li class="breadcrumb-item active">Edit Farmer Slider</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="page-content-wrapper">
      <div class="row">
        <div class="col-12">
          <div class="card m-b-20">
            <div class="card-body">
              @if (session('success'))
                <div class="alert alert-success" role="alert">
                  {{ session('success') }}
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              @endif
              @if (session('error'))
                <div class="alert alert-danger" role="alert">
                  {{ session('error') }}
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              @endif

              <h4 class="mt-0 header-title">Update Farmer Slider</h4>
              <hr style="margin-bottom: 30px; background-color: darkgrey;">

              <form action="{{ route('update_farmer_slider', ['id' => $slider->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

                <!-- Multiple Image Upload -->
                <div class="form-group row">
                  <label for="image" class="col-sm-2 col-form-label"><strong>Images</strong> <span style="color:red;">*</span></label>
                  <div class="col-sm-10">
                    <input type="file" name="image[]" class="form-control" multiple>
                    <small class="text-muted">Leave empty if you don't want to change the images. You can upload multiple images.</small>

                    <!-- Show existing images -->
                    @if($slider->image)
                      <div class="row mt-3">
                        @php
                          $images = json_decode($slider->image, true);
                        @endphp
                        @foreach($images as $image)
                          <div class="col-md-2 mb-3">
                            <img src="{{ asset($image) }}" alt="Slider Image" class="img-fluid rounded" style="height: 100px;">
                          </div>
                        @endforeach
                      </div>
                    @endif
                  </div>
                </div>

                <div class="form-group text-right">
                  <button type="submit" class="btn btn-success">Update</button>
                </div>
              </form>

            </div> <!-- card-body -->
          </div> <!-- card -->
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
