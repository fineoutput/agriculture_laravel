@extends('admin.base_template')

@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Update Vendor</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin_index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.vendor.accepted') }}">Vendors</a></li>
                        <li class="breadcrumb-item active">Update Vendor</li>
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

                            <h4 class="mt-0 header-title"><i class="fa fa-money fa-fw"></i> Update Vendor</h4>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <!-- Debug Vendor Data -->
                            @if(!$vendor)
                                <div class="alert alert-warning">Vendor data not found. Please check the database.</div>
                            @endif

                            <form action="{{ route('admin.vendor.add_data', [base64_encode(2), $idd]) }}" method="POST" id="slide_frm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name"><strong>Name (English)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', $vendor->name ?? '') }}" />
                                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="hi_name"><strong>Name (Hindi)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="hi_name" id="hi_name" class="form-control" required value="{{ old('hi_name', $vendor->hi_name ?? '') }}" />
                                            @error('hi_name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pn_name"><strong>Name (Punjabi)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="pn_name" id="pn_name" class="form-control" required value="{{ old('pn_name', $vendor->pn_name ?? '') }}" />
                                            @error('pn_name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="shop_name"><strong>Shop Name (English)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="shop_name" id="shop_name" class="form-control" required value="{{ old('shop_name', $vendor->shop_name ?? '') }}" />
                                            @error('shop_name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="shop_hi_name"><strong>Shop Name (Hindi)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="shop_hi_name" id="shop_hi_name" class="form-control" required value="{{ old('shop_hi_name', $vendor->shop_hi_name ?? '') }}" />
                                            @error('shop_hi_name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="shop_pn_name"><strong>Shop Name (Punjabi)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="shop_pn_name" id="shop_pn_name" class="form-control" required value="{{ old('shop_pn_name', $vendor->shop_pn_name ?? '') }}" />
                                            @error('shop_pn_name') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="address"><strong>Address (English)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="address" id="address" class="form-control" required value="{{ old('address', $vendor->address ?? '') }}" />
                                            @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="hi_address"><strong>Address (Hindi)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="hi_address" id="hi_address" class="form-control" required value="{{ old('hi_address', $vendor->hi_address ?? '') }}" />
                                            @error('hi_address') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pn_address"><strong>Address (Punjabi)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="pn_address" id="pn_address" class="form-control" required value="{{ old('pn_address', $vendor->pn_address ?? '') }}" />
                                            @error('pn_address') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="district"><strong>District (English)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="district" id="district" class="form-control" required value="{{ old('district', $vendor->district ?? '') }}" />
                                            @error('district') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="hi_district"><strong>District (Hindi)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="hi_district" id="hi_district" class="form-control" required value="{{ old('hi_district', $vendor->hi_district ?? '') }}" />
                                            @error('hi_district') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pn_district"><strong>District (Punjabi)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="pn_district" id="pn_district" class="form-control" required value="{{ old('pn_district', $vendor->pn_district ?? '') }}" />
                                            @error('pn_district') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="state"><strong>State</strong> <span style="color:red;">*</span></label>
                                            <select class="form-control" name="state" id="states" required>
                                                <option value="">--- Select State ---</option>
                                                @foreach($state_data as $state)
                                                    <option value="{{ $state->id }}" {{ $state->id == ($vendor->state ?? '') ? 'selected' : '' }}>{{ $state->state_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('state') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="city"><strong>City (English)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="city" id="city" class="form-control" required value="{{ old('city', $vendor->city ?? '') }}" />
                                            @error('city') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="hi_city"><strong>City (Hindi)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="hi_city" id="hi_city" class="form-control" required value="{{ old('hi_city', $vendor->hi_city ?? '') }}" />
                                            @error('hi_city') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pn_city"><strong>City (Punjabi)</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="pn_city" id="pn_city" class="form-control" required value="{{ old('pn_city', $vendor->pn_city ?? '') }}" />
                                            @error('pn_city') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pincode"><strong>Pin Code</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="pincode" id="pincode" class="form-control" required maxlength="6" minlength="6" onkeypress="return isNumberKey(event)" value="{{ old('pincode', $vendor->pincode ?? '') }}" />
                                            @error('pincode') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gst_no"><strong>GST No (optional)</strong></label>
                                            <input type="text" name="gst_no" id="gst_no" class="form-control" value="{{ old('gst_no', $vendor->gst_no ?? '') }}" />
                                            @error('gst_no') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image"><strong>Shop/Vendor Image</strong></label>
                                            <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png" />
                                            @if($vendor->image ?? false)
                                                <img src="{{ asset($vendor->image) }}" height="50" width="100" alt="Current Image" style="margin-top: 10px;">
                                            @else
                                                <span style="margin-top: 10px; display: block;">Sorry No Image Found</span>
                                            @endif
                                            @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aadhar_no"><strong>Aadhar Number</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="aadhar_no" id="aadhar_no" class="form-control" required value="{{ old('aadhar_no', $vendor->aadhar_no ?? '') }}" />
                                            @error('aadhar_no') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pan_number"><strong>Pan Number</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="pan_number" id="pan_number" class="form-control" required value="{{ old('pan_number', $vendor->pan_number ?? '') }}" />
                                            @error('pan_number') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone"><strong>Phone Number</strong> <span style="color:red;">*</span></label>
                                            <input type="text" name="phone" id="phone" class="form-control" required maxlength="10" minlength="10" readonly onkeypress="return isNumberKey(event)" value="{{ old('phone', $vendor->phone ?? '') }}" />
                                            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email"><strong>Email</strong> <span style="color:red;">*</span></label>
                                            <input type="email" name="email" id="email" class="form-control" required value="{{ old('email', $vendor->email ?? '') }}" />
                                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
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

