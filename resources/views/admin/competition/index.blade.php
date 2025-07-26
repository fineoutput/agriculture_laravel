@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
  <section class="content-header">
    <h1>Competition Entries</h1>
    <ol class="breadcrumb">
      <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="{{ route('admin.competition.index') }}"><i class="fa fa-dashboard"></i> Competition Entries</a></li>
      <li class="active">Competition Entries</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-lg-12">
        <a class="btn btn-info cticket" href="{{ route('admin.competition.create') }}" role="button" style="margin-bottom:12px;">Add Competition</a>

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

        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Competition Entries</h3>
          </div>
          <div class="panel-body">
            <div class="box-body table-responsive no-padding">
              <table class="table table-bordered table-hover table-striped" id="userTable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Competition Date</th>
                    <th>State</th>
                    <th>City</th>
                    <th>Period</th>
                    <!-- <th>Slot Time</th> -->
                    <th>Judge</th>
                    <th>Entry Fees</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($form_data as $index => $row)
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td>{{ $row->start_date ?? '' }}</td>
                      <td>{{ $row->end_date ?? '' }}</td>
                      <td>{{ $row->competition_date ?? '' }}</td>
                      <td>{{ $row->state_name ?? 'N/A' }}</td>
                      <td>{{ implode(', ', $row->city_names) }}</td>
                      <td>{{ $row->time_slot ?? '' }}</td>
                      <!-- <td>{{ $row->slot_time ?? '' }}</td> -->
                      <!-- <td>{{ $row->doctor->name ?? '' }}</td> -->
                       
                        @php
                              $judges = explode(',', $row->judge);  // Exploding the comma-separated meal plans
                          @endphp
<td>
                           @foreach ($judges as $propertyId)
                        @php
                           $property = \App\Models\Doctor::find($propertyId);  
                        @endphp
                  
                        @if ($property)
                          
                          {{ $property->name }},

                          @else
                            Judge not found
                          @endif
                      @endforeach

</td>

                      <td>{{ $row->entry_fees ?? '' }}</td>
                      <td>
                        <a href="{{ route('admin.competition.edit', base64_encode($row->id)) }}" class="btn btn-primary btn-sm">Edit</a>
                        <a href="{{ route('admin.time-slot.edit', base64_encode($row->id)) }}" class="btn btn-primary btn-sm">Add Time Slot</a>
                        <a href="{{ route('admin.competition.rankings', $row->id) }}" class="btn btn-success btn-sm">View Rankings</a>
                        <form action="{{ route('admin.competition.destroy', base64_encode($row->id)) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<style>
  label {
    margin: 5px;
  }
</style>

@push('scripts')
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables/dataTables.bootstrap.js') }}"></script>
<script>
  $(document).ready(function() {
    $('#userTable').DataTable();
  });
</script>
@endpush
@endsection
