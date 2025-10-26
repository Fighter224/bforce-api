<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
            'vehicle_type' => ['required', 'string'],
            'plate_number' => ['required', 'string', 'max:255'],
            'ic_no' => ['required', 'string'],
            'agreement_check' => ['nullable', 'boolean', 'in:1,true'],
            'phone' => ['required', 'string', 'max:255'],
            'profile_image' => ['nullable', 'file', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || $user->role !== 'technician') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Update phone on users table
        $user->phone = $request->input('phone');
        $user->save();

        // Handle profile image upload
        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        // Create or update user profile
        $profile = UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'vehicle_type' => $request->input('vehicle_type'),
                'plate_number' => $request->input('plate_number'),
                'ic_no' => $request->input('ic_no'),
                'agreement_check' => (bool) $request->input('agreement'),
                'profile_image' => $profileImagePath,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile saved',
            'profile' => $profile,
        ], 201);
    }

    public function getTechnicianProfile($email)
    {
        $user = User::where('email', $email)
        ->where('role', 'technician')
        ->first();


        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $userProfile = UserProfile::where('user_id', $user->id)->first();

        if (!$userProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Technician profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Technician profile retrieved successfully',
            'data' => $userProfile
        ], 200);
    }




}


