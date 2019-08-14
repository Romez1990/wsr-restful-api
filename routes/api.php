<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('product')->group(function () {
    Route::get('/', 'ProductController@index');
    Route::post('/', 'ProductController@store');
    Route::get('{product_id}', 'ProductController@show');
    Route::post('{product_id}', 'ProductController@update');
    Route::delete('{product_id}', 'ProductController@destroy');
    Route::get('tag/{tag_name}', 'ProductController@searchByTag');
});
