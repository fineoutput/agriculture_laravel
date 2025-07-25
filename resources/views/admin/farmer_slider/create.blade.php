@extends('admin.base_template')
@section('main')
<!-- Start content -->
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="page-title-box">
          <h4 class="page-title">Add New Farmer Slider</h4>
          <ol class="breadcrumb">
            {{-- <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li> --}}
            <li class="breadcrumb-item active">Add Farmer Slider</li>
          </ol>
        </div>
      </div>
    </div>
    <!-- end row -->

    <div class="page-content-wrapper">
      <div class="row">
        <div class="col-12">
          <div class="card m-b-20">
            <div class="card-body">
              <!-- Success & Error Messages -->
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
              <!-- End Messages -->

              <h4 class="mt-0 header-title">Upload Farmer Slider</h4>
              <hr style="margin-bottom: 30px; background-color: darkgrey;">

              <form action="{{ route('store_farmer_slider') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group row">
                  <label for="image" class="col-sm-2 col-form-label"><strong>Image</strong> <span style="color:red;">*</span></label>
                  <div class="col-sm-10">
                    <input type="file" name="image" class="form-control" required>
                  </div>
                </div>
                <div class="form-group text-right">
                  <button type="submit" class="btn btn-success">Save</button>
                </div>
              </form>

            </div> <!-- card-body -->
          </div> <!-- card -->
        </div> <!-- end col -->
      </div> <!-- end row -->
    </div> <!-- end page-content-wrapper -->
  </div> <!-- container-fluid -->
</div> <!-- content -->
@endsection
