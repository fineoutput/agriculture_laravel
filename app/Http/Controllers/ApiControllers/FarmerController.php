<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Order2;
use App\Models\Vendor;
use App\Models\InventoryTxn;
use App\Models\VendorNotification;
use App\Models\PaymentTransaction;
use App\Models\State;
use App\Models\Order1;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use Razorpay\Api\Api; // Add this import
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;

class FarmerController extends Controller
{
   public function addToCart(Request $request)
    {
        Log::info('addToCart request', [
            'product_id' => $request->input('product_id'),
            'vendor_id' => $request->input('vendor_id'),
            'is_admin' => $request->input('is_admin'),
            'authentication_header' => $request->header('Authentication'),
        ]);

        // Validate input
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'vendor_id' => 'required|integer',
            'is_admin' => 'required|in:0,1',
            // 'Authentication' => 'required|string',
        ], [
            'Authentication.required' => 'Authentication token is required',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ' . $validator->errors()->first());
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        try {
            // Get token from Authentication header
            $token = $request->header('Authentication');

            // Authenticate user by token
            $user = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$user) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            $product_id = $request->input('product_id');
            $vendor_id = $request->input('vendor_id');
            $is_admin = $request->input('is_admin');

            // Check if product is already in cart
            $cartItem = Cart::where([
                'farmer_id' => $user->id,
                'product_id' => $product_id,
                'vendor_id' => $vendor_id,
                'is_admin' => $is_admin,
            ])->first();

            if ($cartItem) {
                Log::info('Product already in cart', [
                    'farmer_id' => $user->id,
                    'product_id' => $product_id,
                ]);
                return response()->json([
                    'message' => 'Product is already in your cart!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            // Verify product
            $product = Product::where('id', $product_id)
                ->where('is_active', 1)
                ->first();

            if (!$product) {
                Log::warning('Product not found or inactive', ['product_id' => $product_id]);
                return response()->json([
                    'message' => 'Product Not Found!',
                    'status' => 201,
                    'data' => [],
                ], 404);
            }

            if ($product->inventory <= 0) {
                Log::warning('Product out of stock', ['product_id' => $product_id]);
                return response()->json([
                    'message' => 'Product is out of Stock!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            // Add to cart
            $cartData = [
                'farmer_id' => $user->id,
                'vendor_id' => $vendor_id,
                'product_id' => $product_id,
                'is_admin' => $is_admin,
                'qty' => $product->min_qty ?? 1,
                'date' => now(),
            ];

            $cart = Cart::create($cartData);

            // Get cart item count
            $cartCount = Cart::where('farmer_id', $user->id)->count();

            Log::info('Product added to cart', [
                'farmer_id' => $user->id,
                'product_id' => $product_id,
                'cart_id' => $cart->id,
                'cart_count' => $cartCount,
            ]);

            return response()->json([
                'message' => 'Product Successfully Added!',
                'status' => 200,
                'data' => $cartCount,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in addToCart', [
                'product_id' => $request->input('product_id'),
                'farmer_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error adding product to cart: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

  public function getCart(Request $request)
    {
        Log::info('getCart request', [
            'lang' => $request->query('lang', 'en'),
            'authentication_header' => $request->header('Authentication'),
        ]);

        // Validate inputs
        $lang = $request->query('lang', 'en');
        $token = $request->header('Authentication');

        $validator = Validator::make([
            'lang' => $lang,
            // 'Authentication' => $token,
        ], [
            'lang' => 'nullable|string|in:en,hi,pn',
            // 'Authentication' => 'required|string',
        ], [
            'Authentication.required' => 'Authentication token is required',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ' . $validator->errors()->first());
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        try {
            // Authenticate user by token
            $user = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$user) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            // Fetch cart items
            $cartItems = Cart::where('farmer_id', $user->id)->get();
            $data = [];
            $total = 0;

            if ($cartItems->isNotEmpty()) {
                foreach ($cartItems as $cart) {
                    // Fetch product
                    $product = Product::where('id', $cart->product_id)
                        ->where('is_active', 1)
                        ->first();

                    if ($product) {
                        // Handle image
                        $image = '';
                        if ($product->image) {
                            $imageArray = json_decode($product->image, true);
                            if (is_array($imageArray) && !empty($imageArray)) {
                                $image = asset($imageArray[0]);
                            } else {
                                $image = asset($product->image);
                            }
                        }

                        // Check stock
                        $stock = $product->inventory > 0 ? 'In Stock' : 'Out of Stock';

                        // Calculate total
                        $total += $product->selling_price * $cart->qty;

                        // Prepare data based on language
                        $item = [
                            'cart_id' => $cart->id,
                            'pro_id' => $product->id,
                            'image' => $image,
                            'min_qty' => $product->min_qty ?? 1,
                            'selling_price' => $product->selling_price * $cart->qty,
                            'stock' => $stock,
                            'vendor_id' => $product->added_by,
                            'is_admin' => $cart->is_admin,
                            'qty' => $cart->qty,
                            'product_cod' => $product->cod,
                            'is_cod' => $user->cod,
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
                    } else {
                        // Remove invalid cart item
                        Cart::where('farmer_id', $user->id)
                            ->where('product_id', $cart->product_id)
                            ->delete();
                        Log::info('Removed invalid cart item', [
                            'farmer_id' => $user->id,
                            'product_id' => $cart->product_id,
                        ]);
                    }
                }

                // Get cart count
                $count = Cart::where('farmer_id', $user->id)->count();

                Log::info('Cart retrieved successfully', [
                    'farmer_id' => $user->id,
                    'cart_count' => $count,
                    'total' => $total,
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $data,
                    'count' => $count,
                    'total' => $total,
                ], 200);
            } else {
                $count = Cart::where('farmer_id', $user->id)->count();
                Log::info('Cart is empty', ['farmer_id' => $user->id]);
                return response()->json([
                    'message' => 'Cart is empty!',
                    'status' => 201,
                    'data' => [],
                    'count' => $count,
                ], 200);
            }

        } catch (\Exception $e) {
            Log::error('Error in getCart', [
                'farmer_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error retrieving cart: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

     public function updateCart(Request $request)
    {
        Log::info('updateCart request', [
            'product_id' => $request->input('product_id'),
            'qty' => $request->input('qty'),
            'authentication_header' => $request->header('Authentication'),
        ]);

        // Validate inputs
        $token = $request->header('Authentication');
        $validator = Validator::make(array_merge($request->all(), ['Authentication' => $token]), [
            'product_id' => 'required|integer',
            'qty' => 'required|integer|min:1',
            // 'Authentication' => 'required|string',
        ], [
            'Authentication.required' => 'Authentication token is required',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ' . $validator->errors()->first());
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        try {
            // Authenticate user by token
            $user = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$user) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            $product_id = $request->input('product_id');
            $qty = $request->input('qty');

            // Check if cart exists for the farmer
            $cartItems = Cart::where('farmer_id', $user->id)->get();
            if ($cartItems->isEmpty()) {
                Log::info('Cart is empty', ['farmer_id' => $user->id]);
                return response()->json([
                    'message' => 'Cart is empty!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            // Verify product exists and is active
            $product = Product::where('id', $product_id)
                ->where('is_active', 1)
                ->first();

            if (!$product) {
                Log::warning('Product not found or inactive', ['product_id' => $product_id]);
                return response()->json([
                    'message' => 'Product Not Found!',
                    'status' => 201,
                    'data' => [],
                ], 404);
            }

            // Check inventory
            if ($product->inventory < $qty) {
                Log::warning('Product out of stock', ['product_id' => $product_id, 'requested_qty' => $qty]);
                return response()->json([
                    'message' => 'Product is out of Stock!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            // Check minimum quantity
            if ($product->min_qty && $qty < $product->min_qty) {
                Log::warning('Quantity below minimum', [
                    'product_id' => $product_id,
                    'requested_qty' => $qty,
                    'min_qty' => $product->min_qty,
                ]);
                return response()->json([
                    'message' => "Minimum Quantity should be {$product->min_qty}",
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            // Update cart item
            $cartItem = Cart::where('farmer_id', $user->id)
                ->where('product_id', $product_id)
                ->first();

            if (!$cartItem) {
                Log::warning('Product not in cart', ['farmer_id' => $user->id, 'product_id' => $product_id]);
                return response()->json([
                    'message' => 'Product not in cart!',
                    'status' => 201,
                    'data' => [],
                ], 404);
            }

            $cartItem->qty = $qty;
            $cartItem->save();

            // Calculate amount and total
            $amount = $product->selling_price * $qty;
            $total = 0;

            $cartItems = Cart::where('farmer_id', $user->id)->get();
            foreach ($cartItems as $cart) {
                $cartProduct = Product::where('id', $cart->product_id)
                    ->where('is_active', 1)
                    ->first();

                if ($cartProduct) {
                    $total += $cartProduct->selling_price * $cart->qty;
                } else {
                    // Remove invalid cart item
                    Cart::where('farmer_id', $user->id)
                        ->where('product_id', $cart->product_id)
                        ->delete();
                    Log::info('Removed invalid cart item', [
                        'farmer_id' => $user->id,
                        'product_id' => $cart->product_id,
                    ]);
                }
            }

            Log::info('Cart updated', [
                'farmer_id' => $user->id,
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

        } catch (\Exception $e) {
            Log::error('Error in updateCart', [
                'product_id' => $request->input('product_id'),
                'farmer_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error updating cart: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function removeCart(Request $request)
    {
        Log::info('removeCart request', [
            'product_id' => $request->input('product_id'),
            'token' => $request->bearerToken(),
            'authentication_header' => $request->header('Authentication'),
        ]);

        // Validate input
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ' . $validator->errors()->first());
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        try {
            // Get bearer token
            $token = $request->header('Authentication');
            if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }

            // Authenticate user by token
            $user = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$user) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            $product_id = $request->input('product_id');

            // Delete cart item
            $deleted = Cart::where('farmer_id', $user->id)
                ->where('product_id', $product_id)
                ->delete();

            // Get updated cart count
            $count = Cart::where('farmer_id', $user->id)->count();

            Log::info('Cart item removed', [
                'farmer_id' => $user->id,
                'product_id' => $product_id,
                'deleted' => $deleted,
                'cart_count' => $count,
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $count,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in removeCart', [
                'product_id' => $request->input('product_id'),
                'farmer_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error removing cart item: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function calculate(Request $request)
    {
        try {
            $token = $request->header('Authentication');
            if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }

            $user = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$user) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            // Fetch cart items
            $cartItems = Cart::where('farmer_id', $user->id)->get();
            if ($cartItems->isEmpty()) {
                Cart::where('farmer_id', $user->id)->delete();
                $count = Cart::where('farmer_id', $user->id)->count();
                return response()->json([
                    'message' => 'Cart is empty!',
                    'status' => 201,
                    'data' => [],
                    'count' => $count,
                ], 200);
            }

            $total = 0;
            $is_admin = 0;
            $charges = 0;
            $vendor_id = 0;
            $total_qty = 0;

            foreach ($cartItems as $cart) {
                $product = Product::where('id', $cart->product_id)
                    ->where('is_active', 1)
                    ->first();

                if (!$product) {
                    Cart::where('farmer_id', $user->id)
                        ->where('product_id', $cart->product_id)
                        ->delete();
                    continue;
                }

                // Check inventory
                if ($product->inventory < $cart->qty) {
                    return response()->json([
                        'message' => "{$product->name_english} is out of stock. Please remove this from cart!",
                        'status' => 201,
                    ], 200);
                }

                // Check minimum quantity
                if ($product->min_qty && $cart->qty < $product->min_qty) {
                    return response()->json([
                        'message' => "{$product->name_english} minimum quantity should be {$product->min_qty}",
                        'status' => 201,
                        'data' => [],
                    ], 200);
                }

                $is_admin = $cart->is_admin;
                $vendor_id = $product->added_by;
                $total += $product->selling_price * $cart->qty;
                $total_qty += $cart->qty;

                // Calculate charges for vendor products
                if ($cart->is_admin == 0) {
                    $charges += $cart->qty * config('constants.VENDOR_CHARGES', 10.00);
                }
            }

            // Calculate charges for admin products
            if ($is_admin == 1) {
                if ($total <= config('constants.ADMIN_AMOUNT', 500.00)) {
                    $charges = config('constants.ADMIN_CHARGES', 50.00);
                } else {
                    $charges = 0;
                }
            }

            // Calculate discount based on total quantity
            $discount = ($user->qty_discount ?? 0) * $total_qty;

            // Create order1 entry
            $order1Data = [
                'farmer_id' => $user->id,
                'is_admin' => $is_admin,
                'vendor_id' => $vendor_id,
                'total_amount' => $total,
                'charges' => $charges,
                'final_amount' => $total + $charges - $discount,
                'payment_status' => 0,
                'order_status' => 0,
                'date' => now(),
            ];

            $order1 = Order1::create($order1Data);

            // Create order2 entries
            foreach ($cartItems as $cart) {
                $product = Product::where('id', $cart->product_id)
                    ->where('is_active', 1)
                    ->first();

                if ($product) {
                    $order2Data = [
                        'main_id' => $order1->id,
                        'product_id' => $product->id,
                        'discount' => ($user->qty_discount ?? 0) * $cart->qty,
                        'product_name_en' => $product->name_english,
                        'product_name_hi' => $product->name_hindi,
                        'product_name_pn' => $product->name_punjabi,
                        'product_name_mr' => $product->name_marathi ?? '',
                        'image' => $product->image,
                        'qty' => $cart->qty,
                        'mrp' => $product->mrp,
                        'selling_price' => $product->selling_price,
                        'gst' => $product->gst ?? 0,
                        'gst_price' => $product->gst_price ?? 0,
                        'selling_price_wo_gst' => $product->selling_price_wo_gst ?? $product->selling_price,
                        'total_amount' => $product->selling_price * $cart->qty,
                        'date' => now(),
                    ];

                    Order2::create($order2Data);
                }
            }

            $send = [
                'order_id' => $order1->id,
                'total' => $total,
                'charges' => $charges,
                'final' => $total + $charges - $discount,
                'discount' => $discount,
            ];

            Log::info('Order calculated', [
                'farmer_id' => $user->id,
                'order_id' => $order1->id,
                'total' => $total,
                'charges' => $charges,
                'discount' => $discount,
                'final_amount' => $total + $charges - $discount,
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $send,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in calculate', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error calculating order: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function checkout(Request $request)
    {
        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('Checkout auth attempt', [
                'user_id' => $user ? $user->id : null,
                'is_active' => $user ? ($user->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
            ]);

            if (!$user || !$user->is_active) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|integer',
                'name' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|integer',
                'district' => 'required|string',
                'pincode' => 'required|string',
                'phone' => 'required|string',
                'cod' => 'required|in:0,1,2',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Fetch cart items
            $cartItems = Cart::where('farmer_id', $user->id)->get();
            if ($cartItems->isEmpty()) {
                Cart::where('farmer_id', $user->id)->delete();
                $count = Cart::where('farmer_id', $user->id)->count();
                return response()->json([
                    'message' => 'Cart is empty!',
                    'status' => 201,
                    'data' => [],
                    'count' => $count,
                ], 200);
            }

            // Validate cart items
            foreach ($cartItems as $cart) {
                $product = Product::where('id', $cart->product_id)
                    ->where('is_active', 1)
                    ->first();

                if (!$product) {
                    Cart::where('farmer_id', $user->id)
                        ->where('product_id', $cart->product_id)
                        ->delete();
                    continue;
                }

                if ($product->inventory < $cart->qty) {
                    return response()->json([
                        'message' => "{$product->name_english} is out of stock. Please remove this from cart!",
                        'status' => 201,
                    ], 200);
                }

                if ($product->min_qty && $cart->qty < $product->min_qty) {
                    return response()->json([
                        'message' => "{$product->name_english} minimum quantity should be {$product->min_qty}",
                        'status' => 201,
                        'data' => [],
                    ], 200);
                }
            }

            // Fetch state details
            $state = State::find($request->state);
            $state_detail = $state ? preg_replace('/\s*\[.*?\]/', '', $state->state_name) : '';

            $order_id = $request->order_id;
            $name = $request->name;
            $address = $request->address;
            $city = $request->city;
            $district = $request->district;
            $pincode = $request->pincode;
            $phone = $request->phone;
            $cod = $request->cod;

            $order1 = Order1::find($order_id);
            if (!$order1 || $order1->farmer_id != $user->id) {
                return response()->json([
                    'message' => 'Invalid order ID!',
                    'status' => 201,
                ], 404);
            }

            $success_url = route('payment.success');
            $fail_url = route('payment.failed');

            if ($cod == 0) {
                // CC Avenue
                $txn_id = mt_rand(999999, 999999999999);
                $order1->update([
                    'txn_id' => $txn_id,
                    'name' => $name,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state_detail,
                    'district' => $district,
                    'pincode' => $pincode,
                    'phone' => $phone,
                    'gateway' => 'CC Avenue',
                ]);

                $post = [
                    'txn_id' => $txn_id,
                    'merchant_id' => config('constants.MERCHAND_ID'),
                    'order_id' => $order_id,
                    'amount' => $order1->final_amount,
                    'currency' => 'INR',
                    'redirect_url' => $success_url,
                    'cancel_url' => $fail_url,
                    'billing_name' => $name,
                    'billing_address' => $address,
                    'billing_city' => $city,
                    'billing_state' => $state_detail,
                    'billing_zip' => $pincode,
                    'billing_country' => 'India',
                    'billing_tel' => $phone,
                    'billing_email' => '',
                    'merchant_param1' => 'Order Payment',
                ];

                $merchant_data = '';
                $working_key = config('constants.WORKING_KEY');
                $access_code = config('constants.ACCESS_CODE');

                foreach ($post as $key => $value) {
                    $merchant_data .= $key . '=' . $value . '&';
                }

                $key = pack('H*', md5($working_key));
                $initVector = pack('C*', 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
                $encrypted_data = bin2hex(openssl_encrypt($merchant_data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector));

                $send = [
                    'order_id' => $order_id,
                    'access_code' => $access_code,
                    'redirect_url' => $success_url,
                    'cancel_url' => $fail_url,
                    'enc_val' => $encrypted_data,
                    'plain' => $merchant_data,
                    'merchant_param1' => 'Order Payment',
                ];

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $send,
                ], 200);
            } elseif ($cod == 2) {
                // Razorpay
                $txn_id = mt_rand(999999, 999999999999);
                $order1->update([
                    'txn_id' => $txn_id,
                    'name' => $name,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state_detail,
                    'district' => $district,
                    'pincode' => $pincode,
                    'phone' => $phone,
                    'gateway' => 'Razorpay',
                ]);

                $api = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));

                $orderData = [
                    'receipt' => (string) $txn_id,
                    'amount' => $order1->final_amount * 100,
                    'currency' => 'INR',
                    'payment_capture' => 1,
                ];

                $razorpayOrder = $api->order->create($orderData);
                $razorpay_order_id = $razorpayOrder['id'];

                $send = [
                    'order_id' => $razorpay_order_id,
                    'amount' => $order1->final_amount,
                    'currency' => 'INR',
                    'name' => $name,
                    'email' => '',
                    'contact' => $phone,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state_detail,
                    'zip' => $pincode,
                    'success_url' => $success_url,
                    'failure_url' => $fail_url,
                    'merchant_param1' => 'Order Payment',
                    'razorpay_key' => config('services.razorpay.key_id'),
                    'razorpay_order_id' => $razorpay_order_id,
                ];

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $send,
                ], 200);
            } else {
                // COD
                if (!$user->cod) {
                    return response()->json([
                        'message' => 'Orders cannot be placed with COD!',
                        'status' => 201,
                    ], 403);
                }

                $order1->update([
                    'name' => $name,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state_detail,
                    'district' => $district,
                    'pincode' => $pincode,
                    'phone' => $phone,
                ]);

                if ($order1->payment_status != 0) {
                    return response()->json([
                        'message' => 'Order already processed!',
                        'status' => 201,
                    ], 400);
                }

                // Generate invoice
                $now = now()->format('y');
                $next = now()->addYear()->format('y');
                $invoice_year = "$now-$next";
                $last_order = Order1::where('payment_status', 2)
                    ->where('invoice_year', $invoice_year)
                    ->orderBy('id', 'desc')
                    ->first();

                $invoice_no = $last_order ? $last_order->invoice_no + 1 : 1;

                $order1->update([
                    'payment_status' => 2,
                    'order_status' => 1,
                    'invoice_year' => $invoice_year,
                    'invoice_no' => $invoice_no,
                ]);

                // Update inventory
                $order2_items = Order2::where('main_id', $order_id)->get();
                foreach ($order2_items as $item) {
                    $product = Product::where('id', $item->product_id)
                        ->where('is_active', 1)
                        ->first();

                    if ($product) {
                        $new_inventory = $product->inventory - $item->qty;
                        InventoryTxn::create([
                            'order_id' => $order_id,
                            'at_time' => $product->inventory,
                            'less_inventory' => $item->qty,
                            'updated_inventory' => $new_inventory,
                            'date' => now(),
                        ]);

                        $product->update(['inventory' => $new_inventory]);
                    }
                }

                // Handle vendor/admin logic
                if ($order1->is_admin == 0) {
                    $vendor = Vendor::find($order1->vendor_id);
                    if ($vendor && $vendor->comission) {
                        $amt = $order1->total_amount * $vendor->comission / 100;
                        PaymentTransaction::create([
                            'req_id' => $order_id,
                            'vendor_id' => $order1->vendor_id,
                            'cr' => $order1->total_amount - $amt,
                            'date' => now(),
                        ]);

                        $vendor->update([
                            'account' => $vendor->account + $order1->total_amount - $amt,
                        ]);

                        if ($vendor->fcm_token) {
                            $client = new Client();
                            $response = $client->post('https://fcm.googleapis.com/fcm/send', [
                                'headers' => [
                                    'Authorization' => 'key=' . config('services.fcm.server_key'),
                                    'Content-Type' => 'application/json',
                                ],
                                'json' => [
                                    'to' => $vendor->fcm_token,
                                    'notification' => [
                                        'title' => 'New Order',
                                        'body' => "New order #{$order_id} received with the amount of ₹{$order1->final_amount}",
                                        'sound' => 'default',
                                    ],
                                    'priority' => 'high',
                                ],
                            ]);

                            VendorNotification::create([
                                'vendor_id' => $order1->vendor_id,
                                'name' => 'New Order',
                                'dsc' => "New order #{$order_id} received with the amount of ₹{$order1->final_amount}",
                                'date' => now(),
                            ]);
                        }
                    }
                } else {
                    // Send email to admin
                    Mail::send([], [], function ($message) use ($order1) {
                        $message->to(config('mail.to.address'), 'Dairy Muneem')
                            ->subject('New Order received')
                            ->setBody(
                                "Hello Admin<br/><br/>You have received new Order and below are the details<br/><br/>" .
                                "<b>Order ID</b> - {$order1->id}<br/>" .
                                "<b>Amount</b> - Rs.{$order1->final_amount}<br/>",
                                'text/html'
                            );
                    });

                    // Placeholder for WhatsApp message
                    $this->sendWhatsAppMsgAdmin($order1, $user);
                }

                // Clear cart
                Cart::where('farmer_id', $user->id)->delete();

                $send = [
                    'order_id' => $order_id,
                    'final_amount' => $order1->final_amount,
                ];

                Log::info('Checkout completed', [
                    'farmer_id' => $user->id,
                    'order_id' => $order_id,
                    'payment_method' => 'COD',
                    'final_amount' => $order1->final_amount,
                ]);

                return response()->json([
                    'message' => 'Success',
                    'status' => 200,
                    'data' => $send,
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error in checkout', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'order_id' => $request->order_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error processing checkout: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    protected function sendWhatsAppMsgAdmin($order1, $user)
    {
        // Implement WhatsApp API integration here
        Log::info('WhatsApp message placeholder', [
            'order_id' => $order1->id,
            'farmer_id' => $user->id,
        ]);
    }

    public function paymentSuccess(Request $request)
    {
        Log::info('Payment success callback', [
            'request' => $request->all(),
        ]);
        // Implement CC Avenue/Razorpay verification
        return response()->json([
            'message' => 'Payment success callback received',
            'status' => 200,
        ], 200);
    }

    public function paymentFailed(Request $request)
    {
        Log::info('Payment failed callback', [
            'request' => $request->all(),
        ]);
        // Handle failure
        return response()->json([
            'message' => 'Payment failed callback received',
            'status' => 201,
        ], 200);
    }
}