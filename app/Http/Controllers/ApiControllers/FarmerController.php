<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
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

    
}