<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InterestController;
use App\Http\Controllers\Admin\MyAccountController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\GeneralController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the 'web' middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

// User Route
/* Route::middleware(['auth','user-role:user'])->group(function()
{
    Route::get('/home', [HomeController::class, 'userHome'])->name('home');
}); */

// Business Route
/* Route::middleware(['auth','user-role:business'])->group(function(){
    Route::get('/business/home', [HomeController::class, 'businessHome'])->name('home.business');
}); */

// Admin Route
/* Route::middleware(['auth','user-role:admin'])->group(function(){
    Route::get('/admin/home', [HomeController::class, 'adminHome'])->name('home.admin');
}); */

//+++++++++++++++++++++++ ADMIN ROUTE :: Start +++++++++++++++++++++++//
Route::middleware(['auth','user-role:admin'])->prefix('admin')->group(function(){
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('admin.dashboard');

    Route::resource('categories', CategoryController::class, ['names' => 'admin.category'], [ 'except' => ['update'] ]);
    Route::post('categories/{uuid}/update', [CategoryController::class, 'updateCategory'])->name('admin.category.update');
    Route::post('categories/change-status', [CategoryController::class, 'changeCategoryStatus'])->name('admin.category.change-status');

    Route::resource('interests', InterestController::class, ['names' => 'admin.interest']);
    Route::post('interests/change-status', [InterestController::class, 'changeInterestStatus'])->name('admin.interest.change-status');

    Route::get('myaccount/change-password', [MyAccountController::class, 'changePassword'])->name('admin.myaccount.change-password');
    Route::post('myaccount/change-password-submit', [MyAccountController::class, 'changePasswordSubmit'])->name('admin.myaccount.change-password-submit');

    Route::resource('users', UsersController::class, ['names' => 'admin.users']);
    Route::get('user/{uuid}/posts', [UsersController::class, 'userPosts'])->name('admin.user.posts');

    Route::get('generate-state-list-dropdown', [GeneralController::class, 'generateStateListDropdown'])->name('admin.generate-state-list-dropdown');
});
//+++++++++++++++++++++++ ADMIN ROUTE :: End +++++++++++++++++++++++//
