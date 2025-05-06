<?php


// app/Http/Controllers/UserLoginController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.user-login');  // Custom login form
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials)) {
            return redirect()->intended('/user/dashboard');  // Redirect after login
        }

        return redirect()->back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return redirect()->route('user.login');  // Redirect to login after logout
    }
}

