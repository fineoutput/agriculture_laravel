<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\TeamController; 
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ContactUsController;
use App\Http\Controllers\Admin\CrmController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Auth\adminlogincontroller;
use App\Http\Controllers\Admin\FarmerSliderController;
use App\Http\Controllers\Admin\DiseaseController;
use App\Http\Controllers\Admin\FarmersController;
use App\Http\Controllers\Admin\DoctorsController;
use App\Http\Controllers\Admin\SalePurchaseSliderController;
use App\Http\Controllers\Admin\VendorSliderController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\GiftcardController;
use App\Http\Controllers\Admin\ExpertiseCategoryController;
use App\Http\Controllers\Admin\OptionImageController;
use App\Http\Controllers\Admin\DoctorSliderController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\CategoryImagesController;
use App\Http\Controllers\Admin\SubcategoryImagesController;
use App\Http\Controllers\Admin\EquipmentSalePurchaseController;
use App\Http\Controllers\Admin\VendorAppOrdersController;
use App\Http\Controllers\Admin\AnimalSalePurchaseController;
use App\Http\Controllers\Admin\Admin_orders;
use App\Http\Controllers\Admin\VendorOrderController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/clear-cache', function () {
//     $exitCode = Artisan::call('cache:clear');
//     // $exitCode = Artisan::call('route:clear');
//     // $exitCode = Artisan::call('config:clear');
//     // $exitCode = Artisan::call('view:clear');
//     // return what you want
// });
//=========================================== FRONTEND =====================================================

Route::group(['prefix' => '/'], function () {
    Route::get('/', [HomeController::class, 'index'])->name('/');
});

