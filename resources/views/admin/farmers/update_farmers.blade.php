@extends('admin.base_template')

@section('main')

<style>
    label {
        margin: 5px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Update Farmer</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('admin.farmers.view') }}"><i class="fa fa-users"></i> All Farmers</a></li>
            <li class="active">Update Farmer</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-money fa-fw"></i> Update Farmer</h3>
                    </div>

                    <!-- Flash Messages -->
                    @if (session('smessage'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-check"></i> Alert!</h4>
                            {{ session('smessage') }}
                        </div>
                    @endif
                    @if (session('emessage'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                            {{ session('emessage') }}
                        </div>
                    @endif

                    <div class="panel-body">
                        <div class="col-lg-10">
                            <form action="{{ route('admin.farmers.add_data', [base64_encode(2), base64_encode($farmers->id)]) }}" method="POST" id="slide_frm" enctype="multipart/form-data">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <tr>
                                            <td><strong>Name</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="name" class="form-control" placeholder="Enter name" required value="{{ old('name', $farmers->name) }}" />
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Village</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="village" class="form-control" placeholder="Enter village" required value="{{ old('village', $farmers->village) }}" />
                                                @error('village')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>District</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="district" class="form-control" placeholder="Enter district" required value="{{ old('district', $farmers->district) }}" />
                                                @error('district')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>State</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <select name="state" class="form-control" id="states" required>
                                                    <option value="">-----Select State-----</option>
                                                    @foreach ($state_data as $state)
                                                        <option value="{{ $state->id }}" {{ old('state', $farmers->state) == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('state')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>City</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <select name="city" class="form-control" id="cities" required>
                                                    <option value="">-----Select City-----</option>
                                                    @foreach ($city_data as $city)
                                                        <option value="{{ $city->id }}" {{ old('city', $farmers->city) == $city->id ? 'selected' : '' }}>{{ $city->city_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('city')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pincode</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="Pincode" class="form-control" placeholder="Enter pincode" maxlength="6" minlength="6" required value="{{ old('Pincode', $farmers->Pincode) }}" onkeypress="return isNumberKey(event)" />
                                                @error('Pincode')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone Number</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="text" name="phone_number" class="form-control" placeholder="Enter phone number" maxlength="10" minlength="10" required value="{{ old('phone_number', $farmers->phone_number) }}" onkeypress="return isNumberKey(event)" />
                                                @error('phone_number')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <input type="submit" class="btn btn-success" value="Save">
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
    </section>
</div>

@push('scripts')
<script>
    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    $(document).ready(function() {
        $("#states").change(function() {
            var stateId = $(this).val();
            if (!stateId) {
                $('#cities').html('<option value="">-----Select City-----</option>');
                return false;
            }

            $.ajax({
                url: '{{ route("admin.farmers.get_cities", ":state_id") }}'.replace(':state_id', stateId),
                type: 'GET',
                success: function(response) {
                    if (response !== 'NA') {
                        var options = '<option value="">-----Select City-----</option>';
                        $.each(response, function(i, city) {
                            options += '<option value="' + city.cities_id + '">' + city.city_name + '</option>';
                        });
                        $('#cities').html(options);
                    } else {
                        alert('No cities found');
                        $('#cities').html('<option value="">-----Select City-----</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    alert('Error fetching cities');
                    $('#cities').html('<option value="">-----Select City-----</option>');
                }
            });
        });
    });
</script>
@endpush
@endsection