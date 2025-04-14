@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">{{ $subcategory_images->name ?? 'Update Subcategory Images' }}</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.subcategory_images.view') }}">Subcategory Images</a></li>
                        <li class="breadcrumb-item active">Update</li>
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
                            @if (session('smessage'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('smessage') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                            @endif
                            @if (session('emessage'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('emessage') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                            @endif
                            <!-- End Messages -->

                            <!-- Debug Warning -->
                            @if(!$subcategory_images)
                                <div class="alert alert-warning">Subcategory image data not found. Please check the database.</div>
                            @endif

                            <h4 class="mt-0 header-title"><i class="fa fa-image"></i> Update Subcategory Images</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <form action="{{ route('admin.subcategory_images.add_data', ['t' => 'update', 'iw' => $idd]) }}" method="POST" id="slide_frm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image"><strong>Image (English)</strong></label>
                                            <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png" />
                                            @if($subcategory_images->image)
                                                <img src="{{ asset($subcategory_images->image) }}" height="50" width="100" alt="English Image" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">No Image</span>
                                            @endif
                                            @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image_hindi"><strong>Image (Hindi)</strong></label>
                                            <input type="file" name="image_hindi" id="image_hindi" class="form-control" accept="image/jpeg,image/png" />
                                            @if($subcategory_images->image_hindi)
                                                <img src="{{ asset($subcategory_images->image_hindi) }}" height="50" width="100" alt="Hindi Image" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">No Image</span>
                                            @endif
                                            @error('image_hindi') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image_punjabi"><strong>Image (Punjabi)</strong></label>
                                            <input type="file" name="image_punjabi" id="image_punjabi" class="form-control" accept="image/jpeg,image/png" />
                                            @if($subcategory_images->image_punjabi)
                                                <img src="{{ asset($subcategory_images->image_punjabi) }}" height="50" width="100" alt="Punjabi Image" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">No Image</span>
                                            @endif
                                            @error('image_punjabi') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-success">Save</button>
                                    </div>
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