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
        $feedbacks = Feedback::with('user')->get();

        return response()->json($feedbacks);
    }
}
