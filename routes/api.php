<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\ReviewController;

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


Route::get('/listings', [ListingController::class, 'index']);

Route::get('/search-by-asins/{asin}', [ListingController::class, 'getListingData']);

Route::post('save-review', [ReviewController::class, 'store']);
