@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Update Sale Purchase Slider</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        @if (session('emessage'))
                            <div class="alert alert-danger">{{ session('emessage') }}</div>
                        @endif
                        <form action="{{ route('admin.salepurchaseslider.add_data', [base64_encode(2), $id]) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Current Images</label>
                                @if($slider->image)
                                    @php $images = json_decode($slider->image, true); @endphp
                                    @if(is_array($images) && !empty($images))
                                        @foreach($images as $image)
                                            <img src="{{ asset($image) }}" height="50" width="100" alt="Slider Image">
                                        @endforeach
                                    @else
                                        <img src="{{ asset($slider->image) }}" height="50" width="100" alt="Slider Image">
                                    @endif
                                @endif
                                <label>Upload New Images</label>
                                <input type="file" name="image[]" class="form-control" accept="image/jpeg,image/png" multiple>
                            </div>
                            <div class="form-group">
                                <label>Current Equipment Images</label>
                                @if($slider->eq_image)
                                    @php $eqImages = json_decode($slider->eq_image, true); @endphp
                                    @if(is_array($eqImages) && !empty($eqImages))
                                        @foreach($eqImages as $eqImage)
                                            <img src="{{ asset($eqImage) }}" height="50" width="100" alt="Equipment Image">
                                        @endforeach
                                    @else
                                        <img src="{{ asset($slider->eq_image) }}" height="50" width="100" alt="Equipment Image">
                                    @endif
                                @endif
                                <label>Upload New Equipment Images</label>
                                <input type="file" name="eq_image[]" class="form-control" accept="image/jpeg,image/png" multiple>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection