<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User\User;
use App\Models\Post\Post;
use App\Models\User\Friendship;
use App\Services\FriendService;
use App\Services\FavoriteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    protected $friendService;
    protected $favoriteService;

    public function __construct(FriendService $friendService, FavoriteService $favoriteService)
    {
        $this->friendService = $friendService;
        $this->favoriteService = $favoriteService;
    }

    public function index(): RedirectResponse
    {
        return redirect('/' . Auth::user()->username);
    }

    public function show($username): View
    {
        $user = User::with(['favoriteFilmMedia', 'favoriteBookMedia', 'favoriteSongMedia'])
            ->where('username', $username)
            ->firstOrFail();

        // Guests can view posts if profile is public
        if (!$user->isprivate) {
            $canViewPosts = true;
        } else {
            $canViewPosts = Auth::check() ? Gate::allows('viewPosts', $user) : false;
        }

        // get posts only if user can view them
        $posts = collect();
        $standardPosts = collect();
        $reviewPosts = collect();

        if ($canViewPosts) {
            // get all posts with relationships
            $allPosts = Post::with(['standardPost', 'review.media', 'user'])
                ->where('userid', $user->id)
                ->orderBy('createdat', 'desc')
                ->get();

            // transform the data to match the expected format
            $posts = $allPosts->map(function ($post) {
                // get actual likes and comments counts from database
                $likesCount = \Illuminate\Support\Facades\DB::select("
                    SELECT COUNT(*) as count FROM lbaw2544.post_like 
                    WHERE postId = ?
                ", [$post->id])[0]->count ?? 0;

                $commentsCount = \Illuminate\Support\Facades\DB::select("
                    SELECT COUNT(*) as count FROM lbaw2544.comment 
                    WHERE postId = ?
                ", [$post->id])[0]->count ?? 0;

                // check if current user liked this post
                $isLiked = false;
                if (Auth::check()) {
                    $likedCheck = \Illuminate\Support\Facades\DB::select("
                        SELECT COUNT(*) as count FROM lbaw2544.post_like 
                        WHERE postId = ? AND userId = ?
                    ", [$post->id, Auth::id()])[0]->count ?? 0;
                    $isLiked = $likedCheck > 0;
                }

                $transformedPost = (object) [
                    'id' => $post->id,
                    'created_at' => $post->createdat,
                    'author_name' => $post->user->name,
                    'username' => $post->user->username,
                    'likes_count' => $likesCount,
                    'comments_count' => $commentsCount,
                    'is_liked' => $isLiked,
                ];

                // standard post data
                if ($post->standardPost) {
                    $transformedPost->content = $post->standardPost->text;
                    $transformedPost->image_url = $post->standardPost->imageurl;
                    $transformedPost->post_type = 'standard';
                }

                // review data
                if ($post->review) {
                    $transformedPost->content = $post->review->content;
                    $transformedPost->rating = $post->review->rating;
                    $transformedPost->media_title = $post->review->media ? $post->review->media->title : 'Unknown Media';
                    $transformedPost->media_poster = $post->review->media ? $post->review->media->coverimage : null;
                    $transformedPost->media_creator = $post->review->media ? $post->review->media->creator : null;
                    $transformedPost->media_year = $post->review->media ? $post->review->media->releaseyear : null;

                    // Determine media type by checking related tables
                    if ($post->review->media) {
                        $mediaId = $post->review->media->id;
                        if (\App\Models\Media\Book::where('mediaid', $mediaId)->exists()) {
                            $transformedPost->media_type = 'book';
                        } elseif (\App\Models\Media\Music::where('mediaid', $mediaId)->exists()) {
                            $transformedPost->media_type = 'music';
                        } else {
                            $transformedPost->media_type = 'movie'; // default for films
                        }
                    } else {
                        $transformedPost->media_type = 'movie';
                    }

                    $transformedPost->post_type = 'review';
                }

                return $transformedPost;
            });

            // separate standard posts and reviews for tabs
            $standardPosts = $posts->filter(function ($post) {
                return $post->post_type === 'standard';
            });

            $reviewPosts = $posts->filter(function ($post) {
                return $post->post_type === 'review';
            });
        }

        $friendsCount = Friendship::where('userid1', $user->id)
            ->orWhere('userid2', $user->id)
            ->count();

        $postsCount = Post::where('userid', $user->id)->count();

        // friend button data using the service
        $friendButtonData = $this->friendService->getFriendButtonData($user);

        // get pending friend requests count for notification dot
        $pendingRequestsCount = 0;
        if (Auth::id() === $user->id) {
            $pendingRequestsCount = \App\Models\User\FriendRequest::whereHas('request.notification', function ($query) use ($user) {
                $query->where('receiverid', $user->id);
            })
                ->whereHas('request', function ($query) {
                    $query->where('status', 'pending');
                })
                ->count();
        }

        return view('pages.profile', compact('user', 'posts', 'standardPosts', 'reviewPosts', 'friendsCount', 'postsCount', 'canViewPosts', 'friendButtonData', 'pendingRequestsCount'));
    }

    public function removeFavorite(Request $request)
    {
        $request->validate([
            'type' => 'required|in:book,movie,music'
        ]);

        $result = $this->favoriteService->removeFavorite($request->input('type'));

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json($result);
    }

    public function addFavorite(Request $request)
    {
        $request->validate([
            'type' => 'required|in:book,movie,music',
            'title' => 'required|string|max:255',
            'creator' => 'nullable|string|max:255',
            'releaseYear' => 'nullable|integer|min:1000|max:' . (date('Y') + 10),
            'coverImage' => 'nullable|url|max:500',
        ]);

        $result = $this->favoriteService->addFavorite($request->all());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json($result);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate the input

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'bio' => 'nullable|string|max:1000',
            'profilePicture' => 'nullable|image|max:4096', // max 4MB
        ]);

        try {
            $updateData = [
                'name' => $request->input('name'),
                'username' => $request->input('username'),
                'bio' => $request->input('bio'),
            ];


            // Handle profile picture upload or reset
            if ($request->has('reset_profile_picture') && $request->input('reset_profile_picture') == '1') {
                // User requested to reset to default
                $updateData['profilepicture'] = null;
            } else if ($request->hasFile('profilePicture')) {
                $file = $request->file('profilePicture');
                $fileName = $file->hashName();
                $file->storeAs('profile', $fileName, 'FileStorage');
                $updateData['profilepicture'] = $fileName;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'redirect_url' => '/' . $request->input('username') // redirect to new username if changed
            ]);
        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating your profile'
            ], 500);
        }
    }

    /**
     * Show the form for editing the authenticated user's profile.
     */
    public function edit(): View
    {
        $user = Auth::user();
        return view('pages.edit-profile', compact('user'));
    }

    /**
     * Toggle the privacy setting of the authenticated user's profile.
     */
    public function togglePrivacy(Request $request)
    {
        $user = Auth::user();

        try {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['isprivate' => !$user->isprivate]);

            return response()->json([
                'success' => true,
                'message' => 'Privacy setting updated successfully',
                'isprivate' => !$user->isprivate
            ]);
        } catch (\Exception $e) {
            Log::error('Privacy toggle error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating your privacy setting'
            ], 500);
        }
    }

    /**
     * Change the password of the authenticated user.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:12',
                'regex:/[a-z]/',      // at least one lowercase
                'regex:/[A-Z]/',      // at least one uppercase
                'regex:/[0-9]/',      // at least one number
                'regex:/[@$!%*#?&]/', // at least one special character
                'confirmed'
            ],
        ]);

        $user = Auth::user();

        // Verify old password
        if (!Hash::check($request->old_password, $user->passwordhash)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Check if new password is different from old
        if ($request->old_password === $request->new_password) {
            return response()->json([
                'success' => false,
                'message' => 'New password must be different from current password'
            ], 400);
        }

        try {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['passwordhash' => Hash::make($request->new_password)]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Password change error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while changing your password'
            ], 500);
        }
    }

    /**
     * Validate if the provided password matches the user's current password.
     */
    public function validatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = User::find(Auth::id());
        $isValid = Hash::check($request->password, $user->passwordhash);

        return response()->json([
            'valid' => $isValid
        ]);
    }
}
