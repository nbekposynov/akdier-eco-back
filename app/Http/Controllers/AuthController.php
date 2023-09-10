<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Moderator;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        // Check for admin credentials
        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();
            $token = $user->createToken('token-name')->plainTextToken;
    
            return response()->json(['token' => $token, 'role' => 'admin']);
        }
    
        // Check for company credentials
        if (Auth::guard('company')->attempt($credentials)) {
            $user = Auth::guard('company')->user();
            $token = $user->createToken('token-name')->plainTextToken;
    
            return response()->json(['token' => $token, 'role' => 'company']);
        }
    
        // Check for moderator credentials
        if (Auth::guard('moderator')->attempt($credentials)) {
            $user = Auth::guard('moderator')->user();
            $token = $user->createToken('token-name')->plainTextToken;
    
            return response()->json(['token' => $token, 'role' => 'moderator']);
        }
        
            return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:admin',
            'password' => 'required|string|min:6',
        ]);

        $admin = Admin::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json(['message' => 'Admin registered successfully'], 201);
    }

    public function registerModerator(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:moderator,email',
            'password' => 'required|string|min:6',
        ]);

        $moderator = Moderator::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json(['message' => 'Moderator registered successfully'], 201);
    }

    public function registerCompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:company',
            'password' => 'required|string|min:6',
            'bin_company' => 'required|string|min:12',
            'description' => 'required|string|min:12'
        ]);
    
        $company = Company::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'bin_company' => $request->input('bin_company'),
            'description' => $request->input('description'),
            'moderator_id' => $request->user()->id, // Используем id текущего пользователя
        ]);
    
        return response()->json(['message' => 'Company registered successfully'], 201);
    }
}