//======================================= ADMIN ===================================================
Route::group(['prifix' => 'admin'], function () {
    Route::group(['middleware'=>'admin.guest'],function(){

        Route::get('/admin_index', [adminlogincontroller::class, 'admin_login'])->name('admin_login');
        Route::post('/login_process', [adminlogincontroller::class, 'admin_login_process'])->name('admin_login_process');

    });
Route::group(['middleware'=>'admin.auth'],function(){

 Route::get('/index', [TeamController::class, 'admin_index'])->name('admin_index');
 Route::get('/logout', [adminlogincontroller::class, 'admin_logout'])->name('admin_logout');
 Route::get('/profile', [adminlogincontroller::class, 'admin_profile'])->name('admin_profile');
 Route::get('/view_change_password', [adminlogincontroller::class, 'admin_change_pass_view'])->name('view_change_password');
 Route::post('/admin_change_password', [adminlogincontroller::class, 'admin_change_password'])->name('admin_change_password');

        // Admin Team ------------------------

Route::get('/view_team', [TeamController::class, 'view_team'])->name('view_team');
Route::get('/add_team_view', [TeamController::class, 'add_team_view'])->name('add_team_view');
Route::post('/add_team_process', [TeamController::class, 'add_team_process'])->name('add_team_process');
Route::get('/UpdateTeamStatus/{status}/{id}', [TeamController::class, 'UpdateTeamStatus'])->name('UpdateTeamStatus');
Route::get('/deleteTeam/{id}', [TeamController::class, 'deleteTeam'])->name('deleteTeam');



// Admin CRM settings ------------------------
Route::get('/add_settings', [CrmController::class, 'add_settings'])->name('add_settings');
Route::get('/view_settings', [CrmController::class, 'view_settings'])->name('view_settings');
Route::get('/update_settings/{id}', [CrmController::class, 'update_settings'])->name('update_settings');
Route::post('/add_settings_process', [CrmController::class, 'add_settings_process'])->name('add_settings_process');
Route::post('/update_settings_process/{id}', [CrmController::class, 'update_settings_process'])->name('update_settings_process');
Route::get('/deletesetting/{id}', [CrmController::class, 'deletesetting'])->name('deletesetting');

Route::get('farmer_slider', [FarmerSliderController::class, 'index'])->name('farmer_slider.list');
Route::get('farmer_slider/add', [FarmerSliderController::class, 'createForm'])->name('add_farmer_slider');
Route::post('farmer_slider/store', [FarmerSliderController::class, 'storeSlider'])->name('store_farmer_slider');
Route::get('farmer_slider/edit/{id}', [FarmerSliderController::class, 'editForm'])->name('edit_farmer_slider');
Route::post('farmer_slider/update/{id}', [FarmerSliderController::class, 'updateSlider'])->name('update_farmer_slider');
Route::delete('farmer_slider/delete/{id}', [FarmerSliderController::class, 'destroy'])->name('delete_farmer_slider');

Route::get('farmer_slider/toggle/{id}', [FarmerSliderController::class, 'toggleSliderStatus'])->name('toggle_farmer_slider_status');

////////////////disease
Route::get('disease', [DiseaseController::class, 'index'])->name('disease.index');
Route::get('create', [DiseaseController::class, 'create'])->name('disease.create');
Route::post('store', [DiseaseController::class, 'store'])->name('disease.store');
Route::get('edit/{id}', [DiseaseController::class, 'edit'])->name('disease.edit');
Route::put('update/{id}', [DiseaseController::class, 'update'])->name('disease.update');
Route::get('toggle-status/{id}', [DiseaseController::class, 'toggleStatus'])->name('disease.toggleStatus');
Route::get('delete/{id}', [DiseaseController::class, 'destroy'])->name('disease.delete');

//////Farmer
Route::get('farmers', [FarmersController::class, 'index'])->name('admin.farmers.index');
Route::get('farmers/updateFarmersStatus/{id}/{status}', [FarmersController::class, 'updateFarmerStatus'])->name('admin.farmers.status');
Route::get('farmers/delete/{id}', [FarmersController::class, 'delete'])->name('admin.farmers.delete');
Route::get('farmers/viewrecords/{id}', [FarmersController::class, 'viewRecords'])->name('admin.farmers.records');
Route::post('farmers/store_cod_data', [FarmersController::class, 'storeCod'])->name('admin.farmers.store_cod');
Route::post('farmers/qtyupdate', [FarmersController::class, 'updateQty'])->name('admin.farmers.qtyupdate');


///////Doctors
    Route::get('/new', [DoctorsController::class, 'newDoctors'])->name('admin.doctor.new');
    Route::get('/accepted', [DoctorsController::class, 'acceptedDoctors'])->name('admin.doctor.accepted');
    Route::get('/normal', [DoctorsController::class, 'normalDoctors'])->name('admin.doctor.normal');
    Route::get('/total', [DoctorsController::class, 'totalDoctors'])->name('admin.doctor.total');
    Route::get('/rejected', [DoctorsController::class, 'rejectedDoctors'])->name('admin.doctor.rejected');
    Route::get('/view-pdf/{idd}', [DoctorsController::class, 'viewPdf'])->name('admin.doctor.view-pdf');
    Route::get('/requests', [DoctorsController::class, 'doctorRequest'])->name('admin.doctor.requests');
    Route::get('/delete/{idd}', [DoctorsController::class, 'deleteDoctor'])->name('admin.doctor.delete');
    Route::get('/update-status/{idd}/{t}', [DoctorsController::class, 'updateDoctorStatus'])->name('admin.doctor.update-status');
    Route::get('/edit/{idd}', [DoctorsController::class, 'updateDoctor'])->name('admin.doctor.edit');
    Route::get('/set-commission/{idd}', [DoctorsController::class, 'setCommissionDoctor'])->name('admin.doctor.set-commission');
    Route::post('/update-commission/{idd}', [DoctorsController::class, 'addDoctorData2'])->name('admin.doctor.update-commission');
    Route::get('/add-fees/{y}', [DoctorsController::class, 'addFeesDoctor'])->name('admin.doctor.add-fees');
    Route::post('/store-fees/{y}', [DoctorsController::class, 'addDoctorData3'])->name('admin.doctor.store-fees');
    Route::post('/update/{y}', [DoctorsController::class, 'updateDoctorData'])->name('admin.doctor.update');

///////Products
    Route::get('/Products/View_products', [ProductController::class, 'viewProducts'])->name('admin.products.view');
    Route::get('/Products/vendor_pending_products', [ProductController::class, 'vendorPendingProducts'])->name('admin.products.vendor_pending');
    Route::get('/Products/vendor_accepted_products', [ProductController::class, 'vendorAcceptedProducts'])->name('admin.products.vendor_accepted');
    Route::get('/Products/add_products', [ProductController::class, 'addProducts'])->name('admin.products.add');
    Route::post('/Products/add_products_data/{t}/{iw?}', [ProductController::class, 'addProductsData'])->name('admin.products.add_data');
    Route::get('/Products/update_products/{idd}', [ProductController::class, 'updateProducts'])->name('admin.products.update');
    Route::get('/Products/delete_products/{idd}', [ProductController::class, 'deleteProducts'])->name('admin.products.delete');
    Route::get('/Products/updateproductsStatus/{idd}/{t}', [ProductController::class, 'updateProductsStatus'])->name('admin.products.update_status');
    Route::get('/Products/approvedProduct/{idd}', [ProductController::class, 'approvedProduct'])->name('admin.products.approve');
    Route::post('/Products/product_cod_data', [ProductController::class, 'productCodData'])->name('admin.products.cod_data');

/////////////slider2

    Route::get('Slider1', [SliderController::class, 'viewSlider'])->name('admin.Slider.view');
    Route::get('/Slider/add_slider', [SliderController::class, 'addSlider'])->name('admin.Slider.add');
    Route::post('/Slider/add_slider_data/{t}/{iw?}', [SliderController::class, 'addSliderData'])->name('admin.Slider.add_data');
    Route::get('/Slider/update_slider/{idd}', [SliderController::class, 'updateSlider'])->name('admin.Slider.update');
    // Route::get('/Slider/delete_slider/{idd}', [SliderController::class, 'deleteSlider'])->name('admin.Slider.delete');
    Route::get('/Slider/updatesliderStatus/{idd}/{t}', [SliderController::class, 'updateSliderStatus'])->name('admin.Slider.update_status');
    Route::delete('Slider/delete/{idd}', [SliderController::class, 'destroy'])->name('admin.Slider.delete');


    //////salePurchase
    Route::get('/SalePurchaseSlider/View_slider', [SalePurchaseSliderController::class, 'viewSlider'])->name('admin.salepurchaseslider.view');
    Route::get('/SalePurchaseSlider/add_slider', [SalePurchaseSliderController::class, 'addSlider'])->name('admin.salepurchaseslider.add');
    Route::post('/SalePurchaseSlider/add_slider_data/{t}/{iw?}', [SalePurchaseSliderController::class, 'addSliderData'])->name('admin.salepurchaseslider.add_data');
    Route::get('/SalePurchaseSlider/update_slider/{idd}', [SalePurchaseSliderController::class, 'updateSlider'])->name('admin.salepurchaseslider.update');
    Route::delete('/SalePurchaseSlider/delete_slider/{idd}', [SalePurchaseSliderController::class, 'destroy'])->name('admin.salepurchaseslider.delete');
    Route::get('/SalePurchaseSlider/updatesliderStatus/{idd}/{t}', [SalePurchaseSliderController::class, 'updateSliderStatus'])->name('admin.salepurchaseslider.update_status');

/////VendorSlider


    Route::get('/vendor_slider/view_vendorslider', [VendorSliderController::class, 'viewVendorSlider'])->name('admin.vendorslider.view');
    Route::get('/vendor_slider/add_vendorslider', [VendorSliderController::class, 'addVendorSlider'])->name('admin.vendorslider.add');
    Route::post('/vendor_slider/add_vendorslider_data/{t}/{iw?}', [VendorSliderController::class, 'addVendorSliderData'])->name('admin.vendorslider.add_data');
    Route::get('/vendor_slider/update_vendorslider/{idd}', [VendorSliderController::class, 'updateVendorSlider'])->name('admin.vendorslider.update');
    Route::delete('/vendor_slider/delete_vendorslider/{idd}', [VendorSliderController::class, 'destroy'])->name('admin.vendorslider.delete');
    Route::get('/vendor_slider/updatevendorsliderStatus/{idd}/{t}', [VendorSliderController::class, 'updateVendorSliderStatus'])->name('admin.vendorslider.update_status');
    
    // Vendor Slider Request Routes
    Route::get('/vendor_slider/view_vendorslider_req', [VendorSliderController::class, 'viewVendorSliderRequest'])->name('admin.vendorslider.view_request');
    Route::get('/vendor_slider/updatevendorslider_req/{idd}/{t}', [VendorSliderController::class, 'updateVendorSliderRequest'])->name('admin.vendorslider.update_request');


    ////Manager 
    Route::get('/manager/add_manager', [ManagerController::class, 'addManager'])->name('admin.manager.add');
    Route::post('/manager/add_manager_data', [ManagerController::class, 'addManagerData'])->name('admin.manager.add_data');
    Route::get('/manager/view_manager', [ManagerController::class, 'viewManager'])->name('admin.manager.view');
    Route::get('/manager/view_farmers/{idd}', [ManagerController::class, 'viewFarmers'])->name('admin.manager.view_farmers');
    Route::get('/manager/view_doctors/{idd}', [ManagerController::class, 'viewDoctors'])->name('admin.manager.view_doctors');
    Route::get('/manager/view_vendors/{idd}', [ManagerController::class, 'viewVendors'])->name('admin.manager.view_vendors');
    Route::get('/manager/updatemanagerStatus/{idd}/{t}', [ManagerController::class, 'updateManagerStatus'])->name('admin.manager.update_status');
    Route::delete('/manager/delete_manager/{id}', [ManagerController::class, 'deleteManager'])->name('admin.manager.delete');

/////////GiftCard

    Route::get('/Giftcard', [GiftcardController::class, 'index'])->name('admin.giftcard.index');
    Route::get('/Giftcard/add_giftcard', [GiftcardController::class, 'addGiftcard'])->name('admin.giftcard.add');
    Route::get('/Giftcard/update_giftcard/{idd}', [GiftcardController::class, 'updateGiftcard'])->name('admin.giftcard.update');
    Route::post('/Giftcard/add_giftcard_data/{t}/{iw?}', [GiftcardController::class, 'addGiftcardData'])->name('admin.giftcard.add_data');
    Route::delete('/Giftcard/delete_gift/{id}', [GiftcardController::class, 'deleteGift'])->name('admin.giftcard.delete');
    Route::get('/Giftcard/updateGiftCardStatus/{idd}/{t}', [GiftcardController::class, 'updateGiftCardStatus'])->name('admin.giftcard.update_status');
    Route::get('/Giftcard/allocated/{alt_id}', [GiftcardController::class, 'allocated'])->name('admin.giftcard.allocated');


    ////////ExpertiseCategory
    Route::get('/ExpertiseCategory/view_expertise_category', [ExpertiseCategoryController::class, 'viewExpertiseCategory'])->name('admin.expertise_category.view');
    Route::get('/ExpertiseCategory/add_expertise_category', [ExpertiseCategoryController::class, 'addExpertiseCategory'])->name('admin.expertise_category.add');
    Route::get('/ExpertiseCategory/update_expertise_category/{idd}', [ExpertiseCategoryController::class, 'updateExpertiseCategory'])->name('admin.expertise_category.update');
    Route::post('/ExpertiseCategory/add_expertise_category_data/{t}/{iw?}', [ExpertiseCategoryController::class, 'addExpertiseCategoryData'])->name('admin.expertise_category.add_data');
    Route::get('/ExpertiseCategory/updateexpertise_categoryStatus/{idd}/{t}', [ExpertiseCategoryController::class, 'updateExpertiseCategoryStatus'])->name('admin.expertise_category.update_status');
    Route::delete('/ExpertiseCategory/delete_expertise_category/{id}', [ExpertiseCategoryController::class, 'deleteExpertiseCategory'])->name('admin.expertise_category.delete');

    //////optionImage
    Route::get('/OptionImageController/View_slider', [OptionImageController::class, 'viewSlider'])->name('admin.option_image.view');
    Route::get('/OptionImageController/add_slider', [OptionImageController::class, 'addSlider'])->name('admin.option_image.add');
    Route::post('/OptionImageController/add_slider_data/{t}/{iw?}', [OptionImageController::class, 'addSliderData'])->name('admin.option_image.add_data');
    Route::get('/OptionImageController/update_slider/{idd}', [OptionImageController::class, 'updateSlider'])->name('admin.option_image.update');
    Route::post('/OptionImageController/delete_slider/{idd}', [OptionImageController::class, 'deleteSlider'])->name('admin.option_image.delete');
    Route::get('/OptionImageController/updatesliderStatus/{idd}/{t}', [OptionImageController::class, 'updateSliderStatus'])->name('admin.option_image.update_status');

    /////DoctorSlider
    Route::get('/doctor_slider/view_doctorslider', [DoctorSliderController::class, 'viewDoctorSlider'])->name('admin.doctor_slider.view');
    Route::get('/doctor_slider/add_doctorslider', [DoctorSliderController::class, 'addDoctorSlider'])->name('admin.doctor_slider.add');
    Route::post('/doctor_slider/add_doctorslider_data/{t}/{iw?}', [DoctorSliderController::class, 'addDoctorSliderData'])->name('admin.doctor_slider.add_data');
    Route::get('/doctor_slider/update_doctorslider/{idd}', [DoctorSliderController::class, 'updateDoctorSlider'])->name('admin.doctor_slider.update');
    Route::post('/doctor_slider/delete_doctorslider/{idd}', [DoctorSliderController::class, 'deleteDoctorSlider'])->name('admin.doctor_slider.delete');
    Route::get('/doctor_slider/updatedoctorsliderStatus/{idd}/{t}', [DoctorSliderController::class, 'updateDoctorSliderStatus'])->name('admin.doctor_slider.update_status');

    /////Vendors
   Route::get('/Vendor/new_vendors', [VendorController::class, 'newVendors'])->name('admin.vendor.new');
    Route::get('/Vendor/accepted_vendors', [VendorController::class, 'acceptedVendors'])->name('admin.vendor.accepted');
    Route::get('/Vendor/rejected_vendors', [VendorController::class, 'rejectedVendors'])->name('admin.vendor.rejected');
    Route::post('/Vendor/add_vendor_data/{t}/{iw?}', [VendorController::class, 'addVendorData'])->name('admin.vendor.add_data');
    Route::post('/Vendor/store_cod_data', [VendorController::class, 'storeCodData'])->name('admin.vendor.store_cod');
    Route::post('/Vendor/delete_vendor/{idd}', [VendorController::class, 'deleteVendor'])->name('admin.vendor.delete');
    Route::get('/Vendor/updateVendorStatus/{idd}/{t}', [VendorController::class, 'updateVendorStatus'])->name(  'admin.vendor.update_status');
    Route::get('/Vendor/update_vendor/{idd}', [VendorController::class, 'updateVendor'])->name('admin.vendor.update');
    Route::get('/Vendor/set_comission_vendor/{idd}', [VendorController::class, 'setCommissionVendor'])->name('admin.vendor.set_commission');
    Route::post('/Vendor/add_vendor_data2/{idd}', [VendorController::class, 'addVendorData2'])->name('admin.vendor.add_data2');
    Route::post('/Vendor/qtyupdate', [VendorController::class, 'qtyUpdate'])->name('admin.vendor.qty_update');

    /////////Category_image
        Route::get('/Category_images/View_categoryimages', [CategoryImagesController::class, 'viewCategoryImages'])->name('admin.category_images.view');
        Route::post('/Category_images/add_Categoryimages_data/{t}/{iw?}', [CategoryImagesController::class, 'addCategoryImagesData'])->name('admin.category_images.add_data');
        Route::get('/Category_images/update_Categoryimages/{idd}', [CategoryImagesController::class, 'updateCategoryImages'])->name('admin.category_images.update');
        Route::get('/Category_images/updateCategoryimagesStatus/{idd}/{t}', [CategoryImagesController::class, 'updateCategoryImagesStatus'])->name('admin.category_images.update_status');

    /////////Sub-cat image
        Route::get('/Subcategory_images/View_subcategoryimages', [SubcategoryImagesController::class, 'viewSubcategoryImages'])->name('admin.subcategory_images.view');
        Route::post('/Subcategory_images/add_subcategoryimages_data/{t}/{iw?}', [SubcategoryImagesController::class, 'addSubcategoryImagesData'])->name('admin.subcategory_images.add_data');
        Route::get('/Subcategory_images/update_subcategoryimages/{idd}', [SubcategoryImagesController::class, 'updateSubcategoryImages'])->name('admin.subcategory_images.update');


    //////Equipement sale purchase
        Route::get('/Equipment_Sale_purchase/view_salepurchase', [EquipmentSalePurchaseController::class, 'viewSalePurchase'])->name('admin.equipment_sale_purchase.view');
        Route::get('/Equipment_Sale_purchase/farmer_salepurchse_pending', [EquipmentSalePurchaseController::class, 'farmerSalePurchasePending'])->name('admin.equipment_sale_purchase.pending');
        Route::get('/Equipment_Sale_purchase/farmer_salepurchse_accepted', [EquipmentSalePurchaseController::class, 'farmerSalePurchaseAccepted'])->name('admin.equipment_sale_purchase.accepted');
        Route::get('/Equipment_Sale_purchase/farmer_salepurchse_completed', [EquipmentSalePurchaseController::class, 'farmerSalePurchaseCompleted'])->name('admin.equipment_sale_purchase.completed');
        Route::get('/Equipment_Sale_purchase/farmer_salepurchse_rejected', [EquipmentSalePurchaseController::class, 'farmerSalePurchaseRejected'])->name('admin.equipment_sale_purchase.rejected');
        Route::get('/Equipment_Sale_purchase/updatesalepurchaseStatus/{idd}/{t}', [EquipmentSalePurchaseController::class, 'updateSalePurchaseStatus'])->name('admin.equipment_sale_purchase.update_status');

    ////////Vendor App orders
        Route::get('/VendorAppOrders/new_order', [VendorAppOrdersController::class, 'newOrder'])->name('admin.vendorapporders.new');
        Route::get('/VendorAppOrders/accepted_order', [VendorAppOrdersController::class, 'acceptedOrder'])->name('admin.vendorapporders.accepted');
        Route::get('/VendorAppOrders/dispatched_order', [VendorAppOrdersController::class, 'dispatchedOrder'])->name('admin.vendorapporders.dispatched');
        Route::get('/VendorAppOrders/completed_order', [VendorAppOrdersController::class, 'completedOrder'])->name('admin.vendorapporders.completed');
        Route::get('/VendorAppOrders/cancelled_order', [VendorAppOrdersController::class, 'cancelledOrder'])->name('admin.vendorapporders.cancelled');
        Route::get('/VendorAppOrders/rejected_order', [VendorAppOrdersController::class, 'rejectedOrder'])->name('admin.vendorapporders.rejected');
        Route::get('/VendorAppOrders/updateorderStatus/{idd}/{t}', [VendorAppOrdersController::class, 'updateOrderStatus'])->name('admin.vendorapporders.update_status');
        Route::get('/VendorAppOrders/order_detail/{idd}/{t?}', [VendorAppOrdersController::class, 'orderDetail'])->name('admin.vendorapporders.detail');
        Route::get('/VendorAppOrders/view_bill/{idd}', [VendorAppOrdersController::class, 'viewBill'])->name('admin.vendorapporders.view_bill');
    
    /////animal_sale_purchase
        Route::get('/Animal_Sale_purchase/view_salepurchase', [AnimalSalePurchaseController::class, 'viewSalePurchase'])->name('admin.animal_sale_purchase.view');
        Route::get('/Animal_Sale_purchase/farmer_salepurchse_pending', [AnimalSalePurchaseController::class, 'farmerSalePurchasePending'])->name('admin.animal_sale_purchase.pending');
        Route::get('/Animal_Sale_purchase/farmer_salepurchse_accepted', [AnimalSalePurchaseController::class, 'farmerSalePurchaseAccepted'])->name('admin.animal_sale_purchase.accepted');
        Route::get('/Animal_Sale_purchase/farmer_salepurchse_completed', [AnimalSalePurchaseController::class, 'farmerSalePurchaseCompleted'])->name('admin.animal_sale_purchase.completed');
        Route::get('/Animal_Sale_purchase/farmer_salepurchse_rejected', [AnimalSalePurchaseController::class, 'farmerSalePurchaseRejected'])->name('admin.animal_sale_purchase.rejected');
        Route::get('/Animal_Sale_purchase/updatesalepurchaseStatus/{idd}/{t}', [AnimalSalePurchaseController::class, 'updateSalePurchaseStatus'])->name('admin.animal_sale_purchase.update_status');

    ///////////adminOrders
    Route::get('/orders/all', [Admin_orders::class, 'allOrder'])->name('admin.orders.all');
    Route::get('/orders/new', [Admin_orders::class, 'newOrder'])->name('admin.orders.new');
    Route::get('/orders/accepted', [Admin_orders::class, 'acceptedOrder'])->name('admin.orders.accepted');
    Route::get('/orders/today', [Admin_orders::class, 'todayOrder'])->name('admin.orders.today');
    Route::get('/orders/dispatched', [Admin_orders::class, 'dispatchedOrder'])->name('admin.orders.dispatched');
    Route::get('/orders/completed', [Admin_orders::class, 'completedOrder'])->name('admin.orders.completed');
    Route::get('/orders/cancelled', [Admin_orders::class, 'cancelledOrder'])->name('admin.orders.cancelled');
    Route::get('/orders/rejected', [Admin_orders::class, 'rejectedOrder'])->name('admin.orders.rejected');
    Route::get('/orders/update-status/{idd}/{t}', [Admin_orders::class, 'updateOrderStatus'])->name('admin.orders.update_status');
    Route::get('/orders/detail/{idd}/{t?}', [Admin_orders::class, 'orderDetail'])->name('admin.orders.detail');
    Route::get('/orders/view-bill/{idd}', [Admin_orders::class, 'viewBill'])->name('admin.orders.view_bill');

    // Vendor Orders Routes
    Route::get('/vendor-orders/new', [VendorOrderController::class, 'newOrder'])->name('admin.vendor_orders.new');
    Route::get('/vendor-orders/accepted', [VendorOrderController::class, 'acceptedOrder'])->name('admin.vendor_orders.accepted');
    Route::get('/vendor-orders/dispatched', [VendorOrderController::class, 'dispatchedOrder'])->name('admin.vendor_orders.dispatched');
    Route::get('/vendor-orders/completed', [VendorOrderController::class, 'completedOrder'])->name('admin.vendor_orders.completed');
    Route::get('/vendor-orders/cancelled', [VendorOrderController::class, 'cancelledOrder'])->name('admin.vendor_orders.cancelled');
    Route::get('/vendor-orders/rejected', [VendorOrderController::class, 'rejectedOrder'])->name('admin.vendor_orders.rejected');

});             

});




