@extends('admin.base_template')

@section('main')
<div class="content">
  <div class="container-fluid">

    <!-- Page Header -->
    <div class="row">
      <div class="col-sm-12">
        <div class="page-title-box">
          <h4 class="page-title">Edit Disease</h4>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('disease.index') }}">All Diseases</a></li>
            <li class="breadcrumb-item active">Edit Disease</li>
          </ol>
        </div>
      </div>
    </div>

    <!-- Page Content -->
    <div class="page-content-wrapper">
      <div class="row">
        <div class="col-12">
          <div class="card m-b-20">
            <div class="card-body">

              <!-- Success & Error Messages -->
              @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
              </div>
              @endif

              @if (session('error'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
              </div>
              @endif

              <!-- Form Header -->
              <h4 class="mt-0 header-title mb-3">Update Disease Information</h4>

              <!-- Form -->
              @if($disease)
              <form action="{{ route('disease.update', ['id' => $disease->id]) }}" method="POST" enctype="multipart/form-data">


                @csrf
                @method('PUT')

                <div class="form-group row">
                  <label class="col-sm-2 col-form-label"><strong>Title</strong> <span class="text-danger">*</span></label>
                  <div class="col-sm-10">
                    <textarea name="title" class="form-control" required>{{ old('title', $disease->title) }}</textarea>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-sm-2 col-form-label"><strong>Description</strong> <span class="text-danger">*</span></label>
                  <div class="col-sm-10">
                    <textarea name="content" id="editor1" class="form-control" required>{{ old('content', $disease->content) }}</textarea>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-sm-2 col-form-label"><strong>Image</strong></label>
                  <div class="col-sm-10">
                    <input type="file" name="image1" class="form-control">
                    @if ($disease->image1)
                    <img src="{{ asset($disease->image1) }}" alt="Current Image" class="mt-2" width="100">

                    @endif
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col-sm-10 offset-sm-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                  </div>
                </div>

              </form>
              @else
    <p>Disease not found.</p>
@endif
            </div> <!-- card-body -->
          </div> <!-- card -->
        </div> <!-- col -->
      </div> <!-- row -->
    </div> <!-- page-content-wrapper -->

  </div> <!-- container-fluid -->
</div> <!-- content -->
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/plugins/ckeditor/ckeditor.js') }}"></script>
<script>
  CKEDITOR.replace('editor1');
</script>
@endpush
