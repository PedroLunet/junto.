<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use App\Models\Post\StandardPost;
use App\Models\Notification;
use App\Models\Notification\TagNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:users,id',
        ]);

        if (! $request->filled('content') && ! $request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'Post must contain either text or an image',
            ], 422);
        }

        $tags = $request->input('tags', []);
        if (! empty($tags)) {
            $tags = \App\Models\User\User::whereIn('id', $tags)
                ->where('isadmin', false)
                ->pluck('id')
                ->toArray();
            
            if (!empty($tags)) {
                $friendIds = Auth::user()->friends()->pluck('id')->toArray();
                $invalidTags = array_filter($tags, function ($userId) use ($friendIds) {
                    return !in_array($userId, $friendIds) && $userId !== Auth::id();
                });
                if (! empty($invalidTags)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only tag friends',
                    ], 403);
                }
            }
        }

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $fileName = $request->file('image')->hashName();
                $request->file('image')->storeAs('post', $fileName, 'FileStorage');
                $imagePath = $fileName;
            }

            $post = Post::create([
                'userid' => Auth::id(),
                'createdat' => now(),
            ]);

            StandardPost::create([
                'postid' => $post->id,
                'text' => $request->input('content'),
                'imageurl' => $imagePath,
            ]);

            $tags = $request->input('tags', []);
            if (! empty($tags)) {
                foreach ($tags as $userId) {
                    $post->tags()->attach($userId, ['createdat' => now()]);

                    if ((int)$userId !== Auth::id()) {
                        $notification = Notification::create([
                            'message' => 'You were tagged in a post',
                            'isread' => false,
                            'receiverid' => $userId,
                            'createdat' => now(),
                        ]);

                        TagNotification::create([
                            'notificationid' => $notification->id,
                            'postid' => $post->id,
                            'taggerid' => Auth::id(),
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'post_id' => $post->id,
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Server Error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $post = Post::with('standardPost')->findOrFail($id);
            $this->authorize('update', $post);

            $imagePath = $post->standardPost->imageUrl;

            // image removal
            if ($request->has('remove_image') && $request->remove_image == '1') {
                if ($imagePath && $imagePath !== 'default.jpg') {
                    Storage::disk('FileStorage')->delete('post/'.$imagePath);
                    $imagePath = null;
                }
            }

            // new image upload
            if ($request->hasFile('image')) {
                // delete old image if exists
                if ($imagePath && $imagePath !== 'default.jpg') {
                    Storage::disk('FileStorage')->delete('post/'.$imagePath);
                }
                $fileName = $request->file('image')->hashName();
                $request->file('image')->storeAs('post', $fileName, 'FileStorage');
                $imagePath = $fileName;
            }

            // update the post
            $post->standardPost->update([
                'text' => $request->input('content'),
                'imageurl' => $imagePath,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Post updated successfully']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Server Error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $post = Post::with('standardPost')->findOrFail($id);
            $this->authorize('delete', $post);

            if ($post->standardPost && $post->standardPost->imageUrl && $post->standardPost->imageUrl !== 'default.jpg') {
                Storage::disk('FileStorage')->delete('post/'.$post->standardPost->imageUrl);
            }

            $post->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Post deleted successfully']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Server Error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $currentUserId = Auth::id();
        $post = Post::getPostDetails($id, $currentUserId);

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        return response()->json(['post' => $post]);
    }
}
