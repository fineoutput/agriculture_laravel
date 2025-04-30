<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Order2;
use App\Models\Order1;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class FarmerController extends Controller
{
    public function addToCart(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'vendor_id' => 'required|integer',
            'is_admin' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            if (!$user || !$user->is_active) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Validate token against farmers.token (optional, handled by check.token middleware)
            if ($user->auth !== $request->bearerToken()) {
                return response()->json([
                    'message' => 'Invalid token!',
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
                return response()->json([
                    'message' => 'Product is already in your cart!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            $product = Product::where('id', $product_id)
                ->where('is_active', 1)
                ->first();

            if (!$product) {
                return response()->json([
                    'message' => 'Product Not Found!',
                    'status' => 201,
                    'data' => [],
                ], 404);
            }

            if ($product->inventory <= 0) {
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
            ]);

            return response()->json([
                'message' => 'Product Successfully Added!',
                'status' => 200,
                'data' => $cartCount,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in addToCart', [
                'product_id' => $request->input('product_id'),
                'farmer_id' => auth('farmer')->id() ?? null,
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
        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('GetCart auth attempt', [
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

            // Validate language parameter
            $lang = $request->query('lang', 'en');
            if (!in_array($lang, ['en', 'hi', 'pn'])) {
                return response()->json([
                    'message' => 'Invalid language! Use en, hi, or pn.',
                    'status' => 201,
                ], 400);
            }

            // Fetch cart items
            $cartItems = Cart::where('farmer_id', $user->id)->get();
            $data = [];
            $total = 0;

            if ($cartItems->isNotEmpty()) {
                foreach ($cartItems as $cart) {
                    // Fetch product (admin or vendor, same table)
                    $product = Product::where('id', $cart->product_id)
                        ->where('is_active', 1)
                        ->first();

                    if ($product) {
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
                    }
                }

                // Get cart count
                $count = Cart::where('farmer_id', $user->id)->count();

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $data,
                    'count' => $count,
                    'total' => $total,
                ], 200);
            } else {
                $count = Cart::where('farmer_id', $user->id)->count();
                return response()->json([
                    'message' => 'Cart is empty!',
                    'status' => 201,
                    'data' => [],
                    'count' => $count,
                ], 200);
            }

        } catch (\Exception $e) {
            Log::error('Error in getCart', [
                'farmer_id' => auth('farmer')->id() ?? null,
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
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('UpdateCart auth attempt', [
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

            $product_id = $request->input('product_id');
            $qty = $request->input('qty');

            // Check if cart exists for the farmer
            $cartItems = Cart::where('farmer_id', $user->id)->get();
            if ($cartItems->isEmpty()) {
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
                return response()->json([
                    'message' => 'Product Not Found!',
                    'status' => 201,
                    'data' => [],
                ], 404);
            }

            // Check inventory
            if ($product->inventory < $qty) {
                return response()->json([
                    'message' => 'Product is out of Stock!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }

            // Check minimum quantity
            if ($product->min_qty && $qty < $product->min_qty) {
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
                'farmer_id' => auth('farmer')->id() ?? null,
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
        // Validate input
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('RemoveCart auth attempt', [
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
                'farmer_id' => auth('farmer')->id() ?? null,
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
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('Calculate auth attempt', [
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
}