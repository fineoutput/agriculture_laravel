@extends('admin.base_template') @section('main')
<div class="content-wrapper">
  <section class="content-header">
    <h1>Add New Competition</h1>
    <ol class="breadcrumb">
      <li>
        <a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a>
      </li>
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
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
              &times;
            </button>
            <h4><i class="icon fa fa-check"></i> Alert!</h4>
            {{ session("message") }}
          </div>
          @endif @if(session('emessage'))
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
              &times;
            </button>
            <h4><i class="icon fa fa-ban"></i> Alert!</h4>
            {{ session("emessage") }}
          </div>
          @endif

          <div class="panel-body">
            <div class="col-lg-10">
              <form action="{{ route('admin.competition.store') }}" method="POST" id="competition_form">
                @csrf
                <div class="table-responsive">
                  <table class="table table-hover">
                    <tr>
                      <td>
                        <strong>Start Date</strong>
                        <span style="color: red">*</span>
                      </td>
                      <td>
                        <input type="date" name="start_date" class="form-control" required />
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <strong>End Date</strong>
                        <span style="color: red">*</span>
                      </td>
                      <td>
                        <input type="date" name="end_date" class="form-control" required />
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <strong>Competition Date</strong>
                        <span style="color: red">*</span>
                      </td>
                      <td>
                        <input type="date" name="competition_date" class="form-control" required />
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <strong>State</strong>
                        <span style="color: red">*</span>
                      </td>
                      <td>
                        <select name="state" id="state" class="form-control" required>
                          <option value="">
                            -- Select State --
                          </option>
                          @foreach($states as $state)
                          <option value="{{ $state->id }}">
                            {{ $state->state_name }}
                          </option>
                          @endforeach
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <strong>City</strong>
                        <span style="color: red">*</span>
                      </td>
                      <td>
                        <div id="output"></div>
                        <select name="city[]" id="city" class="form-control" multiple required>
                          <!-- <option value="">-- Select City --</option> -->
                      </select>
                      </td>
                    </tr>
                 
                    <tr>
                      <td>
                        <label class="form-label" style="margin-left: 10px" for="power">Select Judge
                          (Doctor)</label>
                        <div id="output"></div>
                        <select data-placeholder="" name="judge[]" multiple class="chosen-select">
                          @foreach($doctors as
                          $doctor)
                          <option value="{{ $doctor->id }}">
                            {{ $doctor->name }}
                          </option>
                          @endforeach
                        </select>
                        @error('judge')
                        <div style="color: red">
                          {{ $message }}
                        </div>
                        @enderror
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <strong>Entry Fees (₹)</strong>
                        <span style="color: red">*</span>
                      </td>
                      <td>
                        <input type="number" step="0.01" name="entry_fees" class="form-control" required />
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        <input type="submit" class="btn btn-success" value="Save" />
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Then Chosen -->
<link rel="stylesheet" href="https://harvesthq.github.io/chosen/chosen.css" />
<script src="https://harvesthq.github.io/chosen/chosen.jquery.js"></script>

<script>
  document.getElementById("output").innerHTML = location.search;
  $(".chosen-select").chosen();
</script>


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
     
      $("#city").select2({
          placeholder: "Select City",
          allowClear: true
      });
    </script>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
  <script>
  $(document).ready(function () {
      // $(".chosen-select, .chosen-city").chosen();

      $("#state").on("change", function () {
          var state_id = $(this).val();
          console.log("State changed:", state_id);

          if (state_id !== "") {
              $.ajax({
                  url: "/agriculture_laravel/public/competition-cities/" + state_id,
                  method: "GET",
                  success: function (response) {
                      console.log("Cities loaded:", response.cities);

                      $("#city").each(function () {
                          var citySelect = $(this);
                          console.log('nbhagvfuyasvhjfgfvasfasjhgvfhasfiu',response.cities);
                          citySelect.empty().append('<option value="">-- Select City --</option>');

                          $.each(response.cities, function (key, city) {
                              citySelect.append('<option value="' + city.id + '">' + city.city_name + '</option>');
                          });

                          citySelect.trigger("chosen:updated");
                      });
                  },
                  error: function () {
                      alert("City load karne me error aaya.");
                  }
              });
          } else {
              $(".chosen-city").html('<option value="">-- Select City --</option>').trigger("chosen:updated");
          }
      });
  });
  </script>




<script>
  const slotOptions = ["Morning", "Afternoon", "Evening", "Night"];

  $(document).on("click", ".add-more-slot", function () {
    // Get already selected slots
    const selectedSlots = [];
    $(".time-slot-select").each(function () {
      const val = $(this).val();
      if (val) selectedSlots.push(val);
    });

    // Filter options
    const availableSlots = slotOptions.filter(
      (slot) => !selectedSlots.includes(slot)
    );

    if (availableSlots.length === 0) {
      alert("All time slots already added.");
      return;
    }

    // Create new block
    const newBlock = $(
      '<div class="slot-block form-row align-items-end mt-2">'
    );

    let selectHtml =
      '<select name="time_slot[]" class="form-control time-slot-select" required>';
    selectHtml += '<option value="">Select Slot</option>';
    availableSlots.forEach((slot) => {
      selectHtml += '<option value="' + slot + '">' + slot + "</option>";
    });
    selectHtml += "</select>";

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

    $("#slot-wrapper").append(newBlock);
  });

  // Remove block
  $(document).on("click", ".remove-slot", function () {
    $(this).closest(".slot-block").remove();
  });
</script>

@push('scripts') @endpush @endsection