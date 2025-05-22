@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Update Doctor</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li> --}}
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.doctor.view_doctor') }}">All Doctors</a></li> --}}
                        <li class="breadcrumb-item active">Update Doctor</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="page-content-wrapper">
            <div class="row">
                <div class="col-12">
                    <div class="card m-b-20">
                        <div class="card-body">
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

                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="mt-0 header-title"><i class="fa fa-user-md"></i> Update Doctor</h4>
                                </div>
                            </div>
                            <hr style="margin-bottom: 50px; background-color: darkgrey;">

                            <form action="{{ route('admin.doctor.update', ['y' => base64_encode($doctor->id)]) }}" method="POST" id="doctor_form" enctype="multipart/form-data">
                                @csrf
                                @method('POST')
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <tr>
                                            <td><strong>Name</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="name" class="form-control" placeholder="Enter name" required value="{{ old('name', $doctor->name) }}" />
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Name (Hindi)</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="hi_name" class="form-control" placeholder="Enter name in Hindi" required value="{{ old('hi_name', $doctor->hi_name) }}" />
                                                @error('hi_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Name (Punjabi)</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="pn_name" class="form-control" placeholder="Enter name in Punjabi" required value="{{ old('pn_name', $doctor->pn_name) }}" />
                                                @error('pn_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="email" name="email" class="form-control" placeholder="Enter email" required value="{{ old('email', $doctor->email) }}" />
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Doctor/Clinic Image</strong></td>
                                            <td>
                                                <input type="file" name="image" class="form-control" accept="image/*" />
                                                @error('image')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                @if($doctor->image)
                                                    <img src="{{ asset($doctor->image) }}" height="50" width="100" alt="Doctor Image">
                                                @else
                                                    Sorry No image Found
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Type</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <div class="form-check">
                                                    <input type="radio" id="Vet" name="type" value="Vet" class="form-check-input" required {{ $doctor->type == 'Vet' ? 'checked' : '' }}>
                                                    <label for="Vet" class="form-check-label">Vet</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" id="Assistant" name="type" value="Assistant" class="form-check-input" {{ $doctor->type == 'Assistant' ? 'checked' : '' }}>
                                                    <label for="Assistant" class="form-check-label">Livestock Assistant</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" id="PrivatePractitioner" name="type" value="Private Practitioner" class="form-check-input" {{ $doctor->type == 'Private Practitioner' ? 'checked' : '' }}>
                                                    <label for="PrivatePractitioner" class="form-check-label">Private Practitioner</label>
                                                </div>
                                                @error('type')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
    <td><strong>Expertise Category</strong> <span style="color:red;">*</span></td>
    <td>
        <select class="selectpicker form-control" multiple data-live-search="true" name="expert_category[]" required>
            @if(isset($expert_categories) && $expert_categories->isNotEmpty())
                @foreach($expert_categories as $category)
                    <option value="{{ $category->id }}"
                        {{ in_array($category->id, old('expert_category', $doctor->expert_category ?? [])) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            @else
                <option value="">No expertise categories available</option>
            @endif
        </select>
        @error('expert_category')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </td>
</tr>
                                        <tr>
                                            <td><strong>Degree</strong></td>
                                            <td>
                                                <input type="text" name="degree" class="form-control" placeholder="Enter degree" value="{{ old('degree', $doctor->degree) }}" />
                                                @error('degree')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Experience</strong></td>
                                            <td>
                                                <input type="text" name="experience" class="form-control" placeholder="Enter experience" value="{{ old('experience', $doctor->experience) }}" />
                                                @error('experience')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>District (English)</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="district" class="form-control" placeholder="Enter district" required value="{{ old('district', $doctor->district) }}" />
                                                @error('district')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>District (Hindi)</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="hi_district" class="form-control" placeholder="Enter district in Hindi" required value="{{ old('hi_district', $doctor->hi_district) }}" />
                                                @error('hi_district')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>District (Punjabi)</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="pn_district" class="form-control" placeholder="Enter district in Punjabi" required value="{{ old('pn_district', $doctor->pn_district) }}" />
                                                @error('pn_district')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>State</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <select class="form-control" name="state" id="states" required>
                                                    <option value="">--- Select State ---</option>
                                                    @foreach($states as $state)
                                                        <option value="{{ $state->id }}" {{ old('state', $doctor->state) == $state->id ? 'selected' : '' }}>{{ $state->state_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('state')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>City (English)</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="city" class="form-control" placeholder="Enter city" required value="{{ old('city', $doctor->city) }}" />
                                                @error('city')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>City (Hindi)</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="hi_city" class="form-control" placeholder="Enter city in Hindi" required value="{{ old('hi_city', $doctor->hi_city) }}" />
                                                @error('hi_city')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>City (Punjabi)</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="pn_city" class="form-control" placeholder="Enter city in Punjabi" required value="{{ old('pn_city', $doctor->pn_city) }}" />
                                                @error('pn_city')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pincode</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="pincode" class="form-control" placeholder="Enter pincode" required value="{{ old('pincode', $doctor->pincode) }}" />
                                                @error('pincode')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Aadhar Number</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="aadhar_no" class="form-control" placeholder="Enter Aadhar number" required value="{{ old('aadhar_no', $doctor->aadhar_no) }}" />
                                                @error('aadhar_no')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone Number</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="phone" class="form-control" placeholder="Enter phone number" readonly maxlength="10" minlength="10" onkeypress="return isNumberKey(event)" required value="{{ old('phone', $doctor->phone) }}" />
                                                @error('phone')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <button type="submit" class="btn btn-success">Save</button>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
<style>
    label {
        margin: 5px;
    }
    .form-check {
        margin-bottom: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
<script>
    $(document).ready(function() {
        $('.selectpicker').selectpicker();
    });

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }
</script>
@endpush