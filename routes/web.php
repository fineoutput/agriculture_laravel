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
Route::get('farmer_slider/delete/{id}', [FarmerSliderController::class, 'deleteSlider'])->name('delete_farmer_slider');
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









});

});



