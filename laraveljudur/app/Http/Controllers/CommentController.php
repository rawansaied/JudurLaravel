<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Store a new comment
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id', // Change from 'name' to 'user_id'
            'content' => 'required|string',
        ]);

        // Create and save the new comment
        $comment = Comment::create([
            'post_id' => $validatedData['post_id'],
            'user_id' => $validatedData['user_id'], // Change from 'name' to 'user_id'
            'content' => $validatedData['content'],
        ]);

        // Return the newly created comment as a JSON response
        return response()->json($comment, 201);
    }

    // Get comments for a specific post
    public function getCommentsByPost($post_id)
    {
        // Fetch comments related to the given post ID, sorted by the latest first
        $comments = Comment::where('post_id', $post_id)->orderBy('created_at', 'desc')->get();

        // Return the comments as a JSON response
        return response()->json($comments);
    }
}
