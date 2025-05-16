<?php

use App\Http\Controllers\ApiControllers\FarmerController;
use App\Http\Controllers\ApiControllers\BreedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\UserloginController;
use App\Http\Controllers\ApiControllers\FeedController;
use App\Http\Controllers\ApiControllers\DoctorController;
use App\Http\Controllers\ApiControllers\ToolsController;
use App\Http\Controllers\ApiControllers\VendorOrderController;
use App\Http\Controllers\ApiControllers\HomeController;
use App\Http\Controllers\ApiControllers\ManagementController;
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

Route::post('register-farmer', [UserloginController::class, 'farmer_register_process']);

Route::post('registerotpverify', [UserloginController::class, 'farmer_register_otp_verify']);

////
Route::post('Login_process', [UserloginController::class, 'farmer_login_process']);
////

Route::post('loginVerify_otp', [UserloginController::class, 'farmer_login_otp_verify']);


Route::post('register', [UserloginController::class, 'registerWithOtp']);
Route::post('userRegisterOtpVerify', [UserloginController::class, 'register_otp_verify']);
Route::post('login', [UserloginController::class, 'login_process']);
// Route::post('LoginOtpVerify', [UserloginController::class, 'login_otp_verify']);
Route::post('LoginOtpVerify', [UserloginController::class, 'verify_login_otp']);
    // Add e-commerce routes here
    // Route::get('/products', [ProductController::class, 'index']);

