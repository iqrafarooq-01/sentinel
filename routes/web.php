<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// // Login Registration Routes
Route::get('/login', 'App\Http\Controllers\Security\AuthenticationController@login');
Route::post('/login-user', 'App\Http\Controllers\Security\AuthenticationController@loginUser')->name('login-user');

Route::get('/register', 'App\Http\Controllers\Security\AuthenticationController@register');
Route::post('/register-user', 'App\Http\Controllers\Security\AuthenticationController@registerUser')->name('register-user');

Route::group(['prefix' => '/', 'middleware' => ['auth']], function () {

    // Product Route
    Route::resource('/products', 'App\Http\Controllers\ProductController');

    Route::get('/dashboard', 'App\Http\Controllers\Security\AuthenticationController@dashboard');

    Route::get('/logout', 'App\Http\Controllers\Security\AuthenticationController@logout');
});
