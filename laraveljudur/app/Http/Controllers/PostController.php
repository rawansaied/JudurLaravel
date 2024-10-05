<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        // Include the image URL in the response
        $post->imageUrl = asset('storage/' . $post->image);
        
        return response()->json($post);
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

    public function update(Request $request, $id)
    {
        // Validate the input fields
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
            'image' => 'nullable|string' // Accept the image as a base64 encoded string
        ]);

        // Fetch the post
        $post = Post::findOrFail($id);
        $post->title = $request->input('title');
        $post->content = $request->input('content');
        $post->category = $request->input('category');

        // Handle base64 encoded image
        if ($request->image) {
            // Extract the image data
            $imageData = $request->input('image');
            // Decode the image (removing the data:image/jpeg;base64, part)
            $imageData = explode(',', $imageData)[1];
            $imageName = time() . '.jpg';
            Storage::disk('public')->put('uploads/' . $imageName, base64_decode($imageData));

            // Save the file path to the database
            $post->image = 'uploads/' . $imageName;
        }

        // Save the post
        $post->save();

        return response()->json($post, 200);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        // Delete image if exists
        if ($post->image) {
            Storage::delete('public/images/' . $post->image);
        }

        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
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
}
