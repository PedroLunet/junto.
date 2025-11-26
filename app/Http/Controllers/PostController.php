<?php

namespace App\Http\Controllers;

use App\Models\Post;
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
        ]);

        if (! $request->filled('content') && ! $request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'Post must contain either text or an image',
            ], 422);
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

            // get the post to check ownership
            $post = DB::selectOne('
                SELECT p.*, sp.text, sp.imageUrl 
                FROM lbaw2544.post p
                JOIN lbaw2544.standard_post sp ON p.id = sp.postId
                WHERE p.id = ?
            ', [$id]);

            if (! $post) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }

            // check if user owns the post
            if ($post->userid !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $imagePath = $post->imageurl;

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
            DB::update('
                UPDATE lbaw2544.standard_post 
                SET text = ?, imageUrl = ?
                WHERE postId = ?
            ', [$request->input('content'), $imagePath, $id]);

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

            // get the post to check ownership
            $post = DB::selectOne('SELECT * FROM lbaw2544.post WHERE id = ?', [$id]);

            if (! $post) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }

            // check if user owns the post
            if ($post->userid !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $standardPost = DB::selectOne('SELECT imageUrl FROM lbaw2544.standard_post WHERE postId = ?', [$id]);
            if ($standardPost && $standardPost->imageurl && $standardPost->imageurl !== 'default.jpg') {
                Storage::disk('FileStorage')->delete('post/'.$standardPost->imageurl);
            }

            DB::delete('DELETE FROM lbaw2544.post WHERE id = ?', [$id]);

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
}
