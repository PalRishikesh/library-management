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


Route::post('login','UserController@login');
Route::post('users','UserController@store');

Route::group(['middleware' => ['jwt.verify']], function() {
//User
Route::get('users','UserController@index');
Route::get('users/{id}','UserController@edit');
Route::post('users/{id}','UserController@update');
Route::delete('users/{id}','UserController@delete');
Route::get('user-details','UserController@userDetails');

//Book
Route::get('books','BookController@index');
Route::post('books','BookController@store');
Route::get('books/{id}','BookController@edit');
Route::post('books/{id}','BookController@update');
Route::delete('books/{id}','BookController@delete');

//User Book
Route::post('user-rental','BookRentalController@user');
Route::post('book-rental','BookRentalController@book');
Route::post('user-book','BookRentalController@userBook');
Route::post('user-book-return/{id}','BookRentalController@returnUserBook');
Route::get('user-book-detail','BookRentalController@getUserBook');
Route::get('user-book/{id?}','BookRentalController@userRentalBook');
});

