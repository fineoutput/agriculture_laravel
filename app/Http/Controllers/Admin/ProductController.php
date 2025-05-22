<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function viewProducts()
    {
        $products = Product::where('is_admin', 1)->get();
        return view('admin.products.view_products', [
            'user_name' => auth()->guard('admin')->user()->name,
            'products_data' => $products,
            'is_admin' => 1,
            'heading' => 'Admin'
        ]);
    }

    public function vendorPendingProducts()
    {
        $products = Product::where('is_admin', 0)->where('is_approved', 0)->get();
        return view('admin.products.view_products', [
            'user_name' => auth()->guard('admin')->user()->name,
            'products_data' => $products,
            'is_admin' => 0,
            'heading' => 'Vendor Pending'
        ]);
    }

    public function vendorAcceptedProducts()
    {
        $products = Product::where('is_admin', 0)->where('is_approved', 1)->get();
        return view('admin.products.view_products', [
            'user_name' => auth()->guard('admin')->user->name,
            'products_data' => $products,
            'is_admin' => 0,
            'heading' => 'Vendor Accepted'
        ]);
    }

    public function addProducts()
    {
        return view('admin.products.add_products', [
            'user_name' => auth()->guard('admin')->user()->name
        ]);
    }

   public function addProductsData(Request $request, $t, $iw = null)
    {
        Log::info('Request data: ' . json_encode($request->all()));
        Log::info('Request files: ' . json_encode($request->file()));

        $validator = Validator::make($request->all(), [
            'name_english' => 'required|string',
            'name_hindi' => 'required|string',
            'name_punjabi' => 'required|string',
            'name_marathi' => 'required|string',
            'name_gujrati' => 'required|string',
            'description_english' => 'required|string',
            'description_hindi' => 'required|string',
            'description_punjabi' => 'required|string',
            'description_marathi' => 'required|string',
            'description_gujrati' => 'required|string',
            'mrp' => 'nullable|numeric',
            'selling_price' => 'nullable|numeric',
            'gst' => 'nullable|numeric',
            'gst_price' => 'nullable|numeric',
            'selling_price_wo_gst' => 'nullable|numeric',
            'inventory' => 'required|integer',
            'suffix' => 'required|string',
            'tranding_products' => 'required|in:0,1',
            'min_qty' => 'nullable|integer',
            'offer' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp,tiff,webp|max:25000',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv,mkv,flv,webm|max:102400'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ' . json_encode($validator->errors()->all()));
            return redirect()->back()->with('smessage', $validator->errors()->first())->withInput();
        }

        $ip = $request->ip();
        $cur_date = now()->setTimezone('Asia/Kolkata');
        $added_by = auth()->guard('admin')->id();

        $images = [];
        if ($request->hasFile('images')) {
            $uploadPath = public_path('admin_products');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
                Log::info('Created directory: ' . $uploadPath);
            }

            foreach ($request->file('images') as $index => $file) {
                if ($file->isValid()) {
                    $new_file_name = "image_" . $cur_date->format('YmdHis') . "_{$index}_" . $file->getClientOriginalName();
                    $destinationPath = $uploadPath;

                    try {
                        if ($file->move($destinationPath, $new_file_name)) {
                            $images[] = 'admin_products/' . $new_file_name;
                            Log::info('Image uploaded: ' . $new_file_name);
                        } else {
                            Log::error('Failed to move image: ' . $new_file_name);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error moving image: ' . $new_file_name . ' - ' . $e->getMessage());
                    }
                } else {
                    Log::error('Invalid image file at index ' . $index . ': ' . $file->getClientOriginalName());
                }
            }
        }

        $video = null;
        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            $uploadPath = public_path('admin_products');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
                Log::info('Created directory: ' . $uploadPath);
            }

            $file = $request->file('video');
            $new_file_name = "video_" . $cur_date->format('YmdHis') . '_' . $file->getClientOriginalName();
            $destinationPath = $uploadPath;

            try {
                if ($file->move($destinationPath, $new_file_name)) {
                    $video = 'admin_products/' . $new_file_name;
                    Log::info('Video uploaded: ' . $new_file_name);
                } else {
                    Log::error('Failed to move video: ' . $new_file_name);
                }
            } catch (\Exception $e) {
                Log::error('Error moving video: ' . $new_file_name . ' - ' . $e->getMessage());
            }
        }

        $typ = base64_decode($t, true);
        if ($typ === false || !in_array($typ, [1, 2])) {
            return redirect()->back()->with('smessage', 'Invalid request type');
        }

        $data = [
            'name_english' => $request->name_english,
            'name_hindi' => $request->name_hindi,
            'name_punjabi' => $request->name_punjabi,
            'name_marathi' => $request->name_marathi,
            'name_gujrati' => $request->name_gujrati,
            'description_english' => $request->description_english,
            'description_hindi' => $request->description_hindi,
            'description_punjabi' => $request->description_punjabi,
            'description_marathi' => $request->description_marathi,
            'description_gujrati' => $request->description_gujrati,
            'image' => json_encode($images),
            'video' => $video,
            'mrp' => $request->mrp,
            'selling_price' => $request->selling_price,
            'gst' => $request->gst,
            'gst_price' => $request->gst_price,
            'selling_price_wo_gst' => $request->selling_price_wo_gst,
            'inventory' => $request->inventory,
            'suffix' => $request->suffix,
            'tranding_products' => $request->tranding_products,
            'offer' => $request->offer,
            'min_qty' => $request->min_qty,
            'show_product' => $request->show_product,
            'vendor_min_qty' => $request->vendor_min_qty,
            'vendor_selling_price_wo_gst' => $request->vendor_selling_price_wo_gst,
            'vendor_gst_price' => $request->vendor_gst_price,
            'vendor_gst' => $request->vendor_gst,
            'vendor_selling_price' => $request->vendor_selling_price,
            'vendor_mrp' => $request->vendor_mrp,
            'ip' => $ip,
            'added_by' => $added_by,
            'date' => $cur_date
        ];

        if ($typ == 1) {
            $data['is_active'] = 1;
            $data['is_admin'] = 1;
            try {
                $product = Product::create($data);
                $success = $product->id ? true : false;
            } catch (\Exception $e) {
                Log::error('Failed to create product: ' . $e->getMessage());
                return redirect()->back()->with('smessage', 'Failed to save product data');
            }
        } elseif ($typ == 2) {
            $idw = base64_decode($iw, true);
            if ($idw === false) {
                return redirect()->back()->with('smessage', 'Invalid product ID');
            }
            $product = Product::findOrFail($idw);

            // Delete old images if new ones are uploaded
            if (!empty($images)) {
                $existingImages = json_decode($product->image, true);
                if (is_array($existingImages)) {
                    foreach ($existingImages as $img) {
                        $path = public_path($img);
                        if (file_exists($path)) {
                            unlink($path);
                            Log::info('Deleted old image: ' . $path);
                        }
                    }
                }
            } else {
                $data['image'] = $product->image; // Retain old images
            }

            // Delete old video if new one is uploaded
            if ($video && $product->video) {
                $path = public_path($product->video);
                if (file_exists($path)) {
                    unlink($path);
                    Log::info('Deleted old video: ' . $path);
                }
            } elseif (!$video) {
                $data['video'] = $product->video; // Retain old video
            }

            try {
                $success = $product->update($data);
            } catch (\Exception $e) {
                Log::error('Failed to update product: ' . $e->getMessage());
                return redirect()->back()->with('smessage', 'Failed to update product data');
            }
        }

        if ($success) {
            return redirect()->route('admin.products.view')->with('smessage', 'Data inserted successfully');
        }
        return redirect()->back()->with('smessage', 'Sorry, an error occurred');
    }

    public function updateProducts($idd)
    {
        $id = base64_decode($idd);
        $product = Product::findOrFail($id);
        return view('admin.products.update_products', [
            'user_name' => auth()->guard('admin')->user()->name,
            'products' => $product,
            'id' => $idd
        ]);
    }

    public function deleteProducts($idd)
    {
        if (auth()->guard('admin')->user()->position !== 'Super Admin') {
            return view('errors.error500admin', ['e' => "Sorry You Don't Have Permission To Delete Anything."]);
        }

        $id = base64_decode($idd);
        $zapak = Product::destroy($id);
        if ($zapak) {
            return redirect()->route('admin.products.view');
        }
        return "Error";
    }

    public function updateProductsStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $data_update = ['is_active' => $t === 'active' ? 1 : 0];
        $zapak = Product::where('id', $id)->update($data_update);

        if ($zapak) {
            return redirect()->route('admin.products.view');
        }
        return view('errors.error500admin', ['e' => 'Error Occurred']);
    }

    public function approvedProduct($idd)
    {
        $id = base64_decode($idd);
        $zapak = Product::where('id', $id)->update(['is_approved' => 1]);

        if ($zapak) {
            return redirect()->route('admin.products.view')->with('smessage', 'Product approved successfully');
        }
        return view('errors.error500admin', ['e' => 'Error Occurred']);
    }

    // public function productCodData(Request $request)
    // {
    //     if (!$request->ajax()) {
    //         abort(400, 'Invalid request');
    //     }

    //     $user_id = $request->userId;
    //     $is_checked = filter_var($request->isChecked, FILTER_VALIDATE_BOOLEAN);
    //     $zapak = Product::where('id', $user_id)->update(['cod' => $is_checked ? 1 : 0]);

    //     return response()->json(['success' => $zapak]);
    // }

    public function productCodData(Request $request, $id)
    {
       try {
        $zapak = Product::findOrFail($id);
        $cod = $request->has('cod') ? 1 : 0; // Checkbox checked = 1, unchecked = 0

        $zapak->update(['cod' => $cod]);

        return redirect()->back()->with('smessage', 'COD status updated successfully');
    } catch (\Exception $e) {
        Log::error('COD Update Error: ' . $e->getMessage());
        return redirect()->back()->with('emessage', 'Error updating COD status: ' . $e->getMessage());
    }
    }
}