<?php

use App\Http\Controllers\ApiControllers\FarmerController;
use App\Http\Controllers\ApiControllers\BreedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\UserloginController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/msgtest', [UserloginController::class, 'msgtest']);
Route::post('farmerRegister', [UserloginController::class, 'farmer_register_process']);
Route::post('registerotpverify', [UserloginController::class, 'farmer_register_otp_verify']);
Route::post('farmerlogin', [UserloginController::class, 'farmer_login_process']);
Route::post('farmerLoginOtp-verify', [UserloginController::class, 'farmer_login_otp_verify']);
Route::post('register', [UserloginController::class, 'registerWithOtp']);
Route::post('userRegisterOtpVerify', [UserloginController::class, 'register_otp_verify']);
Route::post('/login', [UserloginController::class, 'login_process']);
Route::post('/login/otp-verify', [UserloginController::class, 'login_otp_verify']);
    // Add e-commerce routes here
    // Route::get('/products', [ProductController::class, 'index']);

Route::middleware(['auth:farmer'])->group(function () {
    Route::post('/profile/update', [UserloginController::class, 'update_profile']);
    Route::post('/logout', [UserloginController::class, 'logout']);
    // Route::get('/products', [ProductController::class, 'index']);
    Route::post('AddtoCart', [FarmerController::class, 'addToCart']);
    Route::get('GetCart', [FarmerController::class, 'getCart']);
    Route::post('UpdateCartItems', [FarmerController::class, 'updateCart']);
    Route::post('RemoveCartItems', [FarmerController::class, 'removeCart']);
    Route::get('Calculate', [FarmerController::class, 'calculate']);
    Route::post('CheckOut', [FarmerController::class, 'checkout']);


    //////BreedController
    Route::post('healthInfo', [BreedController::class, 'healthInfo']);
    Route::get('ViewHealth', [BreedController::class, 'viewHealthInfo']);
    Route::post('BreedingRecord', [BreedController::class, 'breedingRecord']);
    Route::get('ViewRecord', [BreedController::class, 'viewBreedingRecord']);
});

Route::post('paymentSuccess', [FarmerController::class, 'paymentSuccess'])->name('payment.success');
Route::post('paymentfailed', [FarmerController::class, 'paymentFailed'])->name('payment.failed');
