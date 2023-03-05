<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InterestController;
use App\Http\Controllers\Api\UserPostController;
use App\Http\Controllers\Api\GeneralController;

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

    /* // Google login
    Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/login/google/callback', [AuthController::class, 'handleGoogleCallback']);

    // Facebook login
    Route::get('/login/facebook', [AuthController::class, 'redirectToFacebook'])->name('login.facebook');
    Route::get('/login/facebook/callback', [AuthController::class, 'handleFacebookCallback']); */

    Route::post('/social/login', [AuthController::class, 'socialLogin']);
});

Route::group(['middleware' => 'api'], function(){
    Route::get('/my/profile', [UserController::class, 'myProfile']);
    Route::post('/my/edit-profile', [UserController::class, 'editProfile']);
    Route::post('/my/upload-profile-picture', [UserController::class, 'uploadProfilePicture']);
    Route::post('/my/upload-banner-picture', [UserController::class, 'uploadBannerPicture']);
    Route::post('/my/delete-picture', [UserController::class, 'deletePicture']);

    Route::post('/user/add-interest', [UserController::class, 'addInterest']);
    Route::post('/user/follow', [UserController::class, 'followUser']);
    Route::post('/user/unfollow', [UserController::class, 'unfollowUser']);
    Route::post('/user/block', [UserController::class, 'blockUser']);
    Route::post('/user/unblock', [UserController::class, 'unblockUser']);
    Route::post('/user/sync', [UserController::class, 'syncUser']);
    Route::get('/user/{recipient}/send-sms', [UserController::class, 'sendSMS']);

    Route::post('/post/upload-picture', [UserPostController::class, 'uploadPostPicture']);
    Route::post('/post/delete-picture', [UserPostController::class, 'deletePostPicture']);
    Route::post('/post/create-update', [UserPostController::class, 'createUpdatePost']);
    Route::post('/post/like-unlike', [UserPostController::class, 'likeUnlikePost']);
    Route::get('/my/post-list', [UserPostController::class, 'getMyPosts']);
    Route::post('/post/delete', [UserPostController::class, 'deletePost']);
});

Route::get('/category/all', [CategoryController::class, 'getAllActiveCategories']);
Route::get('/interest/all', [InterestController::class, 'getAllActiveInterests']);

Route::get('/countries', [GeneralController::class, 'getCountries']);
Route::get('/states', [GeneralController::class, 'getStates']);