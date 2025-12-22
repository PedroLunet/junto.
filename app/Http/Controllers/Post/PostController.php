<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
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

            $result = DB::selectOne('
                INSERT INTO lbaw2544.post (userId, createdAt) 
                VALUES (?, CURRENT_TIMESTAMP) 
                RETURNING id
            ', [Auth::id()]);

            $postId = $result->id;

            DB::insert('
                INSERT INTO lbaw2544.standard_post (postId, text, imageUrl) 
                VALUES (?, ?, ?)
            ', [$postId, $request->input('content'), $imagePath]);

            $tags = $request->input('tags', []);
            if (! empty($tags)) {
                foreach ($tags as $userId) {
                    DB::insert('
                        INSERT INTO lbaw2544.post_tag (postId, userId, createdAt)
                        VALUES (?, ?, CURRENT_TIMESTAMP)
                    ', [$postId, $userId]);

                    if ((int)$userId !== Auth::id()) {
                        $notification = DB::table('lbaw2544.notification')->insertGetId([
                            'message' => 'You were tagged in a post',
                            'isread' => false,
                            'receiverid' => $userId,
                            'createdat' => now(),
                        ]);

                        DB::insert('
                            INSERT INTO lbaw2544.tag_notification (notificationid, postid, taggerid)
                            VALUES (?, ?, ?)
                        ', [$notification, $postId, Auth::id()]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'post_id' => $postId,
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

        $sql = "
            SELECT 
                p.id,
                p.createdAt as created_at,
                u.name as author_name,
                u.username,
                u.profilepicture as author_image,
                g.name as group_name,
                p.groupid as groupid,
                COALESCE(sp.text, r.content) as content,
                CASE 
                    WHEN sp.postid IS NOT NULL THEN 'standard'
                    WHEN r.postid IS NOT NULL THEN 'review'
                END as post_type,
                r.rating,
                m.title as media_title,
                m.coverimage as media_poster,
                m.releaseyear as media_year,
                m.creator as media_creator,
                CASE
                    WHEN EXISTS (SELECT 1 FROM lbaw2544.book b WHERE b.mediaid = m.id) THEN 'book'
                    WHEN EXISTS (SELECT 1 FROM lbaw2544.film f WHERE f.mediaid = m.id) THEN 'movie'
                    WHEN EXISTS (SELECT 1 FROM lbaw2544.music mu WHERE mu.mediaid = m.id) THEN 'music'
                END as media_type,
                (SELECT COUNT(*) FROM lbaw2544.post_like pl WHERE pl.postid = p.id) as likes_count,
                " . ($currentUserId ? '(SELECT COUNT(*) > 0 FROM lbaw2544.post_like pl WHERE pl.postid = p.id AND pl.userid = ?) as is_liked,' : 'FALSE as is_liked,') . "
                (SELECT COUNT(*) FROM lbaw2544.comment c WHERE c.postid = p.id) as comments_count,
                sp.imageurl as image_url
            FROM lbaw2544.post p
            JOIN lbaw2544.users u ON p.userid = u.id
            LEFT JOIN lbaw2544.groups g ON p.groupid = g.id
            LEFT JOIN lbaw2544.standard_post sp ON p.id = sp.postid
            LEFT JOIN lbaw2544.review r ON p.id = r.postid
            LEFT JOIN lbaw2544.media m ON r.mediaid = m.id
            WHERE p.id = ? AND u.isdeleted = false AND u.isblocked = false
        ";

        $params = $currentUserId ? [$currentUserId, $id] : [$id];
        $post = DB::selectOne($sql, $params);

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $posts = [$post];
        $posts = Post::attachTagsToPostData($posts);

        return response()->json(['post' => $posts[0]]);
    }
}
