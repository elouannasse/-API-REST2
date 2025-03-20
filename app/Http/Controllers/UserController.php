<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request){
        $request->validate([
           'name'=>'required|string|max:255',
            "email"=>'required|email|max:255|unique:users,email',
            'password'=>'required|string|min:8',
            'phone_number'=>'nullable|string|max:20',
            'skills'=>'nullable|array'
        ]);
        
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'phone_number'=>$request->phone_number,
            'skills'=>$request->skills
        ]);
        
        // Génération du token JWT après création de l'utilisateur
        $token = Auth::guard('api')->login($user);
        
        return response()->json([
            'message'=>'user Registered successful',
            'user'=>$user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ], 201);
    }

    public function login(Request $request){
        $request->validate([
            "email"=>'required|email',
            'password'=>'required|string' 
        ]);
        
        // Tentative d'authentification avec JWT
        $credentials = $request->only('email', 'password');
        
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['message' => 'invalid email or password'], 401);
        }
        
        return response()->json([
            'message'=>'login successful',
            'user'=> Auth::guard('api')->user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ], 200);
    }
    
    public function logout(Request $request){
        Auth::guard('api')->logout();
        
        return response()->json([
            'message'=>'logout successful'
        ]);
    }
    
    // Méthode pour rafraîchir le token
    public function refresh() {
        return response()->json([
            'user' => Auth::guard('api')->user(),
            'access_token' => Auth::guard('api')->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
    
    // Méthode pour récupérer l'utilisateur actuel
    public function me() {
        return response()->json(Auth::guard('api')->user());
    }
}