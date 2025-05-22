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
                                <input type="text" name="name_marathi" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Name (Gujrati)</label>
                                <input type="text" name="name_gujrati" class="form-control" required>
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
                             <tr>
                      <td> <strong id="mp">Farmer MRP</strong> <span style="color:red;">*</span></strong> </td>
                      <td>
                        <input type="text" name="mrp" class="form-control" onkeypress="return isNumberKey(event)" placeholder="" 
                         value="" />
                      </td>
                    </tr>
                    <tr>
                      <td> <strong id="spp">Farmer Selling Price</strong> <span style="color:red;">*</span></strong> </td>
                      <td>
                        <input type="text" name="selling_price" class="form-control" id="sellingprice" placeholder=""  value="" />
                      </td>
                    </tr>
                    <tr>
                      <td> <strong>Farmer GST%</strong> <span style="color:red;">*</span></strong> </td>
                      <td>
                        <input type="text" name="gst" class="form-control" onkeypress="return isNumberKey(event)" placeholder=""  value="" id="gst" />
                      </td>
                    </tr>
                    <tr>
                      <td> <strong>Farmer GST Price</strong> <span style="color:red;">*</span></strong> </td>
                      <td>
                        <input type="text" name="gst_price" class="form-control" placeholder="" onkeypress="return isNumberKey(event)"  value="" id="gstprice" />
                      </td>
                    </tr>
                    <tr>
                      <td> <strong>Farmer Selling Price(without GST)</strong> <span style="color:red;">*</span></strong> </td>
                      <td>
                        <input type="text" name="selling_price_wo_gst" class="form-control" placeholder="" onkeypress="return isNumberKey(event)"  value="" id="sp" />
                      </td>
                    </tr>
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
                             <tr>
                      <td> <strong>Vendor MRP</strong> <span style="color:red;">*</span></strong> </td>
                      <td>
                        <input type="text" name="vendor_mrp" onkeypress="return isNumberKeys(event)" class="form-control" placeholder=""   value=""  />
                      </td>
                    </tr>

                    <tr>
                      <td> <strong>Vendor Selling Price</strong> <span style="color:red;">*</span></strong> </td>
                      <td>
                        <input type="text" onkeypress="return isNumberKeys(event)" id="vendorsellingprice" name="vendor_selling_price" class="form-control" placeholder=""   value=""  />
                      </td>
                    </tr>

                    <tr>
                      <td> <strong>Vendor GST%</strong> <span style="color:red;">*</span></strong> </td>
                      <td>
                        <input type="text" onkeypress="return isNumberKeys(event)" id="vendorgst" name="vendor_gst" class="form-control" placeholder=""   value=""  />
                      </td>
                    </tr>

                    <tr>
                      <td> <strong>Vendor GST Price</strong> <span style="color:red;">*</span></strong> </td>
                      <td>
                        <input type="text" onkeypress="return isNumberKeys(event)" id="vendorgstprice" name="vendor_gst_price" class="form-control" placeholder=""   value=""  />
                      </td>
                    </tr>

                    <tr>
                      <td> <strong>Vendor Selling Price(without GST)</strong> <span style="color:red;">*</span></strong> </td>
                      <td>
                        <input type="text" onkeypress="return isNumberKeys(event)" id="vendorsellingpricewogst" name="vendor_selling_price_wo_gst" class="form-control" placeholder=""   value="" />
                      </td>
                    </tr>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
  function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
      return false;
    return true;
  }
  $(document).ready(function() {
    $('#gst, #sellingprice').keyup(function() {
      var price = $('#sellingprice').val();
      var gst = $('#gst').val();
      var n = 100 + parseInt(gst);
      var gst_price = price / n * 100;
      var wgst = (price - gst_price).toFixed(2);
      $('#gstprice').val(wgst);
      var sprice = $('#gstprice').val();
      $('#sp').val(gst_price.toFixed(2));
    });
  });
</script>

<script>
  function isNumberKeys(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) 
      return false;
    return true;
  }

  $(document).ready(function() {
    $('#vendorgst, #vendorsellingprice').keyup(function() {
      var price = parseFloat($('#vendorsellingprice').val());
      var gst = parseFloat($('#vendorgst').val());
      if (!isNaN(price) && !isNaN(gst)) {
        var n = 100 + gst;
        var gst_price = (price / n) * 100;
        var wgst = (price - gst_price).toFixed(2);
        $('#vendorgstprice').val(wgst);
        $('#vendorsellingpricewogst').val(gst_price.toFixed(2));
      }
    });
  });
</script>
@endsection