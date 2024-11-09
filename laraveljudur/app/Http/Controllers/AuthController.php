<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Donor;
use App\Models\Notification;
use App\Models\Volunteer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        Log::info('Register request received', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required',
            'age' => 'required|integer|gte:18',
            'phone' => 'required',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'age' => $request->age,
                'phone' => $request->phone,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User registered successfully', ['user_id' => $user->id]);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (\Exception $e) {
            Log::error('Error registering user: ' . $e->getMessage());
            return response()->json(['message' => 'Error registering user'], 500);
        }
    }

    public function registerDonor(Request $request)
    {
        Log::info('Register Donor request received', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'age' => 'required|integer|gte:18',
            'phone' => 'required|string',
            'profile_picture' => [
            'nullable',
            'string',
            function ($attribute, $value, $fail) {
                $data = explode(';', $value);
                if (count($data) < 2 || !str_starts_with($data[0], 'data:image/jpeg') && !str_starts_with($data[0], 'data:image/png')) {
                    return $fail('The profile picture must be a valid JPEG or PNG image.');
                }
            }
        ]
        ]);

        DB::beginTransaction();
        try {
            Log::info('Creating donor user', ['email' => $validated['email']]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => 2,
                'age' => $validated['age'],
                'phone' => $validated['phone'],
            ]);

            if ($request->filled('profile_picture')) {
                Log::info('Processing profile picture for user', ['user_id' => $user->id]);

                $imageData = base64_decode($validated['profile_picture']);

                $imageName = uniqid('profile_pictures/') . '.png';

                Storage::disk('public')->put("{$imageName}", $imageData);

                $user->profile_picture = $imageName;
                $user->save();
            }

            $donorIdNumber = uniqid('DONOR-');
            Log::info('Creating donor record', ['user_id' => $user->id]);

            Donor::create([
                'user_id' => $user->id,
                'donor_id_number' => $donorIdNumber,
            ]);

            DB::commit();
            Log::info('Donor registered successfully', ['user_id' => $user->id]);

            return response()->json(['message' => 'Donor registered successfully', 'data' => $user], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error registering donor: ' . $e->getMessage());

            return response()->json(['message' => 'Error registering donor'], 500);
        }
    }


    public function registerVolunteer(Request $request)
    {
        Log::info('Register Volunteer request received', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'age' => 'required|integer',
            'phone' => 'required|string',
            'skills' => 'required|string',
            'availability' => 'required|string',
            'aim' => 'required|string',
            'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            Log::info('Creating volunteer user', ['email' => $validated['email']]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => 3,
                'age' => $validated['age'],
                'phone' => $validated['phone'],
                'profile_picture' => $this->handleProfilePictureUpload($request),
            ]);

            Volunteer::create([
                'user_id' => $user->id,
                'skills' => $validated['skills'],
                'availability' => $validated['availability'],
                'aim' => $validated['aim'],
                'volunteer_status' => 1,
            ]);

            $admins = User::where('role_id', 1)->get();
            $mentors = User::where('role_id', 6)->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'message' => 'A new volunteer request ' . $user->name . ' has been made. Please review the request through the volunteers management page.',
                    'is_read' => false,
                ]);
            }

            foreach ($mentors as $mentor) {
                Notification::create([
                    'user_id' => $mentor->id,
                    'message' => 'A new volunteer request ' . $user->name . ' has been submitted. Kindly check the volunteers management page for details.',
                    'is_read' => false,
                ]);
            }


            Log::info('Volunteer registered successfully', ['user_id' => $user->id]);

            return response()->json(['message' => 'Volunteer registered successfully', 'data' => $user], 201);
        } catch (\Exception $e) {
            Log::error('Error registering volunteer: ' . $e->getMessage());
            return response()->json(['message' => 'Error registering volunteer'], 500);
        }
    }

    protected function handleProfilePictureUpload($request)
    {
        if ($request->hasFile('profilePicture')) {
            $file = $request->file('profilePicture');
            $path = $file->store('profile_pictures', 'public');
            return $path;
        }
        return null;
    }

    // Log in a user
    public function login(Request $request)
    {
        Log::info('Login request received', $request->only('email'));

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            Log::warning('Invalid login attempt', ['email' => $request->email]);
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('User logged in', ['user_id' => $user->id]);

        $volunteer = Volunteer::where('user_id', $user->id)->first();
        $user->examiner = $volunteer ? $volunteer->examiner : 0;
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'You are logged in',
            'user' => $user,
        ]);
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            Log::info('User logged out', ['user_id' => $request->user()->id]);

            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return response()->json(['error' => 'Logout failed', 'message' => $e->getMessage()], 500);
        }
    }



    //     public function redirectToGoogle()
    //     {
    //         return Socialite::driver('google')->redirect();
    //     }

    //     public function handleGoogleCallback(Request $request)
    //     {
    //         try {
    //             $user = Socialite::driver('google')->user();
    //             $findUser = User::where('google_id', $user->id)->first();

    //             if ($findUser) {
    //                 Auth::login($findUser);
    //                 return response()->json(['message' => 'Login successful', 'user' => $findUser]);
    //             } else {
    //                 $newUser = User::create([
    //                     'name' => $user->name,
    //                     'email' => $user->email,
    //                     'google_id' => $user->id,
    //                     'password' => bcrypt('dummy_password')
    //                 ]);

    //                 Auth::login($newUser);
    //                 return response()->json(['message' => 'User created and logged in', 'user' => $newUser]);
    //             }
    //         } catch (\Exception $e) {
    //             return response()->json(['error' => $e->getMessage()], 500);
    //         }
    //     }

    //     public function redirectToProvider()
    // {
    //     return Socialite::driver('github')->redirect();
    // }

    // public function handleProviderCallback()
    // {
    //     $githubUser = Socialite::driver('github')->user();

    //     if (!$githubUser) {
    //         return response()->json(['error' => 'GitHub authentication failed.'], 401);
    //     }

    //     $user = User::firstOrCreate(
    //         ['github_id' => $githubUser->getId()],
    //         [
    //             'name' => $githubUser->getName(),
    //             'email' => $githubUser->getEmail(),
    //             'avatar' => $githubUser->getAvatar(),
    //         ]
    //     );

    //     Auth::login($user);

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return redirect()->to(env('FRONTEND_URL') . '/login-success?token=' . $token);
    // }


}
