<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('token-name')->plainTextToken;

            return response()->json([
                'token' => $token,
                'role' => $user->role,
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,moderator,company,user', // Указываем допустимые роли
        ]);

        $userData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
        ];

        // Дополнительные данные для компании
        if ($request->input('role') === 'company') {
            $userData['bin_company'] = $request->input('bin_company');
            $userData['description'] = $request->input('description');
            $userData['moderator_id'] = $request->input('moderator_id');
        }

        $user = User::create($userData);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }
}
