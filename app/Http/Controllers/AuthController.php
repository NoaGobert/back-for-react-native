<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        User::insertGetId([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = Auth::user();




        return response()->json([
            "username" => $user->username,
            "token" => $user->createToken('token')->plainTextToken
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }


        $user->tokens()->delete();

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            "username" => $user->username,
            "token" => $token
        ]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            "message" => "logout"
        ]);
    }

    public function checkAuth()
    {
        if (!auth('sanctum')->check()) {
            return response()->json([
                "authenticated" => false,
            ]);
        }

        return response()->json([
            "authenticated" => auth('sanctum')->check(),
            "username" => auth('sanctum')->user()->username,
        ]);
    }

    public function authenticatedUser()
    {
        return response()->json([
            "user" => auth('sanctum')->user(),
        ]);
    }
}