Route::middleware(['auth:farmer'])->group(function () {
    Route::post('/profile/update', [UserloginController::class, 'update_profile']);
    Route::post('/logout', [UserloginController::class, 'logout']);
    // Route::get('/products', [ProductController::class, 'index']);



    //////////FarmerController
    // Route::get('GetCart', [FarmerController::class, 'getCart']);


    //////BreedController

    ///////FeedController
    Route::post('FeedTest', [FeedController::class, 'feedTest']);
    Route::post('checkMyFeed', [FeedController::class, 'checkMyFeed']);
    Route::get('DairyMart', [FeedController::class, 'dairyMart']);
    Route::get('ExpertAdvice', [FeedController::class, 'expertAdvice']);
    Route::get('RadiusVendor', [FeedController::class, 'radiusVendor']);
    Route::post('BuyFeed', [FeedController::class, 'buyFeed']);


    /////////toolsController
    Route::post('project-requirements', [ToolsController::class, 'projectRequirements']);
    Route::post('project-test', [ToolsController::class, 'projectTest']);
    Route::post('snf-calculator', [ToolsController::class, 'snfCalculator']);
    Route::post('calculate-distance', [ToolsController::class, 'calculateDistance']);
    Route::post('expert-advice', [ToolsController::class, 'expertAdvice']);
    Route::post('ReqDoc', [ToolsController::class, 'requestDoctor']);
    Route::get('expert-category', [ToolsController::class, 'expertCategory']);
    // The above api wont work beacuse of false CC avenue key




    //////////////HomeController
    Route::post('homeSubscription/buy', [HomeController::class, 'subscriptionPlan']);
    Route::post('homeBuy/plan', [HomeController::class, 'buyPlan']);
    Route::post('homePhonePay/buyPlan', [HomeController::class, 'phonePeBuyPlan']);
    Route::get('homeplan/payment-sucess', [HomeController::class, 'planPaymentSuccess']);
    Route::get('homePhonePay/payment-sucess', [HomeController::class, 'phonePePlanPaymentSuccess']);
    
    
    ///////managementController
    Route::post('others-sale-purchase-View', [ManagementController::class, 'viewOthersSalePurchase']);
    Route::post('update-animal-status', [ManagementController::class, 'updateAnimalStatus']);
    Route::post('sale-purchase-update', [ManagementController::class, 'salePurchaseUpdate']);
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


    //////////vendorORderController
    Route::post('add/to/cart', [VendorOrderController::class, 'addToCart']);
    Route::get('get/cart', [VendorOrderController::class, 'getCart']);
    Route::post('product/detail', [VendorOrderController::class, 'getProductDetails']);
    Route::post('update/cart', [VendorOrderController::class, 'updateCart']);
    Route::post('remove/cart', [VendorOrderController::class, 'removeCart']);
    Route::post('calculate/cart', [VendorOrderController::class, 'calculate']);
    Route::get('get/orders', [VendorOrderController::class, 'getOrders']);
    Route::post('vendor/checkout', [VendorOrderController::class, 'checkout']);
    
});
Route::post('paymentSuccess', [FarmerController::class, 'paymentSuccess'])->name('payment.success');
Route::post('paymentfailed', [FarmerController::class, 'paymentFailed'])->name('payment.failed');

    Route::get('home/data', [HomeController::class, 'homeData']);
    
    Route::post('AddtoCartFarmer', [FarmerController::class, 'addToCart']);

    
    Route::post('RemoveCartItems', [FarmerController::class, 'removeCart']);

    Route::get('GetFarmerCart', [FarmerController::class, 'getCart']);

    
    Route::post('UpdateCartItems', [FarmerController::class, 'updateCart']);

    
    Route::get('FarmerCalculate', [FarmerController::class, 'calculate']);

    
    Route::post('FarmerCheckOut', [FarmerController::class, 'checkout']);

    
    Route::get('homeGetState', [HomeController::class, 'getState']);

    
    Route::get('homeGetcity/{state_id}', [HomeController::class, 'getCity']);

    
    Route::get('Disease-Info', [ManagementController::class, 'diseaseInfo']);

    
    Route::post('WeightCalculate', [FeedController::class, 'calculateWeight']);

    
    Route::post('DMIcalculator', [FeedController::class, 'dmiCalculator']);

    
    Route::post('AnimalRequirement', [FeedController::class, 'animalRequirements']);

    
    Route::post('FeedCalculator', [FeedController::class, 'feedCalculator']);

    
    Route::post('homeCreateGroup', [HomeController::class, 'createGroup']);

    
    Route::get('homeGetgroup', [HomeController::class, 'getGroup']);
   
   
    Route::post('homeUpdategroup', [HomeController::class, 'updateGroup']);


    Route::post('homeDeletegroup', [HomeController::class, 'deleteGroup']);

    Route::post('BreedMyAnimal', [BreedController::class, 'myAnimal']);

    
    Route::post('homeGetCattle', [HomeController::class, 'getCattle']);

    
    Route::post('homeGetTagNumber', [HomeController::class, 'getTagNo']);

    
    Route::post('BreedHealthInfo', [BreedController::class, 'healthInfo']);

    
    Route::get('BreedViewHealth', [BreedController::class, 'viewHealthInfo']);

    
    Route::post('homeAnimalData', [HomeController::class, 'getAnimalData']);

    
    Route::get('homeGetBull-Tag-No', [HomeController::class, 'getBullTagNo']);

    
    Route::get('homeGetSemenBulls', [HomeController::class, 'getSemenBulls']);

    
    Route::post('homeBreedingRecord', [BreedController::class, 'breedingRecord']);

    
    Route::get('BreedViewRecord', [BreedController::class, 'viewBreedingRecord']);

    
    Route::post('ManagementDaily-records', [ManagementController::class, 'dailyRecords']);

    
    Route::get('ManagementViewRecords', [ManagementController::class, 'viewDailyRecords']);

    
    Route::post('ManagementMilkRecords', [ManagementController::class, 'milkRecords']);

    
    Route::get('ManagementViewMilk-records', [ManagementController::class, 'viewMilkRecords']);


    Route::post('homeMilkTagNo', [HomeController::class, 'getMilkingTagNo']);

    
    Route::post('ManagementMedical-expenses', [ManagementController::class, 'medicalExpenses']);

    
    Route::get('ManagementViewmedical-expenses', [ManagementController::class, 'viewMedicalExpenses']);

    
    Route::post('ManagementReportss', [ManagementController::class, 'reports']);

    
    Route::get('Managementfarm_Summary', [ManagementController::class, 'farmSummary']);

    
    Route::post('ManagementSale-purchase', [ManagementController::class, 'salePurchase']);

    
    Route::get('ManagementViewsale-purchase', [ManagementController::class, 'viewSalePurchase']);

    
    Route::post('ManagementGet-animals', [ManagementController::class, 'getAnimals']);

    
    Route::post('ManagementStock-Handling', [ManagementController::class, 'stockHandling']);

    
    Route::get('ManagementView-Stock-handling', [ManagementController::class, 'viewStocks']);

    
    Route::get('Managementview-Stock-Txn', [ManagementController::class, 'viewStocksTxn']);

    
    Route::post('ManagementSemenTank-add', [ManagementController::class, 'addSemenTank']);

    
    Route::get('ManagementView-Semen-Tank', [ManagementController::class, 'viewSemenTank']);

    
    Route::post('ManagementCanister_updates', [ManagementController::class, 'updateCanister']);

    
    Route::post('Management-Delete-semen-Tank', [ManagementController::class, 'deleteSemenTank']);


    Route::post('Equipment-Sale-Purchase', [ManagementController::class, 'equipmentSalePurchase']);
    
    
    Route::get('ViewEquipment-Sale-Purchase', [ManagementController::class, 'viewEquipmentSalePurchase']);

    
    Route::post('ToolsSilageMaking', [ToolsController::class, 'silageMaking']);

    
    Route::post('Toolspregnancy-calculator', [ToolsController::class, 'pregnancyCalculator']);

    
    Route::post('Tools-AllProducts', [ToolsController::class, 'allProducts']);

    
    Route::post('FeedDoctorOnCall', [FeedController::class, 'doctorOnCall']);

    
    Route::post('Tools-doctor-call', [ToolsController::class, 'DoctorOnCall']);