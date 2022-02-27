<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductTypeController;
use App\Http\Controllers\Api\ChargeController;
use App\Http\Controllers\Api\ChargeTypeController;
use App\Http\Controllers\Api\CompanyTypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OperationController;
use App\Http\Controllers\Api\V2\OrderController as OrderControllerV2;
use App\Http\Controllers\Api\V2\StatsController;
use App\Http\Controllers\LicenseController;

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


// Signal API

Route::get('/', function (Request $request) {
    return $request->json(['status' => 'connected']);
});

// Auth API
Route::prefix('auth')->group(function () {

  Route::post('/login', [AuthController::class, 'login']);
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
  Route::post('/addUser', [AuthController::class, 'addUser'])->middleware(['auth:api', 'is_admin']);
  Route::post('/autorisation', [AuthController::class, 'updateAuth'])->middleware(['auth:api', 'is_admin']);
  Route::get('/users', [AuthController::class, 'users'])->middleware(['auth:api', 'is_admin']);
  Route::get('/user/{id}', [AuthController::class, 'user'])->middleware(['auth:api', 'is_admin']);

});

// Stats API
Route::prefix('stats')->group(function () {

  Route::get('/top/products', [StatsController::class, 'topProducts'])->middleware(['auth:api', 'hasLicense']);

});
// Products API
Route::prefix('products')->group(function () {

  Route::get('/', [ProductController::class, 'get'])->middleware('auth:api');
  Route::get('/deleted', [ProductController::class, 'deleted'])->middleware('auth:api');
  Route::get('/restore/{id}', [ProductController::class, 'restore'])->middleware('auth:api');
  Route::get('/stock/get', [ProductController::class, 'getStock'])->middleware('auth:api');
  Route::get('/export', [ProductController::class, 'export'])->middleware('auth:api');
  Route::get('/stock/find/{id}', [ProductController::class, 'findStock'])->middleware('auth:api');
  Route::post('/stock/update', [ProductController::class, 'updateStock'])->middleware('auth:api');
  Route::post('/stock', [ProductController::class, 'stock'])->middleware('auth:api');
  Route::post('/add', [ProductController::class, 'add'])->middleware('auth:api');
  Route::post('/addProduct', [ProductController::class, 'addProduct'])->middleware('auth:api');

  Route::prefix('types')->group(function () {
    Route::get('/', [ProductTypeController::class, 'get'])->middleware('auth:api');
    Route::get('/{id}', [ProductTypeController::class, 'find'])->middleware('auth:api');
    Route::post('/store', [ProductTypeController::class, 'store'])->middleware('auth:api');
  });

  Route::get('/{id}', [ProductController::class, 'find'])->middleware('auth:api');
  Route::get('/{id}/customers', [ProductController::class, 'customers'])->middleware('auth:api');
  Route::put('/{id}/update', [ProductController::class, 'update'])->middleware('auth:api');
  Route::delete('/{id}/delete', [ProductController::class, 'delete'])->middleware('auth:api');
  Route::post('/storeImage', [ProductController::class, 'storeImage'])->middleware('auth:api');
  Route::delete('/{id}/delete/stock', [ProductController::class, 'deleteStock'])->middleware('auth:api');
});
// Stats API
Route::prefix('charges')->group(function () {
  Route::get('/', [ChargeController::class, 'get'])->middleware('auth:api');
  Route::get('/export', [ChargeController::class, 'export'])->middleware('auth:api');
  Route::post('/add', [ChargeController::class, 'add'])->middleware('auth:api');
  Route::prefix('types')->group(function () {
    Route::get('/', [ChargeTypeController::class, 'get'])->middleware('auth:api');
    Route::get('/{id}', [ChargeTypeController::class, 'find'])->middleware('auth:api');
    Route::post('/store', [ChargeTypeController::class, 'store'])->middleware('auth:api');
  });
  Route::get('/{id}', [ChargeController::class, 'find'])->middleware('auth:api');
  Route::put('/{id}/update', [ChargeController::class, 'update'])->middleware('auth:api');
  Route::delete('/{id}/delete', [ChargeController::class, 'delete'])->middleware('auth:api');
});

Route::prefix('companies/types')->group(function () {

  Route::get('/', [CompanyTypeController::class, 'get']);
  Route::get('/{id}', [CompanyTypeController::class, 'find']);
  Route::post('/store', [CompanyTypeController::class, 'store']);

});

Route::put('users/profile', [UserController::class, 'updateProfile'])->middleware('auth:api');

// Customers API
Route::prefix('customers')->group(function () {
  Route::post('/addCustomer', [CustomerController::class, 'addCustomer'])->middleware('auth:api');
  Route::get('/export', [CustomerController::class, 'export'])->middleware('auth:api');
  Route::get('/', [CustomerController::class, 'get'])->middleware('auth:api');
  Route::post('/add', [CustomerController::class, 'add'])->middleware('auth:api');
  Route::get('/{id}', [CustomerController::class, 'find'])->middleware('auth:api');
  Route::put('/{id}/update', [CustomerController::class, 'update'])->middleware('auth:api');
  Route::delete('/{id}/delete', [CustomerController::class, 'delete'])->middleware('auth:api');
});

// Orders API
Route::prefix('orders')->group(function () {

  Route::get('/', [OrderController::class, 'get'])->middleware('auth:api');
  Route::post('/add', [OrderController::class, 'add'])->middleware('auth:api');
  Route::get('/{id}', [OrderController::class, 'find'])->middleware('auth:api');
  Route::delete('/{id}/delete', [OrderController::class, 'delete'])->middleware('auth:api');

});
// Orders API V2
Route::prefix('ordersV2')->group(function () {

  Route::post('/add', [OrderControllerV2::class, 'add'])->middleware(['auth:api', 'hasLicense']);
  Route::get('/get', [OrderControllerV2::class, 'get'])->middleware(['auth:api', 'hasLicense']);
  Route::get('/quantity', [OrderControllerV2::class, 'updateQuantity'])->middleware('auth:api');
  Route::get('/delete/{id}', [OrderControllerV2::class, 'delete'])->middleware('auth:api');
  Route::get('/return/{id}', [OrderControllerV2::class, 'Return'])->middleware('auth:api');
  Route::post('/discount', [OrderControllerV2::class, 'discount'])->middleware('auth:api');
  Route::get('/deleteAll', [OrderControllerV2::class, 'deleteAll'])->middleware('auth:api');

});

// Operation API
Route::prefix('operations')->group(function () {

  Route::get('/validateOperation', [OperationController::class, 'validateOperation'])->middleware(['auth:api', 'hasLicense']);
  Route::get('/', [OperationController::class, 'get'])->middleware(['auth:api', 'hasLicense']);
  Route::get('/export', [OperationController::class, 'export'])->middleware('auth:api');
  Route::get('/view/{id}', [OperationController::class, 'viewOperation'])->middleware('auth:api');
  Route::get('/stats', [StatsController::class, 'stats'])->middleware('auth:api');

});
Route::get('/gerenateKey', [LicenseController::class, 'gerenateKey'])->middleware('auth:api');

Route::group(['middleware' => [
  'throttle:' . config('licensor.request_throttle')]], function () {

  Route::post(
      config('licensor.key_verification_path'),
      'Sribna\Licensor\Http\Controllers\KeyRequestController@check')
      ->name('licensor.key.check');

  Route::post(
      config('licensor.key_activation_path'),
      'Sribna\Licensor\Http\Controllers\KeyRequestController@activate')
      ->name('licensor.key.activate');
});