@extends('admin.base_template')

@section('main')
<div class="content">
  <div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
      <div class="col-sm-12">
        <div class="page-title-box">
          <h4 class="page-title">View Diseases</h4>
          <ol class="breadcrumb">
            {{-- <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li> --}}
            <li class="breadcrumb-item"><a href="{{ route('disease.index') }}">All Diseases</a></li>
            <li class="breadcrumb-item active">View Diseases</li>
          </ol>
        </div>
      </div>
    </div>

    <!-- Page Content -->
    <div class="page-content-wrapper">
      <div class="row">
        <div class="col-12">
          <div class="card m-b-20">
            <div class="card-body">

              <!-- Success & Error Messages -->
              @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
              </div>
              @endif

              @if (session('error'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
              </div>
              @endif

              <!-- Header -->
              <div class="row mb-3">
                <div class="col-md-10">
                  <h4 class="mt-0 header-title">Disease List</h4>
                </div>
                <div class="col-md-2 text-right">
                  <a href="{{ route('disease.create') }}" class="btn btn-info">Add Disease</a>
                </div>
              </div>

              <!-- Table -->
              <div class="table-responsive">
                <table id="diseaseTable" class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Image</th>
                      <th>Title</th>
                      <th>Description</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php $i = 1; @endphp
                    @foreach($diseases as $disease)
                    <tr>
                      <td>{{ $i++ }}</td>
                      <td>
                        @if($disease->image1)
                          <img src="{{ asset($disease->image1) }}" width="100" height="50" alt="Disease Image">
                        @else
                          <span class="text-danger">No Image</span>
                        @endif
                      </td>
                      <td>{{ $disease->title }}</td>
                      <td>{{ Str::limit($disease->content, 100) }}</td>
                      <td>
                        @if($disease->is_active)
                          <span class="badge badge-success">Active</span>
                        @else
                          <span class="badge badge-warning">Inactive</span>
                        @endif
                      </td>
                      <td>
                        <div class="btn-group">
                          <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
                            Actions
                          </button>
                          <div class="dropdown-menu">
                            @if($disease->is_active)
                              <a class="dropdown-item" href="{{ route('disease.toggleStatus', $disease->id) }}">Deactivate</a>
                            @else
                              <a class="dropdown-item" href="{{ route('disease.toggleStatus', $disease->id)}}">Activate</a>
                            @endif
                            <a class="dropdown-item" href="{{ route('disease.edit', base64_encode($disease->id)) }}">Edit</a>
                            <a class="dropdown-item" href="{{ route('disease.delete', $disease->id) }}">Delete</a>

                          </div>
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

            </div> <!-- card-body -->
          </div> <!-- card -->
        </div> <!-- col -->
      </div> <!-- row -->
    </div> <!-- page-content-wrapper -->
  </div> <!-- container-fluid -->
</div> <!-- content -->

<!-- Delete Confirmation Modal (optional but useful) -->
<div id="deleteModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this disease?
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger btn-sm">Yes</button>
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">No</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('#diseaseTable').DataTable();

    // Handle delete action
    $('.delete-confirm').on('click', function() {
      var id = $(this).data('id');
      var url = "{{ url('admin/disease/delete') }}/" + id;
      $('#deleteForm').attr('action', url);
      $('#deleteModal').modal('show');
    });
  });
</script>
@endpush
