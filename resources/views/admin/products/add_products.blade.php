@extends('admin.base_template')

@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">Add Product</h4>
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

                        <form action="{{ route('admin.products.add_data', [base64_encode(1)]) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Name (English)</label>
                                <input type="text" name="name_english" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Name (Hindi)</label>
                                <input type="text" name="name_hindi" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Name (Punjabi)</label>
                                <input type="text" name="name_punjabi" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Name (Marathi)</label>
                                <input type="text" name="name_punjabi" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Name (Gujrati)</label>
                                <input type="text" name="name_punjabi" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Description (English)</label>
                                <textarea name="description_english" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Description (Hindi)</label>
                                <textarea name="description_hindi" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Description (Punjabi)</label>
                                <textarea name="description_punjabi" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Description (Marathi)</label>
                                <textarea name="description_punjabi" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Description (Gujrati)</label>
                                <textarea name="description_punjabi" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Images</label>
                                <input type="file" name="images[]" class="form-control" multiple>
                            </div>
                            <div class="form-group">
                                <label>Video</label>
                                <input type="file" name="video" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>MRP</label>
                                <input type="number" name="mrp" class="form-control" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Selling Price</label>
                                <input type="number" name="selling_price" class="form-control" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>GST</label>
                                <input type="number" name="gst" class="form-control" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>GST Price</label>
                                <input type="number" name="gst_price" class="form-control" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Selling Price (w/o GST)</label>
                                <input type="number" name="selling_price_wo_gst" class="form-control" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Inventory</label>
                                <input type="number" name="inventory" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Suffix</label>
                                <input type="text" name="suffix" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Trending Products</label>
                                <select name="tranding_products" class="form-control" required>
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Offer</label>
                                <input type="text" name="offer" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Minimum Quantity</label>
                                <input type="number" name="min_qty" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Show Product</label>
                                <input type="checkbox" name="show_product" value="1">
                            </div>
                            <div class="form-group">
                                <label>Vendor Min Quantity</label>
                                <input type="number" name="vendor_min_qty" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Vendor Selling Price (w/o GST)</label>
                                <input type="number" name="vendor_selling_price_wo_gst" class="form-control" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Vendor GST Price</label>
                                <input type="number" name="vendor_gst_price" class="form-control" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Vendor GST</label>
                                <input type="number" name="vendor_gst" class="form-control" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Vendor Selling Price</label>
                                <input type="number" name="vendor_selling_price" class="form-control" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Vendor MRP</label>
                                <input type="number" name="vendor_mrp" class="form-control" step="0.01">
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection