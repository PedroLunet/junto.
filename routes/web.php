<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;

use App\Http\Controllers\HomeController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;

// Home
Route::redirect('/', '/login');

// Home page (authentication required)
Route::middleware('auth')->controller(HomeController::class)->group(function () {
    Route::get('/home', 'index')->name('home');
    Route::get('/posts/{id}/comments', 'getComments')->name('post.comments');
    Route::post('/posts/{id}/comments', 'addComment')->name('post.comments.add');
});

// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
});

Route::controller(LogoutController::class)->group(function () {
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});



