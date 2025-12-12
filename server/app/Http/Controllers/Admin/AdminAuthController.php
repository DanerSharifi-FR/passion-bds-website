<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LoginCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function requestCode(Request $request, LoginCodeService $service)
    {
        $request->validate([
            'email' => ['required', 'string', 'max:255'],
        ]);

        $service->requestAdminCode(
            email: $request->string('email')->toString(),
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json(['ok' => true], 200);
    }

    public function verifyCode(Request $request, LoginCodeService $service)
    {
        $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'code'  => ['required', 'string', 'max:10'],
        ]);

        $user = $service->verifyAdminCode(
            email: $request->string('email')->toString(),
            code: $request->string('code')->toString(),
            ip: $request->ip(),
        );

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json(['ok' => true], 200);
    }
}
