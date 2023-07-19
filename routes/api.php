<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InterestController;
use App\Http\Controllers\Api\UserPostController;
use App\Http\Controllers\Api\MoveController;
use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\NotificationController;

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

//++++++++++++++++++++++++++++++ AUTH :: Start ++++++++++++++++++++++++++++++//
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
    Route::post('/social/login', [AuthController::class, 'socialLogin']);
});
//++++++++++++++++++++++++++++++ AUTH :: End ++++++++++++++++++++++++++++++//

Route::group(['middleware' => 'api'], function(){
    //++++++++++++++++++++++++++++++ MY PROFILE :: Start ++++++++++++++++++++++++++++++//
    Route::get('/my/profile', [UserController::class, 'myProfile']);
    Route::post('/my/edit-profile', [UserController::class, 'editProfile']);
    Route::post('/my/upload-profile-picture', [UserController::class, 'uploadProfilePicture']);
    Route::post('/my/upload-banner-picture', [UserController::class, 'uploadBannerPicture']);
    Route::post('/my/delete-picture', [UserController::class, 'deletePicture']);
    Route::get('/my/interests', [UserController::class, 'myInterests']);
    //++++++++++++++++++++++++++++++ MY PROFILE :: End ++++++++++++++++++++++++++++++//

    //++++++++++++++++++++++++++++++ USER PROFILE :: Start ++++++++++++++++++++++++++++++//
    Route::post('/user/add-interest', [UserController::class, 'addInterest']);
    Route::post('/user/follow', [UserController::class, 'followUser']);
    Route::post('/user/unfollow', [UserController::class, 'unfollowUser']);
    Route::post('/user/block', [UserController::class, 'blockUser']);
    Route::post('/user/unblock', [UserController::class, 'unblockUser']);
    Route::post('/user/sync', [UserController::class, 'syncUser']);
    Route::get('/user/{uuid}/details', [UserController::class, 'userDetails']);
    Route::get('/user/{recipient}/send-sms', [UserController::class, 'sendSMS']);
    Route::get('/user/update-fcm-token', [UserController::class, 'updateFcmToken']);
    //++++++++++++++++++++++++++++++ USER PROFILE :: End ++++++++++++++++++++++++++++++//

    //++++++++++++++++++++++++++++++ POST :: Start ++++++++++++++++++++++++++++++//
    Route::post('/post/upload-picture', [UserPostController::class, 'uploadPostPicture']);
    Route::post('/post/delete-picture', [UserPostController::class, 'deletePostPicture']);
    Route::post('/post/create-update', [UserPostController::class, 'createUpdatePost']);
    Route::post('/post/like-unlike', [UserPostController::class, 'likeUnlikePost']);
    Route::get('/my/post-list', [UserPostController::class, 'getMyPosts']);
    Route::post('/post/delete', [UserPostController::class, 'deletePost']);
    Route::post('/post/all', [UserPostController::class, 'showAllPosts']);
    Route::post('/post/add-comment', [UserPostController::class, 'addComment']);
    Route::post('/post/delete-comment', [UserPostController::class, 'deleteComment']);
    Route::get('/post/{post_uuid}/show-comments', [UserPostController::class, 'showComments']);
    Route::post('/post/share', [UserPostController::class, 'sharePost']);
    Route::get('/post/{uuid}/details', [UserPostController::class, 'postDetails']);
    Route::post('/post/most-liked', [UserPostController::class, 'mostLikedPosts']);
    Route::get('/my-post/most-liked', [UserPostController::class, 'myMostLikedPosts']);
    //++++++++++++++++++++++++++++++ POST :: End ++++++++++++++++++++++++++++++//

    //++++++++++++++++++++++++++++++ MOVE :: Start ++++++++++++++++++++++++++++++//
    Route::post('/move/create', [MoveController::class, 'createMove']);
    Route::post('/move/edit', [MoveController::class, 'editMove']);
    Route::get('/move/{uuid}/details', [MoveController::class, 'moveDetails']);
    Route::get('/my/move-list', [MoveController::class, 'getMyMoves']);
    Route::post('/move/delete-banner', [MoveController::class, 'deleteBanner']);
    Route::post('/move/delete-invited', [MoveController::class, 'deleteInvited']);
    Route::post('/move/delete', [MoveController::class, 'deleteMove']);
    Route::post('/move/save-unsave', [MoveController::class, 'saveUnsaveBookmark']);
    Route::get('/my/saved-moves', [MoveController::class, 'mySavedMoves']);
    Route::get('/move/invited', [MoveController::class, 'movesInvited']);
    Route::post('/move/update-invite-status', [MoveController::class, 'updateInviteStatus']);
    Route::post('/move/trending', [MoveController::class, 'trending']);
    //++++++++++++++++++++++++++++++ MOVE :: End ++++++++++++++++++++++++++++++//

    //++++++++++++++++++++++++++++++ PAYMENT :: Start ++++++++++++++++++++++++++++++//
    Route::get('/payment/get-details', [MembershipController::class, 'getPaymentDetails']);
    Route::post('/stripe/generate-customer', [MembershipController::class, 'generateStripeCustomer']);
    Route::post('/payment/save-details', [MembershipController::class, 'savePaymentDetails']);
    //++++++++++++++++++++++++++++++ PAYMENT :: End ++++++++++++++++++++++++++++++//

    //++++++++++++++++++++++++++++++ CHAT :: Start ++++++++++++++++++++++++++++++//
    Route::post('/chat-room', [ChatController::class, 'chatRoom']);
    Route::post('/create-chat-room', [ChatController::class, 'store']);
    Route::get('/chat/room/{id}', [ChatController::class, 'show']);
    Route::get('/chat/{room_id}/message', [ChatController::class, 'showMessages']);
    Route::post('/send-chat-message', [ChatMessageController::class, 'store']);
    Route::get('/chat/participants', [ChatController::class, 'participantsList']);
    //++++++++++++++++++++++++++++++ CHAT :: End ++++++++++++++++++++++++++++++//

    //++++++++++++++++++++++++++++++ NOTIFICATION :: Start ++++++++++++++++++++++++++++++//
    Route::post('/notifications', [NotificationController::class, 'getUserNotifications']);
    Route::post('/notification/make-read', [NotificationController::class, 'markNotificationsAsRead']);
    //++++++++++++++++++++++++++++++ NOTIFICATION :: End ++++++++++++++++++++++++++++++//
});

Route::post('/category/single', [CategoryController::class, 'getCategory']);
Route::get('/category/all', [CategoryController::class, 'getAllActiveCategories']);
Route::get('/interest/all', [InterestController::class, 'getAllActiveInterests']);

Route::get('/countries', [GeneralController::class, 'getCountries']);
Route::get('/states', [GeneralController::class, 'getStates']);

Route::get('/memberships/all', [GeneralController::class, 'getMemberships']);