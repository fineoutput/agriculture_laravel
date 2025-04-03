@extends('admin.base_template')
@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">View Settings</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                        <li class="breadcrumb-item active">View Settings</li>
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
                            <!-- show success and error messages -->
                            @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                            </div>
                            @endif
                            @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                            </div>
                            @endif
                            <!-- End show success and error messages -->
                            <div class="row">
                                <div class="col-md-10">
                                    <h4 class="mt-0 header-title">View Settings</h4>
                                </div>
                                <div class="col-md-2"> <a class="btn btn-info cticket" href="{{route('add_settings')}}" role="button" style="margin-left: 20px;"> Add settings</a></div>
                            </div>
                            <hr style="margin-bottom: 50px;background-color: darkgrey;">
                            <div class="table-rep-plugin">
                                <div class="table-responsive b-0" data-pattern="priority-columns">
                                    <table id="userTable" class="table  table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th data-priority="1">Sitename</th>
                                                <th data-priority="3">Instagram link</th>
                                                <th data-priority="1">Facebook link</th>
                                                <th data-priority="3">Youtube link</th>
                                                <th data-priority="3">Phone</th>
                                                <th data-priority="6">Address</th>
                                                <th data-priority="6">Logo</th>
                                                <th data-priority="6">Added by</th>
                                                <th data-priority="6">Action</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(!empty($settingsdetails))
                                            @php $a = 0; @endphp
                                            @foreach($settingsdetails as $item)
                                            @php $a++; @endphp
                                            <tr>
                                                <th>{{$a}}</th>
                                                <td>{{ $item->sitename }}</td>
                                                <td>{{ $item->instagram_link }}</td>
                                                <td>{{ $item->facebook_link }}</td>
                                                <td>{{ $item->youtube_link }}</td>
                                                <td>{{ $item->phone }}</td>
                                                <td>{{ $item->address }}</td>
                                                @if($item->logo != "" && $item->logo != null)
                                                <td>
                                                    <img id="slide_img_path" height="100" width="200" src="{{ asset($item->logo) }}">
                                                </td>
                                                @else
                                                <td>Sorry, No Image!</td>
                                                @endif
                                                <td>{{ $item->added }}</td>
                                                <td>
                                                    <div class="btn-group" id="btns{{ $a }}">
                                                        <a href="{{ route('update_settings', ['id' => $item->id]) }}" class="edit-logo" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit"></i></a>
                                                    </div>
                                                    <div style="display:none" id="editBox{{ $a }}">
                                                        <!-- Edit form can be added here -->
                                                    </div>

                                                    <div class="btn-group" id="btns{{ $a }}">
                                                        <a href="javascript:();" class="dCnf" mydata="<?php echo $a ?>" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash danger-icon"></i></a>
                                                    </div>
                                                    <div style="display:none" id="cnfbox<?php echo $a ?>">
                                                        <p> Are you sure delete this </p>
                                                        <a href="{{route('deletesetting',base64_encode($item->id))}}" class="btn btn-danger">Yes</a>
                                                        <a href="javascript:();" class="cans btn btn-default" mydatas="<?php echo $a ?>">No</a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endif
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->
        </div>
        <!-- end page content-->
    </div> <!-- container-fluid -->
</div> <!-- content -->

@endsection