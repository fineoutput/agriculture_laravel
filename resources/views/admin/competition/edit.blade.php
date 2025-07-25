@extends('admin.base_template')

@section('main')

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
                                    <option value="{{ $state->id }}" {{ $competition->state_id == $state->id ? 'selected' : '' }}>
                                        {{ $state->state_name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><strong>City</strong> <span style="color:red;">*</span></td>
                        <td>
                            <select name="city[]" id="city" class="form-control" multiple required>
                                {{-- Cities will be populated by JS --}}
                            </select>
                        </td>
                    </tr>

<tr>

@php
    $selectedJudges = isset($competition->judge) ? explode(',', $competition->judge) : [];
@endphp

<td>
    <label class="form-label" style="margin-left: 10px" for="power">Select Judge (Doctor)</label>
    <div id="output"></div>
    <select data-placeholder="" name="judge[]" multiple class="chosen-select">
        @foreach($doctors as $doctor)
            <option value="{{ $doctor->id }}" 
                @if(in_array($doctor->id, $selectedJudges)) selected @endif>
                {{ $doctor->name }}
            </option>
        @endforeach
    </select>
    @error('judge')
        <div style="color:red;">{{ $message }}</div>
    @enderror
</td>

</tr>
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
    
    <script>
    $(".form-control[multiple]").chosen({width: "100%"});
</script>
 <script>
    // Get selected city IDs from competition model
    let selectedCities = {!! json_encode(explode(',', $competition->city)) !!};
</script>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
    $(document).ready(function () {
        function loadCities(state_id, preselect = true) {
            if (state_id) {
                $.ajax({
                    url: "/competition-cities/" + state_id,
                    method: "GET",
                    success: function (response) {
                        let citySelect = $("#city");
                        citySelect.empty(); // clear old cities

                        $.each(response.cities, function (index, city) {
                            // Mark city as selected if it exists in selectedCities
                            let isSelected = selectedCities.includes(city.id.toString()) ? 'selected' : '';
                            citySelect.append(
                                '<option value="' + city.id + '" ' + isSelected + '>' + city.city_name + '</option>'
                            );
                        });

                        citySelect.trigger("chosen:updated"); // if using Chosen
                    },
                    error: function () {
                        alert("Error loading cities.");
                    }
                });
            } else {
                $("#city").empty().append('<option value="">-- Select City --</option>').trigger("chosen:updated");
            }
        }

        // Initial load (on page load if state is pre-selected)
        let initialStateId = $("#state").val();
        if (initialStateId) {
            loadCities(initialStateId);
        }

        // On state change
        $("#state").on("change", function () {
            selectedCities = []; // Clear selected cities on state change
            loadCities($(this).val(), false);
        });
    });
</script>
@endsection