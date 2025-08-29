<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerificationController extends Controller
{
    public function notice()
    {
        return view('auth.verify_notice');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect('/attendance')->with('status', 'verified');
    }

    public function send(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    }
}
