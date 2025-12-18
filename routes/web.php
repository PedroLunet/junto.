<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Friendship\FriendRequestController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Media\BookController;
use App\Http\Controllers\Media\MovieController;
use App\Http\Controllers\Media\MusicController;
use App\Http\Controllers\Post\CommentController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\Post\ReportController;
use App\Http\Controllers\Post\ReviewController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Search\SearchUserController;
use App\Http\Controllers\User\ProfileController;
use Illuminate\Support\Facades\Route;

// Home
// Route::redirect('/', '/login');

Route::middleware('regular.user')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/posts/{id}/comments', [CommentController::class, 'index'])->name('post.comments');
});

// Home page (authentication required)
Route::middleware(['auth', 'regular.user'])->group(function () {
    Route::get('/friends-feed', [HomeController::class, 'friendsFeed'])->name('friends-feed');
    Route::post('/posts/{id}/comments', [CommentController::class, 'store'])->name('post.comments.add');
    Route::post('/posts/{id}/like', [HomeController::class, 'toggleLike'])->name('post.like');
});


Route::middleware(['auth', 'regular.user'])->controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'index')->name('profile');
    Route::get('/profile/edit', 'edit')->name('profile.edit');
    Route::put('/profile/update', 'update')->name('profile.update');
    Route::post('/profile/remove-favorite', 'removeFavorite')->name('profile.remove-favorite');
    Route::post('/profile/add-favorite', 'addFavorite')->name('profile.add-favorite');
    Route::post('/profile/toggle-privacy', 'togglePrivacy')->name('profile.toggle-privacy');
    Route::post('/profile/change-password', 'changePassword')->name('profile.change-password');
    Route::post('/profile/validate-password', 'validatePassword')->name('profile.validate-password');
    Route::post('/profile/render-alert', 'renderAlert')->name('profile.render-alert');
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

Route::controller(GoogleController::class)->group(function () {
    Route::get('auth/google', 'redirect')->name('google-auth');
    Route::get('auth/google/call-back', 'callbackGoogle')->name('google-call-back');
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

// Friends route with username
Route::middleware('regular.user')->controller(FriendRequestController::class)->group(function () {
    Route::get('/friends-{username}', 'friendsByUsername')->name('friends.by-username');
});

// movie routes
Route::middleware(['auth', 'regular.user'])->controller(MovieController::class)->group(function () {
    Route::get('/movies', 'index')->name('movies');
    Route::get('/movies/search', 'search')->name('movies.search');
    Route::get('/movies/{id}', 'show')->name('movies.show');
});

// temporary music routes
Route::middleware(['auth', 'regular.user'])->controller(MusicController::class)->group(function () {
    Route::get('/music', 'index')->name('music');
    Route::get('/music/search', 'search')->name('music.search');
    Route::post('/music', 'store')->name('music.store');
});

// books routes
Route::middleware(['auth', 'regular.user'])->controller(BookController::class)->group(function () {
    Route::get('/books', 'index')->name('books');
    Route::get('/books/search', 'search')->name('books.search');
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
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/admin/users/{id}/block', [AdminController::class, 'blockUser'])->name('admin.users.block');
    Route::post('/admin/users/{id}/unblock', [AdminController::class, 'unblockUser'])->name('admin.users.unblock');
    Route::get('/admin/reports', [AdminController::class, 'listReports'])->name('admin.reports');
    Route::post('/admin/reports/{id}/accept', [AdminController::class, 'acceptReport'])->name('admin.reports.accept');
    Route::post('/admin/reports/{id}/reject', [AdminController::class, 'rejectReport'])->name('admin.reports.reject');
    Route::get('/admin/groups', [AdminController::class, 'groups'])->name('admin.groups');
    Route::delete('/admin/groups/{id}', [AdminController::class, 'deleteGroup'])->name('admin.groups.delete');
});

// GROUPS ROUTES
Route::middleware(['auth'])->group(function () {
    Route::delete('/groups/{group}/remove-member/{user}', [GroupController::class, 'removeMember'])->name('groups.removeMember');
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::get('/groups/{group}/edit', [GroupController::class, 'edit'])->name('groups.edit');
    Route::put('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
    Route::post('/groups/{group}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');
    Route::post('/groups/{group}/cancel-request', [GroupController::class, 'cancelRequest'])->name('groups.cancelRequest');
    Route::post('/groups/{group}/accept-request/{requestId}', [GroupController::class, 'acceptRequest'])->name('groups.acceptRequest');
    Route::post('/groups/{group}/reject-request/{requestId}', [GroupController::class, 'rejectRequest'])->name('groups.rejectRequest');
    Route::post('/groups/{group}/posts', [GroupController::class, 'storePost'])->name('groups.posts.store');

    // --- ADDED THIS LINE ---
    Route::post('/groups/{group}/reviews', [ReviewController::class, 'store'])->name('groups.reviews.store');
});

// Notifications
Route::middleware(['auth', 'regular.user'])->controller(NotificationController::class)->group(function () {
    Route::get('/notifications', 'index')->name('notifications.index');
    Route::post('/notifications/{id}/read', 'markAsRead')->name('notifications.mark-read');
    Route::post('/notifications/read-all', 'markAllAsRead')->name('notifications.mark-all-read');
    Route::post('/notifications/{id}/snooze', 'snooze')->name('notifications.snooze');
    Route::get('/notifications/unread-count', 'getUnreadCount')->name('notifications.unread-count');
});

// Static pages
Route::group([], function () {
    Route::get('/about', function () {
        return view('pages.about');
    })->name('about');
});


Route::middleware('regular.user')->controller(ProfileController::class)->group(function () {
    Route::get('/{username}', 'show')->name('profile.show');
});
