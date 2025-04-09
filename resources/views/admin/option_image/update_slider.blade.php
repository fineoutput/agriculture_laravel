@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Update Option Image</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.option_image.view') }}">Option Images</a></li>
                        <li class="breadcrumb-item active">Update Slider</li>
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

                            <h4 class="mt-0 header-title">Update Slider</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <form action="{{ route('admin.option_image.add_data', [base64_encode(2), $idd]) }}" method="POST" id="slide_frm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image1"><strong>Image 1</strong></label>
                                            <input type="file" name="image1" id="image1" class="form-control" accept="image/jpeg,image/png" />
                                            @if($slider->image1)
                                                <img src="{{ asset($slider->image1) }}" height="50" width="100" alt="Current Image 1" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image1')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image2"><strong>Image 2</strong></label>
                                            <input type="file" name="image2" id="image2" class="form-control" accept="image/jpeg,image/png" />
                                            @if($slider->image2)
                                                <img src="{{ asset($slider->image2) }}" height="50" width="100" alt="Current Image 2" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image2')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image3"><strong>Image 3</strong></label>
                                            <input type="file" name="image3" id="image3" class="form-control" accept="image/jpeg,image/png" />
                                            @if($slider->image3)
                                                <img src="{{ asset($slider->image3) }}" height="50" width="100" alt="Current Image 3" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image3')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image4"><strong>Image 4</strong></label>
                                            <input type="file" name="image4" id="image4" class="form-control" accept="image/jpeg,image/png" />
                                            @if($slider->image4)
                                                <img src="{{ asset($slider->image4) }}" height="50" width="100" alt="Current Image 4" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image4')
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
</script>
@endpush