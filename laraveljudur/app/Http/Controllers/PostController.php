<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        // Display only posts made by admin (user_id = 1)
        $posts = Post::where('user_id', 1)->get();

        // Format the response to include ID and image URL
        $formattedPosts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'imageUrl' => $post->image ? asset('storage/' . $post->image) : null, // Generate image URL
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ];
        });

        return response()->json($formattedPosts);
    }


    public function show($id)
    {
        $post = Post::with('comments')->findOrFail($id);
        return response()->json($post);
    }

    public function storeComment(Request $request, $postId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $comment = Comment::create([
            'post_id' => $postId,
            'name' => $request->name,
            'content' => $request->content,
        ]);

        return response()->json($comment, 201);
    }
    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'user_id' => 'required|exists:users,id', // Ensure user_id exists in the users table
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
    ]);

    // Handle the image upload
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('posts', 'public'); // Store in 'public/posts' directory
    }

    // Create the post
    $post = Post::create([
        'title' => $request->title,
        'content' => $request->content,
        'user_id' => $request->user_id,
        'image' => $imagePath, // Store the image path
    ]);

    // Return response with the post ID and image URL
    return response()->json([
        'id' => $post->id,
        'imageUrl' => asset('storage/' . $post->image), // Use asset() to generate the correct URL
    ], 201);
}
}
