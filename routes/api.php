<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']); 
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']); 
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::get('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/reset-password-submit', [AuthController::class, 'resetPasswordSubmit']);
});

Route::group(['middleware' => 'api'], function(){
    Route::get('/my/profile', [UserController::class, 'myProfile']); 
    Route::post('user/add-interest', [UserController::class, 'addInterest']);
});