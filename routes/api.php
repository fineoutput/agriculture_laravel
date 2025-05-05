<?php

use App\Http\Controllers\ApiControllers\FarmerController;
use App\Http\Controllers\ApiControllers\BreedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\UserloginController;
use App\Http\Controllers\ApiControllers\FeedController;
use App\Http\Controllers\ApiControllers\DoctorController;
use App\Http\Controllers\ApiControllers\ToolsController;
use App\Http\Controllers\ApiControllers\VendorController;
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
Route::post('login', [UserloginController::class, 'login_process']);
Route::post('LoginOtpVerify', [UserloginController::class, 'login_otp_verify']);
    // Add e-commerce routes here
    // Route::get('/products', [ProductController::class, 'index']);

Route::middleware(['auth:farmer'])->group(function () {
    Route::post('/profile/update', [UserloginController::class, 'update_profile']);
    Route::post('/logout', [UserloginController::class, 'logout']);
    // Route::get('/products', [ProductController::class, 'index']);



    //////////FarmerController
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

    ///////FeedController
    Route::post('WeightCalculate', [FeedController::class, 'calculateWeight']);
    Route::post('DMIcalculator', [FeedController::class, 'dmiCalculator']);
    Route::post('FeedTest', [FeedController::class, 'feedTest']);
    Route::post('FeedCalculator', [FeedController::class, 'feedCalculator']);
    Route::post('AnimalRequirement', [FeedController::class, 'animalRequirements']);
    Route::post('checkMyFeed', [FeedController::class, 'checkMyFeed']);
    Route::get('DairyMart', [FeedController::class, 'dairyMart']);
    Route::get('DoctorOnCall', [FeedController::class, 'doctorOnCall']);
    Route::get('ExpertAdvice', [FeedController::class, 'expertAdvice']);
    Route::get('RadiusVendor', [FeedController::class, 'radiusVendor']);
    Route::post('BuyFeed', [FeedController::class, 'buyFeed']);


    /////////toolsController
    Route::post('SilageMaking', [ToolsController::class, 'silageMaking']);
    Route::post('project-requirements', [ToolsController::class, 'projectRequirements']);
    Route::post('project-test', [ToolsController::class, 'projectTest']);
    Route::post('pregnancy-calculator', [ToolsController::class, 'pregnancyCalculator']);
    Route::post('snf-calculator', [ToolsController::class, 'snfCalculator']);
    Route::post('products-all', [ToolsController::class, 'allProducts']);
    Route::post('calculate-distance', [ToolsController::class, 'calculateDistance']);
    Route::post('doctor-call', [ToolsController::class, 'doctorOnCall']);
    Route::post('expert-advice', [ToolsController::class, 'expertAdvice']);
    Route::post('ReqDoc', [ToolsController::class, 'requestDoctor']);
    Route::get('expert-category', [ToolsController::class, 'expertCategory']);
    // The above api wont work beacuse of false CC avenue key
});

///////////DoctorController

Route::middleware(['auth:doctor'])->group(function () {
Route::get('GetRequests', [DoctorController::class, 'getRequests']);
Route::post('doctor/requests/{id}/complete', [DoctorController::class, 'reqMarkComplete']);
Route::get('Profile', [DoctorController::class, 'getProfile']);
Route::post('UpdateProf', [DoctorController::class, 'updateProfile']);
Route::post('bank-info', [DoctorController::class, 'updateBankInfo']);
Route::post('LocationUpd', [DoctorController::class, 'updateLocation']);
Route::get('PaymentInfo', [DoctorController::class, 'paymentInfo']);
Route::get('AdminPay', [DoctorController::class, 'adminPaymentInfo']);
Route::get('Tank', [DoctorController::class, 'semenTanks']);
Route::post('Add', [DoctorController::class, 'addSemenTank']);
Route::delete('doctor/semen-tanks/{id}', [DoctorController::class, 'deleteSemenTank']);
Route::put('doctor/canisters/{canister_id}', [DoctorController::class, 'updateCanister']);
Route::post('SellSemen', [DoctorController::class, 'sellSemen']);
Route::get('SemenTransaction', [DoctorController::class, 'getSemenTransactions']);
});



Route::middleware(['auth:vendor'])->group(function () {
    Route::post('products-all', [ToolsController::class, 'vendorAllProducts']);
    Route::post('Vendors', [ToolsController::class, 'getVendors']);

    ////vendorController
    Route::get('vendorordersnew', [VendorController::class, 'newOrders']);
    Route::get('VendorOrdersAcepted', [VendorController::class, 'acceptedOrders']);
    Route::get('VendorOrdersDispatched', [VendorController::class, 'dispatchedOrders']);
    Route::get('CompletedORdes', [VendorController::class, 'completedOrders']);
    Route::get('CancelORd', [VendorController::class, 'cancelledOrders']);
    Route::post('update-status', [VendorController::class, 'updateOrderStatus']);
    Route::get('vendorpayment-info', [VendorController::class, 'paymentInfo']);
    Route::get('admin-payment-info', [VendorController::class, 'adminPaymentInfo']);
    Route::post('Product-add', [VendorController::class, 'addVendorProduct']);
    Route::get('Data-Home', [VendorController::class, 'homeData']);
    Route::get('vendor/profile', [VendorController::class, 'getVendorProfile']);
    Route::post('update/Bank-info', [VendorController::class, 'updateBankInfo']);
    Route::post('update/vendor-profile', [VendorController::class, 'updateProfile']);
    Route::post('store/slider', [VendorController::class, 'storeSlider']);
    Route::get('view/slider', [VendorController::class, 'viewVendorSliders']);
    Route::post('delete/slider', [VendorController::class, 'deleteVendorSlider']);
    Route::delete('delete/vendor', [VendorController::class, 'deleteAccount']);
});
Route::post('paymentSuccess', [FarmerController::class, 'paymentSuccess'])->name('payment.success');
Route::post('paymentfailed', [FarmerController::class, 'paymentFailed'])->name('payment.failed');
