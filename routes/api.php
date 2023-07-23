<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ApiRegisterController;
use App\Http\Controllers\Api\V1\ApiProductController;
use App\Http\Controllers\Api\V1\ApiCartController;
use App\Http\Controllers\Api\V1\ApiCategoryController;
use App\Http\Controllers\Api\V1\ApiSubCategoryController;
use App\Http\Controllers\Api\V1\ApiBigStoreController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    // user section for authorized users
    Route::post('/logout', [ApiRegisterController::class, 'logout']);

    // cart section for authorized users
    Route::post('/add/quantity/forOne/Product',[ApiCartController::class,'AddQuantityForOneProduct']);
    Route::delete('/delete/cart/products',[ApiCartController::class,'deleteCartProducts']);
    Route::get('/get/cart/products',[ApiCartController::class,'getCartProducts']);
    Route::post('/add/toCart',[ApiCartController::class,'addToCart']);

    // product section for authorized users
    Route::post('/create/product',[ApiProductController::class,'createProduct']);
    Route::put('/update/product',[ApiProductController::class,'updateProduct']);
    Route::delete('/delete/product',[ApiProductController::class,'deleteProduct']);

    // category section for authorized users
    Route::post('/create/category',[ApiCategoryController::class,'createCategory']);

    // sub category section for authorized users
    Route::post('/create/sub/category',[ApiSubCategoryController::class,'createSubCategory']);

    // big store section for authorized users
    Route::post('/add-Big-Store',[ApiBigStoreController::class,'addBigStore']);
});

// user section for unauthorized users
Route::post('/register',[ApiRegisterController::class,'register']);
Route::post('/login',[ApiRegisterController::class,'login']);

// product section for unauthorized users
Route::get('/get/product/list',[ApiProductController::class,'getProductList']);
Route::get('/filter/product',[ApiProductController::class,'filterProduct']);

// sub category section for unauthorized users
Route::get('/get/sub/catagories',[ApiSubCategoryController::class,'getSubCategories']);

// category section for unauthorized users
Route::get('/get/catagories',[ApiCategoryController::class,'getCategories']);

// big store section for unauthorized users
Route::get('/get/bigStores',[ApiBigStoreController::class,'getBigStores']);
Route::get('/get/bigStore',[ApiBigStoreController::class,'getBigStore']);


















