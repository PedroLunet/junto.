<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller {
    public function store(Request $request) {
        $request->validate([
            'content' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

         try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {

                // storage/app/public/posts
                $path = $request->file('image')->store('posts', 'public');

                $imagePath = $path; 
            }

            $result = DB::selectOne("
                INSERT INTO lbaw2544.post (userId, createdAt) 
                VALUES (?, CURRENT_TIMESTAMP) 
                RETURNING id
            ", [Auth::id()]);

            $postId = $result->id;

            DB::insert("
                INSERT INTO lbaw2544.standard_post (postId, text, imageUrl) 
                VALUES (?, ?, ?)
            ", [$postId, $request->input('content'), $imagePath]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'post_id' => $postId
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}