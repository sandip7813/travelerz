<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;

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
Route::middleware(['auth','user-role:business'])->group(function(){
    Route::get('/business/home', [HomeController::class, 'businessHome'])->name('home.business');
});

// Admin Route
/* Route::middleware(['auth','user-role:admin'])->group(function(){
    Route::get('/admin/home', [HomeController::class, 'adminHome'])->name('home.admin');
}); */

//+++++++++++++++++++++++ ADMIN ROUTE :: Start +++++++++++++++++++++++//
Route::middleware(['auth','user-role:admin'])->prefix('admin')->group(function(){
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('admin.dashboard');
});
//+++++++++++++++++++++++ ADMIN ROUTE :: End +++++++++++++++++++++++//
