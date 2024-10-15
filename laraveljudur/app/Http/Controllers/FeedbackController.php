<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{


    public function store(Request $request)
    {
        $request->validate([
            'feedback' => 'required|string|max:1000',
        ]);

        $userId = Auth::id();

        $feedback = Feedback::create([
            'user_id' => $userId,
            'feedback' => $request->input('feedback'),
        ]);

        return response()->json([
            'message' => 'Feedback submitted successfully!',
            'data' => $feedback,
        ], 201);
    }



    public function index()
    {
        // Get all feedbacks with the associated user data
        $feedbacks = Feedback::with('user')->get();

        // Loop through each feedback to include the user's profile picture
        $feedbacksWithUserImage = $feedbacks->map(function ($feedback) {
            if ($feedback->user) {
                // Check if the user has a profile picture
                if ($feedback->user->profile_picture) {
                    // Generate the full URL to the profile picture
                    $feedback->user->profile_picture = asset('storage/' . $feedback->user->profile_picture);
                    // $user->profile_picture ? asset('storage/' . $user->profile_picture)
                } else {
                    // Provide a default image if none is uploaded
                    $feedback->user->image_url = url('storage/profile_pictures/default.png');
                }
            }

            return $feedback;
        });

        return response()->json($feedbacksWithUserImage);
    }
    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();
        return response()->json(['message' => 'Feedback deleted successfully']);
    }
}
