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
        ]);

         try {
            DB::beginTransaction();

            $result = DB::selectOne("
                INSERT INTO lbaw2544.post (userId, createdAt) 
                VALUES (?, CURRENT_TIMESTAMP) 
                RETURNING id
            ", [Auth::id()]);

            $postId = $result->id;

            DB::insert("
                INSERT INTO lbaw2544.standard_post (postId, text) 
                VALUES (?, ?)
            ", [$postId, $request->input('content')]);

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