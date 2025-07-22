@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
  <section class="content-header">
    <h1>Add New Competition</h1>
    <ol class="breadcrumb">
      <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Add New Competition</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Add New Competition</h3>
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
              <form action="{{ route('admin.competition.store') }}" method="POST" id="competition_form">
                @csrf
                <div class="table-responsive">
                  <table class="table table-hover">
                    <tr>
                      <td><strong>Start Date</strong> <span style="color:red;">*</span></td>
                      <td><input type="date" name="start_date" class="form-control" required></td>
                    </tr>
                    <tr>
                      <td><strong>End Date</strong> <span style="color:red;">*</span></td>
                      <td><input type="date" name="end_date" class="form-control" required></td>
                    </tr>
                    <tr>
                      <td><strong>Competition Date</strong> <span style="color:red;">*</span></td>
                      <td><input type="date" name="competition_date" class="form-control" required></td>
                    </tr>
                    <tr>
                      <td><strong>State</strong> <span style="color:red;">*</span></td>
                      <td>
                        <select name="state" id="state" class="form-control" required>
                          <option value="">-- Select State --</option>
                          @foreach($states as $state)
                            <option value="{{ $state->id }}">{{ $state->state_name }}</option>
                          @endforeach
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td><strong>City</strong> <span style="color:red;">*</span></td>
                      <td>
                        <select name="city" id="city" class="form-control" required>
                          <option value="">-- Select City --</option>
                          
                        </select>
                      </td>
                    </tr>
  <tr>
         <div id="slot-wrapper">
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
</div>

  </tr>

                    <tr>
                      <td><strong>Entry Fees (₹)</strong> <span style="color:red;">*</span></td>
                      <td><input type="number" step="0.01" name="entry_fees" class="form-control" required></td>
                    </tr>
                    <tr>
                      <td colspan="2"><input type="submit" class="btn btn-success" value="Save"></td>
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


<script>
    const slotOptions = ['Morning', 'Afternoon', 'Evening', 'Night'];

    $(document).on('click', '.add-more-slot', function () {
        // Get already selected slots
        const selectedSlots = [];
        $('.time-slot-select').each(function () {
            const val = $(this).val();
            if (val) selectedSlots.push(val);
        });

        // Filter options
        const availableSlots = slotOptions.filter(slot => !selectedSlots.includes(slot));

        if (availableSlots.length === 0) {
            alert('All time slots already added.');
            return;
        }

        // Create new block
        const newBlock = $('<div class="slot-block form-row align-items-end mt-2">');

        let selectHtml = '<select name="time_slot[]" class="form-control time-slot-select" required>';
        selectHtml += '<option value="">Select Slot</option>';
        availableSlots.forEach(slot => {
            selectHtml += '<option value="' + slot + '">' + slot + '</option>';
        });
        selectHtml += '</select>';

        newBlock.append(`
            <div class="form-group col-md-5">
                <label>Time Slot</label>
                ${selectHtml}
            </div>
            <div class="form-group col-md-5">
                <label>Slot Time</label>
                <input type="time" name="slot_time[]" class="form-control" required>
            </div>
            <div class="form-group col-md-2">
                <button type="button" class="btn btn-danger remove-slot">Remove</button>
            </div>
        `);

        $('#slot-wrapper').append(newBlock);
    });

    // Remove block
    $(document).on('click', '.remove-slot', function () {
        $(this).closest('.slot-block').remove();
    });
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('#state').on('change', function () {
        var state_id = $(this).val();
        console.log(state_id,'asgdjhgashdjkhaskhdhsajkdhsajhdhsajhj');

        if (state_id !== '') {
            $.ajax({
                url: '/competition-cities/' + state_id, // <- no route name, direct URL
                method: 'GET',
                success: function (response) {
                    var citySelect = $('#city');
                    citySelect.empty(); // clear previous cities
                    citySelect.append('<option value="">-- Select City --</option>');

                    $.each(response.cities, function (key, city) {
                        citySelect.append('<option value="' + city.id + '">' + city.city_name + '</option>');
                    });
                },
                error: function () {
                    alert('City load karne me error aaya.');
                }
            });
        } else {
            $('#city').html('<option value="">-- Select City --</option>');
        }
    });
});
</script>
@push('scripts')

@endpush
@endsection
