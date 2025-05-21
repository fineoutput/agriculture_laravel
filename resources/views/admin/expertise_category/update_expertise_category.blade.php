@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Update Expertise Category</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.expertise_category.view') }}">Expertise Categories</a></li>
                        <li class="breadcrumb-item active">Update Expertise Category</li>
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

                            <h4 class="mt-0 header-title">Update Expertise Category</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <form action="{{ route('admin.expertise_category.add_data', [base64_encode(2), $idd]) }}" method="POST" id="slide_frm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name"><strong>Name</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control" placeholder="Enter name" required value="{{ old('name', $expertise_category_data->name) }}" />
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image"><strong>Image English</strong></label>
                                            <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png" />
                                            @if($expertise_category_data->image)
                                                <img src="{{ asset($expertise_category_data->image) }}" height="50" width="100" alt="Current Image" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image_hindi"><strong>Image Hindi</strong></label>
                                            <input type="file" name="image_hindi" id="image_hindi" class="form-control" accept="image/jpeg,image/png" />
                                            @if($expertise_category_data->image_hindi)
                                                <img src="{{ asset($expertise_category_data->image_hindi) }}" height="50" width="100" alt="Current Image Hindi" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image_hindi')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image_punjabi"><strong>Image Punjabi</strong></label>
                                            <input type="file" name="image_punjabi" id="image_punjabi" class="form-control" accept="image/jpeg,image/png" />
                                            @if($expertise_category_data->image_punjabi)
                                                <img src="{{ asset($expertise_category_data->image_punjabi) }}" height="50" width="100" alt="Current Image Punjabi" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image_punjabi')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image_marathi"><strong>Image Marathi</strong></label>
                                            <input type="file" name="image_marathi" id="image_marathi" class="form-control" accept="image/jpeg,image/png" />
                                            @if($expertise_category_data->image_marathi)
                                                <img src="{{ asset($expertise_category_data->image_marathi) }}" height="50" width="100" alt="Current Image marathi" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image_marathi')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image_gujrati"><strong>Image Punjabi</strong></label>
                                            <input type="file" name="image_gujrati" id="image_gujrati" class="form-control" accept="image/jpeg,image/png" />
                                            @if($expertise_category_data->image_gujrati)
                                                <img src="{{ asset($expertise_category_data->image_gujrati) }}" height="50" width="100" alt="Current Image Gujrati" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image_gujrati')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
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

@push('scripts')
<script type="text/javascript">
    // No additional JavaScript needed for basic form functionality
    // CKEditor initialization removed as there's no editor1 in this form
</script>
@endpush