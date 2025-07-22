@extends('admin.base_template')

@section('main')

@php
    $isEdit = isset($competition);
    $timeSlots = $isEdit && !empty($competition->time_slot) ? json_decode($competition->time_slot, true) : [];
@endphp
<div class="content-wrapper">
  <section class="content-header">
    <h1>Edit Competition</h1>
    <ol class="breadcrumb">
      <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Edit Competition</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Edit Competition</h3>
          </div>

          @if(session('message'))
            <div class="alert alert-success alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h4><i class="icon fa fa-check"></i> Alert!</h4>
              {{ session('message') }}
            </div>
          @endif

          @if(session('emessage'))
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h4><i class="icon fa fa-ban"></i> Alert!</h4>
              {{ session('emessage') }}
            </div>
          @endif

          <div class="panel-body">
            <div class="col-lg-10">
              <form action="{{ route('admin.competition.update', base64_encode($competition->id)) }}" method="POST" id="competition_form">
                @csrf
                <div class="table-responsive">
                  <table class="table table-hover">
                    <tr>
                      <td><strong>Start Date</strong> <span style="color:red;">*</span></td>
                      <td><input type="date" name="start_date" class="form-control" value="{{ $competition->start_date }}" required></td>
                    </tr>
                    <tr>
                      <td><strong>End Date</strong> <span style="color:red;">*</span></td>
                      <td><input type="date" name="end_date" class="form-control" value="{{ $competition->end_date }}" required></td>
                    </tr>
                    <tr>
                      <td><strong>Competition Date</strong> <span style="color:red;">*</span></td>
                      <td><input type="date" name="competition_date" class="form-control" value="{{ $competition->competition_date }}" required></td>
                    </tr>
                    <tr>
                      <td><strong>State</strong> <span style="color:red;">*</span></td>
                      <td>
                        <select name="state" id="state" class="form-control" required>
                          <option value="">-- Select State --</option>
                          @foreach($states as $state)
                            <option value="{{ $state->id }}" {{ $competition->state == $state->id ? 'selected' : '' }}>{{ $state->state_name }}</option>
                          @endforeach
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td><strong>City</strong> <span style="color:red;">*</span></td>
                      <td>
                        <select name="city" id="city" class="form-control" required>
                          <option value="">-- Select City --</option>
                          @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ $competition->city == $city->id ? 'selected' : '' }}>{{ $city->city_name }}</option>
                          @endforeach
                        </select>
                      </td>
                    </tr>
                    <div id="slot-wrapper">
    @if($isEdit && count($timeSlots))
        @foreach($timeSlots as $slot => $time)
            <div class="slot-block form-row align-items-end mt-2">
                <div class="form-group col-md-5">
                    <label>Time Slot</label>
                    <select name="time_slot[]" class="form-control time-slot-select" required>
                        <option value="">Select Slot</option>
                        @foreach(['Morning', 'Afternoon', 'Evening', 'Night'] as $option)
                            <option value="{{ $option }}" {{ $option == $slot ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-5">
                    <label>Slot Time</label>
                    <input type="time" name="slot_time[]" class="form-control" value="{{ $time }}" required>
                </div>
                <div class="form-group col-md-2">
                    <button type="button" class="btn btn-danger remove-slot">Remove</button>
                </div>
            </div>
        @endforeach
    @else
        <div class="slot-block form-row align-items-end">
            <div class="form-group col-md-5">
                <label>Time Slot</label>
                <select name="time_slot[]" class="form-control time-slot-select" required>
                    <option value="">Select Slot</option>
                    @foreach (['Morning', 'Afternoon', 'Evening', 'Night'] as $slot)
                        <option value="{{ $slot }}">{{ $slot }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-5">
                <label>Slot Time</label>
                <input type="time" name="slot_time[]" class="form-control" required>
            </div>

            <div class="form-group col-md-2">
                <button type="button" class="btn btn-success add-more-slot">Add More</button>
            </div>
        </div>
    @endif
</div>

                    <tr>
                      <td><strong>Entry Fees (₹)</strong> <span style="color:red;">*</span></td>
                      <td><input type="number" step="0.01" name="entry_fees" class="form-control" value="{{ $competition->entry_fees }}" required></td>
                    </tr>
                    <tr>
                      <td colspan="2"><input type="submit" class="btn btn-success" value="Update"></td>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('#state').on('change', function () {
        var state_id = $(this).val();

        if (state_id !== '') {
            $.ajax({
                url: '/competition-cities/' + state_id,
                method: 'GET',
                success: function (response) {
                    var citySelect = $('#city');
                    citySelect.empty();
                    citySelect.append('<option value="">-- Select City --</option>');
                    $.each(response.cities, function (key, city) {
                        citySelect.append('<option value="' + city.id + '">' + city.city_name + '</option>');
                    });
                },
                error: function () {
                    alert('Error loading cities.');
                }
            });
        } else {
            $('#city').html('<option value="">-- Select City --</option>');
        }
    });
});
</script>
@endsection