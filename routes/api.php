<?php

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
Route::post('farmerlogin', [UserloginController::class, 'farmer_login_process']);
Route::post('farmerLoginOtp-verify', [UserloginController::class, 'farmer_login_otp_verify']);
Route::post('/register', [UserloginController::class, 'register_process']);
Route::post('registerotpverify', [UserloginController::class, 'farmer_register_otp_verify']);
Route::post('/login', [UserloginController::class, 'login_process']);
Route::post('/login/otp-verify', [UserloginController::class, 'login_otp_verify']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});



