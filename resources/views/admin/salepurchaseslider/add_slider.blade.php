@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Add Sale Purchase Slider</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        @if (session('emessage'))
                            <div class="alert alert-danger">{{ session('emessage') }}</div>
                        @endif
                        <form action="{{ route('admin.salepurchaseslider.add_data', [base64_encode(1)]) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Images</label>
                                <input type="file" name="image[]" class="form-control" accept="image/jpeg,image/png" multiple>
                            </div>
                            <div class="form-group">
                                <label>Equipment Images</label>
                                <input type="file" name="eq_image[]" class="form-control" accept="image/jpeg,image/png" multiple>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection