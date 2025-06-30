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

////////////vendor register and login start

Route::post('register', [UserloginController::class, 'registerWithOtp']);
Route::post('userRegisterOtpVerify', [UserloginController::class, 'register_otp_verify']);

Route::post('login', [UserloginController::class, 'login_process']);
// Route::post('LoginOtpVerify', [UserloginController::class, 'login_otp_verify']);
Route::post('LoginOtpVerify', [UserloginController::class, 'verify_login_otp']);


////////////vendor register and login end


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
    Route::get('DairyMart', [FeedController::class, 'dairyMart']);
    Route::get('ExpertAdvice', [FeedController::class, 'expertAdvice']);
    Route::get('RadiusVendor', [FeedController::class, 'radiusVendor']);


    /////////toolsController
    Route::post('calculate-distance', [ToolsController::class, 'calculateDistance']);
    Route::post('ReqDoc', [ToolsController::class, 'requestDoctor']);
    // The above api wont work beacuse of false CC avenue key




    //////////////HomeController
    Route::post('homeSubscription/buy', [HomeController::class, 'subscriptionPlan']);
    Route::post('homeBuy/plan', [HomeController::class, 'buyPlan']);
    Route::post('homePhonePay/buyPlan', [HomeController::class, 'phonePeBuyPlan']);
    Route::get('homeplan/payment-sucess', [HomeController::class, 'planPaymentSuccess']);
    Route::get('homePhonePay/payment-sucess', [HomeController::class, 'phonePePlanPaymentSuccess']);
    
    
    ///////managementController
    Route::post('update-animal-status', [ManagementController::class, 'updateAnimalStatus']);
    Route::post('sale-purchase-update', [ManagementController::class, 'salePurchaseUpdate']);
});

///////////DoctorController

Route::middleware(['auth:doctor'])->group(function () {
Route::post('bank-info', [DoctorController::class, 'updateBankInfo']);
Route::post('LocationUpd', [DoctorController::class, 'updateLocation']);
Route::get('PaymentInfo', [DoctorController::class, 'paymentInfo']);
Route::get('AdminPay', [DoctorController::class, 'adminPaymentInfo']);
Route::get('SemenTransaction', [DoctorController::class, 'getSemenTransactions']);
});



