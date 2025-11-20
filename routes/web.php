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

// temporary test route for posts
Route::get('/test-posts', function () {
    $posts = Post::getPostsWithDetails();
    
    echo "<h1>Posts from Database:</h1>";
    foreach($posts as $post) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
        echo "<strong>{$post->author_name} (@{$post->username})</strong><br>";
        if($post->rating) echo "<em>Rating: {$post->rating}/5 for {$post->media_title}</em><br>";
        echo "<p>{$post->content}</p>";
        echo "</div>";
    }
});

