<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id', 
            'content' => 'required|string',
        ]);
        $comment = Comment::create([
            'post_id' => $validatedData['post_id'],
            'user_id' => $validatedData['user_id'], 
            'content' => $validatedData['content'],
        ]);

        return response()->json($comment, 201);
    }

    public function getCommentsByPost($post_id)
    {
        $comments = Comment::with('user')->where('post_id', $post_id)->orderBy('created_at', 'desc')->get();

        return response()->json($comments);
    }
    public function deleteComment($commentId)
{
    $comment = Comment::find($commentId);

    if ($comment) {
        $comment->delete(); 
        return response()->json(['message' => 'Comment permanently deleted.'], 200);
    }

    return response()->json(['error' => 'Comment not found.'], 404);
}

}
