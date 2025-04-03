@extends('admin.base_template')
@section('main')
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title"> Update Settings  </h4>
                    <ol class="breadcrumb" style="display:none">
                        <!-- <li class="breadcrumb-item"><a href="javascript:void(0);">CMS</a></li> -->
                        <li class="breadcrumb-item"><a href="javascript:void(0);"> Update Settings </a></li>
                        <li class="breadcrumb-item active">Update Settings</li>
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
                            <h4 class="mt-0 header-title">Update Settings</h4>
                            <hr style="margin-bottom: 50px;background-color: darkgrey;">
                            <form action="{{ route('update_settings_process', ['id' => $data->id]) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                     
                                        <div class="form-floating">
                                            <input type="text" class="form-control" type="text"  id="name" name="sitename" placeholder="Enter sitename" value="{{ $data->sitename }}"   required>
                                            <label for="name">Enter sitename &nbsp;<span style="color:red;">*</span></label>
                                        </div>
                                        @error('sitename')
                                        <div style="color:red">{{$message}}</div>
                                        @enderror
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" type="text" value="{{ $data->instagram_link }}" id="name" name="instagram_link" placeholder="Enter instagram link" required>
                                            <label for="instagram_link">Enter instagram link &nbsp;<span style="color:red;">*</span></label>
                                        </div>
                                        @error('instagram_link')
                                        <div style="color:red">{{$message}}</div>
                                        @enderror
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" type="text" value="{{ $data->facebook_link }}" id="name" name="facebook_link" placeholder="Enter facebook_link" required>
                                            <label for="facebook_link">Enter facebook link   &nbsp;<span style="color:red;">*</span></label>
                                        </div>
                                        @error('facebook_link')
                                        <div style="color:red">{{$message}}</div>
                                        @enderror
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" type="text" value="{{ $data->youtube_link }}" id="name" name="youtube_link" placeholder="Enter instagram link" required>
                                            <label for="youtube_link">Enter youtube link   &nbsp;<span style="color:red;">*</span></label>
                                        </div>
                                        @error('youtube_link')
                                        <div style="color:red">{{$message}}</div>
                                        @enderror
                                    </div>
                                 
                                    <div class="col-sm-4">
                                        <div class="form-floating">
                                            <input class="form-control" type="text" value="{{ $data->phone }}" id="phone" name="phone" placeholder="phone no. (Optional)" onkeypress="return isNumberKey(event)" maxlength="10" minlength="10">
                                            <label for="phone">Phone (optional)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <div class="form-floating">
                                            <input class="form-control" type="text" value="{{ $data->address }}" id="address" name="address" placeholder="address(Optional)">
                                            <label for="address">Address (optional)</label>
                                        </div>
                                    </div>
                                    
                                     
                                    
                                    <div class="form-group row">
                                        
                                        <div class="col-sm-4"><br>
                                            <label class="form-label" style="margin-left: 10px" for="power">Image</label>
                                            <div>
                                    <img id="slide_img_path" height="100" width="200" src="{{ asset($data->logo) }}">
                                    </div>
                                            <input class="form-control" style="margin-left: 10px" type="file" value=""   name="logo">
                                        </div>
                                         
                                    </div>
                                    <div class="form-group">
                                        <div class="w-100 text-center">
                                            <button type="submit" style="margin-top: 10px;" class="btn btn-danger"><i class="fa fa-user"></i> Submit</button>
                                        </div>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->
        </div>
        <!-- end page content-->
    </div> <!-- container-fluid -->
</div> <!-- content -->
@endsection