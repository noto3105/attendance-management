<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password)
        ]);
        Auth::login($user);
        $user->sendEmailVerificationNotification(); 
        return redirect()->route('verification.notice');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if(Auth::attempt($credentials)){
            return redirect('/attendance');
        }
        return redirect('/login')->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    public function showAdminLogin()
    {
        return view('auth.admin_login');
    }

    public function AdminLogin(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if((int)$user->role === 2) {
                return redirect('/admin/attendance_list');
            }
            Auth::logout();
            return redirect('/admin_login');
        }
        
        return redirect('/admin_login')->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
