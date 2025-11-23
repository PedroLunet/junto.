<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FriendRequestController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\SearchUserController;
use App\Http\Controllers\MusicController;

// Home
Route::redirect('/', '/login');

// Home page (authentication required)
Route::middleware('auth')->controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/posts/{id}/comments', 'getComments')->name('post.comments');
    Route::post('/posts/{id}/comments', 'addComment')->name('post.comments.add');
    Route::post('/posts/{id}/like', 'toggleLike')->name('post.like');
});



Route::middleware('auth')->controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'index')->name('profile');
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

Route::controller(SearchUserController::class)->group(function () {
    Route::get('/search-users', 'index')->name('search.users');
});

// Friend Requests (authentication required)
Route::middleware('auth')->controller(FriendRequestController::class)->group(function () {
    Route::get('/friend-requests', 'index')->name('friend-requests.index');
    Route::get('/friend-requests/sent', 'sent')->name('friend-requests.sent');
    Route::post('/friend-requests', 'store')->name('friend-requests.store');
    Route::post('/friend-requests/{requestId}/accept', 'accept')->name('friend-requests.accept');
    Route::post('/friend-requests/{requestId}/reject', 'reject')->name('friend-requests.reject');
    Route::delete('/friend-requests/{requestId}/cancel', 'cancel')->name('friend-requests.cancel');
    Route::get('/friends', 'friends')->name('friends.index');
    Route::delete('/friends/{userId}', 'unfriend')->name('friends.unfriend');
});

// temporary music routes
Route::middleware('auth')->controller(MusicController::class)->group(function () {
    Route::get('/music', 'search')->name('music.search');
    Route::post('/music', 'store')->name('music.store');
});

Route::controller('auth')->controller(ProfileController::class)->group(function () {
    Route::get('/{username}', 'show')->name('profile.show');
});



