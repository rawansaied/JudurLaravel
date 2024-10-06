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
        $posts = Post::all();
    
        // Map posts to include the image URL
        $posts->map(function ($post) {
            $post->image = asset('storage/' . $post->image); // Assuming the images are stored in the 'storage' folder
            return $post;
        });
    
        return response()->json($posts);
    }
    public function show($id)
    {
        $post = Post::find($id);
    
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
    
        // Include the image URL in the response
        $post->image = asset('storage/' . $post->image);
    
        return response()->json($post);
    }
    

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            
        ]);
    
        $postData = $request->all();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $postData['image'] = $imagePath; // Store the path
        }
    
        $post = Post::create($postData);
        return response()->json($post, 201);
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
