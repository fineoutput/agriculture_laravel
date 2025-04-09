@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Add New Manager</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li> --}}
                        <li class="breadcrumb-item active">Add Manager</li>
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
                            <div id="errorDiv" class="alert alert-danger alert-dismissible" style="display:none;">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                                <h4><i class="fa fa-ban"></i> Alert!</h4>
                                <span id="errorMessage"></span>
                            </div>
                            <!-- End Messages -->

                            <h4 class="mt-0 header-title">Add New Manager</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <form action="{{ route('admin.manager.add_data', base64_encode(1)) }}" method="POST" id="slide_frm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name"><strong>Full Name</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control" placeholder="Enter full name" required value="{{ old('name') }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="address"><strong>Address</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="address" id="address" class="form-control" placeholder="Enter address" required value="{{ old('address') }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone"><strong>Phone Number</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter phone number" required value="{{ old('phone') }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email"><strong>Email</strong> <span style="color:red;">*</span></label>
                                            <input type="email" name="email" id="email" class="form-control" placeholder="Enter email" required value="{{ old('email') }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aadhar"><strong>Aadhar Number</strong></label>
                                            <input type="text" name="aadhar" id="aadhar" class="form-control" placeholder="Enter Aadhar number" value="{{ old('aadhar') }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="refer_code"><strong>Your Refer Code</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="refer_code" id="refer" class="form-control" placeholder="Generated refer code" required value="{{ old('refer_code') }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="images"><strong>Aadhar Upload</strong></label>
                                            <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*" />
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
    $(document).ready(function() {
        // Limit file uploads to 2
        $('#images').on('change', function() {
            if (this.files.length > 2) {
                alert('You can only upload a maximum of 2 files');
                this.value = ''; // Clear the input
            }
        });

        // Generate referral code based on name
        $('#name').on('input', function() {
            const name = this.value.trim().replace(/\s+/g, '');
            const id = name.slice(0, 4).padEnd(4, 'X').toUpperCase();
            const randomString = Math.random().toString(36).substring(2, 8).toUpperCase();
            const referralCode = id + randomString;
            $('#refer').val(referralCode);
        });

        // Restrict Aadhar to 12 digits
        $('#aadhar').on('input', function(event) {
            const aadhar = event.target.value.replace(/\D/g, '').slice(0, 12);
            event.target.value = aadhar;
        });

        // Restrict Phone to 10 digits
        $('#phone').on('input', function(event) {
            const phone = event.target.value.replace(/\D/g, '').slice(0, 10);
            event.target.value = phone;
        });

        // Form submission validation
        $('#slide_frm').on('submit', function(event) {
            const aadhar = $('#aadhar').val();
            const phone = $('#phone').val();
            let errorMessage = '';

            if (aadhar.length > 0 && aadhar.length !== 12) {
                errorMessage += 'Aadhar number must be exactly 12 digits.<br>';
            }
            if (phone.length !== 10) {
                errorMessage += 'Phone number must be exactly 10 digits.<br>';
            }

            if (errorMessage) {
                $('#errorMessage').html(errorMessage);
                $('#errorDiv').show();
                event.preventDefault();
            } else {
                $('#errorDiv').hide();
            }
        });
    });
</script>
@endpush