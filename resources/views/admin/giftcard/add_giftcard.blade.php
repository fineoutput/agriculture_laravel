@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Add New Gift Card</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li>
                        <li class="breadcrumb-item active">Add Gift Card</li>
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

                            <h4 class="mt-0 header-title">Add New Gift Card</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <form action="{{ route('admin.giftcard.add_data', base64_encode(1)) }}" method="POST" id="slide_frm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="amount"><strong>Amount</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="amount" id="amount" class="form-control" placeholder="Enter amount" required value="{{ old('amount') }}" />
                                            @error('amount')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="count"><strong>Gift Count</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="count" id="count" class="form-control" placeholder="Enter gift count" required value="{{ old('count') }}" />
                                            @error('count')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_range"><strong>Start Range</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="start_range" id="start_range" class="form-control" placeholder="Enter start range" required value="{{ old('start_range') }}" />
                                            @error('start_range')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_range"><strong>End Range</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="end_range" id="end_range" class="form-control" placeholder="Enter end range" required value="{{ old('end_range') }}" />
                                            @error('end_range')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image1"><strong>Image</strong> <span style="color:red;">*</span></label>
                                            <input type="file" name="image1" id="image1" class="form-control" required accept="image/*,.xlsx,.csv,.xls,.pdf,.doc,.docx,.txt" />
                                            @error('image1')
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