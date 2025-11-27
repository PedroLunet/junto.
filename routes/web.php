<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AdminController;

use App\Http\Controllers\SearchUserController;
use Illuminate\Support\Facades\Route;

// Home
// Route::redirect('/', '/login');

Route::middleware('regular.user')->controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/posts/{id}/comments', 'getComments')->name('post.comments');
});

// Home page (authentication required)
Route::middleware(['auth', 'regular.user'])->controller(HomeController::class)->group(function () {
    Route::post('/posts/{id}/comments', 'addComment')->name('post.comments.add');
    Route::post('/posts/{id}/like', 'toggleLike')->name('post.like');
});

Route::middleware(['auth', 'regular.user'])->controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'index')->name('profile');
    Route::put('/profile/update', 'update')->name('profile.update');
    Route::post('/profile/remove-favorite', 'removeFavorite')->name('profile.remove-favorite');
    Route::post('/profile/add-favorite', 'addFavorite')->name('profile.add-favorite');
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

Route::middleware('regular.user')->controller(SearchUserController::class)->group(function () {
    Route::get('/search-users', 'index')->name('search.users');
});

// Friend Requests (authentication required)
Route::middleware(['auth', 'regular.user'])->controller(FriendRequestController::class)->group(function () {
    Route::get('/friend-requests', 'index')->name('friend-requests.index');
    Route::get('/friend-requests/sent', 'sent')->name('friend-requests.sent');
    Route::post('/friend-requests', 'store')->name('friend-requests.store');
    Route::post('/friend-requests/{requestId}/accept', 'accept')->name('friend-requests.accept');
    Route::post('/friend-requests/{requestId}/reject', 'reject')->name('friend-requests.reject');
    Route::delete('/friend-requests/{requestId}/cancel', 'cancel')->name('friend-requests.cancel');
    Route::get('/friends', 'friends')->name('friends.index');
    Route::delete('/friends/{userId}', 'unfriend')->name('friends.unfriend');
});

// movie routes
Route::middleware(['auth', 'regular.user'])->controller(MovieController::class)->group(function () {
    Route::get('/movies', 'index')->name('movies');
    Route::get('/movies/search', 'search')->name('movies.search');
    Route::get('/movies/{id}', 'show')->name('movies.show');
});

// temporary music routes
Route::middleware(['auth', 'regular.user'])->controller(MusicController::class)->group(function () {
    Route::get('/music', 'search')->name('music.search');
    Route::post('/music', 'store')->name('music.store');
});

// books routes
Route::middleware(['auth', 'regular.user'])->controller(BookController::class)->group(function () {
    Route::get('/books', 'search')->name('books.search');
    Route::post('/books', 'store')->name('books.store');
});

// posts routes
Route::middleware(['auth', 'regular.user'])->group(function () {
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{id}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
});

// file upload routes
Route::middleware('auth')->controller(FileController::class)->group(function () {
    Route::post('/file/upload', 'upload')->name('file.upload');
    Route::post('/file/delete', 'delete')->name('file.delete');
});

// reports routes
Route::middleware(['auth', 'regular.user'])->controller(ReportController::class)->group(function () {
    Route::post('/posts/{id}/report', 'reportPost')->name('post.report');
});

Route::middleware(['auth', 'admin'])->controller(ReportController::class)->group(function () {
    Route::get('/reports', 'index')->name('reports.index');
    Route::get('/reports/pending', 'pending')->name('reports.pending');
    Route::post('/reports/{id}/status', 'updateStatus')->name('reports.update');
});

// admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
});

Route::middleware('regular.user')->controller(ProfileController::class)->group(function () {
    Route::get('/{username}', 'show')->name('profile.show');
});
