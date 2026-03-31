<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\DashboardController;  
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Artisan; 

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

Route::get('/setup', function () {
    Artisan::call('optimize:clear');
    Artisan::call('storage:link');
    return response()->json([
        'message' => 'All caches cleared and storage linked successfully!'
    ]);
});

// Home Route
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get("/admin/create",[AdminAuthController::class,'createAdmin']);



// QR Routes
Route::get('/qr/review', [QRController::class, 'generateReviewQR'])->name('qr.review'); // QR for reviews
Route::get('/qr/feedback', [QRController::class, 'generateFeedbackQR'])->name('qr.feedback'); // QR for feedback (complaints & suggestions)

// Review Routes
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::get('/submit-review', [ReviewController::class, 'create'])->name('review.create');
Route::post('/submit-review', [ReviewController::class, 'store'])->name('review.store');
Route::get('/reviews/thank-you', function () {
    return view('pages.reviews.thank-you');
})->name('review.thank_you');

// Feedback Routes
Route::get('/feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');

// Authentication Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [AdminAuthController::class, 'login']);
Route::post('register', [RegisterController::class, 'register']);

Route::get('password/forget', function () {
    return view('pages.forgot-password');
})->name('password.forget');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Authenticated Routes (only accessible after login)
Route::group(['middleware' => 'auth'], function () {

    // Logout and Cache Clear Routes
    Route::get('/logout', [LoginController::class, 'logout']);
    Route::get('/clear-cache', [HomeController::class, 'clearCache']);

    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
      Route::get('/vehicle', [DashboardController::class, 'vehicle'])->name('vehicle');
      Route::get('/vehicle/{id}/edit', [DashboardController::class, 'getVehicleData'])->name('vehicle.edit.ajax');
     Route::post('/update-vehicle', [DashboardController::class, 'updateVehicle'])->name('update.vehicle');
       Route::get('/bookings', [DashboardController::class, 'booking'])->name('booking');
      Route::delete('/admin/vehicle/{id}', [DashboardController::class, 'destroy'])->name('vehicles.destroy');

      Route::post("/saveVehicle",[DashboardController::class, 'saveVehicle']);
      
      
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');

    // // User Management (only accessible to those with 'manage_user' permission)
    // Route::group(['middleware' => 'can:manage_user'], function () {
    //     Route::get('/users', [UserController::class, 'index']);
    //     Route::get('/user/get-list', [UserController::class, 'getUserList']);
    //     Route::get('/user/create', [UserController::class, 'create']);
    //     Route::post('/user/create', [UserController::class, 'store'])->name('create-user');
    //     Route::get('/user/{id}', [UserController::class, 'edit']);
    //     Route::post('/user/update', [UserController::class, 'update']);
    //     Route::get('/user/delete/{id}', [UserController::class, 'delete']);
    // });

    // // Role Management (only accessible to those with 'manage_role' permission)
    // Route::group(['middleware' => 'can:manage_role|manage_user'], function () {
    //     Route::get('/roles', [RolesController::class, 'index']);
    //     Route::get('/role/get-list', [RolesController::class, 'getRoleList']);
    //     Route::post('/role/create', [RolesController::class, 'create']);
    //     Route::get('/role/edit/{id}', [RolesController::class, 'edit']);
    //     Route::post('/role/update', [RolesController::class, 'update']);
    //     Route::get('/role/delete/{id}', [RolesController::class, 'delete']);
    // });

    // // Permission Management (only accessible to those with 'manage_permission' permission)
    // Route::group(['middleware' => 'can:manage_permission|manage_user'], function () {
    //     Route::get('/permission', [PermissionController::class, 'index']);
    //     Route::get('/permission/get-list', [PermissionController::class, 'getPermissionList']);
    //     Route::post('/permission/create', [PermissionController::class, 'create']);
    //     Route::get('/permission/update', [PermissionController::class, 'update']);
    //     Route::get('/permission/delete/{id}', [PermissionController::class, 'delete']);
    // });

    // // Get Permissions
    // Route::get('get-role-permissions-badge', [PermissionController::class, 'getPermissionBadgeByRole']);

    // Basic demo routes
    include('modules/demo.php');

    // Inventory routes
    include('modules/inventory.php');

    // Accounting routes
    include('modules/accounting.php');
});

// Public Routes (Registration, Login Pages)
Route::get('/register', function () {
    return view('pages.register');
});
Route::get('/login-1', function () {
    return view('pages.login');
});
