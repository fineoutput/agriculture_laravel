<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

            // Authenticate vendor
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('AddToCart: Auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'product_id' => $product_id,
                'is_admin' => $is_admin,
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
            ]);

            if (!$vendor || !$vendor->is_active) {
                Log::warning('AddToCart: Authentication failed or vendor inactive', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'product_id' => $product_id,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

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
                'vendor_id' => auth('vendor')->id() ?? null,
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
                'vendor_id' => auth('vendor')->id() ?? null,
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
}
