
@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Send Notification</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Dashboard</a></li> --}}
                        <li class="breadcrumb-item"><a href="{{ route('admin.pushnotifications.view') }}">View Push Notifications</a></li>
                        <li class="breadcrumb-item active">Send Notification</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="page-content-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-bell fa-fw"></i> Send New Push Notification</h3>
                        </div>
                        <div class="card-body">
                            @if (Auth::guard('admin')->user()->position == 'Manager')
                                <div class="alert alert-danger">
                                    <h4><i class="icon fa fa-ban"></i> Access Denied!</h4>
                                    Managers cannot send push notifications.
                                </div>
                            @else
                                @if (session('smessage'))
                                    <div class="alert alert-success alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                                        {{ session('smessage') }}
                                    </div>
                                @endif
                                @if (session('emessage'))
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                                        {{ session('emessage') }}
                                    </div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="col-lg-10">
                                    <form action="{{ route('admin.pushnotifications.add', ['t' => base64_encode(1)]) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group">
                                            <label for="App">Select App <span class="text-danger">*</span></label>
                                            <select name="App" id="App" class="form-control" required>
                                                <option value="" {{ old('App') ? '' : 'selected' }}>Select App</option>
                                                <option value="1" {{ old('App') == '1' ? 'selected' : '' }}>Vendor</option>
                                                <option value="2" {{ old('App') == '2' ? 'selected' : '' }}>Farmer</option>
                                            </select>
                                            @error('App')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="title">Title <span class="text-danger">*</span></label>
                                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="image">Image (jpg, jpeg, png, max 25MB) <span class="text-danger">*</span></label>
                                            <input type="file" name="image" id="image" class="form-control-file" accept="image/jpeg,image/png" required>
                                            @error('image')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="content">Content</label>
                                            <textarea name="content" id="content" class="form-control" rows="4">{{ old('content') }}</textarea>
                                            @error('content')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-success">Save</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-group {
        margin-bottom: 1.5rem;
    }
    .text-danger {
        font-size: 0.875rem;
    }
</style>
@endpush