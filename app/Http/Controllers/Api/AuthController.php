<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Validator;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = Role::where('name', 'user')->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
        ]);

        $token = JWTAuth::fromUser($user);
        $user->load(['role', 'profile']);

        return response()->json([
            'message' => 'Register Berhasil',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Login Gagal, Email/Password tidak valid'], 401);
        }

        $user = auth()->user();
        $user->load(['role', 'profile']);

        return response()->json([
            'message' => 'Login Berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function me()
    {
        $user = auth()->user();
        $user->load(['role', 'profile']);
        return response()->json([
            'message' => 'Berhasil get user',
            'user' => $user
        ]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Logout Berhasil']);
    }

}
