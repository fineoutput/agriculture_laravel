@extends('admin.base_template')

@section('main')

@php
    $isEdit = isset($competition);
    $timeSlots = [];

    if ($isEdit && !empty($competition->time_slot)) {
        $decoded = json_decode($competition->time_slot, true);
        if (is_array($decoded)) {
            $timeSlots = $decoded;
        }
    }
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
              <form action="{{ route('admin.time-slot.update', base64_encode($competition->id)) }}" method="POST" id="competition_form">
                @csrf
                <div class="table-responsive">
                  <table class="table table-hover">
                    
                    <div id="slot-wrapper">
    @if($isEdit && count($timeSlots))
        @foreach($timeSlots as $slot => $entries)
            @foreach($entries as $entry)
                <div class="slot-block form-row align-items-end mt-2">
                    <div class="form-group col-md-3">
                        <label>Time Slot</label>
                        <select name="time_slot[]" class="form-control time-slot-select" required>
                            <option value="">Select Slot</option>
                            @foreach(['Morning', 'Afternoon', 'Evening', 'Night'] as $option)
                                <option value="{{ $option }}" {{ $option === $slot ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-2">
                        <label>Start Time</label>
                        <input type="time" name="start_time[]" class="form-control" value="{{ $entry['start_time'] }}" required>
                    </div>

                    <div class="form-group col-md-2">
                        <label>End Time</label>
                        <input type="time" name="end_time[]" class="form-control" value="{{ $entry['end_time'] }}" required>
                    </div>

                    <div class="form-group col-md-3">
                        <label>Date</label>
                        <input type="date" name="slot_date[]" class="form-control" value="{{ $entry['date'] }}" required>
                    </div>

                    <div class="form-group col-md-2">
                        <button type="button" class="btn btn-danger remove-slot">Remove</button>
                    </div>
                </div>
            @endforeach
        @endforeach
    @else
        <div class="slot-block form-row align-items-end">
            <div class="form-group col-md-3">
                <label>Time Slot</label>
                <select name="time_slot[]" class="form-control time-slot-select" required>
                    <option value="">Select Slot</option>
                    @foreach(['Morning', 'Afternoon', 'Evening', 'Night'] as $slot)
                        <option value="{{ $slot }}">{{ $slot }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-2">
                <label>Start Time</label>
                <input type="time" name="start_time[]" class="form-control" required>
            </div>

            <div class="form-group col-md-2">
                <label>End Time</label>
                <input type="time" name="end_time[]" class="form-control" required>
            </div>

            <div class="form-group col-md-3">
                <label>Date</label>
                <input type="date" name="slot_date[]" class="form-control" required>
            </div>
        </div>
    @endif
</div>



                    <div class="form-group col-md-2">
                <button type="button" class="btn btn-success add-more-slot">Add More</button>
            </div>
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
    const slotOptions = ['Morning', 'Afternoon', 'Evening', 'Night'];

    $(document).on('click', '.add-more-slot', function () {
        const selectedSlots = [];
        $('.time-slot-select').each(function () {
            const val = $(this).val();
            if (val) selectedSlots.push(val);
        });

        const availableSlots = slotOptions.filter(slot => !selectedSlots.includes(slot));

        if (availableSlots.length === 0) {
            alert('All time slots already added.');
            return;
        }

       const newBlock = $('<div class="slot-block form-row align-items-end mt-2">');

newBlock.append(`
    <div class="form-group col-md-3">
        <label>Time Slot</label>
        <select name="time_slot[]" class="form-control time-slot-select" required>
            <option value="">Select Slot</option>
            ${slotOptions.map(slot => `<option value="${slot}">${slot}</option>`).join('')}
        </select>
    </div>
    <div class="form-group col-md-2">
        <label>Start Time</label>
        <input type="time" name="start_time[]" class="form-control" required>
    </div>
    <div class="form-group col-md-2">
        <label>End Time</label>
        <input type="time" name="end_time[]" class="form-control" required>
    </div>
    <div class="form-group col-md-3">
        <label>Date</label>
        <input type="date" name="slot_date[]" class="form-control" required>
    </div>
    <div class="form-group col-md-2">
        <button type="button" class="btn btn-danger remove-slot">Remove</button>
    </div>
`);

$('#slot-wrapper').append(newBlock);

    });

    // Remove slot block
    $(document).on('click', '.remove-slot', function () {
        $(this).closest('.slot-block').remove();
    });
</script>
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