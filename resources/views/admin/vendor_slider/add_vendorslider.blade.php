@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Add New Vendor Slider</h1>
        <ol class="breadcrumb">
            {{-- <li><a href="{{ route('admin.home') }}"><i class="fa fa-dashboard"></i> Home</a></li> --}}
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Add New Vendor Slider</h3>
                    </div>
                    @if (session('smessage'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-check"></i> Alert!</h4>
                            {{ session('smessage') }}
                        </div>
                    @endif
                    @if (session('emessage'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                            {{ session('emessage') }}
                        </div>
                    @endif
                    <div class="panel-body">
                        <div class="col-lg-10">
                            <form action="{{ route('admin.vendorslider.add_data', base64_encode(1)) }}" method="POST" id="slide_frm" enctype="multipart/form-data">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <tr>
                                            <td><strong>Images</strong> <span style="color:red;">*</span></td>
                                            <td>
                                                <input type="file" name="image[]" class="form-control" multiple accept="image/jpeg,image/png" required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <input type="submit" class="btn btn-success" value="Save">
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
@endsection