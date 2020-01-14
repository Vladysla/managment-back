<?php

use Illuminate\Http\Request;

Route::group(['prefix' =>     'auth'], function () {
    Route::post('login',                   'AuthController@login');
    Route::post('signup',                  'AuthController@signup');
});

Route::group(['middleware' => ['auth:api', 'admin']], function (){
    Route::post('/products',               'ProductController@storeProduct');
    Route::get( 'user',                    'AuthController@user');
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::get( 'logout',                  'AuthController@logout');

    Route::get( '/my/products',            'ProductController@getSeparatedProductsForPlace');
    // TRANSFER
    Route::post('/my/transfer',            'TransferController@transferProducts');
    Route::get( '/my/transfer/get',        'TransferController@getListMyIncomeProducts');
    Route::get( '/my/transfer/history',    'TransferController@getListMyHistory');
    Route::post('/my/transfer/apply',      'TransferController@applyTransfer');
    Route::post('/my/transfer/cancel',     'TransferController@cancelTransfer');
    // SELLING
    Route::post('/my/sell',                'SellController@sellProducts');
    Route::get( '/my/sell/history',        'SellController@getSoldProductsPerDay');
    Route::get( '/my/sell/history/{date}', 'SellController@getListHistoryByDate');
});

Route::get('/products',                    'ProductController@getAllAvailableProducts');
Route::get('/product/{id}',                'ProductController@getProductInfo');

Route::get('/places',                      'ProductController@getPlaces');
Route::get('/types',                       'ProductController@getTypes');
Route::get('/colors',                      'ProductController@getColors');
Route::get('/sizes',                       'ProductController@getSizes');
Route::get('/models',                      'ProductController@getAllModels');

Route::get('/sale/products',               'ProductController@getSoldProducts');
Route::get('/currency',                    'ProductController@getCurrency');

// TESTS
Route::get('/test',                    'ProductController@testDatabase');
Route::get('/test-sell', 'SellController@testDatabaseSell');
