<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\VendorNotification;
use App\Models\VendorOrder1;
use App\Models\InventoryTxn;
use App\Models\PaymentTransaction;
use App\Models\State;
use App\Models\VendorOrder2;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VendorOrderController extends Controller
{
    public function addToCart(Request $request)
{
    try {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|min:1',
            'is_admin' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            Log::warning('AddToCart: Validation failed', [
                'ip' => $request->ip(),
                'errors' => $validator->errors(),
                'url' => $request->fullUrl(),
            ]);
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        $product_id = $request->input('product_id');
        $is_admin = $request->input('is_admin');

        // Authenticate vendor using Authentication header
        $token = $request->header('Authentication');
        if (!$token) {
            Log::warning('No bearer token provided');
            return response()->json([
                'message' => 'Token required!',
                'status' => 201,
            ], 401);
        }

        $vendor = Vendor::where('auth', $token)
            ->where('is_active', 1)
            ->first();

        if (!$vendor) {
            Log::warning('Invalid or inactive user for token', ['token' => $token]);
            return response()->json([
                'message' => 'Invalid token or inactive user!',
                'status' => 201,
            ], 403);
        }

        if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
            Log::warning('AddToCart: Authentication failed or vendor inactive/unapproved', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? $vendor->is_active : null,
                'is_approved' => $vendor ? $vendor->is_approved : null,
            ]);
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 201,
            ], 403);
        }

        Log::info('AddToCart: Auth attempt', [
            'vendor_id' => $vendor ? $vendor->id : null,
            'product_id' => $product_id,
            'is_admin' => $is_admin,
            'ip' => $request->ip(),
            'host' => $request->getHost(),
            'url' => $request->fullUrl(),
        ]);

        // Check if product is already in cart
        $cartItem = Cart::where('farmer_id', $vendor->id)
            ->where('vendor_id', $vendor->id)
            ->where('product_id', $product_id)
            ->where('is_admin', $is_admin)
            ->first();

        if ($cartItem) {
            Log::info('AddToCart: Product already in cart', [
                'vendor_id' => $vendor->id,
                'product_id' => $product_id,
                'cart_id' => $cartItem->id,
            ]);
            return response()->json([
                'message' => 'Product is already in your cart!',
                'status' => 201,
                'data' => [],
            ], 200);
        }

        // Check product exists and is active
        $product = Product::where('id', $product_id)
            ->where('is_active', 1)
            ->first();

        if (!$product) {
            Log::warning('AddToCart: Product not found', [
                'vendor_id' => $vendor->id,
                'product_id' => $product_id,
            ]);
            return response()->json([
                'message' => 'Product Not Found!',
                'status' => 201,
                'data' => [],
            ], 404);
        }

        // Check inventory
        if ($product->inventory <= 0) {
            Log::warning('AddToCart: Product out of stock', [
                'vendor_id' => $vendor->id,
                'product_id' => $product_id,
                'inventory' => $product->inventory,
            ]);
            return response()->json([
                'message' => 'Product is out of Stock!',
                'status' => 201,
                'data' => [],
            ], 200);
        }

        // Prepare cart data
        $cartData = [
            'farmer_id' => $vendor->id,
            'vendor_id' => $vendor->id,
            'product_id' => $product_id,
            'is_admin' => $is_admin,
            'qty' => $product->vendor_min_qty ?? 1,
            'date' => Carbon::now('Asia/Kolkata'),
        ];

        // Insert into cart
        $cart = Cart::create($cartData);
        $lastId = $cart->id;

        // Count total cart items
        $cartCount = Cart::where('farmer_id', $vendor->id)->count();

        Log::info('AddToCart: Product added to cart', [
            'vendor_id' => $vendor->id,
            'product_id' => $product_id,
            'cart_id' => $lastId,
            'cart_count' => $cartCount,
        ]);

        return response()->json([
            'message' => 'Product Successfully Added!',
            'status' => 200,
            'data' => $cartCount,
        ], 200);
    } catch (\Illuminate\Database\QueryException $e) {
        Log::error('AddToCart: Database error', [
            'vendor_id' => $vendor->id ?? null,
            'product_id' => $product_id ?? null,
            'error' => $e->getMessage(),
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
        ]);
        return response()->json([
            'message' => 'Database error: ' . $e->getMessage(),
            'status' => 201,
            'data' => [],
        ], 500);
    } catch (\Exception $e) {
        Log::error('AddToCart: General error', [
            'vendor_id' => $vendor->id ?? null,
            'product_id' => $product_id ?? null,
            'error' => $e->getMessage(),
        ]);
        return response()->json([
            'message' => 'Error processing request: ' . $e->getMessage(),
            'status' => 201,
            'data' => [],
        ], 500);
    }
}



    public function getCart(Request $request)
{
    try {
        // Validate X-Language header
        $validator = Validator::make(['lang' => $request->header('X-Language')], [
            'lang' => 'required|in:en,hi,pn',
        ]);

        if ($validator->fails()) {
            Log::warning('GetCart: Validation failed for X-Language header', [
                'ip' => $request->ip(),
                'errors' => $validator->errors(),
                'url' => $request->fullUrl(),
                'header' => $request->header('X-Language'),
            ]);
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        $lang = $request->header('X-Language');

        // Authenticate vendor using header token
        $token = $request->header('Authentication');
        if (!$token) {
            Log::warning('No bearer token provided');
            return response()->json([
                'message' => 'Token required!',
                'status' => 201,
            ], 401);
        }

        $vendor = Vendor::where('auth', $token)
            ->where('is_active', 1)
            ->first();

        if (!$vendor) {
            Log::warning('Invalid or inactive user for token', ['token' => $token]);
            return response()->json([
                'message' => 'Invalid token or inactive user!',
                'status' => 201,
            ], 403);
        }

        if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
            Log::warning('GetCart: Authentication failed or vendor inactive/unapproved', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? $vendor->is_active : null,
                'is_approved' => $vendor ? $vendor->is_approved : null,
            ]);
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 201,
            ], 403);
        }

        Log::info('GetCart: Auth attempt', [
            'vendor_id' => $vendor ? $vendor->id : null,
            'lang' => $lang,
            'ip' => $request->ip(),
            'host' => $request->getHost(),
            'url' => $request->fullUrl(),
        ]);

        // Fetch cart items
        $cartItems = Cart::where('vendor_id', $vendor->id)->get();

        if ($cartItems->isEmpty()) {
            $cartCount = Cart::where('vendor_id', $vendor->id)->count();
            Log::info('GetCart: Cart is empty', [
                'vendor_id' => $vendor->id,
                'cart_count' => $cartCount,
            ]);
            return response()->json([
                'message' => 'Cart is empty!',
                'status' => 201,
                'data' => [],
                'count' => $cartCount,
            ], 200);
        }

        $data = [];
        $total = 0;

        foreach ($cartItems as $cart) {
            // Fetch product
            $product = Product::where('id', $cart->product_id)
                ->where('is_active', 1)
                ->first();

            if (!$product) {
                // Delete cart item if product is inactive
                Cart::where('vendor_id', $vendor->id)
                    ->where('product_id', $cart->product_id)
                    ->delete();
                Log::info('GetCart: Deleted cart item for inactive product', [
                    'vendor_id' => $vendor->id,
                    'product_id' => $cart->product_id,
                    'cart_id' => $cart->id,
                ]);
                continue;
            }

            // Handle image
            $image = '';
            if ($product->image) {
                $imageArray = json_decode($product->image, true);
                if (is_array($imageArray) && !empty($imageArray)) {
                    $image = url($imageArray[0]);
                } else {
                    $image = url($product->image);
                }
            }

            // Check inventory
            $stock = $product->inventory != 0 ? 'In Stock' : 'Out of Stock';

            // Calculate total
            $total += $product->vendor_selling_price * $cart->qty;

            // Prepare cart item data based on language
            $item = [
                'cart_id' => $cart->id,
                'pro_id' => $product->id,
                'image' => $image,
                'min_qty' => $product->vendor_min_qty ?? 1,
                'selling_price' => $product->vendor_selling_price * $cart->qty,
                'stock' => $stock,
                'vendor_id' => $product->added_by,
                'is_admin' => $cart->is_admin,
                'qty' => $cart->qty,
                'product_cod' => $product->cod,
                'is_cod' => $vendor->cod,
                'qty_discount' => $vendor->qty_discount ?? null,
            ];

            if ($lang === 'en') {
                $item['name'] = $product->name_english;
                $item['description'] = $product->description_english;
            } elseif ($lang === 'hi') {
                $item['name'] = $product->name_hindi;
                $item['description'] = $product->description_hindi;
            } elseif ($lang === 'pn') {
                $item['name'] = $product->name_punjabi;
                $item['description'] = $product->description_punjabi;
            }

            $data[] = $item;
        }

        // Count cart items
        $cartCount = Cart::where('vendor_id', $vendor->id)->count();

        Log::info('GetCart: Cart items retrieved', [
            'vendor_id' => $vendor->id,
            'lang' => $lang,
            'cart_count' => $cartCount,
            'item_count' => count($data),
            'total' => $total,
        ]);

        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => $data,
            'count' => $cartCount,
            'total' => $total,
        ], 200);
    } catch (\Illuminate\Database\QueryException $e) {
        Log::error('GetCart: Database error', [
            'vendor_id' => $vendor->id ?? null,
            'lang' => $request->header('X-Language') ?? null,
            'error' => $e->getMessage(),
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
        ]);
        return response()->json([
            'message' => 'Database error: ' . $e->getMessage(),
            'status' => 201,
        ], 500);
    } catch (\Exception $e) {
        Log::error('GetCart: General error', [
            'vendor_id' => $vendor->id ?? null,
            'lang' => $request->header('X-Language') ?? null,
            'error' => $e->getMessage(),
        ]);
        return response()->json([
            'message' => 'Error processing request: ' . $e->getMessage(),
            'status' => 201,
        ], 500);
    }
}


   public function getProductDetails(Request $request)
{
    try {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            Log::warning('GetProductDetails: Validation failed', [
                'ip' => $request->ip(),
                'errors' => $validator->errors(),
                'url' => $request->fullUrl(),
            ]);
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 400,
            ], 422);
        }

        // Validate X-Language header
        $lang = $request->header('X-Language', 'en');
        $validator = Validator::make(['lang' => $lang], [
            'lang' => 'in:en,hi,pn',
        ]);

        if ($validator->fails()) {
            Log::warning('GetProductDetails: Validation failed for X-Language header', [
                'ip' => $request->ip(),
                'errors' => $validator->errors(),
                'url' => $request->fullUrl(),
                'header' => $request->header('X-Language'),
            ]);
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 400,
            ], 422);
        }

        $product_id = $request->input('product_id');

        // Authenticate vendor using header token
        $token = $request->header('Authentication');
        if (!$token) {
            Log::warning('No bearer token provided');
            return response()->json([
                'message' => 'Token required!',
                'status' => 201,
            ], 401);
        }

        $vendor = Vendor::where('auth', $token)
            ->where('is_active', 1)
            ->first();

        if (!$vendor) {
            Log::warning('Invalid or inactive user for token', ['token' => $token]);
            return response()->json([
                'message' => 'Invalid token or inactive user!',
                'status' => 201,
            ], 403);
        }

        if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
            Log::warning('GetProductDetails: Authentication failed or vendor inactive/unapproved', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? $vendor->is_active : null,
                'is_approved' => $vendor ? $vendor->is_approved : null,
            ]);
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 201,
            ], 403);
        }

        Log::info('GetProductDetails: Auth attempt', [
            'vendor_id' => $vendor ? $vendor->id : null,
            'product_id' => $product_id,
            'lang' => $lang,
            'ip' => $request->ip(),
            'host' => $request->getHost(),
            'url' => $request->fullUrl(),
        ]);

        // Fetch product
        $product = Product::where('id', $product_id)
            ->where('is_active', 1)
            ->first();

        if (!$product) {
            Log::warning('GetProductDetails: Product not found', [
                'vendor_id' => $vendor->id,
                'product_id' => $product_id,
            ]);
            return response()->json([
                'message' => 'Product not found!',
                'status' => 404,
            ], 404);
        }

        // Handle images
        $images = [];
        if ($product->image) {
            $imageArray = json_decode($product->image, true);
            if (is_array($imageArray) && !empty($imageArray)) {
                foreach ($imageArray as $imagePath) {
                    $images[] = url($imagePath);
                }
            } else {
                $images[] = url($product->image);
            }
        }

        // Handle video
        if ($product->video) {
            $images[] = url($product->video);
        }

        // Check inventory
        $stock = $product->inventory != 0 ? 'In Stock' : 'Out of Stock';

        // Check cart
        $cart = Cart::where('vendor_id', $vendor->id)
            ->where('product_id', $product_id)
            ->first();
        $cart_qty = $cart ? $cart->qty : 0;

        // Calculate discount
        $discount = (int)$product->vendor_mrp - (int)$product->vendor_selling_price;
        $percent = 0;
        if ($discount > 0 && $product->vendor_mrp > 0) {
            $percent = round(($discount / $product->vendor_mrp) * 100);
        }

        // Prepare response data
        $data = [
            'pro_id' => $product->id,
            'images' => $images,
            'stock' => $stock,
            'vendor_id' => $product->added_by,
            'percent' => $percent,
            'cod' => $product->cod,
            'cart_qty' => $cart_qty,
            'is_cod' => $vendor->cod,
            'vendor_mrp' => $product->vendor_mrp,
            'vendor_min_qty' => $product->vendor_min_qty ?? 1,
            'vendor_selling_price' => $product->vendor_selling_price,
            'suffix' => $product->suffix,
            'offer' => $product->offer,
        ];

        // Set language-specific fields
        if ($lang === 'en') {
            $data['name'] = $product->name_english;
            $data['description'] = $product->description_english;
        } elseif ($lang === 'hi') {
            $data['name'] = $product->name_hindi;
            $data['description'] = $product->description_hindi;
        } elseif ($lang === 'pn') {
            $data['name'] = $product->name_punjabi;
            $data['description'] = $product->description_punjabi;
        }

        Log::info('GetProductDetails: Product details retrieved', [
            'vendor_id' => $vendor->id,
            'product_id' => $product_id,
            'lang' => $lang,
        ]);

        return response()->json([
            'message' => 'Product details fetched successfully!',
            'status' => 200,
            'data' => $data,
        ], 200);
    } catch (\Illuminate\Database\QueryException $e) {
        Log::error('GetProductDetails: Database error', [
            'vendor_id' => $vendor->id ?? null,
            'product_id' => $product_id ?? null,
            'lang' => $request->header('X-Language') ?? null,
            'error' => $e->getMessage(),
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
        ]);
        return response()->json([
            'message' => 'Database error: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    } catch (\Exception $e) {
        Log::error('GetProductDetails: General error', [
            'vendor_id' => $vendor->id ?? null,
            'product_id' => $product_id ?? null,
            'lang' => $request->header('X-Language') ?? null,
            'error' => $e->getMessage(),
        ]);
        return response()->json([
            'message' => 'Error processing request: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}


    public function updateCart(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->has(['product_id', 'qty'])) {
                Log::warning('UpdateCart: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer|min:1',
                'qty' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                Log::warning('UpdateCart: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $product_id = $request->input('product_id');
            $qty = $request->input('qty');

            // Authenticate vendor
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('UpdateCart: Auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'product_id' => $product_id,
                'qty' => $qty,
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
            ]);

            if (!$vendor || !$vendor->is_active) {
                Log::warning('UpdateCart: Authentication failed or vendor inactive', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'product_id' => $product_id,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Check if cart exists
            $cartItems = Cart::where('vendor_id', $vendor->id)->get();
            if ($cartItems->isEmpty()) {
                Log::info('UpdateCart: Cart is empty', [
                    'vendor_id' => $vendor->id,
                    'product_id' => $product_id,
                ]);
                return response()->json([
                    'message' => 'Cart is empty!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            // Check if cart item exists for product
            $cartItem = Cart::where('vendor_id', $vendor->id)
                ->where('product_id', $product_id)
                ->first();

            if (!$cartItem) {
                Log::warning('UpdateCart: Cart item not found', [
                    'vendor_id' => $vendor->id,
                    'product_id' => $product_id,
                ]);
                return response()->json([
                    'message' => 'Product Not Found in Cart!',
                    'status' => 201,
                    'data' => [],
                ], 404);
            }

            // Check product exists and is active
            $product = Product::where('id', $product_id)
                ->where('is_active', 1)
                ->first();

            if (!$product) {
                Log::warning('UpdateCart: Product not found', [
                    'vendor_id' => $vendor->id,
                    'product_id' => $product_id,
                ]);
                return response()->json([
                    'message' => 'Product Not Found!',
                    'status' => 201,
                    'data' => [],
                ], 404);
            }

            // Check inventory
            if ($product->inventory < $qty) {
                Log::warning('UpdateCart: Product out of stock', [
                    'vendor_id' => $vendor->id,
                    'product_id' => $product_id,
                    'inventory' => $product->inventory,
                    'requested_qty' => $qty,
                ]);
                return response()->json([
                    'message' => 'Product is out of Stock!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            // Check minimum quantity
            if ($product->vendor_min_qty && $qty > 0 && $qty < $product->vendor_min_qty) {
                Log::warning('UpdateCart: Minimum quantity not met', [
                    'vendor_id' => $vendor->id,
                    'product_id' => $product_id,
                    'qty' => $qty,
                    'vendor_min_qty' => $product->vendor_min_qty,
                ]);
                return response()->json([
                    'message' => "Minimum Quantity should be {$product->vendor_min_qty}",
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            // Update cart
            $cartItem->qty = $qty;
            $cartItem->date = Carbon::now('Asia/Kolkata');
            $cartItem->save();

            // Calculate amount and total
            $amount = $product->vendor_selling_price * $qty;
            $total = 0;

            // Recalculate total for all cart items
            $cartItems = Cart::where('vendor_id', $vendor->id)->get();
            foreach ($cartItems as $cart) {
                $cartProduct = Product::where('id', $cart->product_id)
                    ->where('is_active', 1)
                    ->first();

                if (!$cartProduct) {
                    // Delete cart item if product is inactive
                    Cart::where('vendor_id', $vendor->id)
                        ->where('product_id', $cart->product_id)
                        ->delete();
                    Log::info('UpdateCart: Deleted cart item for inactive product', [
                        'vendor_id' => $vendor->id,
                        'product_id' => $cart->product_id,
                        'cart_id' => $cart->id,
                    ]);
                    continue;
                }

                $total += $cartProduct->vendor_selling_price * $cart->qty;
            }

            Log::info('UpdateCart: Cart updated successfully', [
                'vendor_id' => $vendor->id,
                'product_id' => $product_id,
                'qty' => $qty,
                'amount' => $amount,
                'total' => $total,
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'amount' => $amount,
                'total' => $total,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('UpdateCart: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'product_id' => $product_id ?? null,
                'qty' => $qty ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        } catch (\Exception $e) {
            Log::error('UpdateCart: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'product_id' => $product_id ?? null,
                'qty' => $qty ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function removeCart(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->has('product_id')) {
                Log::warning('RemoveCart: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                Log::warning('RemoveCart: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $product_id = $request->input('product_id');

            // Authenticate vendor
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('RemoveCart: Auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'product_id' => $product_id,
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
            ]);

            if (!$vendor || !$vendor->is_active) {
                Log::warning('RemoveCart: Authentication failed or vendor inactive', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'product_id' => $product_id,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Delete cart item
            $deleted = Cart::where('vendor_id', $vendor->id)
                ->where('product_id', $product_id)
                ->delete();

            // Count remaining cart items
            $cartCount = Cart::where('vendor_id', $vendor->id)->count();

            Log::info('RemoveCart: Cart item removed', [
                'vendor_id' => $vendor->id,
                'product_id' => $product_id,
                'deleted' => $deleted,
                'cart_count' => $cartCount,
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $cartCount,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('RemoveCart: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'product_id' => $product_id ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        } catch (\Exception $e) {
            Log::error('RemoveCart: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'product_id' => $product_id ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function calculate(Request $request)
    {
        try {
            // Authenticate vendor
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('Calculate: Auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
            ]);

            if (!$vendor || !$vendor->is_active) {
                Log::warning('Calculate: Authentication failed or vendor inactive', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Fetch cart items
            $cartItems = Cart::where('vendor_id', $vendor->id)->get();

            if ($cartItems->isEmpty()) {
                Cart::where('vendor_id', $vendor->id)->delete();
                $cartCount = Cart::where('vendor_id', $vendor->id)->count();
                Log::info('Calculate: Cart is empty', [
                    'vendor_id' => $vendor->id,
                    'cart_count' => $cartCount,
                ]);
                return response()->json([
                    'message' => 'Cart is empty!',
                    'status' => 201,
                    'data' => [],
                    'count' => $cartCount,
                ], 200);
            }

            $total = 0;
            $charges = 0;
            $discount = 0;
            $is_admin = 0;
            $validItems = [];

            foreach ($cartItems as $cart) {
                // Fetch product
                $product = Product::where('id', $cart->product_id)
                    ->where('is_active', 1)
                    ->first();

                if (!$product) {
                    // Delete cart item if product is inactive
                    Cart::where('vendor_id', $vendor->id)
                        ->where('product_id', $cart->product_id)
                        ->delete();
                    Log::info('Calculate: Deleted cart item for inactive product', [
                        'vendor_id' => $vendor->id,
                        'product_id' => $cart->product_id,
                        'cart_id' => $cart->id,
                    ]);
                    continue;
                }

                // Check inventory
                if ($product->inventory < $cart->qty) {
                    Log::warning('Calculate: Product out of stock', [
                        'vendor_id' => $vendor->id,
                        'product_id' => $cart->product_id,
                        'inventory' => $product->inventory,
                        'requested_qty' => $cart->qty,
                    ]);
                    return response()->json([
                        'message' => "{$product->name_english} is out of stock. Please remove this from cart!",
                        'status' => 201,
                    ], 200);
                }

                // Check minimum quantity
                if ($product->vendor_min_qty && $cart->qty < $product->vendor_min_qty) {
                    Log::warning('Calculate: Minimum quantity not met', [
                        'vendor_id' => $vendor->id,
                        'product_id' => $cart->product_id,
                        'qty' => $cart->qty,
                        'vendor_min_qty' => $product->vendor_min_qty,
                    ]);
                    return response()->json([
                        'message' => "{$product->name_english} minimum quantity should be {$product->vendor_min_qty}",
                        'status' => 201,
                        'data' => [],
                    ], 200);
                }

                $is_admin = $cart->is_admin;
                $total += $product->vendor_selling_price * $cart->qty;
                $discount += ($vendor->qty_discount ?? 0) * $cart->qty;
                $validItems[] = [
                    'cart' => $cart,
                    'product' => $product,
                ];
            }

            // Calculate charges
            if ($is_admin == 1) {
                $charges = $total <= config('app.admin_amount') ? config('app.admin_charges') : 0;
            } else {
                $charges = array_sum(array_map(function ($item) {
                    return $item['cart']->qty * config('app.vendor_charges');
                }, $validItems));
            }

            // Insert into vendor_order1
            $order1Data = [
                'is_admin' => $is_admin,
                'vendor_id' => $vendor->id,
                'total_amount' => $total,
                'charges' => $charges,
                'final_amount' => $total + $charges - $discount,
                'payment_status' => 0,
                'order_status' => 0,
                'date' => Carbon::now('Asia/Kolkata'),
            ];

            $order1 = VendorOrder1::create($order1Data);
            $order1_id = $order1->id;

            // Insert into vendor_order2
            foreach ($validItems as $item) {
                $cart = $item['cart'];
                $product = $item['product'];

                $order2Data = [
                    'main_id' => $order1_id,
                    'product_id' => $product->id,
                    'discount' => ($vendor->qty_discount ?? 0) * $cart->qty,
                    'product_name_en' => $product->name_english,
                    'image' => $product->image,
                    'qty' => $cart->qty,
                    'mrp' => $product->vendor_mrp,
                    'selling_price' => $product->vendor_selling_price,
                    'gst' => $product->vendor_gst,
                    'gst_price' => $product->gst_price,
                    'selling_price_wo_gst' => $product->vendor_selling_price_wo_gst,
                    'total_amount' => $product->vendor_selling_price * $cart->qty,
                    'date' => Carbon::now('Asia/Kolkata'),
                ];

                VendorOrder2::create($order2Data);
            }

            $responseData = [
                'order_id' => $order1_id,
                'total' => $total,
                'charges' => $charges,
                'final' => $total + $charges - $discount,
                'discount' => $discount,
            ];

            Log::info('Calculate: Order created successfully', [
                'vendor_id' => $vendor->id,
                'order_id' => $order1_id,
                'total' => $total,
                'charges' => $charges,
                'discount' => $discount,
                'final' => $responseData['final'],
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $responseData,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Calculate: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        } catch (\Exception $e) {
            Log::error('Calculate: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function getOrders(Request $request)
    {
        try {
            // Authenticate vendor
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('GetOrders: Auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
            ]);

            if (!$vendor || !$vendor->is_active) {
                Log::warning('GetOrders: Authentication failed or vendor inactive', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Fetch orders
            $orders = VendorOrder1::where('vendor_id', $vendor->id)
                ->whereIn('payment_status', [1, 2])
                ->orderBy('id', 'desc')
                ->get();

            if ($orders->isEmpty()) {
                Log::info('GetOrders: No orders found', [
                    'vendor_id' => $vendor->id,
                ]);
                return response()->json([
                    'message' => 'No Orders Found!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            $data = [];

            foreach ($orders as $order) {
                // Map order status
                $statusMap = [
                    1 => ['status' => 'Pending', 'bg_color' => '#65bcd7'],
                    2 => ['status' => 'Accepted', 'bg_color' => '#3b71ca'],
                    3 => ['status' => 'Dispatched', 'bg_color' => '#e4a11b'],
                    4 => ['status' => 'Completed', 'bg_color' => '#139c49'],
                    5 => ['status' => 'Rejected', 'bg_color' => '#dc4c64'],
                    6 => ['status' => 'Cancelled', 'bg_color' => '#dc4c64'],
                ];

                $statusInfo = $statusMap[$order->order_status] ?? ['status' => 'Unknown', 'bg_color' => '#000000'];

                // Fetch order details
                $orderDetails = VendorOrder2::where('main_id', $order->id)->get();
                $details = [];

                foreach ($orderDetails as $orderDetail) {
                    $image = $orderDetail->image ? url($orderDetail->image) : '';
                    $details[] = [
                        'id' => $orderDetail->id,
                        'en' => $orderDetail->product_name_en,
                        'image' => $image,
                        'qty' => $orderDetail->qty,
                        'selling_price' => $orderDetail->selling_price,
                        'total_amount' => $orderDetail->total_amount,
                    ];
                }

                // Determine shop name
                $en = $order->is_admin == 1 ? 'Dairy Mart' : ($vendor->shop_name ?? 'Vendor not found');

                // Format date
                $date = Carbon::parse($order->date)->format('d/m/Y');

                $data[] = [
                    'id' => $order->id,
                    'charges' => $order->charges,
                    'total_amount' => $order->total_amount,
                    'final_amount' => $order->final_amount,
                    'status' => $statusInfo['status'],
                    'bg_color' => $statusInfo['bg_color'],
                    'en' => $en,
                    'date' => $date,
                    'details' => $details,
                ];
            }

            Log::info('GetOrders: Orders retrieved successfully', [
                'vendor_id' => $vendor->id,
                'order_count' => count($data),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('GetOrders: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        } catch (\Exception $e) {
            Log::error('GetOrders: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function checkout(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->isMethod('post')) {
                Log::warning('Checkout: Invalid request method', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|integer|min:1',
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'state' => 'required|integer|min:1',
                'district' => 'required|string|max:100',
                'pincode' => 'required|string|size:6',
                'phone' => 'required|string|size:10',
                'cod' => 'required|in:0,1',
            ]);

            if ($validator->fails()) {
                Log::warning('Checkout: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate vendor
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('Checkout: Auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'order_id' => $request->input('order_id'),
                'cod' => $request->input('cod'),
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
            ]);

            if (!$vendor || !$vendor->is_active) {
                Log::warning('Checkout: Authentication failed or vendor inactive', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'order_id' => $request->input('order_id'),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Fetch cart items
            $cartItems = Cart::where('vendor_id', $vendor->id)->get();

            if ($cartItems->isEmpty()) {
                Cart::where('vendor_id', $vendor->id)->delete();
                $cartCount = Cart::where('vendor_id', $vendor->id)->count();
                Log::info('Checkout: Cart is empty', [
                    'vendor_id' => $vendor->id,
                    'cart_count' => $cartCount,
                ]);
                return response()->json([
                    'message' => 'Cart is empty!',
                    'status' => 201,
                    'data' => [],
                    'count' => $cartCount,
                ], 200);
            }

            // Extract input data
            $order_id = $request->input('order_id');
            $name = $request->input('name');
            $address = $request->input('address');
            $city = $request->input('city');
            $state = $request->input('state');
            $district = $request->input('district');
            $pincode = $request->input('pincode');
            $phone = $request->input('phone');
            $cod = $request->input('cod');

            // Validate cart items
            foreach ($cartItems as $cart) {
                $product = Product::where('id', $cart->product_id)
                    ->where('is_active', 1)
                    ->first();

                if (!$product) {
                    Log::warning('Checkout: Product not found', [
                        'vendor_id' => $vendor->id,
                        'product_id' => $cart->product_id,
                    ]);
                    return response()->json([
                        'message' => 'Product not found!',
                        'status' => 201,
                    ], 404);
                }

                // Check inventory
                if ($product->inventory < $cart->qty) {
                    Log::warning('Checkout: Product out of stock', [
                        'vendor_id' => $vendor->id,
                        'product_id' => $cart->product_id,
                        'inventory' => $product->inventory,
                        'requested_qty' => $cart->qty,
                    ]);
                    return response()->json([
                        'message' => "{$product->name_english} is out of stock. Please remove this from cart!",
                        'status' => 201,
                    ], 200);
                }

                // Check minimum quantity
                if ($product->vendor_min_qty && $cart->qty < $product->vendor_min_qty) {
                    Log::warning('Checkout: Minimum quantity not met', [
                        'vendor_id' => $vendor->id,
                        'product_id' => $cart->product_id,
                        'qty' => $cart->qty,
                        'vendor_min_qty' => $product->vendor_min_qty,
                    ]);
                    return response()->json([
                        'message' => "{$product->name_english} minimum quantity should be {$product->vendor_min_qty}",
                        'status' => 201,
                        'data' => [],
                    ], 200);
                }
            }

            // Fetch state name
            $stateData = State::where('id', $state)->first();
            if (!$stateData) {
                Log::warning('Checkout: State not found', [
                    'vendor_id' => $vendor->id,
                    'state_id' => $state,
                ]);
                return response()->json([
                    'message' => 'Invalid state ID!',
                    'status' => 201,
                ], 422);
            }

            $currentDate = Carbon::now('Asia/Kolkata');

            if ($cod == 0) {
                // Non-COD: Prepare CC Avenue payment
                $txn_id = mt_rand(999999, 999999999999);

                // Update vendor_order1
                $orderUpdate = [
                    'txn_id' => $txn_id,
                    'name' => $name,
                    'address' => $address,
                    'city' => $city,
                    'state' => $stateData->state_name,
                    'district' => $district,
                    'pincode' => $pincode,
                    'phone' => $phone,
                    'gateway' => 'CC Avenue',
                ];

                $order1 = VendorOrder1::where('id', $order_id)->update($orderUpdate);
                if (!$order1) {
                    Log::warning('Checkout: Order not found', [
                        'vendor_id' => $vendor->id,
                        'order_id' => $order_id,
                    ]);
                    return response()->json([
                        'message' => 'Order not found!',
                        'status' => 201,
                    ], 404);
                }

                $order1Data = VendorOrder1::where('id', $order_id)->first();

                // Prepare CC Avenue data
                $successUrl = url('api/vendor/payment_success');
                $failUrl = url('api/vendor/payment_failed');
                $post = [
                    'txn_id' => $txn_id,
                    'merchant_id' => config('app.ccavenue.merchant_id'),
                    'order_id' => $order_id,
                    'amount' => $order1Data->final_amount,
                    'currency' => 'INR',
                    'redirect_url' => $successUrl,
                    'cancel_url' => $failUrl,
                    'billing_name' => $name,
                    'billing_address' => $address,
                    'billing_city' => $city,
                    'billing_state' => $stateData->state_name,
                    'billing_zip' => $pincode,
                    'billing_country' => 'India',
                    'billing_tel' => $phone,
                    'billing_email' => '',
                    'merchant_param1' => 'Order Payment',
                ];

                $merchant_data = '';
                $working_key = config('app.ccavenue.working_key');
                $access_code = config('app.ccavenue.access_code');

                foreach ($post as $key => $value) {
                    $merchant_data .= $key . '=' . $value . '&';
                }

                $key = pack('H*', substr(hash('md5', $working_key), 0, 32));
                $initVector = pack('C*', 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
                $encrypted_data = bin2hex(openssl_encrypt($merchant_data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector));

                $send = [
                    'order_id' => $order_id,
                    'access_code' => $access_code,
                    'redirect_url' => $successUrl,
                    'cancel_url' => $failUrl,
                    'enc_val' => $encrypted_data,
                    'plain' => $merchant_data,
                    'merchant_param1' => 'Order Payment',
                ];

                Log::info('Checkout: CC Avenue payment initiated', [
                    'vendor_id' => $vendor->id,
                    'order_id' => $order_id,
                    'txn_id' => $txn_id,
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $send,
                ], 200);
            } else {
                // COD: Process order
                if (!$vendor->cod) {
                    Log::warning('Checkout: COD not supported for vendor', [
                        'vendor_id' => $vendor->id,
                        'order_id' => $order_id,
                    ]);
                    return response()->json([
                        'message' => 'Orders cannot be placed with COD!',
                        'status' => 201,
                    ], 403);
                }

                // Update vendor_order1 with billing details
                $orderUpdate = [
                    'name' => $name,
                    'address' => $address,
                    'city' => $city,
                    'state' => $stateData->state_name,
                    'district' => $district,
                    'pincode' => $pincode,
                    'phone' => $phone,
                ];

                $order1 = VendorOrder1::where('id', $order_id)->update($orderUpdate);
                if (!$order1) {
                    Log::warning('Checkout: Order not found', [
                        'vendor_id' => $vendor->id,
                        'order_id' => $order_id,
                    ]);
                    return response()->json([
                        'message' => 'Order not found!',
                        'status' => 201,
                    ], 404);
                }

                $order1Data = VendorOrder1::where('id', $order_id)
                    ->where('payment_status', 0)
                    ->first();

                if (!$order1Data) {
                    Log::warning('Checkout: Order not eligible for COD', [
                        'vendor_id' => $vendor->id,
                        'order_id' => $order_id,
                    ]);
                    return response()->json([
                        'message' => 'Order not eligible for COD!',
                        'status' => 201,
                    ], 403);
                }

                // Generate invoice number
                $now = Carbon::now('Asia/Kolkata')->format('y');
                $next = Carbon::now('Asia/Kolkata')->addYear()->format('y');
                $invoice_year = "$now-$next";

                $latestOrder = VendorOrder1::where('payment_status', 2)
                    ->where('invoice_year', $invoice_year)
                    ->orderBy('id', 'desc')
                    ->first();

                $invoice_no = $latestOrder ? $latestOrder->invoice_no + 1 : 1;

                // Update order status
                $order1Data->update([
                    'payment_status' => 2,
                    'order_status' => 1,
                    'invoice_year' => $invoice_year,
                    'invoice_no' => $invoice_no,
                ]);

                $order2Data = VendorOrder2::where('main_id', $order_id)->get();

                // Update inventory and create transactions
                foreach ($order2Data as $orderItem) {
                    $product = Product::where('id', $orderItem->product_id)
                        ->where('is_active', 1)
                        ->first();

                    if (!$product) {
                        Log::warning('Checkout: Product not found for inventory update', [
                            'vendor_id' => $vendor->id,
                            'product_id' => $orderItem->product_id,
                            'order_id' => $order_id,
                        ]);
                        continue;
                    }

                    $new_inventory = $product->inventory - $orderItem->qty;

                    // Create inventory transaction
                    InventoryTxn::create([
                        'order_id' => $order_id,
                        'at_time' => $product->inventory,
                        'less_inventory' => $orderItem->qty,
                        'updated_inventory' => $new_inventory,
                        'date' => $currentDate,
                    ]);

                    // Update product inventory
                    $product->update(['inventory' => $new_inventory]);
                }

                // Delete cart
                Cart::where('vendor_id', $vendor->id)->delete();

                if ($order1Data->is_admin == 0) {
                    // Non-admin order: Handle vendor commission and notification
                    if ($vendor->comission) {
                        $amt = $order1Data->total_amount * $vendor->comission / 100;
                        $credit = $order1Data->total_amount - $amt;

                        // Create payment transaction
                        PaymentTransaction::create([
                            'req_id' => $order_id,
                            'vendor_id' => $vendor->id,
                            'cr' => $credit,
                            'date' => $currentDate,
                        ]);

                        // Update vendor account
                        $vendor->update([
                            'account' => ($vendor->account ?? 0) + $credit,
                        ]);
                    }

                    // Send FCM notification
                    if ($vendor->fcm_token) {
                        $url = 'https://fcm.googleapis.com/fcm/send';
                        $title = 'New Order';
                        $message = "New order #{$order_id} received with the amount of ₹{$order1Data->final_amount}";
                        $msg = [
                            'title' => $title,
                            'body' => $message,
                            'sound' => 'default',
                        ];
                        $fields = [
                            'to' => $vendor->fcm_token,
                            'notification' => $msg,
                            'priority' => 'high',
                        ];

                        $client = new \GuzzleHttp\Client();
                        $response = $client->post($url, [
                            'headers' => [
                                'Authorization' => 'key=AAAAAIDR4rw:APA91bHaVxhjsODWyIDSiQXCpBhC46GL-9Ycxa9VKwtsPefjLy6NfiiLsajh8db55tRrIOag_A9wh9iXREo2-Obbt1U-fdHmpjy3zvgvTWFleqY5S_8dJtoYz0uKxPRZ76E3sXpgjISv',
                                'Content-Type' => 'application/json',
                            ],
                            'json' => $fields,
                        ]);

                        // Log FCM response
                        Log::info('Checkout: FCM notification sent', [
                            'vendor_id' => $vendor->id,
                            'order_id' => $order_id,
                            'response' => $response->getBody()->getContents(),
                        ]);

                        // Save notification
                        VendorNotification::create([
                            'vendor_id' => $vendor->id,
                            'name' => $title,
                            'dsc' => $message,
                            'date' => $currentDate,
                        ]);
                    }
                } else {
                    // Admin order: Send email
                    $message = <<<EOD
                    Hello Admin<br/><br/>
                    You have received new Order and below are the details<br/><br/>
                    <b>Order ID</b> - {$order_id}<br/>
                    <b>Amount</b> - Rs.{$order1Data->final_amount}<br/>
                    EOD;

                    Mail::html($message, function ($message) use ($order_id) {
                        $message->from(config('app.smtp.from'))
                            ->to(config('app.smtp.to'), 'Dairy Muneem')
                            ->subject('New Order received');
                    });

                    // Log WhatsApp message (replacement for send_whatsapp_msg_admin)
                    Log::info('Checkout: WhatsApp message to admin (not implemented)', [
                        'vendor_id' => $vendor->id,
                        'order_id' => $order_id,
                        'final_amount' => $order1Data->final_amount,
                    ]);
                }

                $send = [
                    'order_id' => $order_id,
                    'final_amount' => $order1Data->final_amount,
                ];

                Log::info('Checkout: COD order processed successfully', [
                    'vendor_id' => $vendor->id,
                    'order_id' => $order_id,
                    'invoice_no' => $invoice_no,
                    'invoice_year' => $invoice_year,
                ]);

                return response()->json([
                    'message' => 'Success',
                    'status' => 200,
                    'data' => $send,
                ], 200);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Checkout: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'order_id' => $request->input('order_id') ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        } catch (\Exception $e) {
            Log::error('Checkout: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'order_id' => $request->input('order_id') ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

}
