
@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Update Expert Doctor</h4>
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li> --}}
                        <li class="breadcrumb-item"><a href="{{ route('admin.doctor.accepted') }}">Accepted Doctors</a></li>
                        <li class="breadcrumb-item active">Update Expert Doctor</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="page-content-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-money fa-fw"></i> Update Expert Doctor</h3>
                        </div>
                        <div class="card-body">
                            @if (session('smessage'))
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <h4><i class="icon fa fa-check"></i> Success!</h4>
                                    {{ session('smessage') }}
                                </div>
                            @endif
                            @if (session('emessage'))
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
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
                                <form action="{{ route('admin.doctor.add_commission', ['idd' => $id]) }}" method="POST" id="commission_form">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <tr>
                                                <td><strong>Set Fees</strong> <span class="text-danger">*</span></td>
                                                <td>
                                                    <input type="number" name="fees" class="form-control" placeholder="Enter fees" required value="{{ old('fees', $doctor->fees) }}" min="0" step="0.01" />
                                                    @error('fees')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Set Commission (%)</strong> <span class="text-danger">*</span></td>
                                                <td>
                                                    <input type="number" name="set_commission" class="form-control" placeholder="Enter commission percentage" required value="{{ old('set_commission', $doctor->commission) }}" min="0" max="100" step="0.01" />
                                                    @error('set_commission')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <button type="submit" class="btn btn-success">Save</button>
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
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td, .table th {
        vertical-align: middle;
    }
    .text-danger {
        font-size: 0.875rem;
    }
</style>
@endpush