Route::middleware(['auth:vendor'])->group(function () {

    Route::get('CancelORd', [VendorController::class, 'cancelledOrders']);
    Route::get('vendorpayment-info', [VendorController::class, 'paymentInfo']);
    Route::get('admin-payment-info', [VendorController::class, 'adminPaymentInfo']);
    Route::post('update/Bank-info', [VendorController::class, 'updateBankInfo']);
    Route::delete('delete/vendor', [VendorController::class, 'deleteAccount']);


    //////////vendorORderController
    
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



    Route::get('FarmerGet-Orders', [FarmerController::class, 'getOrders']);

    
    Route::get('homeGetState', [HomeController::class, 'getState']);

    
    Route::get('homeGetcity/{state_id}', [HomeController::class, 'getCity']);

    
    Route::get('Disease-Info', [ManagementController::class, 'diseaseInfo']);

    
    Route::post('WeightCalculate', [FeedController::class, 'calculateWeight']);

    
    Route::post('DMIcalculator', [FeedController::class, 'dmiCalculator']);

    
    Route::post('AnimalRequirement', [FeedController::class, 'animalRequirements']);

    Route::post('test-animalRequirements', [FeedController::class, 'testAnimalRequirements']);

    
    Route::post('FeedCalculator', [FeedController::class, 'feed_calculator']);

    
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

    
    Route::post('Tools-doctorOn-call', [ToolsController::class, 'DoctorOnCall']);

    
    Route::post('Tools-expert-advice', [ToolsController::class, 'toolsExpertAdvice']);

    
    Route::get('Tools-expert-category', [ToolsController::class, 'expertCategory']);

    
    Route::post('Tools-GetVendors', [ToolsController::class, 'getVendors']);

    
    Route::post('Tools-project-requirements', [ToolsController::class, 'projectRequirements']);

    
    Route::post('Tools-snf-calculator', [ToolsController::class, 'snfCalculator']);


    Route::get('SalePurchase-Option-images', [HomeController::class, 'getOptionSalePurchaseImages']);


    Route::get('home-SalePurchase-Slider', [HomeController::class, 'getSalePurchaseSliders']);

    
    Route::get('farmer-GetProfile', [FarmerController::class, 'getFarmerProfile']);


    Route::post('farmer-Update-Profile', [FarmerController::class, 'updateFarmerProfile']);

    
    Route::post('OtherSalePurchase-view', [ManagementController::class, 'viewOthersSalePurchase']);

    
    Route::post('CheckFeed-My', [FeedController::class, 'checkMyFeed']);
    
    Route::post('project-test', [ToolsController::class, 'projectTest']);

    
    Route::post('Buy-Feed', [FeedController::class, 'buyFeed']);



    
    ////vendorController
    Route::get('vendorordersnew', [VendorController::class, 'newOrders']);

    
    Route::get('Data-Home', [VendorController::class, 'homeData']);

    
    Route::get('view/slider', [VendorController::class, 'viewVendorSliders']);

    
    Route::get('vendor/profile', [VendorController::class, 'getVendorProfile']);

    
    Route::post('update/vendor-profile', [VendorController::class, 'updateProfile']);

    
    Route::post('store/slider', [VendorController::class, 'storeSlider']);

    
    Route::post('Delete-Vendor_slider', [VendorController::class, 'deleteVendorSlider']);

    
    Route::post('Product-add', [VendorController::class, 'addVendorProduct']);

    
    Route::post('Vendor-products-all', [ToolsController::class, 'vendorAllProducts']);


    Route::get('Vendor_products', [VendorController::class, 'vendorProducts']);

    
    Route::get('VendorOrdersAcepted', [VendorController::class, 'acceptedOrders']);

    
    Route::get('VendorOrdersDispatched', [VendorController::class, 'dispatchedOrders']);

    
    Route::get('CompletedORdes', [VendorController::class, 'completedOrders']);

    
    Route::post('update-status', [VendorController::class, 'updateOrderStatus']);

    
    Route::post('product/detail', [VendorOrderController::class, 'getProductDetails']);

    
    Route::post('add/to/cart', [VendorOrderController::class, 'addToCart']);

    
    Route::get('get/cart', [VendorOrderController::class, 'getCart']);

    
    Route::post('update/cart', [VendorOrderController::class, 'updateCart']);

    
    Route::post('remove/cart', [VendorOrderController::class, 'removeCart']);   


    Route::get('calculate/cart', [VendorOrderController::class, 'calculate']);

    
    Route::post('vendor/checkout', [VendorOrderController::class, 'checkout']);

    
    Route::get('get/orders', [VendorOrderController::class, 'getOrders']);


    Route::get('get/Expert', [DoctorController::class, 'getExpertCategories']);

    //////////DoctorController

    
Route::get('GetRequests', [DoctorController::class, 'getRequests']);


Route::post('doctor/requests/complete{id}', [DoctorController::class, 'reqMarkComplete']);


Route::get('Profile', [DoctorController::class, 'getProfile']);


Route::post('UpdateProf', [DoctorController::class, 'updateProfile']);


Route::get('Doctor_homeData', [DoctorController::class, 'homeData']);


Route::post('Add_SemenTank', [DoctorController::class, 'addSemenTank']);


Route::get('Get_Tank', [DoctorController::class, 'semenTanks']);


// Route::post('doctor/canisters/Update', [DoctorController::class, 'updateCanister']);

Route::post('doctor/update-canister', [DoctorController::class, 'updateCanister']);


Route::post('doctor/delete-semen-tank', [DoctorController::class, 'deleteSemenTank']);


Route::post('Sell_Semen', [DoctorController::class, 'sellSemen']);
