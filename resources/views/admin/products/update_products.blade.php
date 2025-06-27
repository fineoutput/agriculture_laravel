@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Update Product</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        @if (session('smessage'))
                            <div class="alert alert-danger">{{ session('smessage') }}</div>
                        @endif

                        <form action="{{ route('admin.products.add_data', [base64_encode(2), $id]) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Name (English)</label>
                                <input type="text" name="name_english" class="form-control" value="{{ $products->name_english }}" required>
                            </div>
                            <div class="form-group">
                                <label>Name (Hindi)</label>
                                <input type="text" name="name_hindi" class="form-control" value="{{ $products->name_hindi }}" required>
                            </div>
                            <div class="form-group">
                                <label>Name (Punjabi)</label>
                                <input type="text" name="name_punjabi" class="form-control" value="{{ $products->name_punjabi }}" required>
                            </div>
                            <div class="form-group">
                                <label>Name (Marathi)</label>
                                <input type="text" name="name_marathi" class="form-control" value="{{ $products->name_marathi }}" required>
                            </div>
                            <div class="form-group">
                                <label>Name (Gujrati)</label>
                                <input type="text" name="name_gujrati" class="form-control" value="{{ $products->name_gujrati }}" required>
                            </div>
                            <div class="form-group">
                                <label>Description (English)</label>
                                <textarea name="description_english" class="form-control" required>{{ $products->description_english }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Description (Hindi)</label>
                                <textarea name="description_hindi" class="form-control" required>{{ $products->description_hindi }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Description (Punjabi)</label>
                                <textarea name="description_punjabi" class="form-control" required>{{ $products->description_punjabi }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Description (Marathi)</label>
                                <textarea name="description_marathi" class="form-control" required>{{ $products->description_marathi }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Description (Gujrati)</label>
                                <textarea name="description_gujrati" class="form-control" required>{{ $products->description_gujrati }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Current Images</label>
                                @if($products->image)
                                    @foreach(json_decode($products->image) as $img)
                                        <img src="{{ asset($img) }}" height="50" width="100" alt="Product Image">
                                    @endforeach
                                @endif
                                <label>Upload New Images</label>
                                <input type="file" name="images[]" class="form-control" multiple>
                            </div>
                            <div class="form-group">
                                <label>Current Video</label>
                                @if($products->video)
                                    <video width="320" height="240" controls>
                                        <source src="{{ asset($products->video) }}" type="video/mp4">
                                    </video>
                                @endif
                                <label>Upload New Video</label>
                                <input type="file" name="video" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>MRP</label>
                                <input type="number" name="mrp" class="form-control" value="{{ $products->mrp }}" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Selling Price</label>
                                <input type="number" name="selling_price" class="form-control" value="{{ $products->selling_price }}" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>GST</label>
                                <input type="number" name="gst" class="form-control" value="{{ $products->gst }}" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>GST Price</label>
                                <input type="number" name="gst_price" class="form-control" value="{{ $products->gst_price }}" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Selling Price (w/o GST)</label>
                                <input type="number" name="selling_price_wo_gst" class="form-control" value="{{ $products->selling_price_wo_gst }}" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Inventory</label>
                                <input type="number" name="inventory" class="form-control" value="{{ $products->inventory }}" required>
                            </div>
                            <div class="form-group">
                                <label>Suffix</label>
                                <input type="text" name="suffix" class="form-control" value="{{ $products->suffix }}" required>
                            </div>
                            <div class="form-group">
                                <label>Trending Products</label>
                                <select name="tranding_products" class="form-control" required>
                                    <option value="0" {{ $products->tranding_products == 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ $products->tranding_products == 1 ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Offer</label>
                                <input type="text" name="offer" class="form-control" value="{{ $products->offer }}">
                            </div>
                            <div class="form-group">
                                <label>Minimum Quantity</label>
                                <input type="number" name="min_qty" class="form-control" value="{{ $products->min_qty }}">
                            </div>
                            <div class="form-group">
                                <label>Show Products</label>
                                <select class="form-control" name="show_product">
                          <option>---select---</option>
                          <option value="0" {{ $products->show_product == 0 ? 'selected' : '' }}>Farmer</option>
                          <option value="1" {{ $products->show_product == 1 ? 'selected' : '' }}>Vendor</option>
                          <option value="2" {{ $products->show_product == 2 ? 'selected' : '' }}>Both</option>

                        </select>
                            </div>
                            <div class="form-group">
                                <label>Vendor Min Quantity</label>
                                <input type="number" name="vendor_min_qty" class="form-control" value="{{ $products->vendor_min_qty }}">
                            </div>
                            <div class="form-group">
                                <label>Vendor Selling Price (w/o GST)</label>
                                <input type="number" name="vendor_selling_price_wo_gst" class="form-control" value="{{ $products->vendor_selling_price_wo_gst }}" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Vendor GST Price</label>
                                <input type="number" name="vendor_gst_price" class="form-control" value="{{ $products->vendor_gst_price }}" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Vendor GST</label>
                                <input type="number" name="vendor_gst" class="form-control" value="{{ $products->vendor_gst }}" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Vendor Selling Price</label>
                                <input type="number" name="vendor_selling_price" class="form-control" value="{{ $products->vendor_selling_price }}" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Vendor MRP</label>
                                <input type="number" name="vendor_mrp" class="form-control" value="{{ $products->vendor_mrp }}" step="0.01">
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection