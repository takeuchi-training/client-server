<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login() {
        return view('auth.login');
    }
    
    public function authenticate(LoginRequest $request) {
        $credentials = $request->validated();

        if (auth()->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.'
        ]);

    }

    public function register() {
        return view('auth.register');
    }

    public function store(RegisterRequest $request) {
        $userInput = $request->validated();

        User::create([
            'name' => $userInput['name'],
            'email' => $userInput['email'],
            'password' => bcrypt($userInput['password']),
        ]);

        return redirect()->route('login');
    }

    public function logout(Request $request) {
        auth()->logout();
 
        $request->session()->invalidate();
     
        $request->session()->regenerateToken();
     
        return redirect('/');
    }
}
