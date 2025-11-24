<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\SearchUserController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\PostController;


// Home
// Route::redirect('/', '/login');

Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/posts/{id}/comments', 'getComments')->name('post.comments');
});

// Home page (authentication required)
Route::middleware('auth')->controller(HomeController::class)->group(function () {
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

// movie routes
Route::middleware('auth')->controller(MovieController::class)->group(function () {
    Route::get('/movies', 'index')->name('movies');
    Route::get('/movies/search', 'search')->name('movies.search');
});

// temporary music routes
Route::middleware('auth')->controller(MusicController::class)->group(function () {
    Route::get('/music', 'search')->name('music.search');
    Route::post('/music', 'store')->name('music.store');
});

// books routes
Route::middleware('auth')->controller(BookController::class)->group(function () {
    Route::get('/books', 'search')->name('books.search');
    Route::post('/books', 'store')->name('books.store');
});

// posts routes
Route::middleware('auth')->group(function () {
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
});

// reports routes
Route::middleware('auth')->controller(ReportController::class)->group(function () {
    Route::post('/posts/{id}/report', 'reportPost')->name('post.report');
    Route::get('/reports', 'index')->name('reports.index')->middleware('admin');
    Route::get('/reports/pending', 'pending')->name('reports.pending')->middleware('admin');
    Route::post('/reports/{id}/status', 'updateStatus')->name('reports.update')->middleware('admin');
});

Route::controller('auth')->controller(ProfileController::class)->group(function () {
    Route::get('/{username}', 'show')->name('profile.show');
});

