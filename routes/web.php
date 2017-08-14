<?php

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

Route::get('/', function () {
    return view('index');
});

Route::get('/fashida','FashidaController@index');
Route::get('/fashida/collection','FashidaController@collection');

Route::get('/search','SearchController@index');

Route::get('/detail', 'ResourceController@index');
Route::post('/import', 'ResourceController@import');


Route::get('/upload', "ResourceController@upload");
