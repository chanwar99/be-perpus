<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Profile;

class ProfileController extends Controller
{
    public function updateOrCreateProfile(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'bio' => 'required|string',
            'age' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->user();

        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'bio' => $request->bio,
                'age' => $request->age,
            ]
        );

        return response()->json([
            'message' => 'Profile berhasil diubah',
            'data' => $profile
        ]);
    }
}
