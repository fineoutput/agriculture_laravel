@extends('admin.base_template')
@section('main')
<!-- Start content -->
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="page-title-box">
          <h4 class="page-title">Farmer Sliders</h4>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Sliders</a></li>
            <li class="breadcrumb-item active">View Farmer Sliders</li>
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
              @if (session('success'))
                <div class="alert alert-success" role="alert">
                  {{ session('success') }}
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              @endif
              @if (session('error'))
                <div class="alert alert-danger" role="alert">
                  {{ session('error') }}
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              @endif
              <!-- End Messages -->

              <div class="row">
                <div class="col-md-10">
                  <h4 class="mt-0 header-title">Farmer Slider List</h4>
                </div>
                <div class="col-md-2">
                  <a href="{{ route('add_farmer_slider') }}" class="btn btn-info">Add New</a>
                </div>
              </div>
              <hr style="margin-bottom: 50px; background-color: darkgrey;">

              <div class="table-rep-plugin">
                <div class="table-responsive b-0" data-pattern="priority-columns">
                  <table id="sliderTable" class="table table-striped">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php $count = 1; @endphp
                      @foreach($sliders as $slider)
                        <tr>
                          <td>{{ $count++ }}</td>
                          <td>
                            <img src="{{ asset($slider->image) }}" alt="Slider Image" style="height: 100px;">
                          </td>
                          <td>
                            @if($slider->is_active)
                              <span class="badge badge-success">Active</span>
                            @else
                              <span class="badge badge-danger">Inactive</span>
                            @endif
                          </td>
                          <td>
                            @if($slider->id)
                                <a href="{{ route('edit_farmer_slider', ['id' => $slider->id]) }}" class="btn btn-warning btn-sm">Edit</a>
                                <a href="{{ route('delete_farmer_slider', ['id' => $slider->id]) }}" class="btn btn-danger btn-sm">Delete</a>
                                <a href="{{ route('toggle_farmer_slider_status', ['id' => $slider->id]) }}" class="btn btn-secondary btn-sm">Toggle Status</a>
                            @else
                                <span class="text-danger">Invalid Slider ID</span>
                            @endif
                        </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>

            </div> <!-- card-body -->
          </div> <!-- card -->
        </div> <!-- end col -->
      </div> <!-- end row -->
    </div> <!-- end page-content-wrapper -->
  </div> <!-- container-fluid -->
</div> <!-- content -->

@endsection
