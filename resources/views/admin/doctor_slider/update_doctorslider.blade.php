@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Update Doctor Slider</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.doctor_slider.view') }}">Doctor Sliders</a></li>
                        <li class="breadcrumb-item active">Update Doctor Slider</li>
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

                            <h4 class="mt-0 header-title">Update Doctor Slider</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <form action="{{ route('admin.doctor_slider.add_data', [base64_encode(2), $idd]) }}" method="POST" id="slide_frm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image"><strong>Image</strong></label>
                                            <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png" />
                                            @if($doctorslider->image)
                                                <img src="{{ asset($doctorslider->image) }}" height="50" width="100" alt="Current Image" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image')
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